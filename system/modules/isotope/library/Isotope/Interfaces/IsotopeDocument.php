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

namespace Isotope\Interfaces;
use Isotope\Model\Config;


/**
 * Documents print a collection
 */
interface IsotopeDocument
{
    /**
     * Set the collection
     */
    public function setCollection(IsotopeProductCollection $collection);

    /**
     * Set the config
     */
    public function setConfig(Config $config);

    /**
     * Generate the document and send it to browser
     */
    public function printToBrowser();

    /**
     * Generate the document and store it to a given path
     */
    public function store($path);
}