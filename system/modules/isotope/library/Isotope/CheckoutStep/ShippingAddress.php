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

    /**
     * Return review information for last page of checkout
     * @return  string
     */
    public function review()
    {
        $objCart = Isotope::getCart();
        $objAddress = $objCart->getShippingAddress();

        if ($objAddress->id == $objCart->getBillingAddress()->id)
        {
            return false;
        }

        return array('shipping_address' => array
        (
            'headline'    => $GLOBALS['TL_LANG']['MSC']['shipping_address'],
            'info'        => $objAddress->generateHtml(Isotope::getConfig()->shipping_fields),
            'edit'        => \Isotope\Module\Checkout::generateUrlForStep('address'),
        ));
    }

    /**
     * Get available address options
     * @return  array
     */
    protected function getAddressOptions()
    {
        $arrOptions = parent::getAddressOptions();

        array_insert($arrOptions, 0, array(array(
            'value'     => '-1',
            'label'     => (Isotope::getCart()->requiresPayment() ? $GLOBALS['TL_LANG']['MSC']['useBillingAddress'] : $GLOBALS['TL_LANG']['MSC']['useCustomerAddress']),
            'default'   => '1',
        )));

        $arrOptions[] = array(
            'value'     => '0',
            'label'     => $GLOBALS['TL_LANG']['MSC']['differentShippingAddress'],
            'default'   => ($this->getDefaultAddress()->id == Isotope::getCart()->address2_id),
        );

        return $arrOptions;
    }

    /**
     * Get address object for a selected option
     * @param   string
     * @return  Isotope\Model\Address
     */
    protected function getAddressForOption($varValue)
    {
        if ($varValue === '-1') {
            return Isotope::getCart()->getBillingAddress();
        }
        elseif ($varValue === '0') {
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
        $objAddress = AddressModel::findOneBy(array('ptable=?', 'pid=?', 'isDefaultShipping=?'), array('tl_iso_product_collection', Isotope::getCart()->id, '1'));

        if (null === $objAddress) {
            $objShippingAddress = Isotope::getCart()->getShippingAddress();

            if (null === $objShippingAddress) {
                $objAddress = new AddressModel();
            } else {
                $objAddress = clone $objShippingAddress;
            }

            $objAddress->ptable = 'tl_iso_product_collection';
            $objAddress->pid = Isotope::getCart()->id;
            $objAddress->isDefaultShipping = '1';
            $objAddress->isDefaultBilling = '';
        }

        return $objAddress;
    }

    /**
     * Get field configuration for this address type
     * @return  array
     */
    protected function getAddressFields()
    {
        return Isotope::getConfig()->shipping_fields;
    }

    /**
     * Get allowed countries for this address type
     * @return  array
     */
    protected function getAddressCountries()
    {
        return Isotope::getConfig()->getShippingCountries();
    }

    /**
     * Get default country for this address type
     * @return  string
     */
    protected function getDefaultCountry()
    {
        return Isotope::getConfig()->shipping_country;
    }

    /**
     * Get the current address (from Cart) for this address type
     * @return  Isotope\Model\Address
     */
    protected function getAddress()
    {
        return Isotope::getCart()->getShippingAddress();
    }

    /**
     * Set new address in cart
     * @param   Isotope\Model\Address
     */
    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setShippingAddress($objAddress);
    }
}
