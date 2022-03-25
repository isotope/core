<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\IntegrityCheck;

use Contao\File;
use Contao\System;
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
            $this->blnError = \array_key_exists('isotope_rules', System::getContainer()->getParameter('kernel.bundles')) && Rule::countAll() == 0;
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

        $objFile = new File('system/modules/isotope_rules/.skip', true);

        if (!$objFile->exists()) {
            $objFile->write('Remove this file to enable the module');
            $objFile->close();
        }

        try {
            System::getContainer()
                  ->get('filesystem')
                  ->remove(System::getContainer()->get('kernel')->getCacheDir())
            ;
        } catch (\Exception $e) {
            // Ignore cache removal errors
        }
    }
}
