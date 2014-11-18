<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2014 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://isotopeecommerce.org
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\Report;

use Isotope\Isotope;
use Isotope\Model\Config;
use Haste\Generator\RowClass;
use Isotope\Model\OrderStatus;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollectionItem;


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

        $objData = \Database::getInstance()->prepare("
            SELECT
                c.id AS config_id,
                c.currency,
                o.locked AS date,
                COUNT(o.id) AS total_orders,
                COUNT(i.id) AS total_products,
                COUNT(DISTINCT o.id) AS total_orders,
                COUNT(DISTINCT i.id) AS total_products,
                SUM(i.quantity) AS total_items,
                SUM(i.tax_free_price * i.quantity) AS total_sales,
                DATE_FORMAT(FROM_UNIXTIME(o.{$this->strDateField}), ?) AS dateGroup
            FROM " . ProductCollection::getTable() . " o
            LEFT JOIN " . ProductCollectionItem::getTable() . " i ON o.id=i.pid
            LEFT JOIN " . OrderStatus::getTable() . " os ON os.id=o.order_status
            LEFT OUTER JOIN " . Config::getTable() . " c ON o.config_id=c.id
            WHERE o.type='order' AND o.order_status>0 AND o.locked!=''
            " . ($intStatus > 0 ? " AND o.order_status=".$intStatus : '') . "
            " . $this->getProductProcedure('i', 'product_id') . "
            " . ($intConfig > 0 ? " AND c.id=".$intConfig : '') . "
            " . $this->getConfigProcedure('c') . "
            GROUP BY config_id, dateGroup
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
        ")->execute($sqlDate);

        $arrCurrencies = array();
        $arrData = $this->initializeData($strPeriod, $intStart, $intStop, $privateDate, $publicDate);
        $arrChart = $this->initializeChart($strPeriod, $intStart, $intStop, $privateDate, $publicDate);

        while ($objData->next())
        {
            $arrCurrencies[$objData->currency] = $objData->config_id;

            $arrData['rows'][$objData->dateGroup]['columns'][1]['value'] += $objData->total_orders;
            $arrData['rows'][$objData->dateGroup]['columns'][2]['value'] += $objData->total_products;
            $arrData['rows'][$objData->dateGroup]['columns'][3]['value'] += $objData->total_items;

            if (!is_array($arrData['rows'][$objData->dateGroup]['columns'][4]['value']))
            {
                $arrData['rows'][$objData->dateGroup]['columns'][4]['value'] = array();
            }

            $arrData['rows'][$objData->dateGroup]['columns'][4]['value'][$objData->currency] = $arrData['rows'][$objData->dateGroup]['columns'][4]['value'][$objData->currency] + $objData->total_sales;

            // Summary in the footer
            $arrData['footer'][1]['value'] += $objData->total_orders;
            $arrData['footer'][2]['value'] += $objData->total_products;
            $arrData['footer'][3]['value'] += $objData->total_items;
            $arrData['footer'][4]['value'][$objData->currency] = ((float) $arrData['footer'][4]['value'][$objData->currency] + $objData->total_sales);

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
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['period'],
                'header'        => true,
            ),
            array
            (
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['orders#'],
                'attributes'    => ' style="text-align:right"',
            ),
            array
            (
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['products#'],
                'attributes'    => ' style="text-align:right"',
            ),
            array
            (
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['items#'],
                'attributes'    => ' style="text-align:right"',
            ),
            array
            (
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['sales#'],
                'attributes'    => ' style="text-align:right"',
            ),
        );

        $arrData['footer']      = array
        (
            array
            (
                'value'         => $GLOBALS['TL_LANG']['ISO_REPORT']['sums'],
            ),
            array
            (
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ),
            array
            (
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ),
            array
            (
                'value'         => 0,
                'attributes'    => ' style="text-align:right"',
            ),
            array
            (
                'value'         => array(),
                'attributes'    => ' style="text-align:right"',
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
                        'value'         => \Date::parse($publicDate, $intStart),
                    ),
                    array
                    (
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ),
                    array
                    (
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ),
                    array
                    (
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ),
                    array
                    (
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
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
        $configTable = Config::getTable();

        $arrData = array();
        $arrCurrencies = \Database::getInstance()->execute("
            SELECT DISTINCT currency FROM $configTable WHERE currency!=''
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
                $arrData[$currency]['data'][date($privateDate, $intStart)]['x'] = ($strPeriod == 'day' ? $intStart : \Date::parse($publicDate, $intStart));
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
            if (is_array($arrRow['columns'][4]['value']))
            {
                foreach ($arrRow['columns'][4]['value'] as $currency => $varValue)
                {
                    /** @type Config $objConfig */
                    $objConfig = Config::findByPk($arrCurrencies[$currency]);
                    Isotope::setConfig($objConfig);

                    $arrData['rows'][$dateGroup]['columns'][4]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
                }
            }
        }

        // Format footer totals
        foreach ($arrData['footer'][4]['value'] as $currency => $varValue)
        {
            /** @type Config $objConfig */
            $objConfig = Config::findByPk($arrCurrencies[$currency]);
            Isotope::setConfig($objConfig);

            $arrData['footer'][4]['value'][$currency] = Isotope::formatPriceWithCurrency($varValue);
        }

        if (empty($arrData['footer'][4]['value']))
        {
            $arrData['footer'][4]['value'] = 0;
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
            try {
                $objDate = new \Date($arrSession[$this->name]['stop'], $GLOBALS['TL_CONFIG']['dateFormat']);
                $arrSession[$this->name]['stop'] = $objDate->tstamp;
            } catch (\OutOfBoundsException $e) {
                \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['date'], $GLOBALS['TL_CONFIG']['dateFormat']));
                $arrSession[$this->name]['stop'] = time();
            }
        }

        if ($arrSession[$this->name]['start'] == '')
        {
            $arrSession[$this->name]['start'] = strtotime('-6 months');
        }
        elseif (!is_numeric($arrSession[$this->name]['start']))
        {
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

