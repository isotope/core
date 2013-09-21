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

        // don't catch the exception here because we want it to be shown to the user
        $this->objForm->addFieldsFromFormGenerator($this->objModule->iso_order_conditions);

        // Manually create widgets because we need to know if there are uploadable widgets
        $this->objForm->createWidgets();

        // change enctype if there are uploads
        if ($this->objForm->hasUploads()) {
            $this->objModule->Template->enctype = 'multipart/form-data';
        }

        if (!$this->objForm->isSubmitted() || !$this->objForm->validate()) {
            $this->blnError = true;
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
     * Return array of tokens for email templates
     * @param   IsotopeProductCollection
     * @return  array
     */
    public function getEmailTokens(IsotopeProductCollection $objCollection)
    {
        $arrTokens = array();

        foreach ($this->objForm->fetchAll() as $strName => $varValue) {
            if ($this->objForm->getWidget($strName) instanceof \uploadable) {
                $arrFile = $_SESSION['FILES'][$strName];
                $varValue = str_replace(TL_ROOT . '/', '', dirname($arrFile['tmp_name'])) . '/' . rawurlencode($arrFile['name']);
            }

            $arrTokens['form_' . $strName] = $varValue;
        }

        return $arrTokens;
    }
}
