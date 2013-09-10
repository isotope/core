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

        if ($objForm->validate()) {
            foreach ($objForm->fetchAll() as $strName => $varValue) {
                $this->objModule->arrOrderData['form_' . $strName] = $varValue;

                // @todo file handling?
                // Isotope 1.4.* code was:
                // $this->objModule->arrOrderData['form_' . $name] = \Environment::get('base') . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
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
                    $this->objModule->arrOrderData['form_' . $name] = \Environment::get('base') . str_replace(TL_ROOT . '/', '', dirname($file['tmp_name'])) . '/' . rawurlencode($file['name']);
                }
            }
        }

        return '';
    }
}
