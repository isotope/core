<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Backend\ProductCollection;

use \Haste\Haste;
use Haste\Util\Debug;
use Haste\Util\Format;
use Isotope\Frontend;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Document;
use Isotope\Model\ProductCollection\Order;
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
            $args[2]   = $arrTokens['hcard_fn'];
        }

        $args[3] = Isotope::formatPriceWithCurrency($row['grandTotal']);

        /** @var \Isotope\Model\OrderStatus $objStatus */
        if (($objStatus = $objOrder->getRelated('order_status')) !== null) {
            $args[4] = '<span style="' . $objStatus->getColorStyles() . '">' . $objOrder->getStatusLabel() . '</span>';
        } else {
            $args[4] = '<span>' . $objOrder->getStatusLabel() . '</span>';
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

        $GLOBALS['TL_CSS'][] = Debug::uncompressedFile('system/modules/isotope/assets/css/print.min.css|print');

        // Try to find a order details module or create a dummy FE module model
        if (($objModuleModel = \ModuleModel::findOneBy('type', 'iso_orderdetails')) === null) {
            $objModuleModel = new \ModuleModel();
            $objModuleModel->type = 'iso_orderdetails';
            $objModuleModel->iso_collectionTpl = 'iso_collection_default';
        }

        // Generate a regular order details module
        \Input::setGet('uid', $objOrder->uniqid);
        $objModule = new OrderDetails($objModuleModel);

        return Haste::getInstance()->call('replaceInsertTags', $objModule->generate(true));
    }

    /**
     * Generate the order details view when editing an order
     *
     * @param object $dc
     *
     * @return string
     * @deprecated  we should probably remove this in 3.0 as it does no longer make sense
     */
    public function generateEmailData($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null === $objOrder) {
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrEmail = deserialize($objOrder->email_data, true);

        if (empty($arrEmail) || !is_array($arrEmail)) {
            return '<div class="tl_info">' . $GLOBALS['TL_LANG']['tl_iso_product_collection']['noEmailData'] . '</div>';
        }

        $strBuffer = '
<div>
<table cellpadding="0" cellspacing="0" class="tl_show" summary="Table lists all details of an entry" style="width:650px">
  <tbody>';

        $i = 0;

        foreach ($arrEmail as $k => $v) {
            $strClass = ++$i % 2 ? '' : ' class="tl_bg"';

            if (is_array($v)) {
                $strValue = implode(', ', $v);
            } else {
                $strValue = ((strip_tags($v) == $v) ? nl2br($v) : $v);
            }

            $strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">' . $k . ': </span></td>
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

        \System::loadLanguageFile($objAddress->getTable());
        $this->loadDataContainer($objAddress->getTable());

        $strBuffer = '
<div>
<table cellpadding="0" cellspacing="0" class="tl_show" summary="Table lists all details of an entry" style="width:650px">
  <tbody>';

        $i = 0;

        foreach ($GLOBALS['TL_DCA'][$objAddress->getTable()]['fields'] as $k => $v) {
            if (!isset($objAddress->$k)) {
                continue;
            }

            $v        = $objAddress->$k;
            $strClass = (++$i % 2) ? '' : ' class="tl_bg"';

            $strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">' . Format::dcaLabel($objAddress->getTable(), $k) . ': </span></td>
    <td' . $strClass . '>' . Format::dcaValue($objAddress->getTable(), $k, $v) . '</td>
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
        if (\Input::get('act') == 'delete' || \Input::get('act') == 'deleteAll') {
            \System::log('Only admin can delete orders!', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrIds     = array(0);
        $arrConfigs = $this->User->iso_configs;

        if (is_array($arrConfigs) && !empty($arrConfigs)) {
            $objOrders = \Database::getInstance()->query("SELECT id FROM tl_iso_product_collection WHERE config_id IN (" . implode(',', $arrConfigs) . ")");

            if ($objOrders->numRows) {
                $arrIds = $objOrders->fetchEach('id');
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['root'] = $arrIds;

        if (\Input::get('id') != '' && !in_array(\Input::get('id'), $arrIds)) {
            \System::log('Trying to access disallowed order ID ' . \Input::get('id'), __METHOD__, TL_ERROR);
            \Controller::redirect(\Environment::get('script') . '?act=error');
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
        return $row['payment_id'] > 0 ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ' : '';
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
        return $row['shipping_id'] > 0 ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ' : '';
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

        if (\Input::post('FORM_SUBMIT') == 'tl_iso_print_document') {
            if (($objOrder = Order::findByPk($dc->id)) === null) {
                \Message::addError('Could not find order id.');
                \Controller::redirect($strRedirectUrl);
            }

            if (($objConfig = $objOrder->getRelated('config_id')) === null) {
                \Message::addError('Could not find config id.');
                \Controller::redirect($strRedirectUrl);
            }

            // Set current config
            Isotope::setConfig($objConfig);

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
            'eval'       => array('mandatory' => true)
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
  ' . $objSelect->parse() . '
  <p class="tl_help">' . $objSelect->description . '</p>
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
        if ($dc->activeRecord && $dc->activeRecord->order_status != $varValue) {

            /** @var Order $objOrder */
            if (($objOrder = Order::findByPk($dc->id)) !== null) {

                if (TL_MODE == 'BE') {
                    if ($objOrder->pageId == 0) {
                        unset($GLOBALS['objPage']);
                    }

                    Frontend::loadOrderEnvironment($objOrder);
                }

                // Status update has been cancelled, do not update
                if (!$objOrder->updateOrderStatus($varValue)) {
                    return $dc->activeRecord->order_status;
                }
            }
        }

        return $varValue;
    }

    /**
     * Execute the saveCollection hook when a collection is saved
     * @param   object
     * @return  void
     */
    public function executeSaveHook($dc)
    {
        if (($objOrder = Order::findByPk($dc->id)) !== null) {
            // !HOOK: add additional functionality when saving collection
            if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && is_array($GLOBALS['ISO_HOOKS']['saveCollection'])) {
                foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback) {
                    $objCallback = \System::importStatic($callback[0]);
                    $objCallback->$callback[1]($objOrder);
                }
            }
        }
    }
}
