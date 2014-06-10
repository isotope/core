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
use Isotope\Interfaces\IsotopeAttributeForVariants;
use Isotope\Model\Attribute;


/**
 * Attribute to impelement RadioButton widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class RadioButton extends Attribute implements IsotopeAttribute, IsotopeAttributeWithOptions, IsotopeAttributeForVariants
{

    /**
     * Adjust the options wizard for this attribute
     * @return  array
     */
    public function prepareOptionsWizard($objWidget, $arrColumns)
    {
        unset($arrColumns['group']);

        if ($this->isVariantOption()) {
            unset($arrColumns['default']);
        }

        return $arrColumns;
    }

    /**
     * Set SQL field for this attribute
     * @param   arary
     */
    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        $arrData['fields'][$this->field_name]['sql'] = "varchar(255) NOT NULL default ''";

        if ($this->fe_filter) {
            $arrData['config']['sql']['keys'][$this->field_name] = 'index';
        }
    }
}
