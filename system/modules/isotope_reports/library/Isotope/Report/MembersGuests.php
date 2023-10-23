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
use Haste\Generator\RowClass;
use Isotope\Isotope;
use Isotope\Model\Config;
use Isotope\Report\Period\PeriodFactory;
use Isotope\Report\Period\PeriodInterface;

class MembersGuests extends Sales
{

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'iso_report_members_guests';

    protected function compile()
    {
        $arrSession    = Session::getInstance()->get('iso_reports');

        $intConfig = (int) ($arrSession[$this->name]['iso_config'] ?? 0);
        $strPeriod = (string) ($arrSession[$this->name]['period'] ?? '');
        $intStart  = (int) ($arrSession[$this->name]['start'] ?? 0);
        $intStop   = (int) ($arrSession[$this->name]['stop'] ?? 0);
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
                me.firstname AS member_firstname,
                me.lastname AS member_lastname,
                me.id AS member_number,
                COUNT(DISTINCT o.id) AS total_orders,
                COUNT(DISTINCT i.id) AS total_products,
                SUM(i.quantity) AS total_items,
                SUM(i.tax_free_price * i.quantity) AS total_sales,
                $dateGroup AS dateGroup
            FROM tl_iso_product_collection o
            LEFT JOIN tl_iso_product_collection_item i ON o.id=i.pid
            LEFT JOIN tl_iso_orderstatus os ON os.id=o.order_status
            LEFT OUTER JOIN tl_iso_config c ON o.config_id=c.id
            LEFT OUTER JOIN tl_member me ON o.member=me.id
            WHERE o.type='order' AND o.order_status>0 AND o.{$this->strDateField} IS NOT NULL
            " . ($intStatus > 0 ? " AND o.order_status=".$intStatus : '') . "
            " . static::getProductProcedure('i', 'product_id') . "
            " . ($intConfig > 0 ? " AND c.id=".$intConfig : '') . "
            " . static::getConfigProcedure('c') . "
            GROUP BY config_id, dateGroup, member_number
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
        ");

        $arrCurrencies = array();
        $arrDataMember = $this->initializeData($period, $intStart, $intStop);
        $arrDataGuests = $this->initializeData($period, $intStart, $intStop);

        $arrChart = $this->initializeChart($period, $intStart, $intStop);

        while ($objData->next()) {
            $arrCurrencies[$objData->currency] = $objData->config_id;

            if ($objData->member_number > 0) {
                $arrDataMember = $this->fillData($arrDataMember, $objData);
                // Generate chart data
                $arrChart[$objData->currency . '_Members']['data'][$objData->dateGroup][1] = ((float) $arrChart[$objData->currency . '_Members']['data'][$objData->dateGroup][1] + $objData->total_sales);
            } else {
                $arrDataGuests = $this->fillData($arrDataGuests, $objData);
                // Generate chart data
                $arrChart[$objData->currency . '_Guests']['data'][$objData->dateGroup][1] = ((float) $arrChart[$objData->currency . '_Guests']['data'][$objData->dateGroup][1] + $objData->total_sales);
            }
        }

        // Apply formatting
        $arrDataMember = $this->formatValues($arrDataMember, $arrCurrencies);
        $arrDataGuests = $this->formatValues($arrDataGuests, $arrCurrencies);

        // Switch from associative array to index based for apexcharts
        $arrChart = array_map(function ($arrItem) {
            $arrItem['data'] = array_values($arrItem['data']);
            return $arrItem;
        },$arrChart);

        $this->Template->dataMember   = $arrDataMember;
        $this->Template->dataGuests   = $arrDataGuests;
        $this->Template->chart        = $arrChart;
        $this->Template->periodFormat = $period->getJavascriptClosure();
    }

    /**
     * @param array  $arrData
     * @param object $objData
     *
     * @return array
     */
    private function fillData($arrData, $objData)
    {
        $arrData['rows'][$objData->dateGroup]['columns']['orders']['value'] += $objData->total_orders;
        $arrData['rows'][$objData->dateGroup]['columns']['products']['value'] += $objData->total_products;
        $arrData['rows'][$objData->dateGroup]['columns']['items']['value'] += $objData->total_items;

        if (!\is_array($arrData['rows'][$objData->dateGroup]['columns']['sales']['value'])) {
            $arrData['rows'][$objData->dateGroup]['columns']['sales']['value'] = array();
        }

        $arrData['rows'][$objData->dateGroup]['columns']['sales']['value'][$objData->currency] = ($arrData['rows'][$objData->dateGroup]['columns']['sales']['value'][$objData->currency] ?? 0) + $objData->total_sales;

        // Summary in the footer
        $arrData['footer']['orders']['value'] += $objData->total_orders;
        $arrData['footer']['products']['value'] += $objData->total_products;
        $arrData['footer']['items']['value'] += $objData->total_items;
        $arrData['footer']['sales']['value'][$objData->currency] = ((float) ($arrData['footer']['sales']['value'][$objData->currency] ?? 0) + $objData->total_sales);

        return $arrData;
    }

    protected function initializeData(PeriodInterface $period, $intStart, $intStop)
    {
        $arrData = array('rows' => array());

        $arrData['header'] = [
            "period" => [
                'value'  => &$GLOBALS['TL_LANG']['ISO_REPORT']['period'],
                'header' => true,
            ],
            "orders" => [
                'value'      => &$GLOBALS['TL_LANG']['ISO_REPORT']['orders#'],
                'attributes' => ' style="text-align:right"',
            ],
            "products" => [
                'value'      => &$GLOBALS['TL_LANG']['ISO_REPORT']['products#'],
                'attributes' => ' style="text-align:right"',
            ],
            "items" => [
                'value'      => &$GLOBALS['TL_LANG']['ISO_REPORT']['items#'],
                'attributes' => ' style="text-align:right"',
            ],
            "sales" => [
                'value'      => &$GLOBALS['TL_LANG']['ISO_REPORT']['sales#'],
                'attributes' => ' style="text-align:right"',
            ],
        ];

        $arrData['footer'] = [
            "period" => [
                'value' => $GLOBALS['TL_LANG']['ISO_REPORT']['sums'],
            ],
            "orders" => [
                'value'      => 0,
                'attributes' => ' style="text-align:right"',
            ],
            "products" => [
                'value'      => 0,
                'attributes' => ' style="text-align:right"',
            ],
            "items" => [
                'value'      => 0,
                'attributes' => ' style="text-align:right"',
            ],
            "sales" => [
                'value'      => [],
                'attributes' => ' style="text-align:right"',
            ],
        ];

        while ($intStart <= $intStop) {
            $arrData['rows'][$period->getKey($intStart)] = [
                'columns' => [
                    "period" => [
                        'value' => $period->format($intStart),
                    ],
                    "orders" => [
                        'value'      => 0,
                        'attributes' => ' style="text-align:right"',
                    ],
                    "products" => [
                        'value'      => 0,
                        'attributes' => ' style="text-align:right"',
                    ],
                    "items" => [
                        'value'      => 0,
                        'attributes' => ' style="text-align:right"',
                    ],
                    "sales" => [
                        'value'      => 0,
                        'attributes' => ' style="text-align:right"',
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
        $arrSession = Session::getInstance()->get('iso_reports');
        $intConfig  = (int) ($arrSession[$this->name]['iso_config'] ?? 0);

        $arrData       = array();
        $arrCurrencies = Database::getInstance()->execute("
            SELECT DISTINCT currency FROM tl_iso_config WHERE currency!=''
            " . static::getConfigProcedure() . "
            " . ($intConfig > 0 ? ' AND id=' . $intConfig : '') . "
        ")->fetchEach('currency');

        foreach ($arrCurrencies as $currency) {
            $arrData[$currency . '_Members']['name']     = sprintf($GLOBALS['TL_LANG']['ISO_REPORT']['members_currency'], $currency);
            $arrData[$currency . '_Guests']['name']      = sprintf($GLOBALS['TL_LANG']['ISO_REPORT']['guests_currency'], $currency);
        }

        while ($intStart <= $intStop) {
            foreach ($arrCurrencies as $currency) {
                $arrData[$currency . '_Members']['data'][$period->getKey($intStart)][0] = $intStart;
                $arrData[$currency . '_Members']['data'][$period->getKey($intStart)][1] = 0;
                $arrData[$currency . '_Guests']['data'][$period->getKey($intStart)][0]  = $intStart;
                $arrData[$currency . '_Guests']['data'][$period->getKey($intStart)][1]  = 0;
            }

            $intStart = $period->getNext($intStart);
        }

        return $arrData;
    }

    protected function formatValues($arrData, $arrCurrencies)
    {
        // Format row totals
        foreach ($arrData['rows'] as $dateGroup => $arrRow) {
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

            $arrData['footer']['sales']['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
        }

        if (empty($arrData['footer']['sales']['value'])) {
            $arrData['footer']['sales']['value'] = 0;
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
