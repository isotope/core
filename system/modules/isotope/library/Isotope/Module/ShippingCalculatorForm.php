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
use Isotope\Isotope;
use Isotope\Model\Address;
use Isotope\Model\ProductCollection;


class ShippingCalculatorForm extends Module
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_iso_shipping_calculator_form';

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

            $objTemplate->wildcard = '### ISOTOPE ECOMMERCE: SHIPPING CALCULATOR FORM ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id    = $this->id;
            $objTemplate->link  = $this->name;
            $objTemplate->href  = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->arrShippingAddressFields = deserialize($this->iso_shippingAddressFields, true);

        if (empty($this->arrShippingAddressFields)) {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $objCart = Isotope::getCart();
        $this->Template->noCart = false;

        // There is no cart initialized
        if (!$objCart->id) {
            $this->Template->noCart = true;
            return;
        }

        \System::loadLanguageFile(Address::getTable());
        $this->loadDataContainer(Address::getTable());

        $objAddress = $objCart->getShippingAddress();

        $objForm = new Form('iso_shipping_calculator_form_' . $this->id, 'POST', function($objHaste) {
            return \Input::post('FORM_SUBMIT') === $objHaste->getFormId();
        });

        $objForm->bindModel($objAddress);

        foreach (Isotope::getConfig()->getShippingFieldsConfig() as $field) {
            if (!$field['enabled'] || !in_array($field['value'], $this->arrShippingAddressFields)) {
                continue;
            }

            $arrDca = $GLOBALS['TL_DCA'][Address::getTable()]['fields'][$field['value']];
            $arrDca['eval']['mandatory'] = $field['mandatory'];

            $objForm->addFormField($field['value'], $arrDca);
        }

        $objForm->addFormField('submit', array(
            'label'     => $GLOBALS['TL_LANG']['MSC']['saveAddressButton'],
            'inputType' => 'submit'
        ));

        // Save the data
        if ($objForm->validate()) {
            $objAddress->pid = $objCart->id;
            $objAddress->tstamp = time();
            $objAddress->ptable = ProductCollection::getTable();
            $objAddress->save();

            $objCart->setShippingAddress($objAddress);
            $objCart->save();

            $this->jumpToOrReload($this->jumpTo);
        }

        $this->Template->form = $objForm->generate();
    }
}
