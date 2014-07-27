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

use Haste\Form\Form;
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
        $arrMethods = array();

        $this->Template->showResults = false;
        $this->Template->requiresShipping = false;

        // Temporary address
        $objAddress = new Address();
        $objAddress->pid = Isotope::getCart()->id;
        $objAddress->tstamp = time();
        $objAddress->ptable = ProductCollection::getTable();

        // Create form and bind address to it
        $objForm = $this->createForm();
        $objForm->bindModel($objAddress);

        if ($objForm->validate()) {
            $this->Template->showResults = true;

            // Add it to the cart
            Isotope::getCart()->setShippingAddress($objAddress);

            if (Isotope::getCart()->requiresShipping()) {
                $this->Template->requiresShipping = true;

                $objShippingMethods = Shipping::findMultipleByIds($this->arrShippingMethods);

                /* @var Shipping $objShipping */
                foreach ($objShippingMethods as $objShipping) {
                    if ($objShipping->isAvailable()) {

                        $fltPrice = $objShipping->getPrice();

                        $arrMethods[] = array(
                            'label' => $objShipping->getLabel(),
                            'price' => $fltPrice,
                            'formatted_price' => Isotope::formatPriceWithCurrency($fltPrice),
                            'shipping' => $objShipping
                        );
                    }
                }

                RowClass::withKey('rowClass')->addCount('row_')->addFirstLast('row_')->addEvenOdd('row_')->applyTo(
                    $arrMethods
                );
            }
        }

        $this->Template->shippingMethods = $arrMethods;
        $this->Template->form = $objForm->generate();

        // Reset shipping address
        Isotope::getCart()->setShippingAddress(null);
    }

    /**
     * Create form
     */
    protected function createForm()
    {
        \System::loadLanguageFile(Address::getTable());
        $this->loadDataContainer(Address::getTable());

        $objForm = new Form('iso_shipping_calculator_' . $this->id, 'POST', function($objHaste) {
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });

        foreach (Isotope::getConfig()->getShippingFieldsConfig() as $field) {
            if (!$field['enabled'] || !in_array($field['value'], $this->arrShippingAddressFields)) {
                continue;
            }

            $arrDca = $GLOBALS['TL_DCA'][Address::getTable()]['fields'][$field['value']];
            // override mandatory settings
            $arrDca['eval']['mandatory'] = $field['mandatory'];

            $objForm->addFormField($field['value'], $arrDca);
        }

        $objForm->addFormField('submit', array(
            'label'     => $GLOBALS['TL_LANG']['MSC']['checkShippingCostsButton'],
            'inputType' => 'submit'
        ));

        return $objForm;
    }
}
