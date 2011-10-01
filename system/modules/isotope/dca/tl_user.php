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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('{account_legend}', '{isotope_legend},iso_product_types,iso_configs,iso_modules;{account_legend}', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('{account_legend}', '{isotope_legend},iso_product_types,iso_configs,iso_modules;{account_legend}', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['iso_product_types'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iso_product_types'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'			  => 'tl_iso_producttypes.name',
	'eval'                    => array('multiple'=>true),
);

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_configs'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iso_configs'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'foreignKey'			  => 'tl_iso_config.name',
	'eval'                    => array('multiple'=>true),
);

$GLOBALS['TL_DCA']['tl_user']['fields']['iso_modules'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_user']['iso_modules'],
	'exclude'                 => true,
	'filter'                  => true,
	'inputType'               => 'checkbox',
	'options_callback'		  => array('tl_user_isotope', 'getIsotopeModules'),
	'reference'               => &$GLOBALS['TL_LANG']['IMD'],
	'eval'                    => array('multiple'=>true, 'helpwizard'=>true),
);


class tl_user_isotope extends Backend
{

	/**
	 * Return all modules except profile modules
	 * @return array
	 */
	public function getIsotopeModules()
	{
		$arrModules = array();

		foreach ($GLOBALS['ISO_MOD'] as $k=>$v)
		{
			$arrModules = array_merge($arrModules, array_keys($v));
		}

		return $arrModules;
	}
}

