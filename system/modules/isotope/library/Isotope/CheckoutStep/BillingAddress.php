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

use Contao\MemberModel;
use Contao\System;
use Isotope\CoreBundle\Model\Address as AddressModel;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\StoreManager;
use Isotope\Interfaces\IsotopeCheckoutStep;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Module\Checkout;


class BillingAddress extends Address implements IsotopeCheckoutStep
{

    /**
     * @inheritdoc
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isSkippable()
    {
        return \Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER') && $this->objModule->canSkipStep('billing_address');
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        $requiresPayment = Isotope::getCart()->requiresPayment();

        $this->Template->headline = $requiresPayment ? $GLOBALS['TL_LANG']['MSC']['billing_address'] : $GLOBALS['TL_LANG']['MSC']['customer_address'];

        if ($this->isSkippable()) {
            $address = $this->getDefaultMemberAddress();
            $address->save();

            Isotope::getCart()->setBillingAddress($address);

            /** @var AddressFormatter $addressFormatter */
            $addressFormatter = System::getContainer()->get(AddressFormatter::class);

            /** @var StoreManager $storeManager */
            $storeManager = System::getContainer()->get(StoreManager::class);
            $addressConfig = $storeManager->getCurrentContext()->getBillingAddressConfig();

            $this->Template->class = $this->getStepClass();
            $this->Template->options = (string) $addressFormatter->get($address, $addressConfig);
            $this->Template->fields = '';

            return $this->Template->parse();
        }

        $this->Template->message  = (\Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER') ? $GLOBALS['TL_LANG']['MSC'][($requiresPayment ? 'billing' : 'customer') . '_address_message'] : $GLOBALS['TL_LANG']['MSC'][($requiresPayment ? 'billing' : 'customer') . '_address_guest_message']);

        return parent::generate();
    }

    /**
     * @inheritdoc
     */
    public function review()
    {
        $draftOrder = Isotope::getCart()->getDraftOrder();
        $blnRequiresPayment  = $draftOrder->requiresPayment();
        $blnRequiresShipping = $draftOrder->requiresShippingAddress();
        $objBillingAddress   = $draftOrder->getBillingAddress();
        $objShippingAddress  = $draftOrder->getShippingAddress();

        $canEdit     = !$this->isSkippable();
        $strHeadline = $GLOBALS['TL_LANG']['MSC']['billing_address'];

        if ($blnRequiresPayment && $blnRequiresShipping && $objBillingAddress->id == $objShippingAddress->id) {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['billing_shipping_address'];
            $canEdit     = $canEdit || !$this->objModule->canSkipStep('shipping_address');
        } elseif ($blnRequiresShipping && $objBillingAddress->id == $objShippingAddress->id) {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['shipping_address'];
            $canEdit     = $canEdit || !$this->objModule->canSkipStep('shipping_address');
        } elseif (!$blnRequiresPayment && !$blnRequiresShipping) {
            $strHeadline = $GLOBALS['TL_LANG']['MSC']['customer_address'];
        }

        /** @var AddressFormatter $addressFormatter */
        $addressFormatter = System::getContainer()->get(AddressFormatter::class);

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);
        $addressConfig = $storeManager->getCurrentContext()->getBillingAddressConfig();

        return array('billing_address' => array
        (
            'headline' => $strHeadline,
            'info'     => (string) $addressFormatter->get($objBillingAddress, $addressConfig),
            'edit'     => $canEdit ? Checkout::generateUrlForStep(Checkout::STEP_ADDRESS) : '',
        ));
    }

    /**
     * @inheritdoc
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        return array();
    }

    /**
     * @inheritdoc
     */
    protected function getAddressOptions()
    {
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        $arrOptions = parent::getAddressOptions($storeManager->getCurrentContext()->getBillingAddressConfig());

        if (0 !== \count($arrOptions)) {
            $arrOptions[] = [
                'value'   => '0',
                'label'   => &$GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'],
                'default' => $this->getDefaultAddress()->id == Isotope::getCart()->billing_address_id,
            ];
        }

        return $arrOptions;
    }

    /**
     * @inheritdoc
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
     * @return AddressModel
     */
    protected function getDefaultAddress()
    {
        $objAddress = AddressModel::findDefaultBillingForProductCollection(Isotope::getCart()->id);

        if (null === $objAddress) {
            /** @var StoreManager $storeManager */
            $storeManager = System::getContainer()->get(StoreManager::class);
            $context = $storeManager->getCurrentContext();

            $objAddress = AddressModel::createForProductCollection(
                Isotope::getCart(),
                $context->getBillingAddressConfig()->getFields(),
                true
            );
        }

        return $objAddress;
    }

    /**
     * @inheritdoc
     */
    protected function getAddressFields()
    {
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);
        $context = $storeManager->getCurrentContext();

        return $context->getBillingAddressConfig()->getAddressFieldsConfig();
    }

    /**
     * @inheritdoc
     */
    protected function getAddressCountries()
    {
        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);

        return $storeManager->getCurrentContext()->getBillingAddressConfig()->getCountryCodes();
    }

    /**
     * @inheritdoc
     */
    protected function getAddress()
    {
        $address = Isotope::getCart()->getBillingAddress();

        if (null !== $address && Isotope::getCart()->billing_address_id != $address->id) {
            Isotope::getCart()->setBillingAddress($address);
        }

        return $address;
    }

    /**
     * @inheritdoc
     */
    protected function setAddress(AddressModel $objAddress)
    {
        Isotope::getCart()->setBillingAddress($objAddress);
    }

    /**
     * @return AddressModel
     */
    private function getDefaultMemberAddress()
    {
        $address = $this->getDefaultAddress();

        /** @var StoreManager $storeManager */
        $storeManager = System::getContainer()->get(StoreManager::class);
        $context = $storeManager->getCurrentContext();

        if ($address->id > 0 && $context->getMemberId() > 0) {
            $member = MemberModel::findById($context->getMemberId());

            if ($member) {
                $data = AddressModel::getAddressDataForMember(
                    $member,
                    $context->getBillingAddressConfig()->getFields(),
                );

                foreach ($data as $k => $v) {
                    $address->{$k} = $v;
                }
            }
        }

        return $address;
    }
}
