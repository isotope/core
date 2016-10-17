<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttributeWithRange;
use Isotope\Model\Attribute;

/**
 * Attribute to implement TextField widget
 */
class TextField extends Attribute implements IsotopeAttributeWithRange
{
    /**
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $maxlength = (int) $this->maxlength ?: 255;

        $arrData['fields'][$this->field_name]['sql'] = "varchar($maxlength) NOT NULL default ''";
    }

    /**
     * Returns whether range filter can be used on this attribute.
     *
     * @return bool
     */
    public function allowRangeFilter()
    {
        return 'digit' === $this->rgxp;
    }
}
