<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\ProductCollection;

use Codefog\HasteBundle\Formatter;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Isotope\CoreBundle\Exception\RuntimeException;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\Model\ProductCollectionLog;
use Isotope\CoreBundle\Store\Order\WorkflowManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class OrderLogListener
{
    public function __construct(
        private readonly WorkflowManager $workflowManager,
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly Formatter $formatter,
        private readonly Environment $twig,
    ) {
    }

    #[AsCallback('tl_iso_product_collection', 'fields.submit_buttons.input_field')]
    public function adjustSubmit(): string
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

        $order = ProductCollection::findById($dc->id);

        if (!$order) {
            throw new RuntimeException('Order not found');
        }

        if (isset($values['order_status'])) {
            $this->workflowManager->getOrderWorkflow()->apply($order, $values['order_status']);
            $values['order_status'] = $order->getOrderStatus();
        }

        if (isset($values['transaction_status'])) {
            $this->workflowManager->getTransactionWorkflow()->apply($order, $values['transaction_status']);
            $values['transaction_status'] = $order->getTransactionStatus();
        }

        if (isset($values['delivery_status'])) {
            $this->workflowManager->getDeliveryWorkflow()->apply($order, $values['delivery_status']);
            $values['delivery_status'] = $order->getDeliveryStatus();
        }

        $oldOrderStatus = $order->order_status;

        //if (CompatibilityHelper::isBackend()) {
        //    if ($order->pageId == 0) {
        //        unset($GLOBALS['objPage']);
        //    }
        //
        //    Frontend::loadOrderEnvironment($order);
        //}

        $sendNotification = (bool) ($values['sendNotification'] ?? null);
        $values['sendNotification'] = '';

        if (empty($values)) {
            return $values;
        }

        //if (isset($update['order_status']) && $update['order_status'] != $oldOrderStatus) {
        //    if (!$order->updateOrderStatus($update, Order::STATUS_UPDATE_SKIP_LOG)) {
        //        return;
        //    }
        //} else {
        //    foreach ($update as $k => $v) {
        //        $order->{$k} = $v;
        //    }
        //    $order->save();
        //}

        $log = new ProductCollectionLog();
        $log->pid = $order->id;
        $log->tstamp = time();
        $log->author = $this->getUserId();
        $log->setData($values);
        $log->save();

        $blnNotificationError = null;

        // Send a notification
        //if (($values['sendNotification'] ?? false) && ($values['notification'] ?? null) && ($objNotification = Notification::findByPk($values['notification'])) !== null) {
        //    $objOldStatus = OrderStatus::findByPk($oldOrderStatus);
        //    $objNewStatus = OrderStatus::findByPk($order->order_status);
        //
        //    $tokens = $order->getNotificationTokens($objNotification->id);
        //
        //    $tokens['order_status_id'] = $objNewStatus->id;
        //    $tokens['order_status'] = $objNewStatus->getName();
        //    $tokens['order_status_id_old'] = $objOldStatus->id;
        //    $tokens['order_status_old'] = $objOldStatus->getName();
        //    $tokens['order_status_tracking_numbers'] = str_replace("\n", '<br>', trim($values['notification_shipping_tracking']));
        //    $tokens['order_status_notes'] = str_replace("\n", '<br>', trim($values['notification_customer_notes']));
        //
        //    /** @var Notification $objNotification */
        //    $arrResult = $objNotification->send($tokens, $order->language);
        //
        //    if (\in_array(false, $arrResult, true)) {
        //        $blnNotificationError = true;
        //        System::getContainer()->get('monolog.logger.contao')->error('Error sending status update notification for order ID ' . $order->id, ['contao' => new ContaoContext(__METHOD__, ContaoContext::ERROR)]);
        //    } elseif (\count($arrResult) > 0) {
        //        $blnNotificationError = false;
        //    }
        //}

        //if (CompatibilityHelper::isBackend()) {
        //    Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusUpdate']);
        //
        //    if ($blnNotificationError === true) {
        //        Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationError']);
        //    } elseif ($blnNotificationError === false) {
        //        Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationSuccess']);
        //    }
        //}

        return $values;
    }

    private function setTransactionStatus(ProductCollection $collection, string $value): string
    {
        $workflow = $this->workflowManager->getTransactionWorkflow();

        if ($workflow) {
            $workflow->apply($collection, $value);
        }

        return $collection->getTransactionStatus();
    }

    private function setDeliveryStatus(ProductCollection $collection, string $value): string
    {
        $workflow = $this->workflowManager->getDeliveryWorkflow();

        if ($workflow) {
            $workflow->apply($collection, $value);
        }

        return $collection->getDeliveryStatus();
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
