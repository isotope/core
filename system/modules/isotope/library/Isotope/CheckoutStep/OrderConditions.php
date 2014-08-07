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

use Haste\Form\Form;
use Isotope\Interfaces\IsotopeProductCollection;


abstract class OrderConditions extends CheckoutStep
{

    /**
     * Haste form
     * @var \Haste\Form\Form
     */
    protected $objForm;

    /**
     * Returns true if order conditions are defined
     * @return  bool
     */
    public function isAvailable()
    {
        return true;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $this->objForm = new Form($this->objModule->getFormId(), 'POST', function ($haste) {
            return \Input::post('FORM_SUBMIT') === $haste->getFormId();
        });


        if ($this->objModule->iso_order_conditions) {
            $objFormConfig = \FormModel::findByPk($this->objModule->iso_order_conditions);

            if (null === $objFormConfig) {
                throw new \InvalidArgumentException('Order condition form "' . $this->objModule->iso_order_conditions . '" not found.');
            }

            $this->objForm->setTableless($objFormConfig->tableless);

            $this->objForm->addFieldsFromFormGenerator(
                $this->objModule->iso_order_conditions,
                function ($strName, &$arrDca) {
                    $arrDca['value'] = $_SESSION['FORM_DATA'][$strName] ?: $arrDca['value'];

                    return true;
                }
            );
        }

        if (!empty($GLOBALS['ISO_HOOKS']['orderConditions']) && is_array($GLOBALS['ISO_HOOKS']['orderConditions'])) {
            foreach ($GLOBALS['ISO_HOOKS']['orderConditions'] as $callback) {
                \System::importStatic($callback[0])->{$callback[1]}($this->objForm, $this->objModule);
            }
        }

        if (!$this->objForm->hasFields()) {
            $this->blnError = false;
            return '';
        }

        // Change enctype if there are uploads
        if ($this->objForm->hasUploads()) {
            $this->objModule->Template->enctype = 'multipart/form-data';
        }

        if ($this->objForm->isSubmitted()) {
            $this->blnError = !$this->objForm->validate();

            $_SESSION['FORM_DATA'] = is_array($_SESSION['FORM_DATA']) ? $_SESSION['FORM_DATA'] : array();
            foreach (array_keys($this->objForm->getFormFields()) as $strField) {
                if ($this->objForm->getWidget($strField) instanceof \uploadable) {
                    $arrFile  = $_SESSION['FILES'][$strField];
                    $varValue = str_replace(TL_ROOT . '/', '', dirname($arrFile['tmp_name'])) . '/' . rawurlencode($arrFile['name']);
                } else {
                    $varValue = $this->objForm->fetch($strField);
                }

                $_SESSION['FORM_DATA'][$strField] = $varValue;
            }

        } else {
            $blnError = false;
            foreach (array_keys($this->objForm->getFormFields()) as $strField) {

                // Clone widget because otherwise we add errors to the original widget instance
                $objClone = clone $this->objForm->getWidget($strField);
                $objClone->validate();

                if ($objClone->hasErrors()) {
                    $blnError = true;
                    break;
                }
            }

            $this->blnError = $blnError;
        }

        $objTemplate = new \Isotope\Template('iso_checkout_order_conditions');
        $this->objForm->addToTemplate($objTemplate);

        return $objTemplate->parse();
    }

    /**
     * Return review information for last page of checkout
     * @return  string
     */
    public function review()
    {
        return '';
    }

    /**
     * Return array of tokens for notification
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getNotificationTokens(IsotopeProductCollection $objCollection)
    {
        $arrTokens = array();

        foreach (array_keys($this->objForm->getFormFields()) as $strField) {
            if (isset($_SESSION['FORM_DATA'][$strField])) {
                $arrTokens['form_' . $strField] = $_SESSION['FORM_DATA'][$strField];
            }
        }

        return $arrTokens;
    }
}
