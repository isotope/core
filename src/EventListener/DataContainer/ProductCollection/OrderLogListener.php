<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Codefog\HasteBundle\Formatter;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Model\OrderStatus;
use Isotope\CoreBundle\Model\ProductCollectionLog;
use Isotope\CoreBundle\Store\Order\OrderNotifier;
use Isotope\CoreBundle\Store\Order\StatusType;
use Isotope\CoreBundle\Store\OrderManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OrderLogListener
{
    public function __construct(
        private readonly OrderManager $orderManager,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly Formatter $formatter,
        private readonly Environment $twig,
        private readonly OrderNotifier $orderNotifier,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $contaoLogger,
    ) {
    }

    #[AsCallback('tl_iso_product_collection', 'fields.submit_buttons.input_field')]
    public function submitButtons(): string
    {
        return $this->twig->render('@Contao/backend/isotope/order-submit.html.twig');
    }

    #[AsCallback('tl_iso_product_collection', 'fields.order_log.input_field')]
    public function viewLog(DataContainer $dc): string
    {
        $logs = [];
        $logTable = ProductCollectionLog::getTable();

        System::loadLanguageFile($logTable);
        Controller::loadDataContainer($logTable);

        if (null !== ($logModels = ProductCollectionLog::findBy('pid', $dc->id, ['order' => 'tstamp']))) {
            $previousLogModel = null;

            foreach ($logModels as $logModel) {
                $log = [
                    'tstamp' => [
                        'label' => $this->formatter->dcaLabel($logTable, 'tstamp'),
                        'value' => $logModel->tstamp ? $this->formatter->dcaValue($logTable, 'tstamp', $logModel->tstamp) : '–'
                    ],
                ];

                // Add author only if it has a value (order can be updated automatically in frontend)
                if ($logModel->author) {
                    $log['author'] = [
                        'label' => $this->formatter->dcaLabel($logTable, 'author'),
                        'value' => $logModel->author ? $this->formatter->dcaValue($logTable, 'author', $logModel->author) : '–'
                    ];
                }

                $logData = $logModel->getData();
                $dependentFields = [];

                // Get the dependent fields on others
                foreach (($GLOBALS['TL_DCA'][$dc->table]['subpalettes'] ?? []) as $k => $v) {
                    foreach (StringUtil::trimsplit(',', $v) as $vv) {
                        $dependentFields[$vv] = $k;
                    }
                }

                foreach ($logData as $field => $value) {
                    // Skip the empty values for the first log
                    if (count($logs) === 0 && !$value) {
                        continue;
                    }

                    // Skip the dependent fields if their parent is not set
                    if (array_key_exists($field, $dependentFields) && (!isset($logData[$dependentFields[$field]]) || !$logData[$dependentFields[$field]])) {
                        continue;
                    }

                    // Skip if the field is a dependency but does not have a value
                    if (in_array($field, $dependentFields, true) && !$value) {
                        continue;
                    }

                    $fieldConfig = $GLOBALS['TL_DCA'][$dc->table]['fields'][$field] ?? [];

                    // Skip the values that did not change since last log
                    if (null !== $previousLogModel && true === ($fieldConfig['eval']['logAlwaysVisible'] ?? true)) {
                        $previousLogData = $previousLogModel->getData();

                        if (($previousLogData[$field] ?? null) === $value) {
                            continue;
                        }
                    }

                    $log[$field] = [
                        'label' => $this->formatter->dcaLabel($dc->table, $field),
                        'value' => $value ? $this->formatter->dcaValue($dc->table, $field, $value) : '–'
                    ];

                    // Support line breaks for textareas
                    if ('textarea' === ($fieldConfig['inputType'] ?? null) && $value) {
                        $log[$field]['value'] = nl2br($log[$field]['value']);
                    }
                }

                $logs[$logModel->id] = $log;
                $previousLogModel = $logModel;
            }

            // Only now reverse the order of the logs or otherwise the comparison of data with each previous log won't work properly
            $logs = array_reverse($logs);

            // Filter out the log entries that contain only tstamp and author
            $logs = array_filter($logs, function (array $entry) {
                return array_keys($entry) !== ['tstamp', 'author'];
            });
        }

        return $this->twig->render('@Contao/backend/isotope/order-log.html.twig', ['logs' => $logs]);
    }

    #[AsCallback('tl_iso_product_collection', 'config.onbeforesubmit')]
    public function storeLog(array $values, DataContainer $dc): array
    {
        if ('toggleSubpalette' === $this->requestStack->getCurrentRequest()?->request->get('action')) {
            return $values;
        }

        $current = $dc->getCurrentRecord();
        $updated = $values;
        $values['sendNotification'] ??= $current['sendNotification'] ?? null;
        unset($updated['sendNotification'], $updated['notification'], $updated['notification_shipping_tracking'], $updated['notification_customer_notes']);

        $updated = $this->orderManager->updateOrder((int) $dc->id, $updated, false);

        $log = new ProductCollectionLog();
        $log->pid = $dc->id;
        $log->tstamp = time();
        $log->author = $this->getUserId();
        $log->setData($values);
        $log->save();

        $blnNotificationError = null;

        // Send a notification
        if ($values['sendNotification'] && ($values['notification'] ?? null)) {
            $tokens = [];

            foreach (StatusType::cases() as $type) {
                if ($status = OrderStatus::findOneByAlias((string) $current[$type->value.'_status'], $type)) {
                    foreach ($status->row() as $k => $v) {
                        $tokens['previous_'.$type->value.'_status_' . $k] = $this->formatter->dcaValue(OrderStatus::getTable(), $k, $v);
                    }

                    $tokens['previous_'.$type->value.'_status'] = $this->translator->trans($status->label ?: $status->name, [], 'isotope');
                }
            }

            $tokens['tracking_numbers'] = str_replace("\n", '<br>', trim($values['notification_shipping_tracking'] ?? ''));
            $tokens['customer_notes'] = str_replace("\n", '<br>', trim($values['notification_customer_notes'] ?? ''));

            $receipt = $this->orderNotifier->sendNotification((int) $dc->id, (int) $values['notification'], $tokens);

            if (!$receipt->wereAllDelivered()) {
                $blnNotificationError = true;
                $this->contaoLogger->error('Error sending status update notification for order ID ' . $dc->id);
            } elseif ($receipt->count() > 0) {
                $blnNotificationError = false;
            }
        }

        Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusUpdate']);

        if (true === $blnNotificationError) {
            Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationError']);
        } elseif (false === $blnNotificationError) {
            Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationSuccess']);
        }

        // Reset the sendNotification checkbox
        $updated['sendNotification'] = false;

        return $updated;
    }

    private function getUserId(): int
    {
        $user = $this->security->getUser();

        if (!$user instanceof BackendUser) {
            return 0;
        }

        return $user->id;
    }
}
