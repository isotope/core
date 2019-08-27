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
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Isotope;
use Isotope\Model\Address as AddressModel;
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
        return true === FE_USER_LOGGED_IN && $this->objModule->canSkipStep('billing_address');
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

            $this->Template->class = $this->getStepClass();
            $this->Template->tableless = isset($this->objModule->tableless) ? $this->objModule->tableless : true;
            $this->Template->options = $address->generate();
            $this->Template->fields = '';

            return $this->Template->parse();
        }

        $this->Template->message  = (FE_USER_LOGGED_IN === true ? $GLOBALS['TL_LANG']['MSC'][($requiresPayment ? 'billing' : 'customer') . '_address_message'] : $GLOBALS['TL_LANG']['MSC'][($requiresPayment ? 'billing' : 'customer') . '_address_guest_message']);

        return parent::generate();
    }

    /**
     * @inheritdoc
     */
    public function review()
    {
        $draftOrder = Isotope::getCart()->getDraftOrder();
        $blnRequiresPayment  = $draftOrder->requiresPayment();
        $blnRequiresShipping = $draftOrder->requiresShipping();
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

        return array('billing_address' => array
        (
            'headline' => $strHeadline,
            'info'     => $objBillingAddress->generate(Isotope::getConfig()->getBillingFieldsConfig()),
            'edit'     => $canEdit ? Checkout::generateUrlForStep('address') : '',
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
    protected function getAddressOptions($arrFields = null)
    {
        $arrOptions = parent::getAddressOptions(Isotope::getConfig()->getBillingFieldsConfig());

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
            $objAddress = AddressModel::createForProductCollection(
                Isotope::getCart(),
                Isotope::getConfig()->getBillingFields(),
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
        return Isotope::getConfig()->getBillingFieldsConfig();
    }

    /**
     * @inheritdoc
     */
    protected function getAddressCountries()
    {
        return Isotope::getConfig()->getBillingCountries();
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

        if ($address->id > 0 && true === FE_USER_LOGGED_IN) {
            $data = AddressModel::getAddressDataForMember(
                Isotope::getCart()->getMember(),
                Isotope::getConfig()->getBillingFields()
            );

            foreach ($data as $k => $v) {
                $address->{$k} = $v;
            }
        }

        return $address;
    }
}
