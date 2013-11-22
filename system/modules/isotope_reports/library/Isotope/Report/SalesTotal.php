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

namespace Isotope\Report;

use Isotope\Isotope;
use Isotope\Model\Config;
use Haste\Generator\RowClass;
use Haste\Util\Format;


class SalesTotal extends Sales
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_report_sales_total';


	protected function compile()
	{
		$arrSession = \Session::getInstance()->get('iso_reports');

		$intConfig = (int) $arrSession[$this->name]['iso_config'];
		$strPeriod = (string) $arrSession[$this->name]['period'];
		$intStart = (int) $arrSession[$this->name]['start'];
		$intStop = (int) $arrSession[$this->name]['stop'];
		$intStatus = (int) $arrSession[$this->name]['iso_status'];

		list($publicDate, $privateDate, $sqlDate) = $this->getPeriodConfiguration($strPeriod);

		$dateFrom = date($privateDate, $intStart);
		$dateTo = date($privateDate, $intStop);

		$objData = \Database::getInstance()->prepare("SELECT
												c.id AS config_id,
												c.currency,
												o.locked AS date,
												COUNT(o.id) AS total_orders,
												SUM(i.quantity) AS total_items,
												SUM(i.tax_free_price * i.quantity) AS total_sales,
												DATE_FORMAT(FROM_UNIXTIME(o.{$this->strDateField}), ?) AS dateGroup
											FROM " . \Isotope\Model\ProductCollection::getTable() . " o
											LEFT JOIN " . \Isotope\Model\ProductCollectionItem::getTable() . " i ON o.id=i.pid
											LEFT JOIN " . \Isotope\Model\OrderStatus::getTable() . " os ON os.id=o.order_status
											LEFT OUTER JOIN " . \Isotope\Model\Config::getTable() . " c ON o.config_id=c.id
											WHERE o.type='Order'
											" . ($intStatus > 0 ? " AND o.order_status=".$intStatus : '') . "
											" . $this->getProductProcedure('i', 'product_id') . "
											" . ($intConfig > 0 ? " AND c.id=".$intConfig : '') . "
											" . $this->getConfigProcedure('c') . "
											GROUP BY config_id, dateGroup
											HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo")
									->execute($sqlDate);

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
			$arrChart[$objData->currency]['data'][$objData->dateGroup]['y'] = ((float) $arrChart['rows'][$objData->dateGroup]['columns'][$objData->currency]['value'] + $objData->total_sales);

		}

		// Apply formatting
		$arrData = $this->formatValues($arrData, $arrCurrencies);

		$this->Template->data = $arrData;
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
				'value'			=> $GLOBALS['ISO_LANG']['REPORT']['sums'],
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

		while ($intStart <= $intStop)
		{
			$arrData['rows'][date($privateDate, $intStart)] = array
			(
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

        RowClass::withKey('class')->addEvenOdd()->applyTo($arrData['rows']);

		return $arrData;
	}


	protected function initializeChart($strPeriod, $intStart, $intStop, $privateDate, $publicDate)
	{
		$arrSession = \Session::getInstance()->get('iso_reports');
		$intConfig = (int) $arrSession[$this->name]['iso_config'];


		$arrData = array();
		$arrCurrencies = \Database::getInstance()->execute("
		    SELECT DISTINCT currency FROM " . \Isotope\Model\Config::getTable() . " WHERE currency!=''
		    " . $this->getConfigProcedure() . "
		    " . ($intConfig > 0 ? ' AND id='.$intConfig : '') . "
        ")->fetchEach('currency');

		foreach ($arrCurrencies as $currency)
		{
			$arrData[$currency]['label'] = $currency;
			$arrData[$currency]['className'] = '.'.strtolower($currency);
		}

		while ($intStart <= $intStop)
		{
			foreach ($arrCurrencies as $currency)
			{
                $arrData[$currency]['data'][date($privateDate, $intStart)]['x'] = $this->parseDate($publicDate, $intStart);
				$arrData[$currency]['data'][date($privateDate, $intStart)]['y'] = 0;
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
					Isotope::setConfig(Config::findByPk($arrCurrencies[$currency]));

					$arrData['rows'][$dateGroup]['columns'][3]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
				}
			}
		}

		// Format footer totals
		foreach ($arrData['footer'][3]['value'] as $currency => $varValue)
		{
			Isotope::setConfig(Config::findByPk($arrCurrencies[$currency]));

			$arrData['footer'][3]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
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
		$arrSession = \Session::getInstance()->get('iso_reports');

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
			$objDate = new \Date($arrSession[$this->name]['stop'], $GLOBALS['TL_CONFIG']['dateFormat']);
			$arrSession[$this->name]['stop'] = $objDate->tstamp;
		}

		if ($arrSession[$this->name]['start'] == '')
		{
			$arrSession[$this->name]['start'] = strtotime('-6 months');
		}
		elseif (!is_numeric($arrSession[$this->name]['start']))
		{
			// Convert date formats into timestamps
			$objDate = new \Date($arrSession[$this->name]['start'], $GLOBALS['TL_CONFIG']['dateFormat']);
			$arrSession[$this->name]['start'] = $objDate->tstamp;
		}

		\Session::getInstance()->set('iso_reports', $arrSession);

		parent::initializeDefaultValues();
	}
}

