<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\BackendModule;

/**
 * This class MUST NOT extend Contao\Backend or we will get a database destruct problem!
 */
class InitializeListener
{
    /**
     * Enable the module tables in setup
     */
    public function enableModuleTablesInSetup()
    {
        if ('iso_setup' !== ($_GET['do'] ?? null)) {
            return;
        }

        foreach ($GLOBALS['ISO_MOD'] as $strGroup => $arrModules) {
            foreach ($arrModules as $strModule => $arrConfig) {
                if (\is_array($arrConfig['tables'] ?? null)) {
                    $GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'] = array_merge(
                        $GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'],
                        $arrConfig['tables']
                    );
                }
            }
        }
    }
}
