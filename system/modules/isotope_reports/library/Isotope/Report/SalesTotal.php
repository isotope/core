<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Report;

use Contao\Database;
use Contao\Date;
use Contao\Message;
use Contao\Session;
use Isotope\Isotope;
use Isotope\Model\Config;
use Haste\Generator\RowClass;
use Isotope\Report\Period\PeriodFactory;
use Isotope\Report\Period\PeriodInterface;


class SalesTotal extends Sales
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_report_sales_total';


    protected function compile()
    {
        $arrSession    = Session::getInstance()->get('iso_reports');

        $intConfig = (int) ($arrSession[$this->name]['iso_config'] ?? 0);
        $strPeriod = (string) $arrSession[$this->name]['period'];
        $intStart  = (int) $arrSession[$this->name]['start'];
        $intStop   = (int) $arrSession[$this->name]['stop'];
        $intStatus = (int) ($arrSession[$this->name]['iso_status'] ?? 0);

        $period   = PeriodFactory::create($strPeriod);
        $intStart = $period->getPeriodStart($intStart);
        $intStop  = $period->getPeriodEnd($intStop);
        $dateFrom = $period->getKey($intStart);
        $dateTo   = $period->getKey($intStop);

        if ('locked' === $this->strDateField) {
            $this->strDateField = $arrSession[$this->name]['date_field'];
        }

        $dateGroup = $period->getSqlField('o.' . $this->strDateField);

        $objData = Database::getInstance()->query("
            SELECT
                c.id AS config_id,
                c.currency,
                COUNT(o.id) AS total_orders,
                COUNT(i.id) AS total_products,
                COUNT(DISTINCT o.id) AS total_orders,
                COUNT(DISTINCT i.id) AS total_products,
                SUM(i.quantity) AS total_items,
                SUM(i.tax_free_price * i.quantity) AS total_sales,
                $dateGroup AS dateGroup
            FROM tl_iso_product_collection o
            LEFT JOIN tl_iso_product_collection_item i ON o.id=i.pid
            LEFT JOIN tl_iso_orderstatus os ON os.id=o.order_status
            LEFT OUTER JOIN tl_iso_config c ON o.config_id=c.id
            WHERE o.type='order' AND o.order_status>0 AND o.{$this->strDateField} IS NOT NULL
            " . ($intStatus > 0 ? " AND o.order_status=".$intStatus : '') . "
            " . static::getProductProcedure('i', 'product_id') . "
            " . ($intConfig > 0 ? " AND c.id=".$intConfig : '') . "
            " . static::getConfigProcedure('c') . "
            GROUP BY config_id, dateGroup
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
        ");

        $arrCurrencies = array();
        $arrData = $this->initializeData($period, $intStart, $intStop);
        $arrChart = $this->initializeChart($period, $intStart, $intStop);

        while ($objData->next()) {
            $arrCurrencies[$objData->currency] = $objData->config_id;

            $arrData['rows'][$objData->dateGroup]['columns'][1]['value'] += $objData->total_orders;
            $arrData['rows'][$objData->dateGroup]['columns'][2]['value'] += $objData->total_products;
            $arrData['rows'][$objData->dateGroup]['columns'][3]['value'] += $objData->total_items;

            if (!\is_array($arrData['rows'][$objData->dateGroup]['columns'][4]['value'])) {
                $arrData['rows'][$objData->dateGroup]['columns'][4]['value'] = array();
            }

            $arrData['rows'][$objData->dateGroup]['columns'][4]['value'][$objData->currency] = ($arrData['rows'][$objData->dateGroup]['columns'][4]['value'][$objData->currency] ?? 0) + $objData->total_sales;

            // Summary in the footer
            $arrData['footer'][1]['value'] += $objData->total_orders;
            $arrData['footer'][2]['value'] += $objData->total_products;
            $arrData['footer'][3]['value'] += $objData->total_items;
            $arrData['footer'][4]['value'][$objData->currency] = ((float) ($arrData['footer'][4]['value'][$objData->currency] ?? 0) + $objData->total_sales);

            // Generate chart data
            $arrChart[$objData->currency]['data'][$objData->dateGroup]['y'] = ((float) $arrChart[$objData->currency]['data'][$objData->dateGroup]['y'] + $objData->total_sales);
        }

        // Apply formatting
        $arrData = $this->formatValues($arrData, $arrCurrencies);

        $this->Template->data         = $arrData;
        $this->Template->chart        = $arrChart;
        $this->Template->periodFormat = $period->getJavascriptClosure();
    }


    protected function initializeData(PeriodInterface $period, $intStart, $intStop)
    {
        $arrData = ['rows' => []];

        $arrData['header'] = [
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['period'],
                'header'        => true,
            ],
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['orders#'],
                'attributes'    => ' style="text-align:right"',
            ],
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['products#'],
                'attributes'    => ' style="text-align:right"',
            ],
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['items#'],
                'attributes'    => ' style="text-align:right"',
            ],
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['sales#'],
                'attributes'    => ' style="text-align:right"',
            ],
        ];

        $arrData['footer'] = [
            [
                'value'         => $GLOBALS['TL_LANG']['ISO_REPORT']['sums'],
            ],
            [
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ],
            [
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ],
            [
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ],
            [
                'value'         => [],
                'attributes'    => ' style="text-align:right"',
            ],
        ];

        while ($intStart <= $intStop) {
            $arrData['rows'][$period->getKey($intStart)] = [
                'columns' => [
                    [
                        'value'         => $period->format($intStart),
                    ],
                    [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                ],
            ];

            $intStart = $period->getNext($intStart);
        }

        RowClass::withKey('class')->addEvenOdd()->applyTo($arrData['rows']);

        return $arrData;
    }


    protected function initializeChart(PeriodInterface $period, $intStart, $intStop)
    {
        $arrSession  = Session::getInstance()->get('iso_reports');
        $intConfig   = (int) ($arrSession[$this->name]['iso_config'] ?? 0);
        $intStart    = strtotime('first day of this month', $intStart);

        $arrData = array();
        $arrCurrencies = Database::getInstance()->execute("
            SELECT DISTINCT currency FROM tl_iso_config WHERE currency!=''
            " . static::getConfigProcedure() . "
            " . ($intConfig > 0 ? ' AND id='.$intConfig : '') . "
        ")->fetchEach('currency');

        foreach ($arrCurrencies as $currency) {
            $arrData[$currency]['label'] = $currency;
            $arrData[$currency]['className'] = '.'.strtolower($currency);
        }

        while ($intStart <= $intStop) {
            foreach ($arrCurrencies as $currency) {
                $arrData[$currency]['data'][$period->getKey($intStart)]['x'] = $intStart;
                $arrData[$currency]['data'][$period->getKey($intStart)]['y'] = 0;
            }

            $intStart = $period->getNext($intStart);
        }

        return $arrData;
    }


    protected function formatValues($arrData, $arrCurrencies)
    {
        // Format row totals
        foreach ($arrData['rows'] as $dateGroup => $arrRow) {
            if (\is_array($arrRow['columns'][4]['value'])) {
                foreach ($arrRow['columns'][4]['value'] as $currency => $varValue) {
                    /** @type Config $objConfig */
                    $objConfig = Config::findByPk($arrCurrencies[$currency]);
                    Isotope::setConfig($objConfig);

                    $arrData['rows'][$dateGroup]['columns'][4]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
                }
            }
        }

        // Format footer totals
        foreach ($arrData['footer'][4]['value'] as $currency => $varValue) {
            /** @type Config $objConfig */
            $objConfig = Config::findByPk($arrCurrencies[$currency]);
            Isotope::setConfig($objConfig);

            $arrData['footer'][4]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
        }

        if (empty($arrData['footer'][4]['value'])) {
            $arrData['footer'][4]['value'] = 0;
        }

        return $arrData;
    }


    protected function initializeDefaultValues()
    {
        // Set default session data
        $arrSession = Session::getInstance()->get('iso_reports');

        if (empty($arrSession[$this->name]['period'])) {
            $arrSession[$this->name]['period'] = 'month';
        }

        if (empty($arrSession[$this->name]['stop'])) {
            $arrSession[$this->name]['stop'] = time();
        } elseif (!is_numeric($arrSession[$this->name]['stop'])) {
            // Convert date formats into timestamps
            try {
                $objDate = new Date($arrSession[$this->name]['stop'], $GLOBALS['TL_CONFIG']['dateFormat']);
                $arrSession[$this->name]['stop'] = $objDate->tstamp;
            } catch (\OutOfBoundsException $e) {
                Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['date'], $GLOBALS['TL_CONFIG']['dateFormat']));
                $arrSession[$this->name]['stop'] = time();
            }
        }

        if (empty($arrSession[$this->name]['start'])) {
            $arrSession[$this->name]['start'] = strtotime('-6 months');
        } elseif (!is_numeric($arrSession[$this->name]['start'])) {
            // Convert date formats into timestamps
            try {
                $objDate = new Date($arrSession[$this->name]['start'], $GLOBALS['TL_CONFIG']['dateFormat']);
                $arrSession[$this->name]['start'] = $objDate->tstamp;
            } catch (\OutOfBoundsException $e) {
                Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['date'], $GLOBALS['TL_CONFIG']['dateFormat']));
                $arrSession[$this->name]['start'] = strtotime('-6 months');
            }
        }

        Session::getInstance()->set('iso_reports', $arrSession);

        parent::initializeDefaultValues();
    }
}
