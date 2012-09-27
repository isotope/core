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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleIsotopeReports extends BackendModule
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_iso_reports';


	public function generate()
	{
		if ($this->Input->get('report') != '')
		{
			$arrReport = $this->findReport($this->Input->get('report'));

			if ($arrReport !== false && $this->classFileExists($arrReport['callback']))
			{
				$objCallback = new $arrReport['callback']($arrReport);
				return $objCallback->generate();
			}
		}

		return parent::generate();
	}


	/**
	 * Generate a icon view of all available reports
	 */
	protected function compile()
	{
		$arrReports = array();
		$arrGroups = &$GLOBALS['BE_MOD']['isotope']['reports']['modules'];

		foreach ($arrGroups as $strGroup => $arrGroup)
		{
			$strLegend = $GLOBALS['ISO_LANG']['REPORT'][$strGroup] ? $GLOBALS['ISO_LANG']['REPORT'][$strGroup] : $strGroup;

			foreach ($arrGroup as $strName => $arrConfig)
			{
				$arrReports[$strLegend][] = array
				(
					'label'		=> $arrConfig['label'],
					'icon'		=> $arrConfig['icon'],
					'href'		=> $this->addToUrl('report='.$strName),
				);
			}
		}

		$this->Template->reports = $arrReports;
	}


	protected function findReport($strName)
	{
		foreach ($GLOBALS['BE_MOD']['isotope']['reports']['modules'] as $strGroup => $arrReports)
		{
			if (isset($arrReports[$strName]))
			{
				$arrData = $arrReports[$strName];
				$arrData['name'] = $strName;
				$arrData['group'] = $strGroup;

				return $arrData;
			}
		}

		return false;
	}
}

