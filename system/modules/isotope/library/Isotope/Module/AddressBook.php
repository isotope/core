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

use Contao\Controller;
use Contao\Environment;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Contao\User;
use Haste\Form\Form;
use Haste\Generator\RowClass;
use Isotope\CompatibilityHelper;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\Config;
use Isotope\Template;
use NotificationCenter\Model\Notification;

/**
 * Front end module Isotope "address book".
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
     *
     * @return string
     */
    public function generate()
    {
        if (CompatibilityHelper::isBackend()) {
            return $this->generateWildcard();
        }

        if (!\Contao\System::getContainer()->get('security.helper')->isGranted('ROLE_MEMBER')) {
            return '';
        }

        $this->arrFields = array_unique(array_merge(Isotope::getConfig()->getBillingFields(), Isotope::getConfig()->getShippingFields()));

        // Return if there are not editable fields
        if (0 === \count($this->arrFields)) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Compile module
     */
    protected function compile()
    {
        $table = Address::getTable();

        System::loadLanguageFile($table);
        Controller::loadDataContainer($table);

        // Call onload_callback (e.g. to check permissions)
        if (\is_array($GLOBALS['TL_DCA'][$table]['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['config']['onload_callback'] as $callback) {
                System::importStatic($callback[0])->{$callback[1]}();
            }
        }

        switch (Input::get('act')) {
            case 'create':
                $this->edit();
                break;

            case 'edit':
                if (\strlen(Input::get('address'))) {
                    $this->edit(Input::get('address'));
                } else {
                    $this->show();
                }
                break;

            case 'delete':
                if (\strlen(Input::get('address'))) {
                    $this->delete(Input::get('address'));
                } else {
                    $this->show();
                }
                break;

            default:
                $this->show();
                break;
        }
    }


    /**
     * List all addresses for the current frontend user
     */
    protected function show()
    {
        /** @var PageModel $objPage */
        global $objPage;

        $arrAddresses = [];
        $strUrl = $objPage->getFrontendUrl();

        /** @var Address[] $objAddresses */
        $objAddresses = Address::findForMember(FrontendUser::getInstance()->id);

        if (null !== $objAddresses) {
            foreach ($objAddresses as $objAddress) {
                $arrAddresses[] = array_merge($objAddress->row(), array(
                    'id'                => $objAddress->id,
                    'class'             => ($objAddress->isDefaultBilling ? 'default_billing' : '') . ($objAddress->isDefaultShipping ? ' default_shipping' : ''),
                    'text'              => $objAddress->generate(),
                    'edit_url'          => \Contao\StringUtil::ampersand($strUrl . '?act=edit&address=' . $objAddress->id),
                    'delete_url'        => \Contao\StringUtil::ampersand($strUrl . '?act=delete&address=' . $objAddress->id),
                    'default_billing'   => $objAddress->isDefaultBilling ? true : false,
                    'default_shipping'  => $objAddress->isDefaultShipping ? true : false,
                ));
            }
        }

        if (0 === \count($arrAddresses)) {
            $this->Template->mtype   = 'empty';
            $this->Template->message = $GLOBALS['TL_LANG']['ERR']['noAddressBookEntries'];
        }

        RowClass::withKey('class')->addFirstLast()->addEvenOdd()->applyTo($arrAddresses);

        $this->Template->addNewAddressLabel   = $GLOBALS['TL_LANG']['MSC']['createNewAddressLabel'];
        $this->Template->editAddressLabel     = $GLOBALS['TL_LANG']['MSC']['editAddressLabel'];
        $this->Template->deleteAddressLabel   = $GLOBALS['TL_LANG']['MSC']['deleteAddressLabel'];
        $this->Template->deleteAddressConfirm = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['deleteAddressConfirm']);
        $this->Template->addresses            = $arrAddresses;
        $this->Template->addNewAddress        = \Contao\StringUtil::ampersand($strUrl . '?act=create');
    }


    /**
     * Edit an address record. Based on the PersonalData core module
     *
     * @param int $intAddressId
     */
    protected function edit($intAddressId = 0)
    {
        $table = Address::getTable();
        System::loadLanguageFile(MemberModel::getTable());

        $this->Template            = new Template($this->memberTpl ?: 'member_default');
        $this->Template->hasError  = false;
        $this->Template->slabel    = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['saveData']);

        if ($intAddressId === 0) {
            $objAddress = Address::createForMember(FrontendUser::getInstance()->id, ['country']);
        } else {
            $objAddress = Address::findOneForMember($intAddressId, FrontendUser::getInstance()->id);
        }

        if (null === $objAddress) {
            /** @var PageModel $objPage */
            global $objPage;

            Controller::redirect($objPage->getAbsoluteUrl());
        }

        $objForm = new Form(
            $table . '_' . $this->id,
            'POST',
            function(Form $objForm) {
                return Input::post('FORM_SUBMIT') === $objForm->getFormId();
            },
            isset($this->tableless) ? (bool) $this->tableless : true
        );

        $objForm->bindModel($objAddress);

        // Add form fields and modify for the address book
        $arrFields = $this->arrFields;
        $objForm->addFieldsFromDca($table, function ($strName, &$arrDca) use ($arrFields) {

            if (!\in_array($strName, $arrFields, true) || !$arrDca['eval']['feEditable']) {
                return false;
            }

            // Map checkboxWizard to regular checkbox widget
            if ('checkboxWizard' === $arrDca['inputType']) {
                $arrDca['inputType'] = 'checkbox';
            }

            // Special field "country"
            if ('country' === $strName) {
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
                if (\is_array($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$table]['config']['onsubmit_callback'] as $callback) {
                        System::importStatic($callback[0])->{$callback[1]}($objAddress);
                    }
                }

                // HOOK: address data has been updated
                if (isset($GLOBALS['ISO_HOOKS']['updateAddressData'])
                    && \is_array($GLOBALS['ISO_HOOKS']['updateAddressData'])
                ) {
                    foreach ($GLOBALS['ISO_HOOKS']['updateAddressData'] as $callback) {
                        System::importStatic($callback[0])->{$callback[1]}($objAddress, $arrOldAddress, $this);
                    }
                }

                // Send notifications
                $this->triggerNotificationCenter($objAddress, $arrOldAddress, FrontendUser::getInstance(), Isotope::getConfig());

                /** @var PageModel $objPage */
                global $objPage;

                Controller::redirect(Controller::generateFrontendUrl($objPage->row()));

            } else {
                $this->Template->hasError = true;
            }
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

        $this->Template->categories     = $categories;
        $this->Template->addressDetails = $GLOBALS['TL_LANG'][$table]['addressDetails'];
        $this->Template->contactDetails = $GLOBALS['TL_LANG'][$table]['contactDetails'];
        $this->Template->personalData   = $GLOBALS['TL_LANG'][$table]['personalData'];
        $this->Template->loginDetails   = $GLOBALS['TL_LANG'][$table]['loginDetails'];
    }

    /**
     * Send a notification when address has been changed
     *
     * @param User    $objMember
     */
    protected function triggerNotificationCenter(Address $objAddress, array $arrOldAddress, $objMember, Config $objConfig)
    {
        if (!$this->nc_notification) {
            return;
        }

        /** @var Notification $objNotification */
        $objNotification = Notification::findByPk($this->nc_notification);

        if (null === $objNotification) {
            return;
        }

        $arrTokens = array();
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];
        $arrTokens['domain'] = Environment::get('host');
        $arrTokens['link'] = Environment::get('base').Environment::get('request');

        foreach ($objAddress->row() as $k => $v) {
            $arrTokens['address_'.$k] = $v;
        }

        foreach ($arrOldAddress as $k => $v) {
            $arrTokens['address_old_' . $k] = $v;
        }

        foreach ($objMember->getData() as $k => $v) {
            $arrTokens['member_' . $k] = $v;
        }

        foreach ($objConfig->row() as $k => $v) {
            $arrTokens['config_' . $k] = $v;
        }

        $objNotification->send($arrTokens);
    }


    /**
     * Delete the given address and make sure it belongs to the current frontend user
     *
     * @param int $intAddressId
     */
    protected function delete($intAddressId)
    {
        if (($objAddress = Address::findOneForMember($intAddressId, FrontendUser::getInstance()->id)) !== null) {
            $objAddress->delete();
        }

        /** @var PageModel $objPage */
        global $objPage;

        Controller::redirect(Controller::generateFrontendUrl($objPage->row()));
    }
}
