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

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\SelectMenu;
use Contao\StringUtil;
use Contao\System;
use Haste\Util\Format;
use Isotope\Frontend;
use Isotope\Interfaces\IsotopeBackendInterface;
use Isotope\Interfaces\IsotopePayment;
use Isotope\Interfaces\IsotopeShipping;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Document;
use Isotope\Model\OrderStatus;
use Isotope\Model\Payment;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionLog;
use Isotope\Model\Shipping;
use Isotope\Module\OrderDetails;
use NotificationCenter\Model\Notification;


class Callback extends Backend
{

    /**
     * Generate the order label and return it as string
     *
     * @param array          $row
     * @param string         $label
     * @param array          $args
     *
     * @return array
     */
    public function getOrderLabel($row, $label, DataContainer $dc, array $args)
    {
        /** @var Order $objOrder */
        $objOrder = Order::findByPk($row['id']);

        if (null === $objOrder) {
            return $args;
        }

        // Override system to correctly format currencies etc
        Isotope::setConfig($objOrder->getRelated('config_id'));

        foreach ($GLOBALS['TL_DCA'][$dc->table]['list']['label']['fields'] as $i => $field) {
            switch ($field) {
                case 'billing_address_id':
                    if (null !== ($objAddress = $objOrder->getBillingAddress())) {
                        $arrTokens = $objAddress->getTokens(Isotope::getConfig()->getBillingFieldsConfig());
                        $args[$i] = $arrTokens['hcard_fn'];
                    }
                    break;

                case 'total':
                    $args[$i] = Isotope::formatPriceWithCurrency($row['total']);
                    break;

                case 'order_status':
                    /** @var OrderStatus $objStatus */
                    if (null !== ($objStatus = $objOrder->getRelated('order_status'))) {
                        $args[$i] = '<span style="' . $objStatus->getColorStyles() . '">' . $objOrder->getStatusLabel() . '</span>';
                    }
                    break;
            }
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

        if (null === $objOrder) {
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
        Input::setGet('uid', $objOrder->uniqid);
        $objModule = new OrderDetails($moduleModel);

        return Controller::replaceInsertTags($objModule->generate(true));
    }

    /**
     * @param DataContainer $dc
     */
    public function generateOrderShow($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null === $objOrder) {
            Controller::redirect('contao/main.php?act=error');
        }

        $strBuffer = '
<div>
<table class="tl_show">
  <tbody>';

        foreach ($GLOBALS['TL_DCA']['tl_iso_product_collection']['fields'] as $field => $config) {
            if (isset($config['eval']['doNotShow']) && $config['eval']['doNotShow']) {
                continue;
            }

            $strBuffer .= '
  <tr>
    <td class="tl_label">' . Format::dcaLabel($dc->table, $field) . ' <small>'.$field.'</small></td>
    <td>' . Format::dcaValue($dc->table, $field, $objOrder->{$field}, $dc) . '</td>
  </tr>';
        }

        $strBuffer .= '
</tbody></table>
</div>';

        return $strBuffer;
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
            throw new InternalServerErrorException('Order ID '.$dc->id.' not found');
        }

        $arrEmail = StringUtil::deserialize($objOrder->email_data, true);

        if (empty($arrEmail) || !\is_array($arrEmail)) {
            return '<div class="tl_info">' . $GLOBALS['TL_LANG']['tl_iso_product_collection']['noEmailData'] . '</div>';
        }

        $strBuffer = '
<div>
<table class="tl_show">
  <tbody>';

        foreach ($arrEmail as $k => $v) {
            if (\is_array($v)) {
                $strValue = implode(', ', $v);
            } else {
                $strValue = ((strip_tags($v) == $v) ? nl2br($v) : $v);
            }

            $strBuffer .= '
  <tr>
    <td class="tl_label">' . ($GLOBALS['TL_LANG']['tl_iso_product_collection']['emailData'][$k] ?? $k) . ': <small>'.$k.'</small></td>
    <td>' . $strValue . '</td>
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

        System::loadLanguageFile(Address::getTable());
        Controller::loadDataContainer(Address::getTable());

        $strBuffer = '
<div>
<table class="tl_show">
  <tbody>';

        foreach ($GLOBALS['TL_DCA'][Address::getTable()]['fields'] as $field => $config) {
            if (!isset($objAddress->{$field})) {
                continue;
            }

            $strBuffer .= '
  <tr>
    <td class="tl_label">' . Format::dcaLabel(Address::getTable(), $field) . ' <small>'.$field.'</small></td>
    <td>' . Format::dcaValue(Address::getTable(), $field, $objAddress->{$field}) . '</td>
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
        if ('delete' === Input::get('act') || 'deleteAll' === Input::get('act')) {
            throw new AccessDeniedException('Only admin can delete orders!');
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
            $memberIds = [];

            foreach ($arrGroups as $id) {
                if ($id == -1) {
                    continue;
                }

                $arrLike[] = "tl_member.groups LIKE '%\"$id\"%'";
                $arrLike[] = "tl_member.groups LIKE '%i:$id;%'";
            }

            if (!empty($arrLike)) {
                $memberIds = Database::getInstance()->execute(
                    'SELECT id FROM tl_member WHERE '.implode(' OR ', $arrLike)
                )->fetchEach('id');
            }

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
            $objOrders = Database::getInstance()->query(
                'SELECT id FROM tl_iso_product_collection WHERE '.implode(' AND ', $arrWhere)
            );

            if ($objOrders->numRows) {
                $arrIds = $objOrders->fetchEach('id');
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['root'] = $arrIds;

        if (Input::get('id') != '' && !\in_array(Input::get('id'), $arrIds)) {
            throw new AccessDeniedException('Trying to access disallowed order ID ' . Input::get('id'));
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
        if (!$row['payment_id']) {
            return '';
        }

        $paymentMethod = Payment::findByPk($row['payment_id']);

        if (!$paymentMethod instanceof IsotopePayment
            || ($paymentMethod instanceof IsotopeBackendInterface && !$paymentMethod->hasBackendInterface((int) $row['id']))
            || (!$paymentMethod instanceof IsotopeBackendInterface && !\is_callable([$paymentMethod, 'backendInterface']))
        ) {
            return '';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
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
                if ($objPayment instanceof IsotopeBackendInterface && $objPayment->hasBackendInterface((int) $dc->id)) {
                    return $objPayment->renderBackendInterface((int) $dc->id);
                }

                if (\is_callable([$objPayment, 'backendInterface'])) {
                    return $objPayment->backendInterface($dc->id);
                }
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
        if (!$row['shipping_id']) {
            return '';
        }

        $shippingMethod = Shipping::findByPk($row['shipping_id']);

        if (!$shippingMethod instanceof IsotopeShipping
            || ($shippingMethod instanceof IsotopeBackendInterface && !$shippingMethod->hasBackendInterface((int) $row['id']))
            || (!$shippingMethod instanceof IsotopeBackendInterface && !\is_callable([$shippingMethod, 'backendInterface']))
        ) {
            return '';
        }

        return '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
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
                if ($objShipping instanceof IsotopeBackendInterface && $objShipping->hasBackendInterface((int) $dc->id)) {
                    return $objShipping->renderBackendInterface((int) $dc->id);
                }

                if (\is_callable([$objShipping, 'backendInterface'])) {
                    return $objShipping->backendInterface($dc->id);
                }
            }
        }

        return '<p class="tl_gerror">' . $GLOBALS['TL_LANG']['MSC']['backendShippingNotFound'] . '</p>';
    }

    /**
     * Return the print button if there is at least one document
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
    public function printButton($row, $href, $label, $title, $icon, $attributes)
    {
        static $hasDocuments = null;

        if (null === $hasDocuments) {
            $hasDocuments = Database::getInstance()->execute('SELECT COUNT(*) AS count FROM tl_iso_document')->count > 0;
        }

        return $hasDocuments ? '<a href="' . Backend::addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . StringUtil::specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : '';
    }

    /**
     * Pass an order to the document
     *
     * @throws \Exception
     * @return string
     */
    public function printDocument(DataContainer $dc)
    {
        $strRedirectUrl = str_replace('&key=print_document', '', Environment::get('request'));

        if ('tl_iso_print_document' === Input::post('FORM_SUBMIT')) {
            if (($objOrder = Order::findByPk($dc->id)) === null) {
                Message::addError('Could not find order id.');
                Controller::redirect($strRedirectUrl);
            }

            Frontend::loadOrderEnvironment($objOrder);

            /** @var \Isotope\Interfaces\IsotopeDocument $objDocument */
            if (($objDocument = Document::findByPk(Input::post('document'))) === null) {
                Message::addError('Could not find document id.');
                Controller::redirect($strRedirectUrl);
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

        $arrSelect = SelectMenu::getAttributesFromDca($arrSelect, $arrSelect['name']);

        if (\count($arrSelect['options'] ?? []) > 1) {
            array_unshift($arrSelect['options'], ['value' => '', 'label' => '-']);
        }

        $objSelect = new SelectMenu($arrSelect);

        $strMessages = Message::generate();
        Message::reset();

        // Return form
        return '
<div id="tl_buttons">
<a href="' . ampersand($strRedirectUrl) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['tl_iso_product_collection']['print_document'][1], $dc->id) . '</h2>' . $strMessages . '

<form id="tl_iso_product_import" class="tl_form" method="post">
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
<input type="submit" name="print" id="print" class="tl_submit" alt="" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product_collection']['print']) . '">
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
<style>
.tl_edit_form > .tl_formbody_submit { display: none }
#pal_status_legend { margin-top:0; padding-bottom:0; border-bottom:0 }
#pal_status_legend:not(.collapsed) {
    border-bottom: 1px solid #d0d0d2
}

#pal_status_legend:not(.collapsed) .tl_formbody_submit {
    padding: 0 1px;
    margin-left: -1px;
    margin-right: -1px;
    border-bottom: 50px solid #eaeaec;
}

#pal_status_legend:not(.collapsed) .tl_submit_container {
    border-bottom: 1px solid #d0d0d2
}
</style>
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

                        if (($previousLogData[$field] ?? null) === $value) {
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
                    System::importStatic($callback[0])->{$callback[1]}($order, $logs);
                }
            }

            // Filter out the log entries that contain only tstamp and author
            $logs = array_filter($logs, function (array $entry) {
                return array_keys($entry) !== ['tstamp', 'author'];
            });
        }

        $template = new BackendTemplate('be_iso_order_log');
        $template->logs = array_values($logs);

        return $template->parse();
    }

    public function prepareOrderLog(DataContainer $dc): void
    {
        // Do not handle order log when toggling the notification subpalette
        if ('toggleSubpalette' === Input::post('action')) {
            return;
        }

        $GLOBALS['ISO_ORDER_LOG'] = [];

        $GLOBALS['TL_DCA']['tl_iso_product_collection']['config']['onsubmit_callback'][] = function (DataContainer $dc) {
            $this->writeOrderLog($dc);
        };

        if ('edit' === Input::get('act')) {
            $GLOBALS['TL_DCA']['tl_iso_product_collection']['edit']['buttons_callback'][] = static function () { return []; };
        }

        foreach ($GLOBALS['TL_DCA']['tl_iso_product_collection']['fields'] as $k => &$v) {
            $v['eval']['doNotSaveEmpty'] = true;
            $v['save_callback'][] = static function ($value, DataContainer $dc) use ($v) {
                $GLOBALS['ISO_ORDER_LOG'][$dc->field] = $value;

                return null;
            };
        }
    }

    /**
     * On data container submit callback.
     */
    public function writeOrderLog($dc): void
    {
        if (empty($GLOBALS['ISO_ORDER_LOG']) || ($order = Order::findByPk($dc->id)) === null) {
            return;
        }

        $order->refresh();
        $oldOrderStatus = $order->order_status;
        $logData = $GLOBALS['ISO_ORDER_LOG'];
        $GLOBALS['ISO_ORDER_LOG'] = [];

        if ('BE' === TL_MODE) {
            if ($order->pageId == 0) {
                unset($GLOBALS['objPage']);
            }

            Frontend::loadOrderEnvironment($order);
        }

        $update = [];
        $tableFields = Database::getInstance()->getFieldNames('tl_iso_product_collection');
        foreach ($logData as $k => $v) {
            if (\in_array($k, $tableFields, true)) {
                $update[$k] = $v;
            }
        }
        $update['sendNotification'] = '';

        if (empty($logData)) {
            return;
        }

        if (isset($update['order_status']) && $update['order_status'] != $oldOrderStatus) {
            if (!$order->updateOrderStatus($update, Order::STATUS_UPDATE_SKIP_LOG)) {
                return;
            }
        } else {
            foreach ($update as $k => $v) {
                $order->{$k} = $v;
            }
            $order->save();
        }

        $log = new ProductCollectionLog();
        $log->pid = $order->id;
        $log->tstamp = time();
        $log->author = BackendUser::getInstance()->id;
        $log->setData($logData);
        $log->save();

        $blnNotificationError = null;

        // Send a notification
        if (($logData['sendNotification'] ?? false) && ($logData['notification'] ?? null) && ($objNotification = Notification::findByPk($logData['notification'])) !== null) {
            $objOldStatus = OrderStatus::findByPk($oldOrderStatus);
            $objNewStatus = OrderStatus::findByPk($order->order_status);

            $tokens = $order->getNotificationTokens($objNotification->id);

            $tokens['order_status_id'] = $objNewStatus->id;
            $tokens['order_status'] = $objNewStatus->getName();
            $tokens['order_status_id_old'] = $objOldStatus->id;
            $tokens['order_status_old'] = $objOldStatus->getName();
            $tokens['order_status_tracking_numbers'] = str_replace("\n", '<br>', trim($logData['notification_shipping_tracking']));
            $tokens['order_status_notes'] = str_replace("\n", '<br>', trim($logData['notification_customer_notes']));

            /** @var Notification $objNotification */
            $arrResult = $objNotification->send($tokens, $order->language);

            if (\in_array(false, $arrResult, true)) {
                $blnNotificationError = true;
                System::log(
                    'Error sending status update notification for order ID ' . $order->id,
                    __METHOD__,
                    TL_ERROR
                );
            } elseif (\count($arrResult) > 0) {
                $blnNotificationError = false;
            }
        }

        if ('BE' === TL_MODE) {
            Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusUpdate']);

            if ($blnNotificationError === true) {
                Message::addError($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationError']);
            } elseif ($blnNotificationError === false) {
                Message::addConfirmation($GLOBALS['TL_LANG']['tl_iso_product_collection']['orderStatusNotificationSuccess']);
            }
        }
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
