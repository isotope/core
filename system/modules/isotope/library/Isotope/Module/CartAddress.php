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

namespace Isotope\Module;

use Haste\Form\Form;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection;


class CartAddress extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'member_default';

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CART ADDRESS ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->iso_address = deserialize($this->iso_address, true);
        $this->iso_addressFields = deserialize($this->iso_addressFields, true);

        if (empty($this->iso_address) || empty($this->iso_addressFields)) {
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
        $this->Template->hasError  = false;
        $this->Template->slabel    = specialchars($GLOBALS['TL_LANG']['MSC']['saveAddressButton']);

        $table = Address::getTable();

        \System::loadLanguageFile($table);
        \Controller::loadDataContainer($table);

        // Call onload_callback (e.g. to check permissions)
        if (is_array($GLOBALS['TL_DCA'][$table]['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['config']['onload_callback'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]();
            }
        }

        $arrFields = $this->iso_addressFields;
        $useBilling = in_array('billing', $this->iso_address);
        $objAddress = $this->getDefaultAddress($useBilling);

        $objForm = new Form('iso_cart_address_' . $this->id, 'POST', function($objHaste) {
            /** @type Form $objHaste */
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        }, (boolean) $this->tableless);

        $objForm->bindModel($objAddress);

        // Add form fields
        $objForm->addFieldsFromDca($table, function ($strName, &$arrDca) use ($arrFields, $useBilling) {

            if (!in_array($strName, $arrFields)
                || !$arrDca['eval']['feEditable']
                || ($arrDca['eval']['membersOnly'] && FE_USER_LOGGED_IN !== true)
            ) {
                return false;
            }

            // Special field "country"
            if ($strName == 'country') {
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
            if (is_array($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'] as $callback) {
                    $objCallback = \System::importStatic($callback[0]);
                    $objCallback->$callback[1]($objAddress);
                }
            }

            // Set the billing address
            if ($useBilling) {
                $objCart->setBillingAddress($objAddress);
            }

            // Set the shipping address
            if (in_array('shipping', $this->iso_address)) {
                $objCart->setShippingAddress($objAddress);
            }

            $this->jumpToOrReload($this->jumpTo);
        }

        $objForm->addToTemplate($this->Template);
        $arrGroups = array();

        // Add groups
        foreach ($objForm->getFormFields() as $strName => $arrConfig) {
            if ($arrConfig['feGroup'] != '') {
                $arrGroups[$arrConfig['feGroup']][$strName] = $objForm->getWidget($strName)->parse();
            }
        }

        foreach ($arrGroups as $k => $v) {
            $this->Template->$k = $v;
        }

        $this->Template->addressDetails = $GLOBALS['TL_LANG'][$table]['addressDetails'];
        $this->Template->contactDetails = $GLOBALS['TL_LANG'][$table]['contactDetails'];
        $this->Template->personalData   = $GLOBALS['TL_LANG'][$table]['personalData'];
        $this->Template->loginDetails   = $GLOBALS['TL_LANG'][$table]['loginDetails'];
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
