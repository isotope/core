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
        $objForm = new Form($this->objModule->getFormId(), 'POST', function($haste) {
            return \Input::post('FORM_SUBMIT') === $haste->getFormId();
        }, (boolean) $this->objModule->tableless);

        // don't catch the exception here because we want it to be shown to the user
        $objForm->addFieldsFromFormGenerator($this->objModule->iso_order_conditions);

        // Manually create widgets because we need to know if there are uploadable widgets
        $objForm->createWidgets();

        // change enctype if there are uploads
        if ($objForm->hasUploads()) {
            $this->objModule->Template->enctype = 'multipart/form-data';
        }

        if ($objForm->isSubmitted() && $objForm->validate()) {
            foreach ($objForm->fetchAll() as $strName => $varValue) {
                if ($objForm->getWidget($strName) instanceof \uploadable) {
                    $arrFile = $_SESSION['FILES'][$strName];
                    $varValue = str_replace(TL_ROOT . '/', '', dirname($arrFile['tmp_name'])) . '/' . rawurlencode($arrFile['name']);
                }

                $this->objModule->arrOrderData['form_' . $strName] = $varValue;
            }
        } else {
            $this->blnError = true;
        }

        $objTemplate = new \Isotope\Template('iso_checkout_order_conditions');
        $objForm->addToTemplate($objTemplate);
        return $objTemplate->parse();
    }

    /**
     * Return review information for last page of checkout
     * @return  string
     */
    public function review()
    {
        if (!$this->hasError())
        {
            if (is_array($_SESSION['FORM_DATA']))
            {
                foreach( $_SESSION['FORM_DATA'] as $name => $value )
                {
                    $this->objModule->arrOrderData['form_' . $name] = $value;
                }
            }

            if (is_array($_SESSION['FILES']))
            {
                foreach( $_SESSION['FILES'] as $name => $file )
                {
                    $this->objModule->arrOrderData['form_' . $name] = str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
                }
            }
        }

        return '';
    }

    /**
     * Return array of tokens for email templates
     * @param   IsotopeProductCollection
     * @param   \Module
     * @return  array
     */
    public function getEmailTokens(IsotopeProductCollection $objCollection, \Module $objModule)
    {
        // @todo return form field values
        /*
        foreach ($objForm->arrFormData as $name => $value)
        {
            $this->objModule->arrOrderData['form_' . $name] = $value;
        }

        foreach ($objForm->arrFiles as $name => $file)
        {
            $this->objModule->arrOrderData['form_' . $name] = \Environment::get('base') . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
        }
        */

        return array();
    }
}
