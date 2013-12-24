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

$this->loadLanguageFile(\Isotope\Model\Group::getTable());

/**
 * Add the Isotope style sheet
 */
if (TL_MODE == 'BE') {
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
);


class tl_iso_reports extends Backend
{

    public function getReports()
    {
        $arrReports = array();
		$arrGroups = &$GLOBALS['BE_MOD']['isotope']['reports']['modules'];

		foreach ($arrGroups as $strGroup => $arrGroup)
		{
			$strLegend = $GLOBALS['ISO_LANG']['REPORT'][$strGroup] ? $GLOBALS['ISO_LANG']['REPORT'][$strGroup] : $strGroup;

			foreach ($arrGroup as $strName => $arrConfig)
			{
				$arrReports[$strLegend][$strName] = ($arrConfig['label'][0] ? $arrConfig['label'][0] : $strName);
			}
		}

        return $arrReports;
    }
}
