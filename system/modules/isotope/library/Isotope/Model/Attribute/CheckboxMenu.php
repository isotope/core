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
use Isotope\Interfaces\IsotopeAttributeWithOptions;
use Isotope\Model\Attribute;


/**
 * Attribute to implement CheckboxMenu widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class CheckboxMenu extends Attribute implements IsotopeAttribute, IsotopeAttributeWithOptions
{

    /**
     * Adjust the options wizard for this attribute
     * @return  array
     */
    public function prepareOptionsWizard($objWidget, $arrColumns)
    {
        unset($arrColumns['group']);

        return $arrColumns;
    }

    /**
     * Set SQL field for this attribute
     * @param   array
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "blob NULL";
    }
}
