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
use Haste\Generator\RowClass;
use Isotope\Isotope;
use Isotope\Model\Address;

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
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: ADDRESS BOOK ###';
            $objTemplate->title    = $this->headline;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        if (FE_USER_LOGGED_IN !== true) {
            return '';
        }

        $this->arrFields = array_unique(array_merge(Isotope::getConfig()->getBillingFields(), Isotope::getConfig()->getShippingFields()));

        // Return if there are not editable fields
        if (empty($this->arrFields)) {
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

        // Do not add a break statement. If ID is not available, it will show all addresses.
        switch (\Input::get('act')) {
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
        $strUrl       = \Controller::generateFrontendUrl($objPage->row()) . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?');
        $objAddresses = Address::findForMember(\FrontendUser::getInstance()->id);

        if (null !== $objAddresses) {
            while ($objAddresses->next()) {
                $objAddress = $objAddresses->current();

                $arrAddresses[] = array_merge($objAddress->row(), array(
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
            $this->Template->mtype   = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'];
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($arrAddresses);

        $this->Template->addNewAddressLabel   = $GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'];
        $this->Template->editAddressLabel     = $GLOBALS['TL_LANG']['MSC']['editAddressLabel'];
        $this->Template->deleteAddressLabel   = $GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'];
        $this->Template->deleteAddressConfirm = specialchars($GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm']);
        $this->Template->addresses            = $arrAddresses;
        $this->Template->addNewAddress        = ampersand($strUrl . 'act=create');
    }


    /**
     * Edit an address record. Based on the PersonalData core module
     * @param integer
     * @return void
     */
    protected function edit($intAddressId = 0)
    {
        $table = Address::getTable();
        \System::loadLanguageFile(\MemberModel::getTable());

        $this->Template            = new \Isotope\Template($this->memberTpl);
        $this->Template->hasError  = false;
        $this->Template->slabel    = specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);

        if ($intAddressId === 0) {
            $objAddress = Address::createForMember(\FrontendUser::getInstance()->id);
        } else {
            $objAddress = Address::findOneForMember($intAddressId, \FrontendUser::getInstance()->id);
        }

        if (null === $objAddress) {
            global $objPage;
            \Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));
        }

        $objForm = new Form($table . '_' . $this->id, 'POST', function($objForm) {
            return \Input::post('FORM_SUBMIT') === $objForm->getFormId();
        }, (boolean) $this->tableless);

        $objForm->bindModel($objAddress);

        // Add form fields and modify for the address book
        $arrFields = $this->arrFields;
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

        if ($objForm->isSubmitted()) {
            $arrOldAddress = $objAddress->row();
            if ($objForm->validate()) {
                $objAddress->save();

                // Call onsubmit_callback
                if (is_array($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'] as $callback) {
                        $objCallback = \System::importStatic($callback[0]);
                        $objCallback->$callback[1]($objAddress);
                    }
                }

                // Send notifications
                $this->triggerNotificationCenter($objAddress, $arrOldAddress, \FrontendUser::getInstance(), Isotope::getConfig());

                global $objPage;
                \Controller::redirect(\Controller::generateFrontendUrl($objPage->row()));

            } else {
                $this->Template->hasError = true;
            }
        }

        $objForm->addToTemplate($this->Template);

        // Add groups
        $arrGroups   = array();
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


    protected function triggerNotificationCenter($objAddress, $arrOldAddress, $objMember, $objConfig)
    {
        if (!$this->nc_notification) {
            return;
        }

        $arrTokens = array();
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrTokens['domain'] = \Environment::get('host');
        $arrTokens['link'] = \Environment::get('base').\Environment::get('request');

        foreach($objAddress->row() as $k => $v) $arrTokens['address_'.$k] = $v;
        foreach($arrOldAddress as $k => $v) $arrTokens['address_old_'.$k] = $v;
        foreach($objMember->getData() as $k => $v) $arrTokens['member_'.$k] = $v;
        foreach($objConfig->row() as $k => $v) $arrTokens['config_'.$k] = $v;

        $objNotification->send($arrTokens);
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
