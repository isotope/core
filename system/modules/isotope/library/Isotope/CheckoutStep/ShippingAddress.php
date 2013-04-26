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


class ShippingAddress extends Address implements IsotopeCheckoutStep
{

    /**
     * Returns true if the current cart has shipping
     * @return  bool
     */
    public function isAvailable()
    {
        if (!Isotope::getCart()->requiresShipping() || count(Isotope::getConfig()->getShippingFields()) == 0) {
            return false;
        }

        return true;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['shipping_address'];
        $this->Template->message = $GLOBALS['TL_LANG']['MSC']['shipping_address_message'];

        if (!$this->hasError())
        {
            $objAddress = Isotope::getCart()->getShippingAddress();

            // No shipping address, use billing address
            if ($objAddress->id == -1)
            {
                $strShippingAddress = (Isotope::getCart()->requiresPayment() ? $GLOBALS['TL_LANG']['MSC']['useBillingAddress'] : $GLOBALS['TL_LANG']['MSC']['useCustomerAddress']);

                $this->objModule->arrOrderData['shipping_address'] = $strShippingAddress;
                $this->objModule->arrOrderData['shipping_address_text'] = $strShippingAddress;
            }
            else
            {
                $this->objModule->arrOrderData['shipping_address'] = $objAddress->generateHtml(Isotope::getConfig()->shipping_fields);
                $this->objModule->arrOrderData['shipping_address_text'] = $objAddress->generateText(Isotope::getConfig()->shipping_fields);
            }
        }

        return parent::generate();
    }


    public function review()
    {
        $objAddress = Isotope::getCart()->getShippingAddress();

        if ($objAddress->id == -1)
        {
            return false;
        }

        return array('shipping_address' => array
        (
            'headline'    => $GLOBALS['TL_LANG']['MSC']['shipping_address'],
            'info'        => $objAddress->generateHtml(Isotope::getConfig()->shipping_fields),
            'edit'        => $this->addToUrl('step=address', true),
        ));
    }


    protected function getAddressOptions()
    {
        $arrOptions = parent::getAddressOptions();

        array_insert($arrOptions, 0, array(array(
            'value' => -1,
            'label' => (Isotope::getCart()->requiresPayment() ? $GLOBALS['TL_LANG']['MSC']['useBillingAddress'] : $GLOBALS['TL_LANG']['MSC']['useCustomerAddress']),
        )));

        $arrOptions[] = array(
            'value' => 0,
            'label' => $GLOBALS['TL_LANG']['MSC']['differentShippingAddress'],
        );

        return $arrOptions;
    }


    protected function getAddressFields()
    {
        return Isotope::getConfig()->shipping_fields;
    }


    protected function getAddressCountries()
    {
        return Isotope::getConfig()->getShippingCountries();
    }


    protected function getDefaultCountry()
    {
        return Isotope::getConfig()->shipping_country;
    }


    protected function getAddress()
    {
        return Isotope::getCart()->getShippingAddress();
    }


    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setShippingAddress($objAddress);
    }
}
