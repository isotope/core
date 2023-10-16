<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Module;

use Isotope\CompatibilityHelper;
use Contao\Controller;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Haste\Form\Form;
use Isotope\Isotope;
use Isotope\Model\Address;

/**
 * CartAddress frontend modules allows to set the billing and shipping address for current cart.
 */
class CartAddress extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'member_default';

    /**
     * @inheritDoc
     */
    protected function getSerializedProperties()
    {
        $props = parent::getSerializedProperties();

        $props[] = 'iso_address';
        $props[] = 'iso_addressFields';

        return $props;
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (CompatibilityHelper::isBackend()) {
            return $this->generateWildcard();
        }

        if (0 === \count($this->iso_address) || 0 === \count($this->iso_addressFields)) {
            return '';
        }

        // Set the custom member template
        if ($this->memberTpl != '') {
            $this->strTemplate = $this->memberTpl;
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->hasError = false;
        $this->Template->slabel = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['saveAddressButton']);

        $table = Address::getTable();

        System::loadLanguageFile($table);
        System::loadLanguageFile('tl_member');
        Controller::loadDataContainer($table);

        // Call onload_callback (e.g. to check permissions)
        if (\is_array($GLOBALS['TL_DCA'][$table]['config']['onload_callback'] ?? null)) {
            foreach ($GLOBALS['TL_DCA'][$table]['config']['onload_callback'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}();
            }
        }

        $arrFields = $this->iso_addressFields;
        $useBilling = \in_array('billing', $this->iso_address, true);
        $objAddress = $this->getDefaultAddress($useBilling);

        $objForm = new Form(
            'iso_cart_address_' . $this->id,
            'POST',
            function(Form $objHaste) {
                return Input::post('FORM_SUBMIT') === $objHaste->getFormId();
            },
            isset($this->tableless) ? (bool) $this->tableless : true
        );

        $objForm->bindModel($objAddress);

        // Add form fields
        $objForm->addFieldsFromDca($table, function ($strName, &$arrDca) use ($arrFields, $useBilling) {

            if (!\in_array($strName, $arrFields, true)
                || !($arrDca['eval']['feEditable'] ?? null)
                || (($arrDca['eval']['membersOnly'] ?? null) && FE_USER_LOGGED_IN !== true)
            ) {
                return false;
            }

            // Special field "country"
            if ('country' === $strName) {
                if ($useBilling) {
                    $arrCountries = Isotope::getConfig()->getBillingCountries();
                    $arrDca['default'] = Isotope::getConfig()->billing_country;
                } else {
                    $arrCountries = Isotope::getConfig()->getShippingCountries();
                    $arrDca['default'] = Isotope::getConfig()->shipping_country;
                }

                $arrDca['reference'] = $arrDca['options'];
                $arrDca['options'] = array_values(array_intersect(array_keys($arrDca['options']), $arrCountries));
            }

            return true;
        });

        $objCart = Isotope::getCart();

        // Save the data
        if ($objForm->validate()) {

            if (!$objCart->id) {
                $objCart->save();
            }

            $objAddress->tstamp = time();
            $objAddress->pid = $objCart->id;
            $objAddress->save();

            // Call onsubmit_callback
            if (\is_array($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'] ?? null)) {
                foreach ($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'] as $callback) {
                    System::importStatic($callback[0])->{$callback[1]}($objAddress);
                }
            }

            // Set the billing address
            if ($useBilling) {
                $objCart->setBillingAddress($objAddress);
            }

            // Set the shipping address
            if (\in_array('shipping', $this->iso_address, true)) {
                $objCart->setShippingAddress($objAddress);
            }

            $this->jumpToOrReload($this->jumpTo);
        }

        $objForm->addToTemplate($this->Template);

        // Predefine the group order (other groups will be appended automatically)
        $arrGroups  = array();
        $categories = array(
            'personal' => array(),
            'address'  => array(),
            'contact'  => array(),
            'login'    => array(),
            'profile'  => array()
        );

        foreach ($objForm->getFormFields() as $strName => $arrConfig) {
            if ($arrConfig['feGroup'] != '') {
                $arrGroups[$arrConfig['feGroup']][$strName] = $objForm->getWidget($strName)->parse();
            }
        }

        foreach ($arrGroups as $k => $v) {
            $this->Template->$k = $v; // backwards compatibility

            $key = $k . (('personal' === $k) ? 'Data' : 'Details');
            $categories[$GLOBALS['TL_LANG']['tl_member'][$key]] = $v;
        }

        $this->Template->categories = $categories;
        $this->Template->addressDetails = $GLOBALS['TL_LANG'][$table]['addressDetails'];
        $this->Template->contactDetails = $GLOBALS['TL_LANG'][$table]['contactDetails'];
        $this->Template->personalData = $GLOBALS['TL_LANG'][$table]['personalData'];
        $this->Template->loginDetails = $GLOBALS['TL_LANG'][$table]['loginDetails'];
    }

    /**
     * Get default address for this collection and address type
     *
     * @param bool $useBilling
     *
     * @return Address
     */
    private function getDefaultAddress($useBilling)
    {
        $objAddress = null;
        $intCart = Isotope::getCart()->id;

        if ($intCart > 0) {
            if ($useBilling) {
                $objAddress = Address::findDefaultBillingForProductCollection($intCart);
            } else {
                $objAddress = Address::findDefaultShippingForProductCollection($intCart);
            }
        }

        if (null === $objAddress) {
            $objAddress = Address::createForProductCollection(
                Isotope::getCart(),
                ($useBilling ? Isotope::getConfig()->getBillingFields() : Isotope::getConfig()->getShippingFields()),
                $useBilling,
                !$useBilling
            );
        }

        return $objAddress;
    }
}
