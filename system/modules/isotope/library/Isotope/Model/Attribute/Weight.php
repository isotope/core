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

use Contao\Database\Result;
use Haste\Units\Mass\WeightAggregate;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\Attribute;

/**
 * Attribute to implement weight formatting
 */
class Weight extends Attribute
{
    /**
     * @inheritdoc
     */
    public function __construct(Result $objResult = null)
    {
        // This class should not be registered
        // Set type or ModelType would throw an exception
        $this->arrData['type'] = 'weight';

        parent::__construct($objResult);
    }

    /**
     * @inheritdoc
     */
    public function getBackendWidget()
    {
        return $GLOBALS['BE_FFL']['timePeriod'];
    }

    /**
     * @inheritdoc
     */
    public function getFrontendWidget()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        if (!$objProduct instanceof WeightAggregate || ($weight = $objProduct->getWeight()) === null) {
            return '';
        }

        return sprintf(
            $arrOptions['format'] ?? '%s %s',
            Isotope::formatPrice($weight->getWeightValue(), false),
            $weight->getWeightUnit()
        );
    }
}
