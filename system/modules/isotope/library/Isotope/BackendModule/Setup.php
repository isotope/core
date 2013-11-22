<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2013 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\BackendModule;

use Isotope\Isotope;


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
        $return = array();

        $this->addIntroduction($return);

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
                        'class'         => $arrConfig['class'],
                    ));

                    $strLabel = str_replace(':hide', '', $strGroup);
                    $return[$strGroup]['label'] = $GLOBALS['TL_LANG']['IMD'][$strLabel] ?: $strLabel;
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
        return \BackendUser::getInstance()->isAdmin || \BackendUser::getInstance()->hasAccess($module, 'iso_modules');
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        $this->Template->before = '<h1 id="tl_welcome">' . sprintf($GLOBALS['TL_LANG']['IMD']['config_module'], Isotope::VERSION) . '</h1>';

        parent::compile();
    }


    /**
     * Adds first steps and fundraising hints
     */
    protected function addIntroduction(&$return)
    {
        if (\BackendUser::getInstance()->isAdmin) {
            $objTemplate = new \BackendTemplate('be_iso_introduction');

            $return['introduction']['label'] = 'Introduction';
            $return['introduction']['html'] = $objTemplate->parse();
        }
    }
}
