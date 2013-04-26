<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\CheckoutStep;

use Isotope\Isotope;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Model\Address as AddressModel;


class BillingAddress extends Address implements IsotopeCheckoutStep
{

    /**
     * Returns true to enable the module
     * @return  bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $blnRequiresPayment = Isotope::getCart()->requiresPayment();

        $this->Template->headline = $blnRequiresPayment ? $GLOBALS['TL_LANG']['MSC']['billing_address'] : $GLOBALS['TL_LANG']['MSC']['customer_address'];
        $this->Template->message = (FE_USER_LOGGED_IN === true ? $GLOBALS['TL_LANG']['MSC'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_message'] : $GLOBALS['TL_LANG']['MSC'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_guest_message']);

/*
        if (!$this->hasError()) {
            $objAddress = Isotope::getCart()->getBillingAddress();

            $this->objModule->arrOrderData['billing_address'] = $objAddress->generateHtml(Isotope::getConfig()->billing_fields);
            $this->objModule->arrOrderData['billing_address_text'] = $objAddress->generateText(Isotope::getConfig()->billing_fields);
        }
*/

        return parent::generate();
    }


    public function review()
    {
        $blnRequiresPayment = Isotope::getCart()->requiresPayment();
        $blnRequiresShipping = Isotope::getCart()->requiresShipping();
        $objAddress = Isotope::getCart()->getShippingAddress();

        $strHeadline = $GLOBALS['TL_LANG']['MSC']['billing_address'];

        if ($blnRequiresPayment && $blnRequiresShipping && $objAddress->id == -1)
        {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['billing_shipping_address'];
        }
        elseif ($blnRequiresShipping && $objAddress->id == -1)
        {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['shipping_address'];
        }
        elseif (!$blnRequiresPayment && !$blnRequiresShipping)
        {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['customer_address'];
        }

        return array('billing_address' => array
        (
            'headline'    => $strHeadline,
            'info'        => Isotope::getCart()->getBillingAddress()->generateHtml(Isotope::getConfig()->billing_fields),
            'edit'        => $this->addToUrl('step=address', true),
        ));
    }

    /**
     * Get available address options
     * @return  array
     */
    protected function getAddressOptions()
    {
        $arrOptions = parent::getAddressOptions();

        $arrAddress = $_SESSION['CHECKOUT_DATA'][$this->getStepClass()] ? $_SESSION['CHECKOUT_DATA'][$field] : Isotope::getCart()->billing_address;
        $intDefaultValue = strlen($arrAddress['id']) ? $arrAddress['id'] : 0;

        if (!empty($arrOptions)) {
            $arrOptions[] = array (
                'value'     => 0,
                'label'     => &$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'],
                'default'   => ($this->getDefaultAddress()->id == Isotope::getCart()->address1_id),
            );
        }

        return $arrOptions;
    }

    /**
     * Get address object for a selected option
     * @param   string
     * @return  Isotope\Model\Address
     */
    protected function getAddressForOption($varValue)
    {
        if ($varValue == 0) {
            $objAddress = $this->getDefaultAddress();
            $arrAddress = $this->validateFields();

            foreach ($arrAddress as $field => $value) {
                $objAddress->$field = $value;
            }

            $objAddress->save();

            return $objAddress;
        }

        return parent::getAddressForOption($varValue);
    }

    /**
     * Get default address for this collection and address type
     * @return  Isotope\Model\Address
     */
    protected function getDefaultAddress()
    {
        $objAddress = AddressModel::findOneBy(array('ptable=?', 'pid=?', 'isDefaultBilling=?'), array('tl_iso_product_collection', Isotope::getCart()->id, '1'));

        if (null === $objAddress) {
            $arrAddress = Isotope::getCart()->getBillingAddress()->row();
            unset($arrAddress['id']);
            $arrAddress['ptable'] = 'tl_iso_product_collection';
            $arrAddress['pid'] = Isotope::getCart()->id;
            $arrAddress['isDefaultBilling'] = '1';

            $objAddress = new AddressModel();
            $objAddress->setRow($arrAddress);
        }

        return $objAddress;
    }

    /**
     * Get field configuration for this address type
     * @return  array
     */
    protected function getAddressFields()
    {
        return Isotope::getConfig()->billing_fields;
    }

    /**
     * Get allowed countries for this address type
     * @return  array
     */
    protected function getAddressCountries()
    {
        return Isotope::getConfig()->getBillingCountries();
    }

    /**
     * Get default country for this address type
     * @return  string
     */
    protected function getDefaultCountry()
    {
        return Isotope::getConfig()->billing_country;
    }

    /**
     * Get the current address (from Cart) for this address type
     * @return  Isotope\Model\Address
     */
    protected function getAddress()
    {
        return Isotope::getCart()->getBillingAddress();
    }

    /**
     * Set new address in cart
     * @param   Isotope\Model\Address
     */
    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setBillingAddress($objAddress);
    }
}
