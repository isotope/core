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

use Contao\Date;
use Contao\Message;
use Contao\System;
use Isotope\Model\OrderStatus;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

abstract class Sales extends Report
{

    // @deprecated
    protected $strDateField = 'locked';


    public function generate()
    {
        $this->initializeDefaultValues();

        return parent::generate();
    }

    protected function initializeDefaultValues()
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

        // Set default session data
        $arrSession = $sessionBag->get('iso_reports');

        if (empty($arrSession[$this->name]['period'])) {
            $arrSession[$this->name]['period'] = 'month';
        }

        if (empty($arrSession[$this->name]['columns'])) {
            $arrSession[$this->name]['columns'] = '6';
        }

        if (!\in_array($arrSession[$this->name]['date_field'] ?? null, ['locked', 'date_paid', 'date_shipped'], true)) {
            $arrSession[$this->name]['date_field'] = 'locked';
        }

        if (empty($arrSession[$this->name]['from'])) {
            $arrSession[$this->name]['from'] = '';
        } elseif (!is_numeric($arrSession[$this->name]['from'])) {
            // Convert date formats into timestamps
            try {
                $objDate = new Date($arrSession[$this->name]['from'], $GLOBALS['TL_CONFIG']['dateFormat']);
                $arrSession[$this->name]['from'] = $objDate->tstamp;
            } catch (\OutOfBoundsException $e) {
                Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['date'], $GLOBALS['TL_CONFIG']['dateFormat']));
                $arrSession[$this->name]['from'] = '';
            }
        }

        $sessionBag->set('iso_reports', $arrSession);
    }


    protected function getSelectFromPanel()
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');
        $arrSession = $sessionBag->get('iso_reports');

        return [
            'name'      => 'from',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['from'],
            'type'      => 'date',
            'format'    => $GLOBALS['TL_CONFIG']['dateFormat'],
            'value'     => ($arrSession[$this->name]['from'] ? Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['from']) : ''),
            'class'     => 'tl_from',
        ];
    }


    protected function getSelectColumnsPanel()
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');
        $arrSession = $sessionBag->get('iso_reports');

        return [
            'name'  => 'columns',
            'label' => &$GLOBALS['TL_LANG']['ISO_REPORT']['columns'],
            'type'  => 'text',
            'value' => (int) $arrSession[$this->name]['columns'],
            'class' => 'tl_columns',
        ];
    }


    protected function getPeriodConfiguration($strPeriod)
    {
        switch ($strPeriod) {
            case 'day':
                $publicDate  = 'd.m.y';
                $privateDate = 'Ymd';
                $sqlDate     = '%Y%m%d';
                $jsDate      = '%d.%m.%y';
                break;

            case 'week':
                $publicDate  = '\K\W W/y';
                $privateDate = 'YW';
                $sqlDate     = '%Y%u';
                $jsDate      = 'KW %U/%y';
                break;

            case 'month':
                $publicDate  = 'm/Y';
                $privateDate = 'Ym';
                $sqlDate     = '%Y%m';
                $jsDate      = '%m/%Y';
                break;

            case 'year':
                $publicDate = 'Y';
                $privateDate = 'Y';
                $sqlDate = '%Y';
                $jsDate = '%Y';
                break;

            default:
                throw new \RuntimeException('Invalid period "' . $strPeriod . '". Reset your session to continue.');
        }

        return array($publicDate, $privateDate, $sqlDate, $jsDate);
    }


    protected function getStatusPanel()
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

        $arrStatus = array(''=>&$GLOBALS['TL_LANG']['ISO_REPORT']['all']);

        /** @type OrderStatus[] $objStatus */
        $objStatus = OrderStatus::findAll(array('order'=>'sorting'));

        if (null !== $objStatus) {
            foreach ($objStatus as $currentStatus) {
                $arrStatus[$currentStatus->id] = $currentStatus->getName();
            }
        }

        $arrSession = $sessionBag->get('iso_reports');
        $varValue = (int) ($arrSession[$this->name]['iso_status'] ?? 0);

        return [
            'name'      => 'iso_status',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['status'],
            'type'      => 'filter',
            'value'     => $varValue,
            'active'    => (bool) $varValue,
            'class'     => 'iso_status',
            'options'   => $arrStatus,
        ];
    }


    protected function getDateFieldPanel()
    {
        /** @var AttributeBagInterface $sessionBag */
        $sessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

        $arrSession = $sessionBag->get('iso_reports');
        $varValue = $arrSession[$this->name]['date_field'];

        return [
            'name'      => 'date_field',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['date_field'],
            'type'      => 'filter',
            'value'     => $varValue,
            'class'     => 'iso_date_field',
            'options' => [
                'locked'       => $GLOBALS['TL_LANG']['ISO_REPORT']['locked'],
                'date_paid'    => $GLOBALS['TL_LANG']['ISO_REPORT']['date_paid'],
                'date_shipped' => $GLOBALS['TL_LANG']['ISO_REPORT']['date_shipped'],
            ],
        ];
    }
}
