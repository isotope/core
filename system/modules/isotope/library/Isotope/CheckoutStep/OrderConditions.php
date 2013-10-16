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

use Isotope\Interfaces\IsotopeProductCollection;
use Haste\Form;


abstract class OrderConditions extends CheckoutStep
{

    /**
     * Haste form
     * @var object
     */
    protected $objForm;

    /**
     * Returns true if order conditions are defined
     * @return  bool
     */
    public function isAvailable()
    {
        return (boolean) $this->objModule->iso_order_conditions;
    }

    /**
     * Generate the checkout step
     * @return  string
     */
    public function generate()
    {
        $this->objForm = new Form($this->objModule->getFormId(), 'POST', function($haste) {
            return \Input::post('FORM_SUBMIT') === $haste->getFormId();
        }, (boolean) $this->objModule->tableless);

        // Don't catch the exception here because we want it to be shown to the user
        $this->objForm->addFieldsFromFormGenerator($this->objModule->iso_order_conditions);

        // Manually create widgets because we need to know if there are uploadable widgets
        $this->objForm->createWidgets();

        // Change enctype if there are uploads
        if ($this->objForm->hasUploads()) {
            $this->objModule->Template->enctype = 'multipart/form-data';
        }

        if ($this->objForm->isSubmitted()) {
            $this->blnError = $this->objForm->validate();
        } else {
            $blnError = false;
            foreach (array_keys($this->objForm->getFormFields()) as $strField) {
                // Clone widget because otherwise we add errors to the original widget instance
                $objClone = clone $this->objForm->getWidget($strField);
                if (!$objClone->validate()) {
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
            if ($this->objForm->isSubmitted()) {
                if ($this->objForm->getWidget($strField) instanceof \uploadable) {
                    $arrFile = $_SESSION['FILES'][$strField];
                    $varValue = str_replace(TL_ROOT . '/', '', dirname($arrFile['tmp_name'])) . '/' . rawurlencode($arrFile['name']);
                } else {
                    $varValue = $this->objForm->fetch($strField);
                }

                $_SESSION['FORM_DATA'][$strField] = $varValue;
                $arrTokens['form_' . $strField]   = $varValue;
            } else {
                if (isset($_SESSION['FORM_DATA'][$strField])) {
                    $arrTokens['form_' . $strField]   = $_SESSION['FORM_DATA'][$strField];
                }
            }
        }

        return $arrTokens;
    }
}
