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

use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\StringUtil;

class Setup extends BackendOverview
{
    /**
     * {@inheritdoc}
     */
    protected function getModules()
    {
        $return = [];

        $this->addIntroduction($return);

        foreach ($GLOBALS['ISO_MOD'] as $strGroup => $arrModules) {
            foreach ($arrModules as $strModule => $arrConfig) {

                if ($this->checkUserAccess($strModule)) {
                    if (\is_array($arrConfig['tables'] ?? null)) {
                        $GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'] += $arrConfig['tables'];
                    }

                    $return[$strGroup]['modules'][$strModule] = array_merge($arrConfig, [
                        'label' => StringUtil::specialchars($GLOBALS['TL_LANG']['IMD'][$strModule][0] ?? $strModule),
                        'description' => StringUtil::specialchars(strip_tags($GLOBALS['TL_LANG']['IMD'][$strModule][1] ?? '')),
                        'href' => TL_SCRIPT.'?do=iso_setup&mod='.$strModule,
                        'class' => $arrConfig['class'] ?? '',
                    ]);

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
        return BackendUser::getInstance()->hasAccess($module, 'iso_modules');
    }


    /**
     * Adds first steps and fundraising hints
     */
    protected function addIntroduction(array &$return)
    {
        if (BackendUser::getInstance()->isAdmin) {
            $objTemplate = new BackendTemplate('be_iso_introduction');

            $return['introduction']['label'] = &$GLOBALS['TL_LANG']['MSC']['isotopeIntroductionLegend'];
            $return['introduction']['html']  = $objTemplate->parse();
        }
    }
}
