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

use Contao\System;
use Isotope\CoreBundle\Model\Address as AddressModel;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Isotope;
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
        if (!Isotope::getCart()->requiresShippingAddress()) {
            return false;
        }

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);
        $context = $storeManager->getCurrentContext();

        return [] !== $context->getShippingAddressConfig()->getFields();
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

        /** @var AddressFormatter $addressFormatter */
        $addressFormatter = System::getContainer()->get(AddressFormatter::class);

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        return array('shipping_address' => array
        (
            'headline' => $GLOBALS['TL_LANG']['MSC']['shipping_address'],
            'info'     => (string) $addressFormatter->get($objAddress, $storeManager->getCurrentContext()->getShippingAddressConfig()),
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
    protected function getAddressOptions()
    {
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        $arrOptions = parent::getAddressOptions($storeManager->getCurrentContext()->getShippingAddressConfig());

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
            /** @var StoreManager $storeManager */
            $storeManager = System::getContainer()->get(StoreManager::class);
            $context = $storeManager->getCurrentContext();

            $objAddress = AddressModel::createForProductCollection(
                Isotope::getCart(),
                $context->getShippingAddressConfig()->getFields(),
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
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        return $storeManager->getCurrentContext()->getShippingAddressConfig()->getCountryCodes();
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
     */
    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setShippingAddress($objAddress);
    }
}
