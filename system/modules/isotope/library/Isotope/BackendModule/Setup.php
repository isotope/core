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

        $this->addFirstStepsHint($return);

        foreach ($GLOBALS['ISO_MOD'] as $strGroup => $arrModules) {
            foreach ($arrModules as $strModule => $arrConfig) {

                if ($this->checkUserAccess($strModule)) {
                    if (is_array($arrConfig['tables'])) {
                        $GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'] += $arrConfig['tables'];
                    }

                    $return[$strGroup]['modules'][$strModule] = array_merge($arrConfig, array
                    (
                        'label'         => specialchars($GLOBALS['TL_LANG']['IMD'][$strModule][0] ?: $strModule),
                        'description'   => specialchars(strip_tags($GLOBALS['TL_LANG']['IMD'][$strModule][1])),
                        'href'          => \Environment::get('script') . '?do=iso_setup&mod=' . $strModule,
                    ));
                    $return[$strGroup]['label'] = $GLOBALS['TL_LANG']['IMD'][$strGroup];
                }
            }
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkUserAccess($module)
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


    /**
     * Adds first steps and fundraising hints
     */
    protected function addFirstStepsHint(&$return)
    {
        if (\BackendUser::getInstance()->isAdmin) {
            $objTemplate = new \BackendTemplate('be_iso_first_steps');

            $return['first_steps']['label'] = 'First steps and fundraising';
            $return['first_steps']['html'] = $objTemplate->parse();
        }
    }
}
