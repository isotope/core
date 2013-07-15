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

namespace Isotope\BackendModule;


/**
 * Class ModuleIsotopeSetup
 *
 * Back end module Isotope "setup".
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 */
class Setup extends BackendOverview
{
    /**
     * {@inheritdoc}
     */
    protected function getModules()
    {
        $this->import('BackendUser', 'User');
        $return = array();

        foreach ($GLOBALS['ISO_MOD'] as $strGroup => $arrModules) {
            foreach ($arrModules as $strModule => $arrConfig) {
                if ($this->User->hasAccess($strModule, 'iso_modules')) {
                    if (is_array($arrConfig['tables'])) {
                        $GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'] += $arrConfig['tables'];
                    }

                    $return[$GLOBALS['TL_LANG']['IMD'][$strGroup]][$strModule] = array_merge($arrConfig, array
                    (
                        'label'         => ($GLOBALS['TL_LANG']['IMD'][$strModule][0] ? $GLOBALS['TL_LANG']['IMD'][$strModule][0] : $strModule),
                        'description'   => $GLOBALS['TL_LANG']['IMD'][$strModule][1],
                        'href'          => \Environment::get('script') . '?do=iso_setup&mod=' . $strModule,
                    ));
                }
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkUserHasAccessToModule($module)
    {
        return $this->User->isAdmin || $this->User->hasAccess($module, 'iso_modules');
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->before = '<h1 id="tl_welcome">' . sprintf($GLOBALS['TL_LANG']['IMD']['config_module'], ISO_VERSION . '.' . ISO_BUILD) . '</h1>';
        parent::compile();
    }
}
