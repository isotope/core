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

use Isotope\Isotope;
use Isotope\Model\Config;
use Haste\Generator\RowClass;
use Isotope\Model\ProductCollection;
use Isotope\Report\Period\PeriodFactory;
use Isotope\Report\Period\PeriodInterface;

class SalesMember extends Sales
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_report_sales_member';

    protected function compile()
    {
        $arrSession    = \Session::getInstance()->get('iso_reports');

        $intConfig = (int) ($arrSession[$this->name]['iso_config'] ?? 0);
        $intStart  = (int) ($arrSession[$this->name]['start'] ?? 0);
        $intStop   = (int) ($arrSession[$this->name]['stop'] ?? 0);
        $intStatus = (int) ($arrSession[$this->name]['iso_status'] ?? 0);

        $period   = PeriodFactory::create('day');
        $intStart = $period->getPeriodStart($intStart);
        $intStop  = $period->getPeriodEnd($intStop);

        $bPrevEnabled = $intStop - $intStart <= 31536000; // Selected not more then a year
        $intPrevStart = $intStart - 31536000; // 1 year before
        $intPrevStop = $intStop - 31536000; // 1 year before

        if ('locked' === $this->strDateField) {
            $this->strDateField = $arrSession[$this->name]['date_field'];
        }

        $prevSql = "
            SELECT
                SUM(i2.tax_free_price * i2.quantity) AS total_sales_prev
            FROM tl_iso_product_collection o2
            LEFT JOIN tl_iso_product_collection_item i2 ON o2.id=i2.pid
            LEFT OUTER JOIN tl_iso_config c2 ON o2.config_id=c2.id
            WHERE o2.type='order' AND o2.order_status>0 AND o2.{$this->strDateField} IS NOT NULL
            " . ($intStatus > 0 ? " AND o2.order_status=".$intStatus : '') . "
            " . static::getProductProcedure('i2', 'product_id') . "
            " . ($intConfig > 0 ? " AND c2.id=".$intConfig : '') . "
            " . static::getConfigProcedure('c2') . "
            AND o2.locked >= ". $intPrevStart . " AND o2.locked <= ". $intPrevStop . "
            AND o2.member = o.member
        ";

        $objData = \Database::getInstance()->query("
            SELECT
                c.id AS config_id,
                c.currency,
                o.member as member,
                tlm.firstname as firstname,
                tlm.lastname as lastname,
                tlm.company as company,
                tlm.city as city,
                COUNT(o.id) AS total_orders,
                COUNT(i.id) AS total_products,
                COUNT(DISTINCT o.id) AS total_orders,
                COUNT(DISTINCT i.id) AS total_products,
                SUM(i.quantity) AS total_items,
                SUM(i.tax_free_price * i.quantity) AS total_sales,
                ($prevSql) as total_sales_prev
            FROM tl_iso_product_collection o
            LEFT JOIN tl_iso_product_collection_item i ON o.id=i.pid
            LEFT OUTER JOIN tl_member tlm ON o.member=tlm.id
            LEFT OUTER JOIN tl_iso_config c ON o.config_id=c.id
            WHERE o.type='order' AND o.order_status>0 AND o.{$this->strDateField} IS NOT NULL
            " . ($intStatus > 0 ? " AND o.order_status=".$intStatus : '') . "
            " . static::getProductProcedure('i', 'product_id') . "
            " . ($intConfig > 0 ? " AND c.id=".$intConfig : '') . "
            " . static::getConfigProcedure('c') . "
            AND o.locked >= ". $intStart . " AND o.locked <= ". $intStop . "
            GROUP BY member, c.id
            ORDER BY total_sales DESC
        ");

        $arrCurrencies = array();

        $arrData = ['rows' => []];

        $arrData['header'] = [
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['customer#'],
                'header'        => true,
            ],
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['city#'],
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
            [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['comparePrevYear#'],
                'attributes'    => ' style="text-align:right"',
            ],
        ];

        $arrData['footer'] = [
            [
                'value'         => $GLOBALS['TL_LANG']['ISO_REPORT']['sums'],
            ],
            [
                'value'         => '',
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
            [
                'value'         => [],
                'attributes'    => ' style="text-align:right"',
            ],
            [
                'value'         => [],
                'attributes'    => ' style="text-align:right"',
            ],
        ];

        RowClass::withKey('class')->addEvenOdd()->applyTo($arrData['rows']);

        while ($objData->next()) {
            $arrCurrencies[$objData->currency] = $objData->config_id;

            $label = '';

            if ($objData->member == 0) {
                $label = '<b>' . ($GLOBALS['TL_LANG']['ISO_REPORT']['guestOrders'] ?? '') . '</b>';
            } else if ($objData->company) {
                $label = sprintf('%s', $objData->company);
            }
            else {
                $label =  sprintf('%s %s', $objData->firstname, $objData->lastname);
            }


            $arrData['rows'][] = [
                'columns' => [
                    [
                        'value' => $label
                    ],
                    [
                        'value' => $objData->city,
                    ],
                    [
                        'value'         => $objData->total_orders,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    [
                        'value'         => $objData->total_products,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    [
                        'value'         => $objData->total_items,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    [
                        'value'         => [$objData->currency => $objData->total_sales],
                        'attributes'    => ' style="text-align:right"',
                    ],
                    [
                        'value'         => $this->calculateTrendPercentage($objData->total_sales, $objData->total_sales_prev),
                        'attributes'    => ' style="text-align:right"',
                    ],
                ],
            ];

            // Summary in the footer
            $arrData['footer'][2]['value'] += $objData->total_orders;
            $arrData['footer'][3]['value'] += $objData->total_products;
            $arrData['footer'][4]['value'] += $objData->total_items;
            $arrData['footer'][5]['value'][$objData->currency] = ((float) ($arrData['footer'][5]['value'][$objData->currency] ?? 0) + $objData->total_sales);
            $arrData['footer'][6]['value'] = '';
        }

        if (!$bPrevEnabled) {
            unset($arrData['header'][6]);
            unset($arrData['footer'][6]);

           foreach ($arrData['rows'] as $dateGroup => $arrRow) {
                unset($arrData['rows'][$dateGroup]['columns'][6]);
            }
        }

        // Apply formatting
        $arrData = $this->formatValues($arrData, $arrCurrencies);

        $this->Template->data         = $arrData;
        $this->Template->periodFormat = $period->getJavascriptClosure();
    }

    protected function calculateTrendPercentage($currentValue, $prevValue) {
        if (!$prevValue || $prevValue <= 0) {
            return null;
        }

        return round($currentValue / $prevValue - 1, 2);
    }

    protected function formatValues($arrData, $arrCurrencies)
    {
        // Format row totals
        foreach ($arrData['rows'] as $dateGroup => $arrRow) {
            if (\is_array($arrRow['columns'][5]['value'])) {
                foreach ($arrRow['columns'][5]['value'] as $currency => $varValue) {
                    /** @type Config $objConfig */
                    $objConfig = Config::findByPk($arrCurrencies[$currency]);
                    Isotope::setConfig($objConfig);

                    $arrData['rows'][$dateGroup]['columns'][5]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
                }
            }

            if (!empty($arrData['rows'][$dateGroup]['columns'][6]['value'])) {
                $number = $arrData['rows'][$dateGroup]['columns'][6]['value'];
                $formated = number_format($number * 100, 0);


                if ($number > 0) {
                    $arrData['rows'][$dateGroup]['columns'][6]['value'] =
                        sprintf('<span style="color:green">+%s %%</span>',  $formated);
                }
                else if ($number == 0) {
                    $arrData['rows'][$dateGroup]['columns'][6]['value'] =
                        sprintf('%s %%',  $formated);
                }
                else if ($number < 0) {
                    $arrData['rows'][$dateGroup]['columns'][6]['value'] =
                        sprintf('<span style="color:red">%s %%</span>',  $formated);
                }
            }
        }

        // Format footer totals
        foreach ($arrData['footer'][5]['value'] as $currency => $varValue) {
            /** @type Config $objConfig */
            $objConfig = Config::findByPk($arrCurrencies[$currency]);
            Isotope::setConfig($objConfig);

            $arrData['footer'][5]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
        }

        if (empty($arrData['footer'][5]['value'])) {
            $arrData['footer'][5]['value'] = 0;
        }

        return $arrData;
    }


    protected function initializeDefaultValues()
    {
        // Set default session data
        $arrSession = \Session::getInstance()->get('iso_reports');

        if (empty($arrSession[$this->name]['stop'])) {
            $arrSession[$this->name]['stop'] = time();
        } elseif (!is_numeric($arrSession[$this->name]['stop'])) {
            // Convert date formats into timestamps
            try {
                $objDate = new \Date($arrSession[$this->name]['stop'], $GLOBALS['TL_CONFIG']['dateFormat']);
                $arrSession[$this->name]['stop'] = $objDate->tstamp;
            } catch (\OutOfBoundsException $e) {
                \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['date'], $GLOBALS['TL_CONFIG']['dateFormat']));
                $arrSession[$this->name]['stop'] = time();
            }
        }

        if (empty($arrSession[$this->name]['start'])) {
            $arrSession[$this->name]['start'] = strtotime('-6 months');
        } elseif (!is_numeric($arrSession[$this->name]['start'])) {
            // Convert date formats into timestamps
            try {
                $objDate = new \Date($arrSession[$this->name]['start'], $GLOBALS['TL_CONFIG']['dateFormat']);
                $arrSession[$this->name]['start'] = $objDate->tstamp;
            } catch (\OutOfBoundsException $e) {
                \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['date'], $GLOBALS['TL_CONFIG']['dateFormat']));
                $arrSession[$this->name]['start'] = strtotime('-6 months');
            }
        }

        \Session::getInstance()->set('iso_reports', $arrSession);

        parent::initializeDefaultValues();
    }
}
