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
 * Attribute to impelement SelectMenu widget
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class SelectMenu extends Attribute implements IsotopeAttribute, IsotopeAttributeWithOptions, IsotopeAttributeForVariants
{

    public function saveToDCA(array &$arrData)
    {
        // Varian select menu cannot have multiple option
        if ($this->isVariantOption()) {
            $this->multiple           = false;
            $this->size               = 1;
            $this->includeBlankOption = true;
        }

        parent::saveToDCA($arrData);

        if ($this->multiple) {
            $arrData['fields'][$this->field_name]['sql'] = "blob NULL";
        } else {
            $arrData['fields'][$this->field_name]['sql'] = "varchar(255) NOT NULL default ''";

            if ($this->fe_filter) {
                $arrData['config']['sql']['keys'][$this->field_name] = 'index';
            }
        }
    }
}
