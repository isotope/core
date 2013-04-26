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


namespace Isotope\CheckoutStep;

use Isotope\Isotope;
use Isotope\Model\Address as AddressModel;

abstract class Address extends CheckoutStep
{

    public function __construct($objModule)
    {
        parent::__construct($objModule);

        \System::loadLanguageFile('tl_iso_addresses');
        $this->loadDataContainer('tl_iso_addresses');
    }


    /**
     * Generate address widget and return it as HTML string
     * @param string
     * @return string
     */
    protected function generateAddressWidget()
    {
        $strBuffer = '';
        $intAddress = 0;
        $arrOptions = $this->getAddressOptions();

        if (!empty($arrOptions)) {

            $strClass = $GLOBALS['TL_FFL']['radio'];

            $arrData = array('id'=>$this->getShortClass(), 'name'=>$this->getShortClass(), 'mandatory'=>true);

            $objWidget = new $strClass($arrData);
            $objWidget->options = $arrOptions;
            $objWidget->value = $intDefaultValue;
            $objWidget->onclick = "Isotope.toggleAddressFields(this, '" . $this->getShortClass() . "_new');";
            $objWidget->storeValues = true;
            $objWidget->tableless = true;

            // Validate input
            if (\Input::post('FORM_SUBMIT') == $this->objModule->getFormId()) {
                $objWidget->validate();

                if ($objWidget->hasErrors()) {
                    $this->objModule->doNotSubmit = true;
                } else {
                    $intAddress = $objWidget->value;
                }
            } elseif ($objWidget->value != '') {
                \Input::setPost($objWidget->name, $objWidget->value);

                $objValidator = clone $objWidget;
                $objValidator->validate();

                if ($objValidator->hasErrors()) {
                    $this->objModule->doNotSubmit = true;
                }
            }

            $strBuffer .= $objWidget->parse();
        }

        if ($intAddress > 0) {
            $objAddress = AddressModel::findByPk($intAddress);

            if (null === $objAddress) {

            }

            $this->setAddress($objAddress);
        }

        $strBuffer .= '<div id="' . $this->getShortClass() . '_new" class="address_new">';
        $strBuffer .= '<span>' . $this->generateAddressWidgets($this->getShortClass(), count($arrOptions)) . '</span>';
        $strBuffer .= '</div>';

        return $strBuffer;
    }


    /**
     * Generate the current step widgets.
     * strResourceTable is used either to load a DCA or else to gather settings related to a given DCA.
     *
     * @todo <table...> was in a template, but I don't get why we need to define the table here?
     * @param string
     * @param integer
     * @return string
     */
    protected function generateAddressWidgets($strAddressType, $intOptions)
    {
        $arrWidgets = array();

        foreach ($this->getAddressFields() as $field) {

            $arrData = $GLOBALS['TL_DCA']['tl_iso_addresses']['fields'][$field['value']];

            if (!is_array($arrData) || !$arrData['eval']['feEditable'] || !$field['enabled'] || ($arrData['eval']['membersOnly'] && FE_USER_LOGGED_IN !== true)) {
                continue;
            }

            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!$this->classFileExists($strClass)) {
                continue;
            }

            // Special field "country"
            if ($field['value'] == 'country') {
                $arrCountries = $this->getAddressCountries();
                $arrData['options'] = array_values(array_intersect($arrData['options'], $arrCountries));

                if ($arrDefault['country'] == '') {
                    $arrDefault['country'] = $this->getDefaultCountry();
                }
            }

            // Special field type "conditionalselect"
            elseif (strlen($arrData['eval']['conditionField'])) {
                $arrData['eval']['conditionField'] = $this->getShortClass() . '_' . $arrData['eval']['conditionField'];
            }

            // Special fields "isDefaultBilling" & "isDefaultShipping"
//            elseif (($field['value'] == 'isDefaultBilling' && $strAddressType == 'billing_address' && $intOptions < 2) || ($field['value'] == 'isDefaultShipping' && $strAddressType == 'shipping_address' && $intOptions < 3))
//            {
//                $arrDefault[$field['value']] = '1';
//            }

//            $objWidget = new $strClass($this->prepareForWidget($arrData, $this->getShortClass() . '_' . $field['value'], (strlen($_SESSION['CHECKOUT_DATA'][$this->getShortClass()][$field['value']]) ? $_SESSION['CHECKOUT_DATA'][$this->getShortClass()][$field['value']] : $arrDefault[$field['value']])));

            $objWidget = new $strClass($this->prepareForWidget($arrData, $this->getShortClass() . '_' . $field['value']));

            $objWidget->mandatory = $field['mandatory'] ? true : false;
            $objWidget->required = $objWidget->mandatory;
            $objWidget->tableless = $this->tableless;
            $objWidget->label = $field['label'] ? Isotope::translate($field['label']) : $objWidget->label;
            $objWidget->storeValues = true;

            // Validate input
            if (\Input::post('FORM_SUBMIT') == $this->objModule->getFormId() && (\Input::post($this->getShortClass()) === '0' || \Input::post($this->getShortClass()) == ''))
            {
                $objWidget->validate();
                $varValue = $objWidget->value;

                // Convert date formats into timestamps
                if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
                {
                    $objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
                    $varValue = $objDate->tstamp;
                }

                // Do not submit if there are errors
                if ($objWidget->hasErrors())
                {
                    $this->objModule->doNotSubmit = true;
                }

                // Store current value
                elseif ($objWidget->submitInput())
                {
                    $arrAddress[$field['value']] = $varValue;
                }
            }
            elseif (\Input::post($this->getShortClass()) === '0' || \Input::post($this->getShortClass()) == '')
            {
                \Input::setPost($objWidget->name, $objWidget->value);

                $objValidator = clone $objWidget;
                $objValidator->validate();

                if ($objValidator->hasErrors())
                {
                    $this->objModule->doNotSubmit = true;
                }
            }

            $arrWidgets[] = $objWidget;
        }

        $arrWidgets = \Isotope\Frontend::generateRowClass($arrWidgets, 'row', 'rowClass', 0, ISO_CLASS_COUNT|ISO_CLASS_FIRSTLAST|ISO_CLASS_EVENODD);

        // Validate input
        if (\Input::post('FORM_SUBMIT') == $this->objModule->getFormId() && !$this->objModule->doNotSubmit && is_array($arrAddress) && !empty($arrAddress))
        {
            $arrAddress['id'] = 0;
            $_SESSION['CHECKOUT_DATA'][$this->getShortClass()] = $arrAddress;
        }

        if (is_array($_SESSION['CHECKOUT_DATA'][$this->getShortClass()]) && $_SESSION['CHECKOUT_DATA'][$this->getShortClass()]['id'] === 0)
        {
            $this->setAddress($_SESSION['CHECKOUT_DATA'][$strAddressType]);
        }

        $strBuffer = '';

        foreach ($arrWidgets as $objWidget)
        {
            $strBuffer .= $objWidget->parse();
        }

        if ($this->tableless)
        {
            return $strBuffer;
        }

        return '
<table>
' . $strBuffer . '
</table>';
    }


    protected function getAddressOptions()
    {
        $arrOptions = array();

        if (FE_USER_LOGGED_IN === true) {
            $arrAddresses =
            $arrCountries = $this->getAddressCountries();

            if (null !== $objAddresses && !empty($arrCountries)) {
                while ($objAddresses->next()) {

                    if (!in_array($objAddresses->country, $arrCountries)) {
                        continue;
                    }

                    $objAddress = $objAddresses->current();

                    $arrOptions[] = array(
                        'value'        => $objAddress->id,
                        'label'        => $objAddress->generateHtml($arrFields),
                    );
                }
            }
        }

        return $arrOptions;
    }


    protected function getAddresses()
    {
        $arrAddresses = array();
        $objAddresses = AddressModel::findForMember($this->User->id, array('order'=>'isDefaultBilling DESC, isDefaultShipping DESC'));

        if (null !== $objAddresses) {
            while ($objAddresses->next()) {
                $arrAddresses[] = $objAddresses->current();
            }
        }

        return $arrAddresses;
    }


    /**
     * Return short name of current class (e.g. for CSS)
     * @return  string
     */
    protected function getShortClass()
    {
        $strClass = get_class($this);

        return substr($strClass, strrpos($strClass, '\\')+1);
    }
}
