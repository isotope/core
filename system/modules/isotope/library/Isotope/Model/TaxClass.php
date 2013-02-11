<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Model;

use Isotope\Isotope;

/**
 * TaxRate implements the tax class model.
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class TaxClass extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_tax_class';


    /**
     * Get a property, unserialize appropriate fields
     * @param  string
     * @return mixed
     */
    public function __get($strKey)
    {
        switch ($strKey)
        {
            case 'rates':
                return deserialize($this->arrData[$strKey]);

            case 'label':
                return $this->arrData['label'] ? Isotope::getInstance()->translate($this->arrData['label']) : '';

            default:
                return parent::__get($strKey);
        }
    }
}
