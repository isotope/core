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

namespace Isotope\CheckoutStep;


abstract class CheckoutStep extends \Controller
{

    /**
     * Checkout module instance
     * @var Module
     */
    protected $objModule;


    public function __construct($objModule)
    {
        parent::__construct();

        $this->objModule = $objModule;
    }

    /**
     * Return short name of current class (e.g. for CSS)
     * @return  string
     */
    protected function getStepClass()
    {
        $strClass = get_class($this);

        return substr($strClass, strrpos($strClass, '\\')+1);
    }
}
