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

        $objTemplate = new \Isotope\Template('iso_checkout_billing_address');

        $objTemplate->headline = $blnRequiresPayment ? $GLOBALS['TL_LANG']['MSC']['billing_address'] : $GLOBALS['TL_LANG']['MSC']['customer_address'];
        $objTemplate->message = (FE_USER_LOGGED_IN === true ? $GLOBALS['TL_LANG']['MSC'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_message'] : $GLOBALS['TL_LANG']['MSC'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_guest_message']);
        $objTemplate->fields = $this->generateAddressWidget();

/*
        if (!$this->objModule->doNotSubmit) {
            $objAddress = Isotope::getCart()->getBillingAddress();

            $this->objModule->arrOrderData['billing_address'] = $objAddress->generateHtml(Isotope::getConfig()->billing_fields);
            $this->objModule->arrOrderData['billing_address_text'] = $objAddress->generateText(Isotope::getConfig()->billing_fields);
        }
*/

        return $objTemplate->parse();
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


    protected function getAddressOptions()
    {
        $arrOptions = parent::getAddressOptions();

        $arrAddress = $_SESSION['CHECKOUT_DATA'][$this->getShortClass()] ? $_SESSION['CHECKOUT_DATA'][$field] : Isotope::getCart()->billing_address;
        $intDefaultValue = strlen($arrAddress['id']) ? $arrAddress['id'] : 0;

        if (!empty($arrOptions)) {
            $arrOptions[] = array (
                'value'    => 0,
                'label' => &$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'],
            );
        }

        return $arrOptions;
    }


    protected function getAddressFields()
    {
        return Isotope::getConfig()->billing_fields;
    }


    protected function getAddressCountries()
    {
        return Isotope::getConfig()->getBillingCountries();
    }


    protected function getDefaultCountry()
    {
        return Isotope::getConfig()->billing_country;
    }


    protected function getAddress()
    {
        return Isotope::getCart()->getBillingAddress();
    }


    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setBillingAddress($objAddress);
    }
}
