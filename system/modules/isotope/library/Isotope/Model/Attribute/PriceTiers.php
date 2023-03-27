<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeProduct;
use Isotope\Model\Attribute;

/**
 * Attribute to implement base price calculation
 */
class PriceTiers extends Attribute
{
    /**
     * @inheritdoc
     */
    public function __construct(\Database\Result $objResult = null)
    {
        // This class should not be registered
        // Set type or ModelType would throw an exception
        $this->arrData['type'] = 'pricetiers';

        parent::__construct($objResult);
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $objPrice = $objProduct->getPrice();

        if (null === $objPrice || !$objPrice->hasTiers()) {
            return '';
        }

        $arrTiers = array();

        foreach ($objPrice->current()->getTiers() as $min => $price) {
            $arrTiers[] = array(
                'min'       => $min,
                'price'     => $price,
                'tax_class' => $objPrice->tax_class,
            );
        }

        $order = $arrOptions['order'] ?? '';
        if ($order != '' && \in_array($order, array_keys($arrTiers[0]))) {

            usort($arrTiers, function ($a, $b) use ($order) {
                return strcmp($a[$order], $b[$order]);
            });
        }

        return $this->generateTable($arrTiers, $objProduct);
    }
}
