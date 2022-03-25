<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CheckoutStep;

use Contao\Controller;
use Contao\StringUtil;
use Isotope\Module\Checkout;

abstract class CheckoutStep extends Controller
{

    /**
     * Checkout module instance
     * @var Checkout
     */
    protected $objModule;

    /**
     * Flag if step has error
     * @var bool
     */
    protected $blnError = false;

    /**
     * Constructor.
     *
     * @param Checkout $objModule
     */
    public function __construct(Checkout $objModule)
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
     * Check if the checkout step is skippable
     * @return bool
     */
    public function isSkippable()
    {
        return false;
    }

    /**
     * Return short name of current class (e.g. for CSS)
     * @return string
     */
    public function getStepClass()
    {
        $strClass = \get_class($this);
        $strClass = substr($strClass, strrpos($strClass, '\\') + 1);

        return StringUtil::standardize($strClass);
    }
}
