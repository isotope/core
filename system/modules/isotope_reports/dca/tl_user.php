<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
