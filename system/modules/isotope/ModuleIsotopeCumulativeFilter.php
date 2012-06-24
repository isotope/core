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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Yanick Witschi <yanick.witschi@certo-net.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Class ModuleIsotopeCumulativeFilter
 * Provides a cumulative filter module.
 */
class ModuleIsotopeCumulativeFilter extends ModuleIsotopeProductFilter
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_filter_cumulative';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: KIENER CUSTOM FILTER ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Override initializeFilters() to prevent module from not being shown in front end if there are no filter fields
	 * @see ModuleIsotopeProductFilter::initializeFilters()
	 * @return boolean
	 */
	protected function initializeFilters()
	{
		$this->iso_filterFields = deserialize($this->iso_filterFields, true);

		if(!empty($this->iso_filterFields))
		{
			return true;
		}

		if ($this->iso_filterTpl)
		{
			$this->strTemplate = $this->iso_filterTpl;
		}

		return false;
	}


	/**
	 * Compile the module
	 */
	protected function compile()
	{
		$this->blnCacheRequest =	($this->Input->get('cfilter') &&
									$this->Input->get('attr') &&
									$this->Input->get('v') &&
									$this->Input->get('mod') == $this->id) ? true : false;

		$this->generateFilter();

		$this->Template->linkClearAll	= ampersand(preg_replace('/\?.*/', '', $this->Environment->request));
		$this->Template->labelClearAll	= $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
	}


	/**
	 * Generates the filter
	 */
	protected function generateFilter()
	{
		// get values
		$strMode		= $this->Input->get('cfilter');
		$strField		= $this->Input->get('attr');
		$intValue		= $this->Input->get('v');
		$strFilterKey	= $strField . '::' . $intValue;

		// set filter values
		if($this->blnCacheRequest)
		{
			if($strMode == 'add')
			{
				$GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey] = array
				(
					'operator'		=> '==',
					'attribute'		=> $strField,
					'value'			=> $intValue
				);
			}
			else
			{
				unset($GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey]);
			}

			// unset GET params because the rest is done by ModuleIsotopeProductFilter::generate()
			$this->Input->setGet('mod', null);
			$this->Input->setGet('cfilter', null);
			$this->Input->setGet('attr', null);
			$this->Input->setGet('v', null);
		}

		// build filter
		foreach($this->iso_filterFields as $strField)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField];

			// Use the default routine to initialize options data
			$arrWidget = $this->prepareForWidget($arrData, $strField);

			$arrOptions = array();

			foreach($arrWidget['options'] as $k => $option)
			{
				$intValue = (int) $option['value'];
				$strFilterKey = $strField . '::' . $intValue;

				// skip zero values (includeBlankOption)
				if($intValue == 0)
					continue;

				$blnIsActive = ($GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey]['value'] == $intValue);

				$arrParams		= array();
				$arrParams[]	= array('mod', $this->id);
				$arrParams[]	= array('attr', $strField);
				$arrParams[]	= array('v', $intValue);

				// add or remove mode
				if($blnIsActive)
				{
					$arrParams[]	= array('cfilter', 'remove');
				}
				else
				{
					$arrParams[]	= array('cfilter', 'add');
				}

				$arrOptions[$k]['label']	= $arrWidget['options'][$k]['label'];
				$arrOptions[$k]['default']	= $GLOBALS['ISO_FILTERS'][$this->id][$strField]['value'] ? '1' : '';
				$arrOptions[$k]['url']		= $this->addToCurrentUrl($arrParams);
				$arrOptions[$k]['isActive']	= $blnIsActive;
			}

			$arrFilters[$strField] = array
			(
				'label'		=> $arrWidget['label'],
				'options'	=> $arrOptions
			);
		}

		$this->Template->filterData = $arrFilters;
	}


	/**
	 * Checks whether there is a ? in the url already and and adds a param to the current url
	 * @param array
	 * @return string
	 */
	protected function addToCurrentUrl($arrParams)
	{
		$strUrl = $this->Environment->request;

		foreach($arrParams as $arrParam)
		{
			$strUrl .= (strpos($strUrl, '?') !== false) ? '&' : '?';
			$strUrl .= $arrParam[0] . '=' . $arrParam[1];
		}

		return $strUrl;
	}
}

