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


class IsotopeReportSalesTotal extends IsotopeReportSales
{

	protected function compile()
	{
		$arrSession = $this->Session->get('iso_reports');

		$intConfig = (int) $arrSession[$this->name]['iso_config'];
		$strPeriod = (string) $arrSession[$this->name]['period'];
		$intStart = (int) $arrSession[$this->name]['start'];
		$intStop = (int) $arrSession[$this->name]['stop'];

		list($publicDate, $privateDate, $sqlDate) = $this->getPeriodConfiguration($strPeriod);


		$dateFrom = date($privateDate, $intStart);
		$dateTo = date($privateDate, $intStop);

		$objData = $this->Database->prepare("SELECT
												c.id AS config_id,
												c.currency,
												o.date AS date,
												COUNT(o.id) AS total_orders,
												SUM(i.product_quantity) AS total_items,
												SUM(i.price * i.product_quantity) AS total_sales,
												DATE_FORMAT(FROM_UNIXTIME(o.date), ?) AS dateGroup
											FROM tl_iso_orders o
											LEFT JOIN tl_iso_order_items i ON o.id=i.pid
											LEFT OUTER JOIN tl_iso_config c ON o.config_id=c.id
											WHERE 1=1
											" . ($intConfig > 0 ? " AND c.id=".$intConfig : '') . "
											GROUP BY config_id, dateGroup
											HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo")
									->execute($sqlDate);


		$i = -1;
		$arrCurrencies = array();
		$arrData = $this->initializeData($strPeriod, $intStart, $intStop, $privateDate, $publicDate);
		$arrChart = $this->initializeChart($strPeriod, $intStart, $intStop, $privateDate, $publicDate);

		while ($objData->next())
		{
			$arrCurrencies[$objData->currency] = $objData->config_id;

			$arrData['rows'][$objData->dateGroup]['columns'][1]['value'] += $objData->total_orders;
			$arrData['rows'][$objData->dateGroup]['columns'][2]['value'] += $objData->total_items;

			if (!is_array($arrData['rows'][$objData->dateGroup]['columns'][3]['value']))
			{
				$arrData['rows'][$objData->dateGroup]['columns'][3]['value'] = array();
			}

			$arrData['rows'][$objData->dateGroup]['columns'][3]['value'][$objData->currency] = $arrData['rows'][$objData->dateGroup]['columns'][3]['value'][$objData->currency] + $objData->total_sales;

			// Summary in the footer
			$arrData['footer'][1]['value'] += $objData->total_orders;
			$arrData['footer'][2]['value'] += $objData->total_items;
			$arrData['footer'][3]['value'][$objData->currency] = ((float) $arrData['footer'][3]['value'][$objData->currency] + $objData->total_sales);

			// Generate chart data
			$arrChart['rows'][$objData->dateGroup]['columns'][$objData->currency]['value'] = ((float) $arrChart['rows'][$objData->dateGroup]['columns'][$objData->currency]['value'] + $objData->total_sales);

		}

		// Apply formatting
		$arrData = $this->formatValues($arrData, $arrCurrencies);

		$this->Template->data = $arrData;
		$this->Template->addChart = true;
		$this->Template->chart = $arrChart;
	}


	protected function initializeData($strPeriod, $intStart, $intStop, $privateDate, $publicDate)
	{
		$arrData = array('rows'=>array());

		$arrData['header'] = array
		(
			array
			(
				'value'			=> 'Zeitraum',
				'header'		=> true,
			),
			array
			(
				'value'			=> '# Bestellungen',
				'attributes'	=> ' style="text-align:right"',
			),
			array
			(
				'value'			=> '# Artikel',
				'attributes'	=> ' style="text-align:right"',
			),
			array
			(
				'value'			=> 'Umsatz',
				'attributes'	=> ' style="text-align:right"',
			),
		);

		$arrData['footer'] = array
		(
			array
			(
				'value'			=> '',
			),
			array
			(
				'value'			=> 0,
				'attributes'	=> ' style="text-align:right"',
			),
			array
			(
				'value'			=> 0,
				'attributes'	=> ' style="text-align:right"',
			),
			array
			(
				'value'			=> array(),
				'attributes'	=> ' style="text-align:right"',
			),
		);

		while ($intStart < $intStop)
		{
			$arrData['rows'][date($privateDate, $intStart)] = array
			(
				'class' => (++$i%2 ? 'odd' : 'even'),
				'columns' => array
				(
					array
					(
						'value'			=> $this->parseDate($publicDate, $intStart),
					),
					array
					(
						'value'			=> 0,
						'attributes'	=> ' style="text-align:right"',
					),
					array
					(
						'value'			=> 0,
						'attributes'	=> ' style="text-align:right"',
					),
					array
					(
						'value'			=> 0,
						'attributes'	=> ' style="text-align:right"',
					),
				),
			);

			$intStart = strtotime('+ 1 '.$strPeriod, $intStart);
		}

		return $arrData;
	}


	protected function initializeChart($strPeriod, $intStart, $intStop, $privateDate, $publicDate)
	{
		$arrSession = $this->Session->get('iso_reports');
		$intConfig = (int) $arrSession[$this->name]['iso_config'];


		$arrData = array('header'=>array(), 'rows'=>array(), 'footer'=>array());
		$arrCurrencies = $this->Database->execute("SELECT DISTINCT currency FROM tl_iso_config" . ($intConfig > 0 ? ' WHERE id='.$intConfig : ''))->fetchEach('currency');

		foreach ($arrCurrencies as $currency)
		{
			$arrData['header'][$currency]['value'] = $currency;
		}

		while ($intStart < $intStop)
		{
			$arrData['footer'][date($privateDate, $intStart)]['value'] = $this->parseDate($publicDate, $intStart);

			foreach ($arrCurrencies as $currency)
			{
				$arrData['rows'][date($privateDate, $intStart)]['columns'][$currency]['value'] = 0;
			}

			$intStart = strtotime('+ 1 '.$strPeriod, $intStart);
		}

		return $arrData;
	}


	protected function formatValues($arrData, $arrCurrencies)
	{
		// Format row totals
		foreach ($arrData['rows'] as $dateGroup => $arrRow)
		{
			if (is_array($arrRow['columns'][3]['value']))
			{
				foreach ($arrRow['columns'][3]['value'] as $currency => $varValue)
				{
					$this->Isotope->overrideConfig($arrCurrencies[$currency]);

					$arrData['rows'][$dateGroup]['columns'][3]['value'][$currency] = $this->Isotope->formatPriceWithCurrency($varValue);
				}
			}
		}

		// Format footer totals
		foreach ($arrData['footer'][3]['value'] as $currency => $varValue)
		{
			$this->Isotope->overrideConfig($arrCurrencies[$currency]);

			$arrData['footer'][3]['value'][$currency] = $this->Isotope->formatPriceWithCurrency($varValue);
		}

		if (empty($arrData['footer'][3]['value']))
		{
			$arrData['footer'][3]['value'] = 0;
		}

		return $arrData;
	}


	protected function initializeDefaultValues()
	{
		// Set default session data
		$arrSession = $this->Session->get('iso_reports');

		if ($arrSession[$this->name]['period'] == '')
		{
			$arrSession[$this->name]['period'] = 'month';
		}

		if ($arrSession[$this->name]['stop'] == '')
		{
			$arrSession[$this->name]['stop'] = time();
		}
		elseif (!is_numeric($arrSession[$this->name]['stop']))
		{
			// Convert date formats into timestamps
			$objDate = new Date($arrSession[$this->name]['stop'], $GLOBALS['TL_CONFIG']['dateFormat']);
			$arrSession[$this->name]['stop'] = $objDate->tstamp;
		}

		if ($arrSession[$this->name]['start'] == '')
		{
			$arrSession[$this->name]['start'] = strtotime('-6 months');
		}
		elseif (!is_numeric($arrSession[$this->name]['start']))
		{
			// Convert date formats into timestamps
			$objDate = new Date($arrSession[$this->name]['start'], $GLOBALS['TL_CONFIG']['dateFormat']);
			$arrSession[$this->name]['start'] = $objDate->tstamp;
		}

		$this->Session->set('iso_reports', $arrSession);
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
					'name'			=> 'stop',
					'label'			=> 'Bis:',
					'type'			=> 'date',
					'format'		=> $GLOBALS['TL_CONFIG']['dateFormat'],
					'value'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['stop']),
					'class'			=> 'tl_stop',
				),
				array
				(
					'name'			=> 'start',
					'label'			=> 'Von:',
					'type'			=> 'date',
					'format'		=> $GLOBALS['TL_CONFIG']['dateFormat'],
					'value'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['start']),
					'class'			=> 'tl_start',
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
			array($this->getFilterByConfigPanel(), /*$this->getLimitPanel(), $this->getSearchPanel(), */$this->getSortingPanel())
		);
	}
}

