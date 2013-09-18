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


abstract class Report extends \Backend
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


	public function __construct($arrData)
	{
		$this->arrData = $arrData;

		parent::__construct();
	}


	public function __get($strKey)
	{
	    if (isset($this->arrObjects[$strKey]))
		{
			return $this->arrObjects[$strKey];
		}

		return $this->arrData[$strKey];
	}


	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}


	public function generate()
	{
		if (\Input::post('FORM_SUBMIT') == 'tl_filters')
		{
			$session = \Session::getInstance()->getData();

			foreach (array_keys($_POST) as $strKey)
			{
				$session['iso_reports'][$this->name][$strKey] = \Input::post($strKey);
			}

			\Session::getInstance()->setData($session);

			$this->reload();
		}

		$this->Template = new \BackendTemplate($this->strTemplate);
		$this->Template->setData($this->arrData);

		// Filter stuff
		$this->Template->action = ampersand($this->Environment->request);
		$this->Template->panels = $this->getPanels();

		// Buttons
		$this->Template->buttons = $this->getButtons();

		$this->Template->headline = $this->arrData['description'];

		$this->compile();

		return $this->Template->parse();
	}


	abstract protected function compile();


	protected function getPanels()
	{
		if (!is_array($this->arrData['panels']))
		{
			return array();
		}

		$return = array();

		foreach ($this->arrData['panels'] as $group=>$callbacks)
		{
			foreach ($callbacks as $callback)
			{
				if (is_array($callback))
				{
					$objCallback = \System::importStatic($callback[0]);
					$buffer = $objCallback->$callback[1]();
				}
				else
				{
					$buffer = $this->$callback();
				}

				if ($buffer !== null)
				{
					$return[$group][] = $buffer;
				}
			}
		}

		return $return;
	}


	protected function getButtons()
	{
    	return array('<a href="contao/main.php?do=reports" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>');
	}


	protected function getLimitPanel()
	{
		if (empty($this->arrLimitOptions))
		{
			return null;
		}

		$arrSession = \Session::getInstance()->get('iso_reports');

		return array
		(
			'name'			=> 'tl_limit',
			'label'			=> 'Anzeigen:',
			'class'			=> 'tl_limit',
			'type'			=> 'filter',
			'value'			=> $arrSession[$this->name]['tl_limit'],
			'options'		=> $this->arrLimitOptions,
			'attributes'	=> ' onchange="this.form.submit()"',
		);
	}


	protected function getSearchPanel()
	{
		if (empty($this->arrSearchOptions))
		{
			return null;
		}

		$arrSession = \Session::getInstance()->get('iso_reports');
		$varValue = array('tl_field'=>(string) $arrSession[$this->name]['tl_field'], 'tl_value'=>(string) $arrSession[$this->name]['tl_value']);

		return array
		(
			'label'			=> 'Suchen:',
			'class'			=> 'tl_search',
			'type'			=> 'search',
			'value'			=> $varValue,
			'active'		=> ($varValue['tl_field'] != '' && $varValue['tl_value'] != ''),
			'options'		=> $this->arrSearchOptions,
		);
	}


	protected function getSortingPanel()
	{
		if (empty($this->arrSortingOptions))
		{
			return null;
		}

		$arrSession = \Session::getInstance()->get('iso_reports');
		$varValue = (string) $arrSession[$this->name]['tl_sort'];

		return array
		(
			'name'			=> 'tl_sort',
			'label'			=> 'Sortieren:',
			'type'			=> 'filter',
			'value'			=> $varValue,
			'class'			=> 'tl_sorting',
			'options'		=> $this->arrSortingOptions,
		);
	}


	protected function getFilterByConfigPanel()
	{
		$arrConfigs = array(''=>&$GLOBALS['ISO_LANG']['REPORT']['all']);
		$objConfigs = \Database::getInstance()->execute("SELECT id, name FROM tl_iso_config ORDER BY name");

		while ($objConfigs->next())
		{
			$arrConfigs[$objConfigs->id] = $objConfigs->name;
		}

		$arrSession = \Session::getInstance()->get('iso_reports');
		$varValue = (string) $arrSession[$this->name]['iso_config'];

		return array
		(
			'name'			=> 'iso_config',
			'label'			=> 'Konfiguration: ',
			'type'			=> 'filter',
			'value'			=> $varValue,
			'active'		=> ($varValue != ''),
			'class'			=> 'iso_config',
			'options'		=> $arrConfigs,
		);
	}


	protected function getSelectPeriodPanel()
	{
		$arrSession = \Session::getInstance()->get('iso_reports');

		return array
		(
			'name'			=> 'period',
			'label'			=> 'Zeitraum:',
			'type'			=> 'filter',
			'value'			=> (string) $arrSession[$this->name]['period'],
			'class'			=> 'tl_period',
			'options'		=> array
			(
				'day'		=> 'Tag',
				'week'		=> 'Woche',
				'month'		=> 'Monat',
				'year'		=> 'Jahr',
			),
		);
	}


	protected function getSelectStartPanel()
	{
		$arrSession = \Session::getInstance()->get('iso_reports');

		return array
		(
			'name'			=> 'start',
			'label'			=> 'Von:',
			'type'			=> 'date',
			'format'		=> $GLOBALS['TL_CONFIG']['dateFormat'],
			'value'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['start']),
			'class'			=> 'tl_start',
		);
	}


	protected function getSelectStopPanel()
	{
		$arrSession = \Session::getInstance()->get('iso_reports');

		return array
		(
			'name'			=> 'stop',
			'label'			=> 'Bis:',
			'type'			=> 'date',
			'format'		=> $GLOBALS['TL_CONFIG']['dateFormat'],
			'value'			=> $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], (int) $arrSession[$this->name]['stop']),
			'class'			=> 'tl_stop',
		);
	}
}

