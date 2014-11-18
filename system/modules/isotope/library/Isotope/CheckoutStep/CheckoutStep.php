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

namespace Isotope\CheckoutStep;


abstract class CheckoutStep extends \Controller
{

    /**
     * Checkout module instance
     * @var \Isotope\Module\Checkout
     */
    protected $objModule;

    /**
     * Flag if step has error
     * @var bool
     */
    protected $blnError = false;


    public function __construct(\Isotope\Module\Checkout $objModule)
    {
        parent::__construct();

        $this->objModule = $objModule;
    }

    /**
     * Check if checkout step has an error
     * @return bool
     */
    public function hasError()
    {
        return $this->blnError;
    }

    /**
     * Return short name of current class (e.g. for CSS)
     * @return string
     */
    public function getStepClass()
    {
        $strClass = get_class($this);

        return substr($strClass, strrpos($strClass, '\\') + 1);
    }
}
