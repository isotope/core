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
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Session;
use Contao\System;
use Haste\Util\Format;
use Isotope\Frontend;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Document;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionLog;
use Isotope\Module\OrderDetails;


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
<table cellpadding="0" cellspacing="0" class="tl_show">
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

        $arrIds     = array(0);
        $arrConfigs = $this->User->iso_configs;

        if (\is_array($arrConfigs) && !empty($arrConfigs)) {
            $objOrders = \Database::getInstance()->query(
                'SELECT id FROM tl_iso_product_collection WHERE config_id IN (' . implode(',', $arrConfigs) . ')'
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
     * Generate the "show" action
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function showAction(DataContainer $dc)
    {
        if (($order = Order::findByPk($dc->id)) === null) {
            Backend::redirect('contao/main.php?act=error');
        }

        $logTable = ProductCollectionLog::getTable();

        // Load the logs data container
        System::loadLanguageFile($logTable);
        Controller::loadDataContainer($logTable);

        // Purge obsolete records
        Database::getInstance()->prepare("DELETE FROM $logTable WHERE tstamp=? AND pid=?")->execute(0, $dc->id);

        // Fix the back button in log create view
        $session = Session::getInstance()->get('referer');
        $session[\Input::get('ref')]['current'] = Environment::get('requestUri');
        $session[\Input::get('ref')][$logTable] = Environment::get('requestUri');
        Session::getInstance()->set('referer', $session);

        // Revise current record
        if (($logModels = ProductCollectionLog::findBy('pid', $dc->id, ['order' => 'tstamp DESC', 'limit' => 2])) !== null && $logModels->count() === 2) {
            $currentLogModel = $logModels->first();
            $currentLogData = array_diff_key($currentLogModel->row(), array_flip(['id', 'tstamp', 'author']));
            $previousLogData = array_diff_key($logModels->last()->row(), array_flip(['id', 'tstamp', 'author']));

            if (count(array_diff($currentLogData, $previousLogData)) === 0) {
                $currentLogModel->delete();
            }
        }

        $template = new BackendTemplate('be_iso_order_show');
        $template->table = $dc->table;
        $template->fieldsets = Session::getInstance()->get('fieldset_states')[$dc->table];
        $template->id = $order->getId();
        $template->backUrl = 'contao/main.php?do=iso_orders&ref=' . \Input::get('ref');
        $template->uniqid = $order->getUniqueId();
        $template->orderDetails = $this->generateOrderDetails($dc);
        $template->emailDetails = $this->generateEmailData($dc);
        $template->billingAddressDetails = $this->generateBillingAddressData($dc);
        $template->shippingAddressDetails = $this->generateShippingAddressData($dc);

        if (BackendUser::getInstance()->canEditFieldsOf('tl_iso_product_collection_log')) {
            $template->createLogUrl = 'contao/main.php?do=iso_orders&table=' . $logTable . '&act=create&mode=2&pid=' . $dc->id . '&rt=' . REQUEST_TOKEN . '&ref=' . \Input::get('ref');
        }

        $logs = [];

        // Generate log entries
        if (($logModels = ProductCollectionLog::findBy('pid', $dc->id, ['order' => 'tstamp DESC'])) !== null) {
            $logFields = [];

            foreach ($GLOBALS['TL_DCA'][$logTable]['fields'] as $name => $config) {
                if (isset($config['eval']['showInOrderView']) && $config['eval']['showInOrderView']) {
                    $logFields[] = $name;
                }
            }

            $previousLogModel = null;

            /** @var ProductCollectionLog $logModel */
            foreach ($logModels as $logModel) {
                $log = [];

                foreach ($logFields as $logField) {
                    // Skip the empty values for the first log
                    if (count($logs) === 0 && !$logModel->$logField) {
                        continue;
                    }

                    // Skip the notification fields if no notification was sent
                    if (in_array($logField, ['notification', 'notification_shipping_tracking', 'notification_customer_notes'], true) && !$logModel->sendNotification) {
                        continue;
                    }

                    // Skip the values that did not change since last log
                    if ($previousLogModel !== null && $previousLogModel->$logField === $logModel->$logField && !in_array($logField, ['date', 'author'], true)) {
                        continue;
                    }

                    $log[Format::dcaLabel($logTable, $logField)] = $logModel->$logField ? Format::dcaValue($logTable, $logField, $logModel->$logField) : '–';
                }

                $logs[] = $log;
                $previousLogModel = $logModel;
            }
        }

        $template->logs = $logs;

        return $template->parse();
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
}
