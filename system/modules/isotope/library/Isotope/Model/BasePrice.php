<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Model;

use Contao\Model;
use Isotope\Translation;


/**
 * ProductType defines a product configuration
 */
class BasePrice extends Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_baseprice';


    /**
     * Get label
     * @return  string
     */
    public function getLabel()
    {
        return $this->label ? Translation::get($this->label) : '';
    }
}
