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
use Isotope\Report\Period\PeriodFactory;
use Isotope\Report\Period\PeriodInterface;

class MembersRegistration extends Sales
{
    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_report_members_registration';

    protected function compile(): void
    {
        $arrSession    = Session::getInstance()->get('iso_reports');

        $strPeriod = (string) $arrSession[$this->name]['period'];
        $intStart  = (int) $arrSession[$this->name]['start'];
        $intStop   = (int) $arrSession[$this->name]['stop'];

        $period   = PeriodFactory::create($strPeriod);
        $intStart = $period->getPeriodStart($intStart);
        $intStop  = $period->getPeriodEnd($intStop);
        $dateFrom = $period->getKey($intStart);
        $dateTo   = $period->getKey($intStop);

        $dateGroup = $period->getSqlField('m.dateAdded');

        $objData = Database::getInstance()->query("
            SELECT
                COUNT(m.id) AS new_members,
                $dateGroup AS dateGroup
            FROM tl_member m
            GROUP BY dateGroup
            HAVING dateGroup>=$dateFrom AND dateGroup<=$dateTo
        ");

        $arrData = $this->initializeData($period, $intStart, $intStop);
        $arrChart = $this->initializeChart($period, $intStart, $intStop);

        $all_members = 0;
        while ($objData->next()) {
            $arrData['rows'][$objData->dateGroup]['columns']['new_members']['value'] = (int)$objData->new_members;
            $all_members += $objData->new_members;
            $arrData['rows'][$objData->dateGroup]['columns']['all_members']['value'] = (int)$all_members;

            // Generate chart data
            $arrChart['series'][0]['data'][$objData->dateGroup] = (int)$all_members;
            $arrChart['series'][1]['data'][$objData->dateGroup] = (int)$objData->new_members;
        }

        $arrData['rows'] = array_reduce($arrData['rows'], function ($arrCarry, $arrItem) {
            $prevItem = sizeof($arrCarry) > 0 ? $arrCarry[sizeof($arrCarry) - 1] : null;
            if($arrItem['columns']['all_members']['value'] == 0 && $prevItem != null) {
                $arrItem['columns']['all_members']['value'] = $prevItem['columns']['all_members']['value'];
            }
            $arrCarry[] = $arrItem;
            return $arrCarry;
        }, array());

        // Switch from associative array to index based for apexcharts
        for ($i = 0, $iMax = count($arrChart['series']); $i < $iMax; $i++) {
            $arrChart['series'][$i]['data'] = is_array($arrChart['series'][$i]['data']) ? array_values($arrChart['series'][$i]['data']): [];
        }
        for ($i = 0, $iMax = count($arrChart['series'][0]['data']); $i < $iMax; $i++) {
            if ($arrChart['series'][0]['data'][$i] == 0 && $i > 0) {
                $arrChart['series'][0]['data'][$i] = $arrChart['series'][0]['data'][$i - 1];
            }
        }

        $this->Template->chart        = $arrChart;
        $this->Template->data         = $arrData;
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
            "new_members" => [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_new'],
                'attributes'    => ' style="text-align:right"',
            ],
            "all_members" => [
                'value'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_all'],
                'attributes'    => ' style="text-align:right"',
            ],
        ];

        while ($intStart <= $intStop) {
            $arrData['rows'][$period->getKey($intStart)] = [
                'columns' => [
                    "period" => [
                        'value'         => $period->format($intStart),
                    ],
                    "new_members" => [
                        'value'         => 0,
                        'attributes'    => ' style="text-align:right"',
                    ],
                    "all_members" => [
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
        $arrData = array();
        $arrData['series'] = [[
            'name' => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_all'],
            'type' => 'line'
        ],
            [
                'name' => &$GLOBALS['TL_LANG']['ISO_REPORT']['members_new'],
                'type' => 'column'
            ]];

        while ($intStart <= $intStop) {
            $arrData['labels'][] = $intStart;
            $arrData['series'][0]['data'][$period->getKey($intStart)] = 0;
            $arrData['series'][1]['data'][$period->getKey($intStart)] = 0;
            $intStart = $period->getNext($intStart);
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
}
