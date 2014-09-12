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


class TemporaryAddress extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_temporary_address';

    /**
     * Address fields
     * @var array
     */
    protected $arrAddressFields = array();

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: TEMPORARY ADDRESS ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->iso_addressTypes = deserialize($this->iso_addressTypes, true);
        $this->arrAddressFields = deserialize($this->iso_addressFields, true);

        if (empty($this->iso_addressTypes) || empty($this->arrAddressFields)) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template            = new \Isotope\Template($this->memberTpl);
        $this->Template->hasError  = false;
        $this->Template->slabel    = specialchars($GLOBALS['TL_LANG']['MSC']['saveAddressButton']);

        $table = Address::getTable();

        \System::loadLanguageFile($table);
        $this->loadDataContainer($table);

        // Call onload_callback (e.g. to check permissions)
        if (is_array($GLOBALS['TL_DCA'][$table]['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['config']['onload_callback'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]();
            }
        }

        $objAddress = $this->getDefaultAddress();

        $objForm = new Form('iso_temporary_address_' . $this->id, 'POST', function($objHaste) {
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        }, (boolean) $this->tableless);

        $objForm->bindModel($objAddress);
        $arrFields = $this->arrAddressFields;

        // Add form fields
        $objForm->addFieldsFromDca($table, function ($strName, &$arrDca) use ($arrFields) {

            if (!in_array($strName, $arrFields) || !$arrDca['eval']['feEditable']) {
                return false;
            }

            // Map checkboxWizard to regular checkbox widget
            if ($arrDca['inputType'] == 'checkboxWizard') {
                $arrDca['inputType'] = 'checkbox';
            }

            // Special field "country"
            if ($strName == 'country') {
                $arrCountries = array_merge(Isotope::getConfig()->getBillingCountries(), Isotope::getConfig()->getShippingCountries());
                $arrDca['reference'] = $arrDca['options'];
                $arrDca['options'] = array_values(array_intersect(array_keys($arrDca['options']), $arrCountries));
                $arrDca['default'] = Isotope::getConfig()->billing_country;
            }

            return true;
        });

        $objCart = Isotope::getCart();

        // Save the data
        if ($objForm->validate()) {
            $objAddress->pid = FE_USER_LOGGED_IN ? \FrontendUser::getInstance()->id : $objCart->id;
            $objAddress->tstamp = time();
            $objAddress->ptable = FE_USER_LOGGED_IN ? 'tl_member' : ProductCollection::getTable();
            $objAddress->save();

            // Call onsubmit_callback
            if (is_array($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'] as $callback) {
                    $objCallback = \System::importStatic($callback[0]);
                    $objCallback->$callback[1]($objAddress);
                }
            }

            // Set the billing address
            if (in_array('billing', $this->iso_addressTypes)) {
                $objCart->setBillingAddress($objAddress);
            }

            // Set the shipping address
            if (in_array('shipping', $this->iso_addressTypes)) {
                $objCart->setShippingAddress($objAddress);
            }

            $objCart->save();

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
     * @return  \Isotope\Model\Address
     */
    protected function getDefaultAddress()
    {
        $strTable = FE_USER_LOGGED_IN ? 'tl_member' : 'tl_iso_product_collection';
        $intPid = FE_USER_LOGGED_IN ? \FrontendUser::getInstance()->id : Isotope::getCart()->id;

        $objAddress = Address::findOneBy(array('ptable=?', 'pid=?', 'isDefaultBilling=?'), array($strTable, $intPid, '1'));

        if ($objAddress === null) {
            $objCartAddress = in_array('billing', $this->iso_addressTypes) ? Isotope::getCart()->getBillingAddress() : Isotope::getCart()->getShippingAddress();

            if ($objCartAddress === null) {
                $objAddress = new AddressModel();
            } else {
                $objAddress = clone $objCartAddress;
            }

            $objAddress->ptable            = $strTable;
            $objAddress->pid               = $intPid;
            $objAddress->isDefaultBilling  = in_array('billing', $this->iso_addressTypes) ? '1' : '';
            $objAddress->isDefaultShipping = in_array('shipping', $this->iso_addressTypes) ? '1' : '';
        }

        return $objAddress;
    }
}
