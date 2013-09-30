<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */

namespace Isotope;

use Isotope\Model\Address;
use Isotope\Model\Document;
use Isotope\Model\Config;
use Isotope\Model\ProductCollection\Order;


/**
 * Class tl_iso_product_collection
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_product_collection extends \Backend
{

    /**
     * Generate the order label and return it as string
     * @param array
     * @param string
     * @return string
     */
    public function getOrderLabel($row, $label, \DataContainer $dc, $args)
    {
        $objOrder = Order::findByPk($row['id']);

        if (null === $objOrder) {
            return $args;
        }

        // Override system to correctly format currencies etc
        Isotope::setConfig($objOrder->getRelated('config_id'));

        $objAddress = $objOrder->getBillingAddress();

        if (null !== $objAddress) {
            $arrTokens = $objAddress->getTokens(Isotope::getConfig()->getBillingFieldsConfig());
            $args[2] = $arrTokens['hcard_fn'];
        }

        $args[3] = Isotope::formatPriceWithCurrency($row['grandTotal']);
        $args[4] = $objOrder->getStatusLabel();

        return $args;
    }


    /**
     * Generate the order details view when editing an order
     * @param object
     * @param string
     * @return string
     */
    public function generateOrderDetails($dc, $xlabel)
    {
        $objOrder = \Database::getInstance()->execute("SELECT * FROM tl_iso_product_collection WHERE id=".$dc->id);

        if (!$objOrder->numRows)
        {
            \Controller::redirect('contao/main.php?act=error');
        }

        $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/print' . (ISO_DEBUG ? '' : '.min') . '.css|print';

        // Generate a regular order details module
        \Input::setGet('uid', $objOrder->uniqid);
        $objModule = new \Isotope\Module\OrderDetails(\Database::getInstance()->execute("SELECT * FROM tl_module WHERE type='iso_orderdetails'"));

        return $objModule->generate(true);
    }


    /**
     * Generate the order details view when editing an order
     * @param object
     * @param string
     * @return string
     */
    public function generateEmailData($dc, $xlabel)
    {
        $objOrder = \Database::getInstance()->execute("SELECT * FROM tl_iso_product_collection WHERE id=" . $dc->id);

        if (!$objOrder->numRows)
        {
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrSettings = deserialize($objOrder->settings, true);

        if (!is_array($arrSettings['email_data']))
        {
            return '<div class="tl_gerror">No email data available.</div>';
        }

        $strBuffer = '
<div>
<table cellpadding="0" cellspacing="0" class="tl_show" summary="Table lists all details of an entry" style="width:650px">
  <tbody>';

        $i=0;

        foreach ($arrSettings['email_data'] as $k => $v)
        {
            $strClass = ++$i%2 ? '' : ' class="tl_bg"';

            if (is_array($v))
            {
                $strValue = implode(', ', $v);
            }
            else
            {
                $strValue = ((strip_tags($v) == $v) ? nl2br($v) : $v);
            }

            $strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">'.$k.': </span></td>
    <td' . $strClass . '>'.$strValue.'</td>
  </tr>';
        }

        $strBuffer .= '
</tbody></table>
</div>';

        return $strBuffer;
    }


    /**
     * Generate the billing address details
     * @param object
     * @param string
     * @return string
     */
    public function generateBillingAddressData($dc, $xlabel)
    {
        $objOrder = Order::findByPk($dc->id);

        return $this->generateAddressData((null === $objOrder) ? null : $objOrder->getBillingAddress());
    }


    /**
     * Generate the shipping address details
     * @param object
     * @param string
     * @return string
     */
    public function generateShippingAddressData($dc, $xlabel)
    {
        $objOrder = Order::findByPk($dc->id);

        return $this->generateAddressData((null === $objOrder) ? null : $objOrder->getShippingAddress());
    }


    /**
     * Generate address details amd return it as string
     * @param   Address
     * @return  string
     */
    protected function generateAddressData(Address $objAddress=null)
    {
        if (null === $objAddress)
        {
            return '<div class="tl_gerror">No address data available.</div>';
        }

        \System::loadLanguageFile('tl_iso_addresses');
        $this->loadDataContainer('tl_iso_addresses');

        $strBuffer = '
<div>
<table cellpadding="0" cellspacing="0" class="tl_show" summary="Table lists all details of an entry" style="width:650px">
  <tbody>';

        $i=0;

        foreach ($GLOBALS['TL_DCA']['tl_iso_addresses']['fields'] as $k => $v)
        {
            if (!isset($objAddress->$k))
            {
                continue;
            }

            $v = $objAddress->$k;
            $strClass = (++$i % 2) ? '' : ' class="tl_bg"';

            $strBuffer .= '
  <tr>
    <td' . $strClass . ' style="vertical-align:top"><span class="tl_label">'.Isotope::formatLabel('tl_iso_addresses', $k).': </span></td>
    <td' . $strClass . '>'.Isotope::formatValue('tl_iso_addresses', $k, $v).'</td>
  </tr>';
        }

        $strBuffer .= '
</tbody></table>
</div>';

        return $strBuffer;
    }


    /**
    * Review order page stores temporary information in this table to know it when user is redirected to a payment provider. We do not show this data in backend.
    * @param object
    * @return void
    */
    public function checkPermission($dc)
    {
        $this->import('BackendUser', 'User');

        if ($this->User->isAdmin)
        {
            return;
        }

        // Only admins can delete orders. Others should set the order_status to cancelled.
        unset($GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['operations']['delete']);
        if (\Input::get('act') == 'delete' || \Input::get('act') == 'deleteAll')
        {
            \System::log('Only admin can delete orders!', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $arrIds = array(0);
        $arrConfigs = $this->User->iso_configs;

        if (is_array($arrConfigs) && !empty($arrConfigs))
        {
            $objOrders = \Database::getInstance()->query("SELECT id FROM tl_iso_product_collection WHERE config_id IN (" . implode(',', $arrConfigs) . ")");

            if ($objOrders->numRows)
            {
                $arrIds = $objOrders->fetchEach('id');
            }
        }

        $GLOBALS['TL_DCA']['tl_iso_product_collection']['list']['sorting']['root'] = $arrIds;

        if (\Input::get('id') != '' && !in_array(\Input::get('id'), $arrIds))
        {
            \System::log('Trying to access disallowed order ID '.\Input::get('id'), __METHOD__, TL_ERROR);
            \Controller::redirect(\Environment::get('script').'?act=error');
        }
    }

	/**
	 * Return the paymnet button if a payment method is available
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function paymentButton($row, $href, $label, $title, $icon, $attributes)
	{
		return $row['payment_id'] > 0 ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : '';
	}

    /**
     * Generate a payment interface and return it as HTML string
     * @param object
     * @return string
     */
    public function paymentInterface($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null !== $objOrder) {
            $objPayment = $objOrder->getRelated('payment_id');

            if (null !== $objPayment) {
                return $objPayment->backendInterface($dc->id);
            }
        }

        return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['MSC']['backendPaymentNotFound'].'</p>';
    }

    /**
	 * Return the shipping button if a shipping method is available
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function shippingButton($row, $href, $label, $title, $icon, $attributes)
	{
		return $row['shipping_id'] > 0 ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : '';
	}

    /**
     * Generate a shipping interface and return it as HTML string
     * @param object
     * @return string
     */
    public function shippingInterface($dc)
    {
        $objOrder = Order::findByPk($dc->id);

        if (null !== $objOrder) {
            $objShipping = $objOrder->getRelated('shipping_id');

            if (null !== $objShipping) {
                return $objShipping->backendInterface($dc->id);
            }
        }

        return '<p class="tl_gerror">'.$GLOBALS['TL_LANG']['MSC']['backendShippingNotFound'].'</p>';
    }


    /**
     * Pass an order to the document
     * @param DataContainer
     */
    public function printDocument(\DataContainer $dc)
    {
        $strRedirectUrl = str_replace('&key=print_document', '', \Environment::get('request'));

        if (\Input::post('FORM_SUBMIT') == 'tl_iso_print_document') {
            if (($objOrder = Order::findByPk($dc->id)) === null) {
                \Message::addError('Could not find order id.');
                \Controller::redirect($strRedirectUrl);
            }

            if ($objOrder->getRelated('config_id') === null) {
                \Message::addError('Could not find config id.');
                \Controller::redirect($strRedirectUrl);
            }

            if (($objDocument = Document::findByPk(\Input::post('document'))) === null) {
                \Message::addError('Could not find document id.');
                \Controller::redirect($strRedirectUrl);
            }

            $objDocument->outputToBrowser($objOrder);
        }

        $arrSelect = array
        (
            'name'          => 'document',
            'label'         => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['document_choice'],
            'inputType'     => 'select',
            'foreignKey'    => 'tl_iso_document.name',
            'eval'          => array('mandatory'=>true)
        );

        $objSelect = new \SelectMenu(\SelectMenu::getAttributesFromDca($arrSelect, $arrSelect['name']));

        $strMessages = \Message::generate();
        \Message::reset();

        // Return form
        return '
<div id="tl_buttons">
<a href="'. ampersand($strRedirectUrl) .'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['tl_iso_product_collection']['print_document'][1], $dc->id).'</h2>'. $strMessages .'

<form action="'.ampersand(\Environment::get('request'), true).'" id="tl_iso_products_import" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_iso_print_document">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">

<div class="tl_tbox block">
  ' . $objSelect->parse() . '
  <p class="tl_help">'.$objSelect->description.'</p>
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="print" id="print" class="tl_submit" alt="" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['tl_iso_product_collection']['print']).'">
</div>

</div>
</form>';
    }


    /**
     * Trigger order status update when changing the status in the backend
     * @param string
     * @param DataContainer
     * @return string
     * @link http://www.contao.org/callbacks.html#save_callback
     */
    public function updateOrderStatus($varValue, $dc)
    {
        if ($dc->activeRecord && $dc->activeRecord->status != $varValue)
        {
            if (($objOrder = Order::findByPk($dc->id)) !== null)
            {
                // Status update has been cancelled, do not update
                if (!$objOrder->updateOrderStatus($varValue))
                {
                    return $dc->activeRecord->order_status;
                }
            }
        }

        return $varValue;
    }


    /**
     * Execute the saveCollection hook when a collection is saved
     * @param object
     * @return void
     */
    public function executeSaveHook($dc)
    {
        if (($objOrder = Order::findByPk($dc->id)) !== null)
        {
            // !HOOK: add additional functionality when saving collection
            if (isset($GLOBALS['ISO_HOOKS']['saveCollection']) && is_array($GLOBALS['ISO_HOOKS']['saveCollection']))
            {
                foreach ($GLOBALS['ISO_HOOKS']['saveCollection'] as $callback)
                {
                    $objCallback = \System::importStatic($callback[0]);
                    $objCallback->$callback[1]($objOrder);
                }
            }
        }
    }
}
