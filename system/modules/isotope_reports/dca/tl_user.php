<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

 use Isotope\CompatibilityHelper;

$this->loadLanguageFile(\Isotope\Model\Group::getTable());

/**
 * Add the Isotope style sheet
 */
if (CompatibilityHelper::isBackend()) {
    $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/css/backend.css';
}

/**
 * Extend tl_user palettes
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace(',iso_modules,', ',iso_modules,iso_reports,', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace(',iso_modules,', ',iso_modules,iso_reports,', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);

/**
 * Add fields to tl_user
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['iso_reports'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iso_reports'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options_callback'        => array('tl_iso_reports', 'getReports'),
    'eval'                    => array('multiple'=>true, 'tl_class'=>'w50 w50h'),
    'sql'                     => 'blob NULL',
);


class tl_iso_reports extends \Contao\Backend
{

    public function getReports()
    {
        $arrReports = array();
        $arrGroups = &$GLOBALS['BE_MOD']['isotope']['reports']['modules'];

        foreach ($arrGroups as $strGroup => $arrGroup)
        {
            // @todo remove ISO_LANG in Isotope 3.0
            $strLegend = $GLOBALS['TL_LANG']['ISO_REPORT'][$strGroup] ?: ($GLOBALS['ISO_LANG']['REPORT'][$strGroup] ?: $strGroup);

            foreach ($arrGroup as $strName => $arrConfig)
            {
                $arrReports[$strLegend][$strName] = ($arrConfig['label'][0] ?: $strName);
            }
        }

        return $arrReports;
    }
}
