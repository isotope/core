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

use Haste\Generator\RowClass;
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection;
use Isotope\Model\Shipping;


class ShippingCalculator extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_shipping_calculator';

    /**
     * Shipping methods
     * @var array
     */
    protected $arrShippingMethods = array();

    /**
     * Shipping address fields
     * @var array
     */
    protected $arrShippingAddressFields = array();

    /**
     * Form
     * @var \Haste\Form\Form
     */
    protected $objForm = null;

    /**
     * Temporary address
     * @var \Isotope\Model\Address
     */
    protected $objTempAddress = null;


    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: SHIPPING CALCULATOR ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->arrShippingMethods = deserialize($this->iso_shipping_modules, true);
        $this->arrShippingAddressFields = deserialize($this->iso_shippingAddressFields, true);


        if (empty($this->arrShippingMethods) || empty($this->arrShippingAddressFields)) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->requiresShipping = true;
        $this->Template->showResults = false;

        $this->createForm();

        if (!$this->objForm->validate()) {
            $this->Template->form = $this->objForm->generate();
            return;
        }

        $this->Template->showResults = true;

        // @todo can we somehow create a temporary address without adding it to the database?
        $this->createTemporaryShippingAddress();
        Isotope::getCart()->setShippingAddress($this->objTempAddress);

        if (!Isotope::getCart()->requiresShipping()) {
            $this->Template->requiresShipping = false;
            $this->Template->noShippingRequiredMsg = $GLOBALS['TL_LANG']['MSC']['noShippingRequiredMsg'];
            $this->Template->form = $this->objForm->generate();
            Isotope::getCart()->setShippingAddress(null);
            return;
        }

        $arrMethods = array();
        $objShippingMethods = Shipping::findMultipleByIds($this->arrShippingMethods);

        /* @var $objShipping Shipping */
        foreach ($objShippingMethods as $objShipping) {
            if ($objShipping->isAvailable()) {

                $fltPrice = $objShipping->getPrice();

                $arrMethods[] = array(
                    'label'             => $objShipping->getLabel(),
                    'price'             => $fltPrice,
                    'formatted_price'   => Isotope::formatPriceWithCurrency($fltPrice),
                    'shipping'          => $objShipping
                );
            }
        }

        if (empty($arrMethods)) {
            $this->Template->msg = $GLOBALS['TL_LANG']['MSC']['noShippingModules'];
        }

        RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo($arrMethods);

        $this->Template->showResults = true;
        $this->Template->availableShippingMethodsMsg = $GLOBALS['TL_LANG']['MSC']['availableShippingMethodsMsg'];
        $this->Template->shippingMethods = $arrMethods;

        // Form
        $this->Template->form = $this->objForm->generate();

        $this->objTempAddress->delete();
        Isotope::getCart()->setShippingAddress(null);
    }

    /**
     * Create form
     */
    protected function createForm()
    {
        \System::loadLanguageFile(Address::getTable());
        $this->loadDataContainer(Address::getTable());

        $this->objForm = new \Haste\Form\Form('iso_shipping_calculator_' . $this->id, 'POST', function($objHaste) {
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });

        foreach (Isotope::getConfig()->getShippingFieldsConfig() as $field) {
            if (!$field['enabled'] || !in_array($field['value'], $this->arrShippingAddressFields)) {
                continue;
            }

            $arrDca = $GLOBALS['TL_DCA'][Address::getTable()]['fields'][$field['value']];
            // override mandatory settings
            $arrDca['eval']['mandatory'] = $field['mandatory'];

            $this->objForm->addFormField($field['value'], $arrDca);
        }

        $this->objForm->addFormField('submit', array(
            'label'     => $GLOBALS['TL_LANG']['MSC']['checkShippingCostsButton'],
            'inputType' => 'submit'
        ));
    }

    /**
     * Create a temptorary shipping address model so shipping methods can check
     * if they are available or not
     */
    protected function createTemporaryShippingAddress()
    {
        if (!$this->objForm->validate()) {
            return;
        }

        $objAddress = new Address();
        $objAddress->pid = Isotope::getCart()->id;
        $objAddress->tstamp = time();
        $objAddress->ptable = ProductCollection::getTable();

        foreach ($this->objForm->fetchAll() as $k => $v) {
            $objAddress->{$k} = $v;
        }

        $objAddress->save();
        $this->objTempAddress = $objAddress;
    }
}
