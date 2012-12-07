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


abstract class IsotopeReportSales extends IsotopeReport
{

	public function generate()
	{
		$this->initializeDefaultValues();

		return parent::generate();
	}


	protected function getPanels()
	{
		$arrSession = $this->Session->get('iso_reports');

		return array
		(
			array
			(
				array
				(
					'name'			=> 'columns',
					'label'			=> 'Spalten:',
					'type'			=> 'text',
					'value'			=> (int) $arrSession[$this->name]['columns'],
					'class'			=> 'tl_columns',
				),
				array
				(
					'name'			=> 'from',
					'label'			=> 'Ab:',
					'type'			=> 'date',
					'format'		=> $GLOBALS['TL_CONFIG']['dateFormat'],
					'value'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['from']),
					'class'			=> 'tl_from',
				),
				array
				(
					'name'			=> 'period',
					'label'			=> 'Zeitraum:',
					'type'			=> 'filter',
					'value'			=> (string) $arrSession[$this->name]['period'],
					'class'			=> 'tl_period',
					'options'		=> array
					(
						'day'		=> 'Tag',
						'week'		=> 'Woche',
						'month'		=> 'Monat',
						'year'		=> 'Jahr',
					),
				),
			),
			array(/*$this->getLimitPanel(), $this->getSearchPanel(), */$this->getSortingPanel())
		);
	}


	protected function initializeDefaultValues()
	{
		// Set default session data
		$arrSession = $this->Session->get('iso_reports');

		if ($arrSession[$this->name]['period'] == '')
		{
			$arrSession[$this->name]['period'] = 'month';
		}

		if ($arrSession[$this->name]['columns'] == '')
		{
			$arrSession[$this->name]['columns'] = '6';
		}

		if ($arrSession[$this->name]['from'] == '')
		{
			$arrSession[$this->name]['from'] = strtotime('-5 months');
		}
		elseif (!is_numeric($arrSession[$this->name]['from']))
		{
			// Convert date formats into timestamps
			$objDate = new Date($arrSession[$this->name]['from'], $GLOBALS['TL_CONFIG']['dateFormat']);
			$arrSession[$this->name]['from'] = $objDate->tstamp;
		}

		$this->Session->set('iso_reports', $arrSession);
	}


	protected function getPeriodConfiguration($strPeriod)
	{
		switch ($strPeriod)
		{
			case 'day':
				$publicDate = 'd.m.Y';
				$privateDate = 'Ymd';
				$sqlDate = '%Y%m%d';
				break;

			case 'week':
				$publicDate = 'W/Y';
				$privateDate = 'YW';
				$sqlDate = '%Y%u';
				break;

			case 'month':
				$publicDate = 'F Y';
				$privateDate = 'Ym';
				$sqlDate = '%Y%m';
				break;

			case 'year':
				$publicDate = 'Y';
				$privateDate = 'Y';
				$sqlDate = '%Y';
				break;

			default:
				throw new Exception('Invalid period "' . $strPeriod . '". Reset your session to continue.');
		}

		return array($publicDate, $privateDate, $sqlDate);
	}
}

