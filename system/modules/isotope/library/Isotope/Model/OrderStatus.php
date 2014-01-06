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

namespace Isotope\Model;

use Isotope\Translation;


/**
 * ProductType defines a product configuration
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 */
class OrderStatus extends \Model
{

    /**
     * Name of the current table
     * @var string
     */
    protected static $strTable = 'tl_iso_orderstatus';

    /**
     * Return if the order status means a collection has been paid (configuration flag)
     * @return  bool
     */
    public function isPaid()
    {
        return (bool) $this->paid;
    }

    /**
     * Return the localized order status name
     * @return  string
     */
    public function getName()
    {
        return Translation::get($this->name);
    }

    /**
     * Return the localized order status name
     * Do not use $this->getName(), the alias should not be localized
     * @return  string
     */
    public function getAlias()
    {
        return standardize($this->name);
    }
}
