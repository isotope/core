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

use Contao\Controller;
use Contao\Date;
use Contao\FrontendUser;
use Contao\Input;
use Contao\System;
use Contao\Widget;
use Haste\Generator\RowClass;
use Isotope\Model\Address as AddressModel;
use Isotope\Module\Checkout;
use Isotope\Template;
use Contao\Model\Registry;

abstract class Address extends CheckoutStep
{

    /**
     * Cache of address widgets
     * @var array
     */
    private $arrWidgets;

    /**
     * Frontend template instance
     * @var Template|\stdClass
     */
    protected $Template;

    /**
     * Load data container and create template
     */
    public function __construct(Checkout $objModule)
    {
        parent::__construct($objModule);

        System::loadLanguageFile(AddressModel::getTable());
        Controller::loadDataContainer(AddressModel::getTable());

        $this->Template = new Template('iso_checkout_address');
    }

    /**
     * Generate the checkout step
     *
     * @return string
     */
    public function generate()
    {
        $blnValidate = Input::post('FORM_SUBMIT') === $this->objModule->getFormId();

        $this->Template->class     = $this->getStepClass();
        $this->Template->tableless = $this->objModule->tableless ?? true;
        $this->Template->options   = $this->generateOptions($blnValidate);
        $this->Template->fields    = $this->generateFields($blnValidate);

        return $this->Template->parse();
    }

    /**
     * Generate address options and return it as HTML string
     *
     * @param bool $blnValidate
     *
     * @return string
     */
    protected function generateOptions($blnValidate = false)
    {
        $strBuffer  = '';
        $varValue   = '0';
        $arrOptions = $this->getAddressOptions();

        if (0 !== \count($arrOptions)) {
            foreach ($arrOptions as $option) {
                if ($option['default']) {
                    $varValue = $option['value'];
                }
            }

            $strClass  = $GLOBALS['TL_FFL']['radio'];

            /** @var Widget $objWidget */
            $objWidget = new $strClass(
                [
                    'id'          => $this->getStepClass(),
                    'name'        => $this->getStepClass(),
                    'mandatory'   => true,
                    'options'     => $arrOptions,
                    'value'       => $varValue,
                    'onclick'     => "Isotope.toggleAddressFields(this, '" . $this->getStepClass() . "_new');",
                    'storeValues' => true,
                    'tableless'   => true,
                ]
            );

            // Validate input
            if ($blnValidate) {
                $objWidget->validate();

                if ($objWidget->hasErrors()) {
                    $this->blnError = true;
                } else {
                    $varValue = (string) $objWidget->value;
                }
            } elseif ($objWidget->value != '') {
                Input::setPost($objWidget->name, $objWidget->value);

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

        if (null === $objAddress || !Registry::getInstance()->isRegistered($objAddress)) {
            $this->blnError = true;
        }  elseif ($blnValidate) {
            $this->setAddress($objAddress);
        }

        return $strBuffer;
    }

    /**
     * Generate the current step widgets.
     *
     * @param bool $blnValidate
     *
     * @return string|array
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
     *
     * @param bool $blnValidate
     *
     * @return array
     */
    protected function validateFields($blnValidate)
    {
        $arrAddress = array();
        $arrWidgets = $this->getWidgets();

        foreach ($arrWidgets as $strName => $objWidget) {

            // Validate input
            if ($blnValidate) {

                $objWidget->validate();
                $varValue = (string) $objWidget->value;

                // Convert date formats into timestamps
                if ('' !== $varValue && \in_array(($objWidget->dca_config['eval']['rgxp'] ?? null), array('date', 'time', 'datim'), true)) {
                    try {
                        $objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$objWidget->dca_config['eval']['rgxp'] . 'Format']);
                        $varValue = $objDate->tstamp;
                    } catch (\OutOfBoundsException $e) {
                        $objWidget->addError(
                            sprintf(
                                $GLOBALS['TL_LANG']['ERR'][$objWidget->dca_config['eval']['rgxp']],
                                $GLOBALS['TL_CONFIG'][$objWidget->dca_config['eval']['rgxp'] . 'Format']
                            )
                        );
                    }
                }

                if (\is_array($objWidget->dca_config['save_callback'] ?? null) && $objWidget->submitInput() && !$objWidget->hasErrors()) {
                    foreach ($objWidget->dca_config['save_callback'] as $callback) {
                        try {
                            if (\is_array($callback)) {
                                $this->import($callback[0]);
                                $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, $this);
                            } elseif (\is_callable($callback)) {
                                $varValue = $callback($varValue, $this);
                            }
                        } catch (\Exception $e) {
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                // Do not submit if there are errors
                if ($objWidget->hasErrors()) {
                    $this->blnError = true;
                } elseif ($objWidget->submitInput()) {
                    $arrAddress[$strName] = $varValue;
                }

            } else {

                Input::setPost($objWidget->name, $objWidget->value);

                $objValidator = clone $objWidget;
                $objValidator->validate();
                $varValue = (string) $objWidget->value;

                if (\is_array($objWidget->dca_config['save_callback'] ?? null) && $objWidget->submitInput() && !$objWidget->hasErrors()) {
                    foreach ($objWidget->dca_config['save_callback'] as $callback) {
                        try {
                            if (\is_array($callback)) {
                                $this->import($callback[0]);
                                $varValue = System::importStatic($callback[0])->{$callback[1]}($varValue, $this);
                            } elseif (\is_callable($callback)) {
                                $varValue = $callback($varValue, $this);
                            }
                        } catch (\Exception $e) {
                            $this->blnError = true;
                        }
                    }
                }

                if ($objValidator->hasErrors()) {
                    $this->blnError = true;
                }
            }
        }

        return $arrAddress;
    }

    /**
     * Get widget objects for address fields
     * @return  Widget[]
     */
    protected function getWidgets()
    {
        if (null === $this->arrWidgets) {
            $this->arrWidgets = array();
            $objAddress       = $this->getDefaultAddress();
            $arrFields        = $this->mergeFieldsWithDca($this->getAddressFields());

            // !HOOK: modify address fields in checkout process
            if (isset($GLOBALS['ISO_HOOKS']['modifyAddressFields'])
                && \is_array($GLOBALS['ISO_HOOKS']['modifyAddressFields'])
            ) {
                foreach ($GLOBALS['ISO_HOOKS']['modifyAddressFields'] as $callback) {
                    $arrFields = System::importStatic($callback[0])->{$callback[1]}($arrFields, $objAddress, $this->getStepClass());
                }
            }

            foreach ($arrFields as $field) {

                if (!\is_array($field['dca'])
                    || !($field['enabled'] ?? null)
                    || !($field['dca']['eval']['feEditable'] ?? null)
                    || (($field['dca']['eval']['membersOnly'] ?? null) && FE_USER_LOGGED_IN !== true)
                ) {
                    continue;
                }

                // Continue if the class is not defined
                if (!\array_key_exists($field['dca']['inputType'], $GLOBALS['TL_FFL'])
                    || !class_exists($GLOBALS['TL_FFL'][$field['dca']['inputType']])
                ) {
                    continue;
                }

                /** @var Widget $strClass */
                $strClass = $GLOBALS['TL_FFL'][$field['dca']['inputType']];

                if ('country' === $field['value']) {
                    // Special field "country"
                    $arrCountries = $this->getAddressCountries();
                    $field['dca']['reference'] = $field['dca']['options'];
                    $field['dca']['options'] = array_values(array_intersect(array_keys($field['dca']['options']), $arrCountries));
                } elseif (!empty($field['dca']['eval']['conditionField'])) {
                    // Special field type "conditionalselect"
                    $field['dca']['eval']['conditionField'] = $this->getStepClass() . '_' . $field['dca']['eval']['conditionField'];
                }

                $objWidget = new $strClass(
                    $strClass::getAttributesFromDca(
                        $field['dca'],
                        $this->getStepClass() . '_' . $field['value'],
                        $objAddress->{$field['value']}
                    )
                );

                $objWidget->mandatory   = $field['mandatory'] ? true : false;
                $objWidget->required    = $objWidget->mandatory;
                $objWidget->tableless   = $this->objModule->tableless ?? true;
                $objWidget->storeValues = true;
                $objWidget->dca_config  = $field['dca'];

                $this->arrWidgets[$field['value']] = $objWidget;
            }
        }

        return $this->arrWidgets;
    }

    /**
     * Get options for all addresses in the user's address book
     *
     * @param array $arrFields
     *
     * @return array
     */
    protected function getAddressOptions($arrFields = null)
    {
        $arrOptions = array();

        if (FE_USER_LOGGED_IN === true) {

            /** @var AddressModel[] $arrAddresses */
            $arrAddresses = $this->getAddresses();
            $arrCountries = $this->getAddressCountries();

            if (0 !== \count($arrAddresses) && 0 !== \count($arrCountries)) {
                $objDefault = $this->getAddress();

                foreach ($arrAddresses as $objAddress) {

                    if (!\in_array($objAddress->country, $arrCountries, true)) {
                        continue;
                    }

                    $arrOptions[] = [
                        'value'   => $objAddress->id,
                        'label'   => $objAddress->generate($arrFields),
                        'default' => $objAddress->id == ($objDefault->id ?? false) ? '1' : '',
                    ];
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
            FrontendUser::getInstance()->id,
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
     */
    abstract protected function setAddress(AddressModel $objAddress);

    /**
     * Append DCA configuration to fields so it can be changed in hook.
     *
     *
     * @return array
     */
    private function mergeFieldsWithDca(array $fieldConfig)
    {
        $fields = [];

        foreach ($fieldConfig as $field) {
            // Do not use reference, otherwise the billing address fields would affect shipping address fields
            $dca = $GLOBALS['TL_DCA'][AddressModel::getTable()]['fields'][$field['value']];

            if (\is_array($dca)) {
                $field['dca'] = $dca;
            }

            $fields[$field['value']] = $field;
        }

        return $fields;
    }
}
