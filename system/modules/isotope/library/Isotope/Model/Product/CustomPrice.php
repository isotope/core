<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Product;

use Contao\System;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Model\Attribute\TextField;
use Isotope\Model\CustomProductPrice;
use Isotope\Model\ProductType;

/**
 * A product that can have a custom price that is entered in the frontend.
 */
class CustomPrice extends Standard
{
    /**
     * Price attribute name
     * @var string
     */
    protected $priceAttribute = 'customPrice';

    /**
     * @inheritdoc
     *
     * @throws \RuntimeException if model does not have a valid type
     */
    public function __construct(\Database\Result $objResult)
    {
        $GLOBALS['TL_DCA']['tl_iso_product']['attributes'][$this->priceAttribute] = $this->createPriceAttribute();

        parent::__construct($objResult);

        // Remove the price attribute if present
        if (isset($GLOBALS['TL_DCA']['tl_iso_product']['attributes']['price'])) {
            unset($GLOBALS['TL_DCA']['tl_iso_product']['attributes']['price']);
        }
    }

    /**
     * @inheritdoc
     *
     * @return ProductType|null
     */
    public function getType()
    {
        if (($type = parent::getType()) === null) {
            return $type;
        }

        $attributes                        = $type->attributes;
        $attributes[$this->priceAttribute] = [
            'enabled'   => 1,
            'name'      => $this->priceAttribute,
            'mandatory' => true,
        ];

        $type->attributes = $attributes;

        return $type;
    }

    /**
     * Create the price attribute
     *
     * @return TextField
     */
    protected function createPriceAttribute()
    {
        System::loadLanguageFile('tl_iso_attribute');

        $attribute                   = new TextField();
        $attribute->name             = $GLOBALS['TL_LANG']['tl_iso_attribute']['price'][0];
        $attribute->field_name       = $this->priceAttribute;
        $attribute->mandatory        = true;
        $attribute->rgxp             = 'price';
        $attribute->customer_defined = true;
        $attribute->legend           = 'price_legend';

        $attribute->preventSaving(false);
        $attribute->saveToDCA($GLOBALS['TL_DCA']['tl_iso_product']);

        return $attribute;
    }

    /**
     * Get product price model
     *
     * @param IsotopeProductCollection $objCollection
     *
     * @return \Isotope\Interfaces\IsotopePrice
     */
    public function getPrice(IsotopeProductCollection $objCollection = null)
    {
        if ($this->objPrice === false) {
            $options = $this->getOptions();

            $this->objPrice      = new CustomProductPrice();
            $this->objPrice->pid = $this->id;
            $this->objPrice->setPrice($options[$this->priceAttribute]);
            $this->objPrice->preventSaving(false);
        }

        return $this->objPrice;
    }
}
