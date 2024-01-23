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

use Contao\StringUtil;
use Isotope\Interfaces\IsotopeProduct;
use Isotope\Isotope;
use Isotope\Model\Attribute;

/**
 * Attribute to implement base price calculation
 */
class BasePrice extends Attribute
{
    /**
     * @inheritdoc
     */
    public function __construct($objResult = null)
    {
        // This class should not be registered
        // Set type or ModelType would throw an exception
        $this->arrData['type'] = 'baseprice';

        parent::__construct($objResult);
    }

    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "text NULL";
    }

    /**
     * @inheritdoc
     */
    public function generate(IsotopeProduct $objProduct, array $arrOptions = array())
    {
        $arrData = StringUtil::deserialize($objProduct->{$this->field_name});

        if (\is_array($arrData) && $arrData['unit'] > 0 && $arrData['value'] != '') {
            $objBasePrice = \Isotope\Model\BasePrice::findByPk((int) $arrData['unit']);

            if (null !== $objBasePrice && null !== $objProduct->getPrice()) {
                return sprintf($objBasePrice->getLabel(), Isotope::formatPriceWithCurrency($objProduct->getPrice()->getAmount() / $arrData['value'] * $objBasePrice->amount), $arrData['value']);
            }
        }

        return '';
    }
}
