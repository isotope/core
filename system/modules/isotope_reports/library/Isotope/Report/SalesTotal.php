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
use Isotope\Model\ProductCollection;
use Isotope\Report\Period\PeriodFactory;
use Isotope\Report\Period\PeriodInterface;


class SalesTotal extends Sales
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_report_sales_total';

    private array $visitorData = array();


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
                c.visitors_config_id,
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
            WHERE o.type='order' AND o.order_status > 0 AND o.{$this->strDateField} IS NOT NULL
            " . ($intStatus > 0 ? " AND o.order_status=".$intStatus : '') . "
            " . static::getProductProcedure('i', 'product_id') . "
            " . ($intConfig > 0 ? " AND c.id=".$intConfig : '') . "
            " . static::getConfigProcedure('c') . "
            GROUP BY config_id, dateGroup
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
        ");

        $arrCurrencies = array();
        $arrData = $this->initializeData($period, $intStart, $intStop);
        $arrChartData = $this->initializeChart($period, $intStart, $intStop);
        $showVisitors = false;

        while ($objData->next()) {
            $arrCurrencies[$objData->currency] = $objData->config_id;
            $showVisitors = $showVisitors || $objData->visitors_config_id > 0;

            $arrData['rows'][$objData->dateGroup]['columns']['orders']['value'] = $objData->total_orders;
            $arrData['rows'][$objData->dateGroup]['columns']['products']['value'] = $objData->total_products;
            $arrData['rows'][$objData->dateGroup]['columns']['items']['value'] = $objData->total_items;

            if($showVisitors) {
                if (!\is_array($arrData['rows'][$objData->dateGroup]['columns']['visitors']['value'])) {
                    $arrData['rows'][$objData->dateGroup]['columns']['visitors']['value'] = array();
                    $arrData['rows'][$objData->dateGroup]['columns']['cr']['value'] = array();
                }
                $visitorData = $this->getVisitorDataForId($objData->visitors_config_id, $period, $dateFrom, $dateTo);
                $arrData['rows'][$objData->dateGroup]['columns']['visitors']['value'][$objData->currency] = $visitorData[$objData->dateGroup];

                $orders = $arrData['rows'][$objData->dateGroup]['columns']['orders']['value'];
                $arrData['rows'][$objData->dateGroup]['columns']['cr']['value'][$objData->currency] = $this->calculateConversationRate($visitorData[$objData->dateGroup], $orders);
            }

            if (!\is_array($arrData['rows'][$objData->dateGroup]['columns']['sales']['value'])) {
                $arrData['rows'][$objData->dateGroup]['columns']['sales']['value'] = array();
            }
            $arrData['rows'][$objData->dateGroup]['columns']['sales']['value'][$objData->currency] = ($arrData['rows'][$objData->dateGroup]['columns']['sales']['value'][$objData->currency] ?? 0) + $objData->total_sales;

            // Summary in the footer
            $arrData['footer']['orders']['value'] += $objData->total_orders;
            $arrData['footer']['products']['value'] += $objData->total_products;
            $arrData['footer']['items']['value'] += $objData->total_items;
            if($showVisitors) {
                $arrData['footer']['visitors']['value'][$objData->currency] += $visitorData[$objData->dateGroup];
                $arrData['footer']['cr']['value'][$objData->currency] = $this->calculateConversationRate($arrData['footer']['visitors']['value'][$objData->currency], $arrData['footer']['orders']['value'][$objData->currency]);
            }
            $arrData['footer']['sales']['value'][$objData->currency] = ((float) ($arrData['footer']['sales']['value'][$objData->currency] ?? 0) + $objData->total_sales);

            // Generate chart data
            $arrChartData[$objData->currency]['data'][$objData->dateGroup][1] = ((float) $arrChartData[$objData->currency]['data'][$objData->dateGroup][1] + $objData->total_sales);
        }

        if($showVisitors){
            $this->fillEmptyVisitorData($arrData, $period, $intStart, $intStop);
        }

        // Apply formatting
        $arrData = $this->formatValues($arrData, $arrCurrencies, $showVisitors);
        // Generate format for apexcharts
        $arrChart = array_reduce($arrChartData, function ($carry, $item) {
            $carry['series'][] = [
                'name' => $item['name'],
                'type' => 'line',
                'data' => array_column($item['data'], 1),
            ];
            $carry['labels'] = array_map(function ($itemLabel) {
                return (string)$itemLabel;
            }, array_column($item['data'], 0));
            return $carry;
        }, array());

        if($showVisitors) {
            $arrChart['series'][] = [
                'name' => $GLOBALS['TL_LANG']['ISO_REPORT']['visitors'],
                'type' => 'column',
                'data' => array_map(function ($dateGroupItem) {
                    return $dateGroupItem['columns']['visitors']['value'];
                }, array_values($arrData['rows'])),
            ];
        }

        $this->Template->data         = $arrData;
        $this->Template->chart        = $arrChart;
        $this->Template->periodFormat = $period->getJavascriptClosure();
    }


    protected function initializeData(PeriodInterface $period, $intStart, $intStop): array
    {
        $arrData = ['rows' => []];

        $arrData['header'] = [
            "period" => [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['period'],
                'header'        => true,
            ],
            "orders" => [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['orders#'],
                'attributes'    => ' style="text-align:right"',
            ],
            "products" => [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['products#'],
                'attributes'    => ' style="text-align:right"',
            ],
            "items" => [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['items#'],
                'attributes'    => ' style="text-align:right"',
            ],
            "sales" => [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['sales#'],
                'attributes'    => ' style="text-align:right"',
            ],
        ];

        $arrData['footer'] = [
            "period" => [
                'value'         => $GLOBALS['TL_LANG']['ISO_REPORT']['sums'],
            ],
            "orders" => [
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ],
            "products" => [
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ],
            "items" => [
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ],
            "sales" => [
                'value'         => [],
                'attributes'    => ' style="text-align:right"',
            ],
        ];

        while ($intStart <= $intStop) {
            $arrData['rows'][$period->getKey($intStart)] = [
                'columns' => [
                    "period" => [
                        'value'         => $period->format($intStart),
                    ],
                    "orders" => [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    "products" => [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    "items" => [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    "sales" => [
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


    protected function initializeChart(PeriodInterface $period, $intStart, $intStop): array
    {
        $arrSession  = Session::getInstance()->get('iso_reports');
        $intConfig   = (int) ($arrSession[$this->name]['iso_config'] ?? 0);

        $arrData = array();
        $arrCurrencies = Database::getInstance()->execute("
            SELECT DISTINCT currency FROM tl_iso_config WHERE currency!=''
            " . static::getConfigProcedure() . "
            " . ($intConfig > 0 ? ' AND id='.$intConfig : '') . "
        ")->fetchEach('currency');

        foreach ($arrCurrencies as $currency) {
            $arrData[$currency]['name'] = $currency;
            $arrData[$currency]['className'] = '.'.strtolower($currency);
        }

        while ($intStart <= $intStop) {
            foreach ($arrCurrencies as $currency) {
                $arrData[$currency]['data'][$period->getKey($intStart)] = [$intStart,0];
            }

            $intStart = $period->getNext($intStart);
        }

        return $arrData;
    }

    protected function formatValues($arrData, $arrCurrencies, $showVisitors): array
    {
        // Format row totals
        foreach ($arrData['rows'] as $dateGroup => $arrRow) {
            if($showVisitors){
                $arrData['rows'][$dateGroup]['columns']['cr']['value'] = number_format($arrData['rows'][$dateGroup]['columns']['cr']['value'],2,',','.') . " %";
            }
            if (\is_array($arrRow['columns']['sales']['value'])) {
                foreach ($arrRow['columns']['sales']['value'] as $currency => $varValue) {
                    /** @type Config $objConfig */
                    $objConfig = Config::findByPk($arrCurrencies[$currency]);
                    Isotope::setConfig($objConfig);

                    $arrData['rows'][$dateGroup]['columns']['sales']['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
                }
            }
        }

        // Format footer totals
        foreach ($arrData['footer']['sales']['value'] as $currency => $varValue) {
            /** @type Config $objConfig */
            $objConfig = Config::findByPk($arrCurrencies[$currency]);
            Isotope::setConfig($objConfig);
            if($showVisitors){
                $arrData['footer']['cr']['value'] = number_format($arrData['footer']['cr']['value'],2,',','.') . " %";
            }
            $arrData['footer']['sales']['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
        }

        if (empty($arrData['footer']['sales']['value'])) {
            $arrData['footer']['sales']['value'] = 0;
        }
        return $arrData;
    }


    protected function initializeDefaultValues(): void
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

    private function calculateConversationRate($visitors, $orders)
    {
        return  $visitors > 0 ?  $orders * 100 / $visitors : 0;
    }

    private function getVisitorDataForId($visitorsConfigId, $period, $dateFrom, $dateTo){
        $dateGroupVisitor = $period->getSqlField('visitors_date', false);
        if(!isset($this->visitorData[$visitorsConfigId])){
            $sqlResult = Database::getInstance()->query("
            SELECT
                sum(visitors_visit) as visitors,
                $dateGroupVisitor AS dateGroup
            FROM `tl_visitors_counter`
            WHERE vid = $visitorsConfigId
            GROUP BY dateGroup
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
            ");
            $result = array_reduce($sqlResult->fetchAllAssoc(), function ($carry, $item) {
                $carry[$item['dateGroup']] = $item['visitors'];
                return $carry;
            }, []);
            $this->visitorData[$visitorsConfigId] = $result;
        }
        return $this->visitorData[$visitorsConfigId];
    }

    private function fillEmptyVisitorData(array &$arrData, $period, $intStart, $intStop): void
    {
        if ($arrData['header'] && !array_key_exists("visitors", $arrData['header'])) {
            $arrData['header']['visitors'] = [
                'value' => $GLOBALS['TL_LANG']['ISO_REPORT']['visitors'],
                'attributes' => ' style="text-align:right"',
            ];
            $arrData['header']['cr'] = [
                'value' => $GLOBALS['TL_LANG']['ISO_REPORT']['conversation_rate'],
                'attributes' => ' style="text-align:right"',
            ];
        }
        if ($arrData['footer'] && !array_key_exists("visitors", $arrData['footer'])) {
            $arrData['footer']['visitors']['value'] = 0;
            $arrData['footer']['cr']['value'] = 0;
        }
        $arrData['footer']['visitors']['attributes'] = ' style="text-align:right"';
        $arrData['footer']['cr']['attributes'] = ' style="text-align:right"';

        while ($intStart <= $intStop) {
            $arrColumns = &$arrData['rows'][$period->getKey($intStart)]['columns'];
            if($arrColumns){
                if(!array_key_exists("visitors", $arrColumns)){
                    $arrColumns['visitors']['value'] = 0;
                    $arrColumns['cr']['value'] = 0;
                }
                $arrColumns['visitors']['attributes'] = ' style="text-align:right"';
                $arrColumns['cr']['attributes'] = ' style="text-align:right"';
            }

            $intStart = $period->getNext($intStart);
        }
    }
}
