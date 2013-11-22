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

namespace Isotope\Module;

use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Haste\Generator\RowClass;

/**
 * Class ModuleIsotopeAddressBook
 *
 * Front end module Isotope "address book".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class AddressBook extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_addressbook';

    /**
     * Disable caching of the frontend page if this module is in use
     * @var bool
     */
    protected $blnDisableCache = true;

    /**
     * Editable fields
     * @var array
     */
    protected $arrFields;


    /**
     * Return a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ADDRESS BOOK ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if (FE_USER_LOGGED_IN !== true) {
            return '';
        }

        $this->arrFields = array_unique(array_merge(Isotope::getConfig()->getBillingFields(), Isotope::getConfig()->getShippingFields()));

        // Return if there are not editable fields
        if (($count = count($this->arrFields)) == 0 || ($count == 1 && $this->arrFields[0] == '')) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate module
     * @return mixed
     */
    protected function compile()
    {
        $table = \Isotope\Model\Address::getTable();

        \System::loadLanguageFile($table);
        $this->loadDataContainer($table);

        // Call onload_callback (e.g. to check permissions)
        if (is_array($GLOBALS['TL_DCA'][$table]['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['config']['onload_callback'] as $callback) {
                $objCallback = \System::importStatic($callback[0]);
                $objCallback->$callback[1]();
            }
        }

        // Do not add a break statement. If ID is not available, it will show all addresses.
        switch (\Input::get('act'))
        {
            case 'create':
                return $this->edit();

            case 'edit':
                if (strlen(\Input::get('address'))) {
                    return $this->edit(\Input::get('address'));
                }

            case 'delete':
                if (strlen(\Input::get('address'))) {
                    return $this->delete(\Input::get('address'));
                }

            default:
                $this->show();
                break;
        }
    }


    /**
     * List all addresses for the current frontend user
     * @return void
     */
    protected function show()
    {
        global $objPage;
        $arrAddresses = array();
        $strUrl = \Controller::generateFrontendUrl($objPage->row()) . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?');
        $objAddresses = Address::findForMember(\FrontendUser::getInstance()->id);

        if (null !== $objAddresses) {
            while ($objAddresses->next()) {
                $objAddress = $objAddresses->current();

                $arrAddresses[] = array_merge($objAddress->getData(), array(
                    'id'                => $objAddresses->id,
                    'class'             => (($objAddress->isDefaultBilling ? 'default_billing' : '') . ($objAddress->isDefaultShipping ? ' default_shipping' : '')),
                    'text'              => $objAddress->generateHtml(),
                    'edit_url'          => ampersand($strUrl . 'act=edit&address=' . $objAddress->id),
                    'delete_url'        => ampersand($strUrl . 'act=delete&address=' . $objAddress->id),
                    'default_billing'   => ($objAddress->isDefaultBilling ? true : false),
                    'default_shipping'  => ($objAddress->isDefaultShipping ? true : false),
                ));
            }
        }

        if (empty($arrAddresses)) {
            $this->Template->mtype = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'];
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($arrAddresses);

        $this->Template->addNewAddressLabel= $GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'];
        $this->Template->editAddressLabel = $GLOBALS['TL_LANG']['MSC']['editAddressLabel'];
        $this->Template->deleteAddressLabel = $GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'];
        $this->Template->deleteAddressConfirm = specialchars($GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm']);
        $this->Template->addresses = $arrAddresses;
        $this->Template->addNewAddress = ampersand($strUrl . 'act=create');
    }


    /**
     * Edit an address record. Based on the PersonalData core module
     * @param integer
     * @return void
     */
    protected function edit($intAddressId=0)
    {
        $table = \Isotope\Model\Address::getTable();
        \System::loadLanguageFile(\MemberModel::getTable());

        if (!strlen($this->memberTpl)) {
            $this->memberTpl = 'member_default';
        }

        $this->Template = new \Isotope\Template($this->memberTpl);
        $this->Template->fields = '';
        $this->Template->tableless = $this->tableless;

        $arrFields = array();
        $doNotSubmit = false;
        $hasUpload = false;
        $row = 0;

        $objAddress = Address::findOneForMember($intAddressId, \FrontendUser::getInstance()->id);

        if (null === $objAddress) {
            $objAddress = Address::createForMember(\FrontendUser::getInstance()->id);
        }

        // Build form
        foreach ($this->arrFields as $field) {

            // Make the address object look like a Data Container (for the save_callback)
            $objAddress->field = $field;

            // Reference DCA, it's faster to lookup than a deep array
            $arrData = &$GLOBALS['TL_DCA'][$table]['fields'][$field];

            // Map checkboxWizard to regular checkbox widget
            if ($arrData['inputType'] == 'checkboxWizard') {
                $arrData['inputType'] = 'checkbox';
            }

            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!class_exists($strClass) || !$arrData['eval']['feEditable']) {
                continue;
            }

            // Special field "country"
            if ($field == 'country') {
                $arrCountries = array();
                $objConfigs = Config::findBy('store_id', Isotope::getCart()->store_id);

                while ($objConfigs->next()) {
                    $arrCountries = array_merge($arrCountries, $objConfigs->getBillingCountries(), $objConfigs->getShippingCountries());
                }

                $arrData['options'] = array_values(array_intersect($arrData['options'], array_unique($arrCountries)));
                $arrData['default'] = Isotope::getConfig()->billing_country;
            }

            $strGroup = $arrData['eval']['feGroup'];

            $arrData['eval']['tableless'] = $this->tableless;
            $arrData['eval']['required'] = ($objAddress->$field == '' && $arrData['eval']['mandatory']) ? true : false;

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, ($objAddress->$field ? $objAddress->$field : $arrData['default'])));

            $objWidget->storeValues = true;
            $objWidget->rowClass = 'row_'.$row . (($row == 0) ? ' row_first' : '') . ((($row % 2) == 0) ? ' even' : ' odd');

            // Validate input
            if (\Input::post('FORM_SUBMIT') == $table . '_' . $this->id) {

                $objWidget->validate();
                $varValue = $objWidget->value;

                // Convert date formats into timestamps
                if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim'))) {
                    $objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
                    $varValue = $objDate->tstamp;
                }

                // Save callback
                if (is_array($arrData['save_callback'])) {
                    foreach ($arrData['save_callback'] as $callback) {
                        $objCallback = \System::importStatic($callback[0]);

                        try {
                            $varValue = $objCallback->$callback[0]->$callback[1]($varValue, $objAddress);
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                // Do not submit if there are errors
                if ($objWidget->hasErrors()) {
                    $doNotSubmit = true;
                }

                // Store current value
                elseif ($objWidget->submitInput()) {
                    // Set new value
                    $varSave = is_array($varValue) ? serialize($varValue) : $varValue;
                    $objAddress->$field = $varSave;
                }
            }

            if ($objWidget instanceof \uploadable) {
                $hasUpload = true;
            }

            $temp = $objWidget->parse();

            $this->Template->fields .= $temp;
            $arrFields[$strGroup][$field] .= $temp;
            ++$row;
        }

        $this->Template->hasError = $doNotSubmit;

        // Redirect or reload if there was no error
        if (\Input::post('FORM_SUBMIT') == $table . '_' . $this->id && !$doNotSubmit) {

            $objAddress->save();

            // Call onsubmit_callback
            if (is_array($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'] as $callback) {
                    $objCallback = \System::importStatic($callback[0]);
                    $objCallback->$callback[1]($objAddress);
                }
            }

            global $objPage;
            \Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));
        }

        $this->Template->addressDetails = $GLOBALS['TL_LANG'][$table]['addressDetails'];
        $this->Template->contactDetails = $GLOBALS['TL_LANG'][$table]['contactDetails'];
        $this->Template->personalData = $GLOBALS['TL_LANG'][$table]['personalData'];
        $this->Template->loginDetails = $GLOBALS['TL_LANG'][$table]['loginDetails'];

        // Add groups
        foreach ($arrFields as $k=>$v) {
            $this->Template->$k = $v;
        }

        $this->Template->formId = $table . '_' . $this->id;
        $this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);
        $this->Template->action = ampersand(\Environment::get('request'), true);
        $this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $this->Template->rowLast = 'row_' . $row . ((($row % 2) == 0) ? ' even' : ' odd');
    }


    /**
     * Delete the given address and make sure it belongs to the current frontend user
     * @param integer
     * @return void
     */
    protected function delete($intAddressId)
    {
        if (($objAddress = Address::findOneForMember($intAddressId, \FrontendUser::getInstance()->id)) !== null) {
            $objAddress->delete();
        }

        global $objPage;
        \Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));
    }
}
