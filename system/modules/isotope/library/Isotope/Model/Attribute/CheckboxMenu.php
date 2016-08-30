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
use Isotope\Interfaces\IsotopeProduct;


/**
 * Attribute to implement CheckboxMenu widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class CheckboxMenu extends AbstractAttributeWithOptions implements IsotopeAttribute
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

        if ($this->multiple) {
            $arrData['fields'][$this->field_name]['sql'] = "blob NULL";
        } else {
            $arrData['fields'][$this->field_name]['sql'] = "char(1) NOT NULL default ''";
        }
    }

    /**
     * Get options of attribute from database
     *
     * @param IsotopeProduct $objProduct
     *
     * @return array|mixed
     *
     * @throws \InvalidArgumentException when optionsSource=product but product is null
     * @throws \UnexpectedValueException for unknown optionsSource
     */
    public function getOptionsForWidget(IsotopeProduct $objProduct = null)
    {
        $options = parent::getOptionsForWidget($objProduct);

        // Make sure that the option value for single checkbox in backend is always "1" (#1658)
        if (!$this->isVariantOption() && !$this->isCustomerDefined() && !$this->multiple) {
            $options[0]['value'] = 1;
        }

        return $options;
    }
}
