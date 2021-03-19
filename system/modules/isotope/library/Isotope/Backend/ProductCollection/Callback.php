<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductCollection;

use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Haste\Util\Format;
use Isotope\Frontend;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Document;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionLog;
use Isotope\Module\OrderDetails;
use NotificationCenter\Model\Notification;


class Callback extends \Backend
{

    /**
     * Generate the order label and return it as string
     *
     * @param array          $row
     * @param string         $label
     * @param \DataContainer $dc
     * @param array          $args
     *
     * @return string
     */
    public function getOrderLabel($row, $label, \DataContainer $dc, $args)
    {
        /** @var Order $objOrder */
        $objOrder = Order::findByPk($row['id']);

        if (null === $objOrder) {
            return $args;
        }

        // Override system to correctly format currencies etc
        Isotope::setConfig($objOrder->getRelated('config_id'));

        $objAddress = $objOrder->getBillingAddress();

        if (null !== $objAddress) {
            $arrTokens = $objAddress->getTokens(Isotope::getConfig()->getBillingFieldsConfig());
            $args[3]   = $arrTokens['hcard_fn'];
        }

        $args[4] = Isotope::formatPriceWithCurrency($row['total']);

        /** @var \Isotope\Model\OrderStatus $objStatus */
        if (($objStatus = $objOrder->getRelated('order_status')) !== null) {
            $args[5] = '<span style="' . $objStatus->getColorStyles() . '">' . $objOrder->getStatusLabel() . '</span>';
        } else {
            $args[5] = '<span>' . $objOrder->getStatusLabel() . '</span>';
        }

        return $args;
    }

    /**
     * Generate the order details view when editing an order
     *
     * @param object $dc
     *
     * @return string
     */
    public function generateOrderDetails($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if ($objOrder === null) {
            return '';
        }

        $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/css/print.css|print';

        // Try to find a order details module or create a dummy FE module model
        if (($config = $objOrder->getRelated('config_id')) === null
            || ($moduleModel = $config->getRelated('orderDetailsModule')) === null
        ) {
            $moduleModel = new \ModuleModel();
            $moduleModel->type = 'iso_orderdetails';
            $moduleModel->iso_collectionTpl = 'iso_collection_default';
        }

        // Generate a regular order details module
        \Input::setGet('uid', $objOrder->uniqid);
        $objModule = new OrderDetails($moduleModel);

        return \Controller::replaceInsertTags($objModule->generate(true));
    }

    /**
     * Generate the order details view when editing an order
     *
     * @param object $dc
     *
     * @return string
     *
     * @deprecated  we should probably remove this in 3.0 as it does no longer make sense
     */
    public function generateEmailData($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null === $objOrder) {
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrEmail = deserialize($objOrder->email_data, true);

        if (empty($arrEmail) || !\is_array($arrEmail)) {
            return '<div class="tl_info">' . $GLOBALS['TL_LANG']['tl_iso_product_collection']['noEmailData'] . '</div>';
        }

        $strBuffer = '
<div>
<table cellpadding="0" cellspacing="0" class="tl_show">
  <tbody>';

        $i = 0;

        foreach ($arrEmail as $k => $v) {
            $strClass = ++$i % 2 ? '' : ' class="tl_bg"';

            if (\is_array($v)) {
                $strValue = implode(', ', $v);
            } else {
                $strValue = ((strip_tags($v) == $v) ? nl2br($v) : $v);
            }

            $strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">' . ($GLOBALS['TL_LANG']['tl_iso_product_collection']['emailData'][$k] ?: $k) . ': </span></td>
    <td' . $strClass . '>' . $strValue . '</td>
  </tr>';
        }

        $strBuffer .= '
</tbody></table>
</div>';

        return $strBuffer;
    }

    /**
     * Generate the billing address details
     *
     * @param object $dc
     *
     * @return string
     */
    public function generateBillingAddressData($dc)
    {
        /** @var Order $objOrder */
        $objOrder = Order::findByPk($dc->id);

        return $this->generateAddressData((null === $objOrder) ? null : $objOrder->getBillingAddress());
    }

    /**
     * Generate the shipping address details
     *
     * @param object $dc
     *
     * @return string
     */
    public function generateShippingAddressData($dc)
    {
        /** @var Order $objOrder */
        $objOrder = Order::findByPk($dc->id);

        return $this->generateAddressData((null === $objOrder) ? null : $objOrder->getShippingAddress());
    }

    /**
     * Generate address details amd return it as string
     *
     * @param Address $objAddress
     *
     * @return string
     */
    protected function generateAddressData(Address $objAddress = null)
    {
        if (null === $objAddress) {
            return '<div class="tl_gerror">No address data available.</div>';
        }

        \System::loadLanguageFile(Address::getTable());
        \Controller::loadDataContainer(Address::getTable());

        $strBuffer = '
<div>
<table class="tl_show"'. (VERSION < 4 ? 'style="margin-left:0; margin-right:0"' : '') .'>
  <tbody>';

        $i = 0;

        foreach ($GLOBALS['TL_DCA'][Address::getTable()]['fields'] as $k => $v) {
            if (!isset($objAddress->$k)) {
                continue;
            }

            $v        = $objAddress->$k;
            $strClass = (++$i % 2) ? '' : ' class="tl_bg"';

            $strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">' . Format::dcaLabel(Address::getTable(), $k) . ': </span></td>
    <td' . $strClass . '>' . Format::dcaValue(Address::getTable(), $k, $v) . '</td>
  </tr>';
        }

        $strBuffer .= '
</tbody></table>
</div>';

        return $strBuffer;
    }

    /**
     * Review order page stores temporary information in this table to know it when user is redirected to a payment provider. We do not show this data in backend.
     */
    public function checkPermission()
    {
        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin) {
            return;
        }

        // Only admins can delete orders. Others should set the order_status to cancelled.
        unset($GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['operations']['delete']);
        if ('delete' === \Input::get('act') || 'deleteAll' === \Input::get('act')) {
            \System::log('Only admin can delete orders!', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrIds = [0];
        $arrWhere = [];

        $arrConfigs = $this->User->iso_configs;
        if (\is_array($arrConfigs) && !empty($arrConfigs)) {
            $arrWhere[] = 'config_id IN ('.implode(',', $arrConfigs).')';
        }

        $arrGroups = $this->User->iso_member_groups;
        if (\is_array($arrGroups) && !empty($arrGroups)) {
            $blnGuests = \in_array(-1, $arrGroups, false);
            $arrLike = [];
            foreach ($arrGroups as $id) {
                if ($id == -1) {
                    continue;
                }

                $arrLike[] = "tl_member.groups LIKE '%\"$id\"%'";
                $arrLike[] = "tl_member.groups LIKE '%i:$id;%'";
            }

            $memberIds = \Database::getInstance()->execute(
                'SELECT id FROM tl_member WHERE '.implode(' OR ', $arrLike)
            )->fetchEach('id');

            if ($blnGuests) {
                array_unshift($memberIds, 0);
            }

            if (empty($memberIds)) {
                $arrWhere[] = false;
            } else {
                $arrWhere[] = 'member IN ('.implode(',', $memberIds).')';
            }
        }

        if (!empty($arrWhere) && !\in_array(false, $arrWhere, true)) {
            $objOrders = \Database::getInstance()->query(
                'SELECT id FROM tl_iso_product_collection WHERE '.implode(' AND ', $arrWhere)
            );

            if ($objOrders->numRows) {
                $arrIds = $objOrders->fetchEach('id');
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['root'] = $arrIds;

        if (\Input::get('id') != '' && !\in_array(\Input::get('id'), $arrIds)) {
            \System::log('Trying to access disallowed order ID ' . \Input::get('id'), __METHOD__, TL_ERROR);
            \Controller::redirect(TL_SCRIPT . '?act=error');
        }
    }

    /**
     * Return the payment button if a payment method is available
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function paymentButton($row, $href, $label, $title, $icon, $attributes)
    {
        return $row['payment_id'] > 0 ? '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : '';
    }

    /**
     * Generate a payment interface and return it as HTML string
     *
     * @param object $dc
     *
     * @return string
     */
    public function paymentInterface($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null !== $objOrder) {

            /** @var \Isotope\Interfaces\IsotopePayment $objPayment */
            $objPayment = $objOrder->getRelated('payment_id');

            if (null !== $objPayment) {
                return $objPayment->backendInterface($dc->id);
            }
        }

        return '<p class="tl_gerror">' . $GLOBALS['TL_LANG']['MSC']['backendPaymentNotFound'] . '</p>';
    }

    /**
     * Return the shipping button if a shipping method is available
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function shippingButton($row, $href, $label, $title, $icon, $attributes)
    {
        return $row['shipping_id'] > 0 ? '<a href="' . \Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . \Image::getHtml($icon, $label) . '</a> ' : '';
    }

    /**
     * Generate a shipping interface and return it as HTML string
     *
     * @param object $dc
     *
     * @return  string
     */
    public function shippingInterface($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null !== $objOrder) {

            /** @var \Isotope\Interfaces\IsotopeShipping $objShipping */
            $objShipping = $objOrder->getRelated('shipping_id');

            if (null !== $objShipping) {
                return $objShipping->backendInterface($dc->id);
            }
        }

        return '<p class="tl_gerror">' . $GLOBALS['TL_LANG']['MSC']['backendShippingNotFound'] . '</p>';
    }


    /**
     * Pass an order to the document
     *
     * @param \DataContainer $dc
     *
     * @throws \Exception
     * @return string
     */
    public function printDocument(\DataContainer $dc)
    {
        $strRedirectUrl = str_replace('&key=print_document', '', \Environment::get('request'));

        if ('tl_iso_print_document' === \Input::post('FORM_SUBMIT')) {
            if (($objOrder = Order::findByPk($dc->id)) === null) {
                \Message::addError('Could not find order id.');
                \Controller::redirect($strRedirectUrl);
            }

            Frontend::loadOrderEnvironment($objOrder);

            /** @var \Isotope\Interfaces\IsotopeDocument $objDocument */
            if (($objDocument = Document::findByPk(\Input::post('document'))) === null) {
                \Message::addError('Could not find document id.');
                \Controller::redirect($strRedirectUrl);
            }

            $objDocument->outputToBrowser($objOrder);
        }

        $arrSelect = array
        (
            'name'       => 'document',
            'label'      => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['document_choice'],
            'inputType'  => 'select',
            'foreignKey' => 'tl_iso_document.name',
            'eval'       => array('mandatory' => true),
        );

        $objSelect = new \SelectMenu(\SelectMenu::getAttributesFromDca($arrSelect, $arrSelect['name']));

        $strMessages = \Message::generate();
        \Message::reset();

        // Return form
        return '
<div id="tl_buttons">
<a href="' . ampersand($strRedirectUrl) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['tl_iso_product_collection']['print_document'][1], $dc->id) . '</h2>' . $strMessages . '

<form action="' . ampersand(\Environment::get('request'), true) . '" id="tl_iso_product_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_print_document">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">

<div class="tl_tbox block">
  <div class="clr widget">
    ' . $objSelect->parse() . '
    <p class="tl_help">' . $objSelect->description . '</p>
  </div>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="print" id="print" class="tl_submit" alt="" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['tl_iso_product_collection']['print']) . '">
</div>

</div>
</form>';
    }

    /**
     * On submit buttons input field callback.
     */
    public function onSubmitButtonsInputFieldCallback()
    {
        return '<div class="tl_formbody_submit" style="margin-top:20px">
<div class="tl_submit_container">
  <button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['save'] . '</button>
  <button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['saveNclose'] . '</button>
</div>
</div>
<style>.tl_edit_form > .tl_formbody_submit { display: none } #pal_status_legend { padding-bottom: 0 }</style>
';
    }

    /**
     * Generate the "show" action
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function onLogInputFieldCallback(DataContainer $dc)
    {
        if (($order = Order::findByPk($dc->id)) === null) {
            return '';
        }

        $logTable = ProductCollectionLog::getTable();

        // Load the logs data container
        System::loadLanguageFile($logTable);
        Controller::loadDataContainer($logTable);

        // Purge obsolete records
        Database::getInstance()->prepare("DELETE FROM $logTable WHERE tstamp=? AND pid=?")->execute(0, $dc->id);

        $logs = [];

        // Generate log entries
        if (($logModels = ProductCollectionLog::findBy('pid', $dc->id, ['order' => 'tstamp'])) !== null) {
            $previousLogModel = null;

            /** @var ProductCollectionLog $logModel */
            foreach ($logModels as $logModel) {
                $log = [
                    'tstamp' => [
                        'label' => Format::dcaLabel($logTable, 'tstamp'),
                        'value' => $logModel->tstamp ? Format::dcaValue($logTable, 'tstamp', $logModel->tstamp) : '–'
                    ],
                ];

                // Add author only if it has a value (order can be updated automatically in frontend)
                if ($logModel->author) {
                    $log['author'] = [
                        'label' => Format::dcaLabel($logTable, 'author'),
                        'value' => $logModel->author ? Format::dcaValue($logTable, 'author', $logModel->author) : '–'
                    ];
                }

                $logData = $logModel->getData();
                $dependentFields = [];

                // Get the dependent fields on others
                if (isset($GLOBALS['TL_DCA'][$dc->table]['subpalettes']) && is_array($GLOBALS['TL_DCA'][$dc->table]['subpalettes'])) {
                    foreach ($GLOBALS['TL_DCA'][$dc->table]['subpalettes'] as $k => $v) {
                        foreach (StringUtil::trimsplit(',', $v) as $vv) {
                            $dependentFields[$vv] = $k;
                        }
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

                    $fieldConfig = isset($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]) ? $GLOBALS['TL_DCA'][$dc->table]['fields'][$field] : [];

                    // Skip the values that did not change since last log
                    if ($previousLogModel !== null && (!isset($fieldConfig['eval']['logAlwaysVisible']) || !$fieldConfig['eval']['logAlwaysVisible'])) {
                        $previousLogData = $previousLogModel->getData();

                        if ($previousLogData[$field] === $value) {
                            continue;
                        }
                    }

                    $log[$field] = [
                        'label' => Format::dcaLabel($dc->table, $field),
                        'value' => $value ? Format::dcaValue($dc->table, $field, $value) : '–'
                    ];

                    // Support line breaks for textareas
                    if (isset($fieldConfig['inputType']) && $fieldConfig['inputType'] === 'textarea' && $value) {
                        $log[$field]['value'] = nl2br($log[$field]['value']);
                    }
                }

                $logs[$logModel->id] = $log;
                $previousLogModel = $logModel;
            }

            // Only now reverse the order of the logs or otherwise the comparison of data with each previous log won't work properly
            $logs = array_reverse($logs);

            if (isset($GLOBALS['ISO_HOOKS']['generateOrderLog']) && \is_array($GLOBALS['ISO_HOOKS']['generateOrderLog'])) {
                foreach ($GLOBALS['ISO_HOOKS']['generateOrderLog'] as $callback) {
                    \System::importStatic($callback[0])->{$callback[1]}($order, $logs);
                }
            }

            // Filter out the log entries that contain only tstamp and author
            $logs = array_filter($logs, function (array $entry) {
                return array_keys($entry) !== ['tstamp', 'author'];
            });
        }

        $template = new BackendTemplate('be_iso_order_log');
        $template->logs = $logs;

        return $template->parse();
    }

    /**
     * Trigger order status update when changing the status in the backend
     *
     * @param   string
     * @param   DataContainer
     *
     * @return  string
     * @link    http://www.contao.org/callbacks.html#save_callback
     */
    public function updateOrderStatus($varValue, $dc)
    {
        $GLOBALS['ISO_ORDER_STATUS'] = false;

        if ($dc->activeRecord && $dc->activeRecord->order_status != $varValue) {
            $GLOBALS['ISO_ORDER_STATUS'] = array($dc->activeRecord->order_status => $varValue);
        }

        return $varValue;
    }

    /**
     * On data container submit callback.
     */
    public function onSubmitCallback($dc)
    {
        if (($order = Order::findByPk($dc->id)) === null) {
            return;
        }

        $order->refresh();

        if ('BE' === TL_MODE) {
            if ($order->pageId == 0) {
                unset($GLOBALS['objPage']);
            }

            Frontend::loadOrderEnvironment($order);
        }

        // Status update has been cancelled, do not update
        if (false !== $GLOBALS['ISO_ORDER_STATUS']) {
            $updates = [
                'order_status' => $dc->activeRecord->order_status,
                'date_paid' => $dc->activeRecord->date_paid,
                'date_shipped' => $dc->activeRecord->date_shipped,
                'notes' => $dc->activeRecord->notes,
            ];

            foreach ($GLOBALS['ISO_ORDER_STATUS'] as $from => $to) {
                $order->order_status = $from;
                if (!$order->updateOrderStatus($updates, Order::STATUS_UPDATE_SKIP_LOG)) {
                    // Will save the old status set in the line above
                    $order->save();
                }
            }
        }

        $logData = [];

        // Collect the log data
        foreach ($GLOBALS['TL_DCA'][$dc->table]['fields'] as $fieldName => $fieldConfig) {
            if (isset($fieldConfig['inputType'])) {
                if (isset($fieldConfig['eval']['doNotSaveEmpty']) && $fieldConfig['eval']['doNotSaveEmpty'] && !$dc->activeRecord->$fieldName) {
                    $logData[$fieldName] = \Input::post($fieldName);
                } else {
                    $logData[$fieldName] = $dc->activeRecord->$fieldName;
                }
            }
        }

        $log = new ProductCollectionLog();
        $log->pid = $order->id;
        $log->tstamp = time();
        $log->author = BackendUser::getInstance()->id;
        $log->setData($logData);
        $log->save();

        $blnNotificationError = null;

        // Send a notification
        if ($dc->activeRecord->sendNotification && \Input::post('notification') && ($objNotification = Notification::findByPk(\Input::post('notification'))) !== null) {
            $objOldStatus = OrderStatus::findByPk($order->order_status);
            $objNewStatus = OrderStatus::findByPk($dc->activeRecord->order_status);

            $tokens = $order->getNotificationTokens($objNotification->id);

            $tokens['order_status_id'] = $objNewStatus->id;
            $tokens['order_status'] = $objNewStatus->getName();
            $tokens['order_status_id_old'] = $objOldStatus->id;
            $tokens['order_status_old'] = $objOldStatus->getName();
            $tokens['order_status_tracking_numbers'] = str_replace("\n", '<br>', trim(\Input::post('notification_shipping_tracking')));
            $tokens['order_status_notes'] = str_replace("\n", '<br>', trim(\Input::post('notification_customer_notes')));

            /** @var Notification $objNotification */
            $arrResult = $objNotification->send($tokens, $order->language);

            if (\in_array(false, $arrResult, true)) {
                $blnNotificationError = true;
                \System::log(
                    'Error sending status update notification for order ID ' . $order->id,
                    __METHOD__,
                    TL_ERROR
                );
            } elseif (\count($arrResult) > 0) {
                $blnNotificationError = false;
            }

            // Reset the sendNotification field
            \Database::getInstance()->query("UPDATE {$dc->table} SET sendNotification=''");
        }

        if ('BE' === TL_MODE) {
            \Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusUpdate']);

            if ($blnNotificationError === true) {
                \Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationError']);
            } elseif ($blnNotificationError === false) {
                \Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationSuccess']);
            }
        }

        // !HOOK: add additional functionality when saving collection
        if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && \is_array($GLOBALS['ISO_HOOKS']['saveCollection'])) {
            foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback) {
                \System::importStatic($callback[0])->{$callback[1]}($order, $log);
            }
        }
    }

    /**
     * Execute the saveCollection hook when a collection is saved
     * @param   object
     * @return  void
     * @deprecated
     */
    public function executeSaveHook($dc)
    {
        trigger_deprecation('isotope/core', '2.7', 'Using this method will no longer work in Isotope 3.0. Use the onSubmitCallback method instead.', E_USER_DEPRECATED);
        $this->onSubmitCallback($dc);
    }

    /**
     * On notification options callback
     *
     * @return array
     */
    public function onNotificationOptionsCallback()
    {
        $options = [];

        if (($notifications = Notification::findBy('type', 'iso_order_status_change', ['order' => 'title'])) !== null) {
            $options = $notifications->fetchEach('title');
        }

        return $options;
    }
}
