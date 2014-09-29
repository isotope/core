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

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address as AddressModel;


class ShippingAddress extends Address implements IsotopeCheckoutStep
{

    /**
     * Returns true if the current cart has shipping
     *
     * @return bool
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
     *
     * @return string
     */
    public function generate()
    {
        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['shipping_address'];
        $this->Template->message  = $GLOBALS['TL_LANG']['MSC']['shipping_address_message'];

        return parent::generate();
    }

    /**
     * Return review information for last page of checkout
     *
     * @return string
     */
    public function review()
    {
        $objAddress = Isotope::getCart()->getShippingAddress();

        if ($objAddress->id == Isotope::getCart()->getBillingAddress()->id) {
            return false;
        }

        return array('shipping_address' => array
        (
            'headline' => $GLOBALS['TL_LANG']['MSC']['shipping_address'],
            'info'     => $objAddress->generateHtml(Isotope::getConfig()->getShippingFieldsConfig()),
            'edit'     => \Isotope\Module\Checkout::generateUrlForStep('address'),
        ));
    }

    /**
     * Return array of tokens for notification
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        return array();
    }

    /**
     * Get available address options
     *
     * @return array
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
            'default'   => ($this->getDefaultAddress()->id == Isotope::getCart()->shipping_address_id),
        );

        return $arrOptions;
    }

    /**
     * Get address object for a selected option
     *
     * @param mixed $varValue
     * @param bool  $blnValidate
     *
     * @return AddressModel
     */
    protected function getAddressForOption($varValue, $blnValidate)
    {
        if ($varValue === '-1') {
            return Isotope::getCart()->getBillingAddress();
        } elseif ($varValue === '0') {
            $objAddress = $this->getDefaultAddress();
            $arrAddress = $this->validateFields($blnValidate);

            if ($blnValidate) {
                foreach ($arrAddress as $field => $value) {
                    $objAddress->$field = $value;
                }

                $objAddress->save();
            }

            return $objAddress;
        }

        return parent::getAddressForOption($varValue, $blnValidate);
    }

    /**
     * Get default address for this collection and address type
     *
     * @return AddressModel
     */
    protected function getDefaultAddress()
    {
        $objAddress = AddressModel::findDefaultShippingForProductCollection(Isotope::getCart()->id);

        if (null === $objAddress) {
            $objAddress = AddressModel::createForProductCollection(
                Isotope::getCart(),
                Isotope::getConfig()->getShippingFields(),
                false,
                true
            );
        }

        return $objAddress;
    }

    /**
     * Get field configuration for this address type
     *
     * @return array
     */
    protected function getAddressFields()
    {
        return Isotope::getConfig()->getShippingFieldsConfig();
    }

    /**
     * Get allowed countries for this address type
     *
     * @return array
     */
    protected function getAddressCountries()
    {
        return Isotope::getConfig()->getShippingCountries();
    }

    /**
     * Get the current address (from Cart) for this address type
     *
     * @return AddressModel
     */
    protected function getAddress()
    {
        return Isotope::getCart()->getShippingAddress();
    }

    /**
     * Set new address in cart
     *
     * @param AddressModel $objAddress
     */
    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setShippingAddress($objAddress);
    }
}
