<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


abstract class IsotopeReport extends Backend
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

		$this->import('Isotope');
	}


	public function __get($strKey)
	{
		return $this->arrData[$strKey];
	}


	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}


	public function generate()
	{
		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
		{
			$session = $this->Session->getData();

			foreach (array_keys($_POST) as $strKey)
			{
				$session['iso_reports'][$this->name][$strKey] = $this->Input->post($strKey);
			}

			$this->Session->setData($session);

			$this->reload();
		}

		$this->Template = new BackendTemplate($this->strTemplate);
		$this->Template->setData($this->arrData);

		// Filter stuff
		$this->Template->action = ampersand($this->Environment->request);
		$this->Template->panels = $this->getPanels();

		// Back button
		$this->Template->back_href = 'contao/main.php?do=reports';
		$this->Template->back_title = specialchars($GLOBALS['TL_LANG']['MSC']['backBT']);
		$this->Template->back_button = $GLOBALS['TL_LANG']['MSC']['backBT'];

		$this->compile();

		return $this->Template->parse();
	}

	abstract protected function compile();


	protected function getPanels()
	{
		return array(array($this->getLimitPanel(), $this->getSearchPanel(), $this->getSortingPanel()));
	}


	protected function getLimitPanel()
	{
		if (empty($this->arrLimitOptions))
		{
			return null;
		}

		$arrSession = $this->Session->get('iso_reports');

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

		$arrSession = $this->Session->get('iso_reports');
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

		$arrSession = $this->Session->get('iso_reports');
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
}

