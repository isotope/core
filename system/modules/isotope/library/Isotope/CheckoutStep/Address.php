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

namespace Isotope\CheckoutStep;

use Haste\Generator\RowClass;
use Isotope\Model\Address as AddressModel;

abstract class Address extends CheckoutStep
{

    /**
     * Cache of address widgets
     * @var array
     */
    private $arrWidgets;

    /**
     * Frontend template instance
     * @var object
     */
    protected $Template;


    /**
     * Load data container and create template
     * @param   object
     */
    public function __construct($objModule)
    {
        parent::__construct($objModule);

        \System::loadLanguageFile(\Isotope\Model\Address::getTable());
        $this->loadDataContainer(\Isotope\Model\Address::getTable());

        $this->Template = new \Isotope\Template('iso_checkout_address');
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $blnValidate = \Input::post('FORM_SUBMIT') == $this->objModule->getFormId();

        $this->Template->class     = $this->getStepClass();
        $this->Template->tableless = $this->objModule->tableless;
        $this->Template->options   = $this->generateOptions($blnValidate);
        $this->Template->fields    = $this->generateFields($blnValidate);

        return $this->Template->parse();
    }

    /**
     * Generate address options and return it as HTML string
     * @param string
     * @return string
     */
    protected function generateOptions($blnValidate = false)
    {
        $strBuffer  = '';
        $varValue   = '0';
        $arrOptions = $this->getAddressOptions();

        if (!empty($arrOptions)) {

            foreach ($arrOptions as $option) {
                if ($option['default']) {
                    $varValue = $option['value'];
                }
            }

            $strClass  = $GLOBALS['TL_FFL']['radio'];

            /** @type \Widget $objWidget */
            $objWidget = new $strClass(array(
                'id'            => $this->getStepClass(),
                'name'          => $this->getStepClass(),
                'mandatory'     => true,
                'options'       => $arrOptions,
                'value'         => $varValue,
                'onclick'       => "Isotope.toggleAddressFields(this, '" . $this->getStepClass() . "_new');",
                'storeValues'   => true,
                'tableless'     => true,
            ));

            // Validate input
            if ($blnValidate) {
                $objWidget->validate();

                if ($objWidget->hasErrors()) {
                    $this->blnError = true;
                } else {
                    $varValue = (string) $objWidget->value;
                }
            } elseif ($objWidget->value != '') {
                \Input::setPost($objWidget->name, $objWidget->value);

                $objValidator = clone $objWidget;
                $objValidator->validate();

                if ($objValidator->hasErrors()) {
                    $this->blnError = true;
                }
            }

            $strBuffer .= $objWidget->parse();
        }

        if ($varValue !== '0') {
            $this->Template->style = 'display:none;';
        }

        $objAddress = $this->getAddressForOption($varValue, $blnValidate);

        /** @type \Model\Registry $objModelRegistry */
        $objModelRegistry = \Model\Registry::getInstance();

        if (null === $objAddress || !$objModelRegistry->isRegistered($objAddress)) {
            $this->blnError = true;
        } elseif ($blnValidate) {
            $this->setAddress($objAddress);
        }

        return $strBuffer;
    }


    /**
     * Generate the current step widgets.
     * @param   bool
     * @return  string|array
     */
    protected function generateFields($blnValidate = false)
    {
        $strBuffer  = '';
        $arrWidgets = $this->getWidgets();

        RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo($arrWidgets);

        foreach ($arrWidgets as $objWidget) {
            $strBuffer .= $objWidget->parse();
        }

        return $strBuffer;
    }

    /**
     * Validate input and return address data
     * @return  array
     */
    protected function validateFields($blnValidate)
    {
        $arrAddress = array();
        $arrWidgets = $this->getWidgets();

        foreach ($arrWidgets as $strName => $objWidget) {
            $arrData = &$GLOBALS['TL_DCA'][\Isotope\Model\Address::getTable()]['fields'][$strName];

            // Validate input
            if ($blnValidate) {

                $objWidget->validate();
                $varValue = $objWidget->value;

                // Convert date formats into timestamps
                if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim'))) {
                    try {
                        $objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
                        $varValue = $objDate->tstamp;
                    } catch (\OutOfBoundsException $e) {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR'][$arrData['eval']['rgxp']], $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']));
                    }
                }

                // Do not submit if there are errors
                if ($objWidget->hasErrors()) {
                    $this->blnError = true;
                } // Store current value
                elseif ($objWidget->submitInput()) {
                    $arrAddress[$strName] = $varValue;
                }

            } else {

                \Input::setPost($objWidget->name, $objWidget->value);

                $objValidator = clone $objWidget;
                $objValidator->validate();

                if ($objValidator->hasErrors()) {
                    $this->blnError = true;
                }
            }
        }

        return $arrAddress;
    }

    /**
     * Get widget objects for address fields
     * @return  \Widget[]
     */
    protected function getWidgets()
    {
        if (null === $this->arrWidgets) {
            $this->arrWidgets = array();
            $objAddress       = $this->getDefaultAddress();

            foreach ($this->getAddressFields() as $field) {

                // Do not use reference, otherwise the billing address fields would affect shipping address fields
                $arrData = $GLOBALS['TL_DCA'][\Isotope\Model\Address::getTable()]['fields'][$field['value']];

                if (!is_array($arrData) || !$arrData['eval']['feEditable'] || !$field['enabled'] || ($arrData['eval']['membersOnly'] && FE_USER_LOGGED_IN !== true)) {
                    continue;
                }

                /** @type \Widget $strClass */
                $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

                // Continue if the class is not defined
                if ($strClass == '' || !class_exists($strClass)) {
                    continue;
                }

                // Special field "country"
                if ($field['value'] == 'country') {
                    $arrCountries = $this->getAddressCountries();
                    $arrData['reference'] = $arrData['options'];
                    $arrData['options'] = array_values(array_intersect(array_keys($arrData['options']), $arrCountries));
                } // Special field type "conditionalselect"
                elseif (strlen($arrData['eval']['conditionField'])) {
                    $arrData['eval']['conditionField'] = $this->getStepClass() . '_' . $arrData['eval']['conditionField'];
                }

                $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $this->getStepClass() . '_' . $field['value'], $objAddress->{$field['value']}));

                $objWidget->mandatory   = $field['mandatory'] ? true : false;
                $objWidget->required    = $objWidget->mandatory;
                $objWidget->tableless   = $this->objModule->tableless;
                $objWidget->storeValues = true;

                $this->arrWidgets[$field['value']] = $objWidget;
            }
        }

        return $this->arrWidgets;
    }

    /**
     * Get options for all addresses in the user's address book
     *
     * @return array
     */
    protected function getAddressOptions()
    {
        $arrOptions = array();

        if (FE_USER_LOGGED_IN === true) {
            $arrAddresses = $this->getAddresses();
            $arrCountries = $this->getAddressCountries();

            if (!empty($arrAddresses) && !empty($arrCountries)) {
                $objDefault = $this->getAddress();

                foreach ($arrAddresses as $objAddress) {

                    if (!in_array($objAddress->country, $arrCountries)) {
                        continue;
                    }

                    $arrOptions[] = array(
                        'value'   => $objAddress->id,
                        'label'   => $objAddress->generateHtml(),
                        'default' => ($objAddress->id == $objDefault->id ? '1' : ''),
                    );
                }
            }
        }

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
        $arrAddresses = $this->getAddresses();

        foreach ($arrAddresses as $objAddress) {
            if ($objAddress->id == $varValue) {
                return $objAddress;
            }
        }

        return null;
    }

    /**
     * Get addresses for the current member
     *
     * @return AddressModel[]
     */
    protected function getAddresses()
    {
        $objAddresses = AddressModel::findForMember(
            \FrontendUser::getInstance()->id,
            array(
                'order' => 'isDefaultBilling DESC, isDefaultShipping DESC'
            )
        );

        return null === $objAddresses ? array() : $objAddresses->getModels();
    }

    /**
     * Get default address for this collection and address type
     *
     * @return AddressModel
     */
    abstract protected function getDefaultAddress();

    /**
     * Get field configuration for this address type
     *
     * @return array
     */
    abstract protected function getAddressFields();

    /**
     * Get allowed countries for this address type
     *
     * @return array
     */
    abstract protected function getAddressCountries();

    /**
     * Get the current address (from Cart) for this address type
     *
     * @return AddressModel
     */
    abstract protected function getAddress();

    /**
     * Set new address in cart
     *
     * @param AddressModel $objAddress
     */
    abstract protected function setAddress(AddressModel $objAddress);
}
