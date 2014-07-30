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

namespace Isotope\IntegrityCheck;

use Isotope\Model\Rule;

class UnusedRules extends AbstractIntegrityCheck
{

    /**
     * @var bool
     */
    protected $blnError;


    /**
     * Check if rules module is enabled
     *
     * @return bool
     */
    public function hasError()
    {
        if (null === $this->blnError) {
            $this->blnError = in_array('isotope_rules', \Config::getInstance()->getActiveModules()) && Rule::countAll() == 0;
        }

        return $this->blnError;
    }

    /**
     * Return true if this issue can be automatically repaired
     *
     * @return bool
     */
    public function canRepair()
    {
        return true;
    }

    /**
     * Try to fix the integrity issue
     */
    public function repair()
    {
        if (!$this->hasError()) {
            return;
        }

        \System::disableModule('isotope_rules');
    }
}