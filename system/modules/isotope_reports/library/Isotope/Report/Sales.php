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

use Isotope\Model\OrderStatus;

abstract class Sales extends Report
{

    // Allow extensions to use date_paid or date_shipped
    protected $strDateField = 'locked';


    public function generate()
    {
        $this->initializeDefaultValues();

        return parent::generate();
    }


    protected function initializeDefaultValues()
    {
        // Set default session data
        $arrSession = \Session::getInstance()->get('iso_reports');

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
            $arrSession[$this->name]['from'] = '';
        }
        elseif (!is_numeric($arrSession[$this->name]['from']))
        {
            // Convert date formats into timestamps
            try {
                $objDate = new \Date($arrSession[$this->name]['from'], $GLOBALS['TL_CONFIG']['dateFormat']);
                $arrSession[$this->name]['from'] = $objDate->tstamp;
            } catch (\OutOfBoundsException $e) {
                \Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['date'], $GLOBALS['TL_CONFIG']['dateFormat']));
                $arrSession[$this->name]['from'] = '';
            }
        }

        if (!isset($arrSession[$this->name]['iso_status']))
        {
            $orderStatusTable = OrderStatus::getTable();
            $objStatus = \Database::getInstance()->query("SELECT id FROM $orderStatusTable WHERE paid=1 ORDER BY sorting");
            $arrSession[$this->name]['iso_status'] = $objStatus->id;
        }

        \Session::getInstance()->set('iso_reports', $arrSession);
    }


    protected function getSelectFromPanel()
    {
        $arrSession = \Session::getInstance()->get('iso_reports');

        return array
        (
            'name'      => 'from',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['from'],
            'type'      => 'date',
            'format'    => $GLOBALS['TL_CONFIG']['dateFormat'],
            'value'     => ($arrSession[$this->name]['from'] ? \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['from']) : ''),
            'class'     => 'tl_from',
        );
    }


    protected function getSelectColumnsPanel()
    {
        $arrSession = \Session::getInstance()->get('iso_reports');

        return array
        (
            'name'  => 'columns',
            'label' => &$GLOBALS['TL_LANG']['ISO_REPORT']['columns'],
            'type'  => 'text',
            'value' => (int) $arrSession[$this->name]['columns'],
            'class' => 'tl_columns',
        );
    }


    protected function getPeriodConfiguration($strPeriod)
    {
        switch ($strPeriod)
        {
            case 'day':
                $publicDate = 'd.m.y';
                $privateDate = 'Ymd';
                $sqlDate = '%Y%m%d';
                break;

            case 'week':
                $publicDate = '\K\W W/y';
                $privateDate = 'YW';
                $sqlDate = '%Y%u';
                break;

            case 'month':
                $publicDate = 'm/Y';
                $privateDate = 'Ym';
                $sqlDate = '%Y%m';
                break;

            case 'year':
                $publicDate = 'Y';
                $privateDate = 'Y';
                $sqlDate = '%Y';
                break;

            default:
                throw new \Exception('Invalid period "' . $strPeriod . '". Reset your session to continue.');
        }

        return array($publicDate, $privateDate, $sqlDate);
    }


    protected function getStatusPanel()
    {
        $arrStatus = array(''=>&$GLOBALS['TL_LANG']['ISO_REPORT']['all']);

        /** @type OrderStatus[] $objStatus */
        $objStatus = OrderStatus::findAll(array('order'=>'sorting'));

        if (null !== $objStatus) {
            foreach ($objStatus as $currentStatus) {
                $arrStatus[$currentStatus->id] = $currentStatus->getName();
            }
        }

        $arrSession = \Session::getInstance()->get('iso_reports');
        $varValue = (int) $arrSession[$this->name]['iso_status'];

        return array
        (
            'name'      => 'iso_status',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['status'],
            'type'      => 'filter',
            'value'     => $varValue,
            'active'    => ($varValue != ''),
            'class'     => 'iso_status',
            'options'   => $arrStatus,
        );
    }
}

