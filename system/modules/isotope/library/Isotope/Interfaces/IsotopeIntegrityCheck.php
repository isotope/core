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

namespace Isotope\Interfaces;

/**
 * Documents print a collection
 */
interface IsotopeIntegrityCheck
{

    /**
     * Get a unique ID for each integrity check
     *
     * @return string
     */
    public function getId();

    /**
     * Get a name for the integrity check
     *
     * @return string
     */
    public function getName();

    /**
     * Get a description for the problem
     *
     * @return string
     */
    public function getDescription();

    /**
     * Return true if the issue was found
     *
     * @return bool
     */
    public function hasError();

    /**
     * Return true if this issue can be automatically repaired
     *
     * @return bool
     */
    public function canRepair();

    /**
     * Try to fix the integrity issue
     */
    public function repair();
}
