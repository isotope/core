<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Isotope;
use Isotope\Model\Address as AddressModel;
use Isotope\Module\Checkout;

/**
 * ShippingAddress checkout step lets the user enter a shipping address
 */
class ShippingAddress extends Address implements IsotopeCheckoutStep
{

    /**
     * Returns true if the current cart has shipping
     *
     * @inheritdoc
     */
    public function isAvailable()
    {
        return Isotope::getCart()->requiresShippingAddress() && \count(Isotope::getConfig()->getShippingFields()) > 0;
    }

    /**
     * @inheritdoc
     */
    public function isSkippable()
    {
        return $this->objModule->canSkipStep('shipping_address');
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if ($this->isSkippable()) {
            Isotope::getCart()->setShippingAddress(Isotope::getCart()->getBillingAddress());

            return '';
        }

        $this->Template->headline = $GLOBALS['TL_LANG']['MSC']['shipping_address'];
        $this->Template->message  = $GLOBALS['TL_LANG']['MSC']['shipping_address_message'];

        return parent::generate();
    }

    /**
     * @inheritdoc
     */
    public function review()
    {
        $objAddress = Isotope::getCart()->getDraftOrder()->getShippingAddress();

        if ($objAddress->id == Isotope::getCart()->getDraftOrder()->getBillingAddress()->id) {
            return false;
        }

        return array('shipping_address' => array
        (
            'headline' => $GLOBALS['TL_LANG']['MSC']['shipping_address'],
            'info'     => $objAddress->generate(Isotope::getConfig()->getShippingFieldsConfig()),
            'edit'     => $this->isSkippable() ? '' : Checkout::generateUrlForStep(Checkout::STEP_ADDRESS),
        ));
    }

    /**
     * Get available address options
     *
     * @param array $arrFields
     *
     * @return array
     */
    protected function getAddressOptions($arrFields = null)
    {
        $arrOptions = parent::getAddressOptions(Isotope::getConfig()->getShippingFieldsConfig());

        array_unshift(
            $arrOptions,
            [
               'value'     => '-1',
               'label'     => Isotope::getCart()->requiresPayment() ? $GLOBALS['TL_LANG']['MSC']['useBillingAddress'] : $GLOBALS['TL_LANG']['MSC']['useCustomerAddress'],
               'default'   => '1',
            ]
        );

        $defaultAddress = $this->getDefaultAddress();

        $arrOptions[] = [
            'value'     => '0',
            'label'     => $GLOBALS['TL_LANG']['MSC']['differentShippingAddress'],
            'default'   => null !== $defaultAddress->id && (int) $defaultAddress->id === (int) Isotope::getCart()->shipping_address_id,
        ];

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
        $billingAddress = Isotope::getCart()->getBillingAddress();
        $shippingAddress = Isotope::getCart()->getShippingAddress();

        if (null !== $shippingAddress
            && null !== $billingAddress
            && ($shippingAddress === $billingAddress
                || $shippingAddress->id < 1
            )
            && Isotope::getCart()->shipping_address_id != $billingAddress->id
        ) {
            Isotope::getCart()->setShippingAddress($billingAddress);
        } elseif (null !== $shippingAddress
            && Isotope::getCart()->shipping_address_id != $shippingAddress->id
        ) {
            Isotope::getCart()->setShippingAddress($shippingAddress);
        }

        return ($shippingAddress === $billingAddress) ? null : $shippingAddress;
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
