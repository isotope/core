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

namespace Isotope\Model\Attribute;

use Isotope\Interfaces\IsotopeAttribute;
use Isotope\Model\Attribute;

/**
 * Attribute to implement TextField widget
 */
class TextField extends Attribute implements IsotopeAttribute
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
}
