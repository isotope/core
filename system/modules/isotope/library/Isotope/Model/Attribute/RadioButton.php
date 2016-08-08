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

use Isotope\Interfaces\IsotopeAttributeForVariants;

/**
 * Attribute to impelement RadioButton widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class RadioButton extends AbstractAttributeWithOptions implements IsotopeAttributeForVariants
{
    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function saveToDCA(array &$arrData)
    {
        $this->multiple = false;

        parent::saveToDCA($arrData);

        if ('attribute' === $this->optionsSource) {
            $arrData['fields'][$this->field_name]['sql'] = "varchar(255) NOT NULL default ''";
        } else {
            $arrData['fields'][$this->field_name]['sql'] = "int(10) NOT NULL default '0'";
        }

        if ($this->fe_filter) {
            $arrData['config']['sql']['keys'][$this->field_name] = 'index';
        }
    }
}
