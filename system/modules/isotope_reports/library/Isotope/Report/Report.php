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

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\BackendUser;
use Contao\Controller;
use Contao\Date;
use Contao\Input;
use Contao\Session;
use Contao\StringUtil;
use Contao\System;
use Isotope\Backend\Product\Permission;
use Isotope\Model\Config;


abstract class Report extends Backend
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'iso_report_default';

    /**
     * Report config
     * @var array
     */
    protected $arrData;

    /**
     * Isotope object
     * @var object
     */
    protected $Isotope;

    /**
     * Limit options
     * @var array
     */
    protected $arrLimitOptions = array();

    /**
     * Search options
     * @var array
     */
    protected $arrSearchOptions = array();

    /**
     * Sorting options
     * @var array
     */
    protected $arrSortingOptions = array();


    public function __construct($dc, $arrData)
    {
        $this->arrData = $arrData;

        parent::__construct();
    }

    public function __get($strKey)
    {
        if (isset($this->arrObjects[$strKey])) {
            return $this->arrObjects[$strKey];
        }

        return $this->arrData[$strKey];
    }

    public function __set($strKey, $varValue)
    {
        $this->arrData[$strKey] = $varValue;
    }

    public function __isset($strKey)
    {
        return isset($this->arrData[$strKey]);
    }

    public function generate()
    {
        if ('tl_filters' === Input::post('FORM_SUBMIT')) {
            $session = Session::getInstance()->getData();

            if (Input::post('filter_reset')) {
                $session['iso_reports'][$this->name] = [];
            } else {
                foreach ($_POST as $strKey => $v) {
                    $session['iso_reports'][$this->name][$strKey] = Input::post($strKey);
                }
            }

            Session::getInstance()->setData($session);
            Controller::reload();
        }

        $this->Template = new BackendTemplate($this->strTemplate);
        $this->Template->setData($this->arrData);

        // Filter stuff
        $this->Template->panels = $this->getPanels();

        $this->Template->buttons = $this->getButtons();
        $this->Template->headline = $this->arrData['description'];
        $this->Template->class = $this->arrData['name'] . ($this->arrData['class'] ? ' '.$this->arrData['name'] : '');

        $this->compile();

        return $this->Template->parse();
    }


    abstract protected function compile();


    protected function getPanels()
    {
        if (!\is_array($this->arrData['panels'])) {
            return array();
        }

        $return = array();

        foreach ($this->arrData['panels'] as $group=>$callbacks) {
            foreach ($callbacks as $callback) {
                if (\is_array($callback)) {
                    $objCallback = System::importStatic($callback[0]);
                    $buffer = $objCallback->{$callback[1]}();
                } else {
                    $buffer = $this->$callback();
                }

                if ($buffer !== null) {
                    $return[$group][] = $buffer;
                }
            }
        }

        return $return;
    }


    protected function getButtons()
    {
        return array('<a href="contao/main.php?do=reports" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>');
    }


    protected function getLimitPanel()
    {
        if (empty($this->arrLimitOptions)) {
            return null;
        }

        $arrSession = Session::getInstance()->get('iso_reports');

        return [
            'name'          => 'tl_limit',
            'label'         => &$GLOBALS['TL_LANG']['ISO_REPORT']['show'],
            'class'         => 'tl_limit',
            'type'          => 'filter',
            'value'         => $arrSession[$this->name]['tl_limit'],
            'options'       => $this->arrLimitOptions,
            'attributes'    => ' onchange="this.form.submit()"',
        ];
    }


    protected function getSearchPanel()
    {
        if (empty($this->arrSearchOptions)) {
            return null;
        }

        $arrSession = Session::getInstance()->get('iso_reports');
        $varValue = array('tl_field'=>(string) $arrSession[$this->name]['tl_field'], 'tl_value'=>(string) $arrSession[$this->name]['tl_value']);

        return [
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['search'],
            'class'     => 'tl_search',
            'type'      => 'search',
            'value'     => $varValue,
            'active'    => ($varValue['tl_field'] != '' && $varValue['tl_value'] != ''),
            'options'   => $this->arrSearchOptions,
        ];
    }


    protected function getSortingPanel()
    {
        if (empty($this->arrSortingOptions)) {
            return null;
        }

        $arrSession = Session::getInstance()->get('iso_reports');
        $varValue = (string) $arrSession[$this->name]['tl_sort'];

        return [
            'name'      => 'tl_sort',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['sort'],
            'type'      => 'filter',
            'value'     => $varValue,
            'class'     => 'tl_sorting',
            'options'   => $this->arrSortingOptions,
        ];
    }


    protected function getFilterByConfigPanel()
    {
        $arrConfigs = array(''=>&$GLOBALS['TL_LANG']['ISO_REPORT']['all']);
        $objConfigs = Config::findAll(array('order'=>'name'));

        if (null !== $objConfigs) {
            while ($objConfigs->next()) {
                $arrConfigs[$objConfigs->id] = $objConfigs->name;
            }
        }

        $arrSession = Session::getInstance()->get('iso_reports');
        $varValue = (string) ($arrSession[$this->name]['iso_config'] ?? '');

        return [
            'name'      => 'iso_config',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['shop_config'],
            'type'      => 'filter',
            'value'     => $varValue,
            'active'    => ($varValue != ''),
            'class'     => 'iso_config',
            'options'   => $arrConfigs,
        ];
    }


    protected function getSelectPeriodPanel()
    {
        $arrSession = Session::getInstance()->get('iso_reports');

        return [
            'name'      => 'period',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['period'],
            'type'      => 'filter',
            'value'     => (string) $arrSession[$this->name]['period'],
            'class'     => 'tl_period',
            'options'   =>
                [
                'day'   => &$GLOBALS['TL_LANG']['ISO_REPORT']['day'],
                'week'  => &$GLOBALS['TL_LANG']['ISO_REPORT']['week'],
                'month' => &$GLOBALS['TL_LANG']['ISO_REPORT']['month'],
                'year'  => &$GLOBALS['TL_LANG']['ISO_REPORT']['year']
                ]
        ];
    }


    protected function getSelectStartPanel()
    {
        $arrSession = Session::getInstance()->get('iso_reports');

        return [
            'name'      => 'start',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['from'],
            'type'      => 'date',
            'format'    => $GLOBALS['TL_CONFIG']['dateFormat'],
            'value'     => Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['start']),
            'class'     => 'tl_start',
        ];
    }


    protected function getSelectStopPanel()
    {
        $arrSession = Session::getInstance()->get('iso_reports');

        return [
            'name'      => 'stop',
            'label'     => &$GLOBALS['TL_LANG']['ISO_REPORT']['to'],
            'type'      => 'date',
            'format'    => $GLOBALS['TL_CONFIG']['dateFormat'],
            'value'     => Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['stop']),
            'class'     => 'tl_stop',
        ];
    }

    /**
     * Return string to filter database query by user allowed products
     *
     * @param string $strTable  Table name or alias (optional)
     * @param string $strField  Table field or alias (optional)
     * @param string $strPrefix Prefix for query (e.g. AND)
     *
     * @return string
     */
    public static function getProductProcedure($strTable = 'tl_iso_product', $strField = 'id', $strPrefix = ' AND ')
    {
        $arrAllowedProducts = Permission::getAllowedIds();

        if (true === $arrAllowedProducts) {
            return '';
        }

        if (false === $arrAllowedProducts || empty($arrAllowedProducts)) {
            $arrAllowedProducts = array(0);
        }

        return $strPrefix . $strTable . '.' . $strField . ' IN (' . implode(',', $arrAllowedProducts) . ')';
    }

    /**
     * Return string to filter database query by user allowed shop configs
     *
     * @param string $strTable  Table name or alias (optional)
     * @param string $strField  Table field or alias (optional)
     * @param string $strPrefix Prefix for query (e.g. AND)
     *
     * @return string
     */
    public static function getConfigProcedure($strTable = 'tl_iso_config', $strField = 'id', $strPrefix = ' AND ')
    {
        if (BackendUser::getInstance()->isAdmin) {
            return '';
        }

        $arrConfig = StringUtil::deserialize(BackendUser::getInstance()->iso_configs);

        if (empty($arrConfig) || !\is_array($arrConfig)) {
            $arrConfig = array(0);
        }

        return $strPrefix . $strTable . '.' . $strField . ' IN (' . implode(',', $arrConfig) . ')';
    }
}
