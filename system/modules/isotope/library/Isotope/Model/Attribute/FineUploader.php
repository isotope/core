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
 * Attribute to implement terminal42/contao-fineuploader
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class FineUploader extends Attribute implements IsotopeAttribute, \uploadable
{

    /**
     * Upload widget is always customer defined
     * @return    bool
     */
    public function isCustomerDefined()
    {
        return true;
    }

    public function getBackendWidget()
    {
        return false;
    }

    public function saveToDCA(array &$arrData)
    {
        parent::saveToDCA($arrData);

        // An upload field is always customer defined
        $arrData['fields'][$this->field_name]['attributes']['customer_defined'] = true;

        $arrData['fields'][$this->field_name]['save_callback'][] = function ($value) {
            return basename($value);
        };
    }
}
