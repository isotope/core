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


class BillingAddress extends Address implements IsotopeCheckoutStep
{

    /**
     * Returns true to enable the module
     *
     * @return bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     *
     * @return string
     */
    public function generate()
    {
        $blnRequiresPayment = Isotope::getCart()->requiresPayment();

        $this->Template->headline = $blnRequiresPayment ? $GLOBALS['TL_LANG']['MSC']['billing_address'] : $GLOBALS['TL_LANG']['MSC']['customer_address'];
        $this->Template->message  = (FE_USER_LOGGED_IN === true ? $GLOBALS['TL_LANG']['MSC'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_message'] : $GLOBALS['TL_LANG']['MSC'][($blnRequiresPayment ? 'billing' : 'customer') . '_address_guest_message']);

        return parent::generate();
    }

    /**
     * Return review information for last page of checkout
     *
     * @return string
     */
    public function review()
    {
        $blnRequiresPayment  = Isotope::getCart()->requiresPayment();
        $blnRequiresShipping = Isotope::getCart()->requiresShipping();
        $objBillingAddress   = Isotope::getCart()->getBillingAddress();
        $objShippingAddress  = Isotope::getCart()->getShippingAddress();

        $strHeadline = $GLOBALS['TL_LANG']['MSC']['billing_address'];

        if ($blnRequiresPayment && $blnRequiresShipping && $objBillingAddress->id == $objShippingAddress->id) {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['billing_shipping_address'];
        } elseif ($blnRequiresShipping && $objBillingAddress->id == $objShippingAddress->id) {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['shipping_address'];
        } elseif (!$blnRequiresPayment && !$blnRequiresShipping) {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['customer_address'];
        }

        return array('billing_address' => array
        (
            'headline' => $strHeadline,
            'info'     => $objBillingAddress->generateHtml(Isotope::getConfig()->getBillingFieldsConfig()),
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

        if (!empty($arrOptions)) {
            $arrOptions[] = array(
                'value'   => '0',
                'label'   => &$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'],
                'default' => ($this->getDefaultAddress()->id == Isotope::getCart()->billing_address_id),
            );
        }

        return $arrOptions;
    }

    /**
     * Get address object for a selected option
     *
     * @param string $varValue
     * @param bool   $blnValidate
     *
     * @return AddressModel
     */
    protected function getAddressForOption($varValue, $blnValidate)
    {
        if ($varValue == '0') {
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
     * @return Address
     */
    protected function getDefaultAddress()
    {
        $objAddress = AddressModel::findDefaultBillingForProductCollection(Isotope::getCart()->id);

        if (null === $objAddress) {
            $objAddress = AddressModel::createForProductCollection(
                Isotope::getCart(),
                Isotope::getConfig()->getBillingFields(),
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
        return Isotope::getConfig()->getBillingFieldsConfig();
    }

    /**
     * Get allowed countries for this address type
     *
     * @return array
     */
    protected function getAddressCountries()
    {
        return Isotope::getConfig()->getBillingCountries();
    }

    /**
     * Get the current address (from Cart) for this address type
     *
     * @return Address
     */
    protected function getAddress()
    {
        return Isotope::getCart()->getBillingAddress();
    }

    /**
     * Set new address in cart
     *
     * @param AddressModel $objAddress
     */
    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setBillingAddress($objAddress);
    }
}
