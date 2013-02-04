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
	protected $strTemplate = 'mod_iso_cumulativefilter';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: CUMULATIVE FILTER ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Remove setting to prevent override of the module template
		$this->iso_filterTpl = '';
		$this->navigationTpl = $this->navigationTpl ? $this->navigationTpl : 'nav_default';

		return parent::generate();
	}


	/**
	 * Compile the module
	 */
	protected function compile()
	{
	    $arrFilter = explode(';', base64_decode($this->Input->get('cumulativefilter', true)), 4);

	    if ($arrFilter[0] == $this->id && in_array($arrFilter[2], $this->iso_filterFields))
	    {
    	    $this->blnCacheRequest = true;

    	    // Unique filter key is necessary to unset the filter
    	    $strFilterKey = $arrFilter[2].'='.$arrFilter[3];

    	    if ($arrFilter[1] == 'add')
			{
				$GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey] = array
				(
					'operator'		=> '==',
					'attribute'		=> $arrFilter[2],
					'value'			=> $arrFilter[3]
				);
			}
			else
			{
				unset($GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey]);
			}

			// unset GET parameter or it would be included in the redirect URL
			$this->Input->setGet('cumulativefilter', null);
	    }
	    else
	    {
    		$this->generateFilter();

    		$this->Template->linkClearAll = ampersand(preg_replace('/\?.*/', '', $this->Environment->request));
    		$this->Template->labelClearAll = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
    	}
	}


	/**
	 * Generates the filter
	 */
	protected function generateFilter()
	{
	    $blnShowClear = false;
		$arrFilters = array();

		foreach ($this->iso_filterFields as $strField)
		{
		    $blnTrail = false;
			$arrItems = array();
			$arrWidget = $this->prepareForWidget($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField], $strField); // Use the default routine to initialize options data

			foreach ($arrWidget['options'] as $option)
			{
				$varValue = $option['value'];

				// skip zero values (includeBlankOption)
				if ($varValue === '' || $varValue === '-') {
					continue;
				}

				$strFilterKey = $strField . '=' . $varValue;
				$blnActive = isset($GLOBALS['ISO_FILTERS'][$this->id][$strFilterKey]);
				$blnTrail = $blnActive ? true : $blnTrail;

				$arrItems[] = array
				(
				    'href'  => IsotopeFrontend::addQueryStringToUrl('cumulativefilter=' . base64_encode($this->id . ';' . ($blnActive ? 'del' : 'add') . ';' . $strField . ';' . $varValue)),
				    'class' => ($blnActive ? 'active' : ''),
				    'title' => specialchars($option['label']),
				    'link'  => $option['label'],
				);
			}

			if (!empty($arrItems) || ($this->iso_iso_filterHideSingle && count($arrItems) < 2))
			{
    			$objTemplate = new FrontendTemplate($this->navigationTpl);

    			$objTemplate->level = 'level_2';
    			$objTemplate->items = IsotopeFrontend::generateRowClass($arrItems, ($blnTrail ? 'sibling' : ''), 'class', 0, ISO_CLASS_NAME & ISO_CLASS_FIRSTLAST);

    			$arrFilters[$strField] = array
    			(
    				'label'     => $arrWidget['label'],
    				'subitems'  => $objTemplate->parse(),
    				'isActive'  => $blnTrail,
    			);
    			
    			$blnShowClear = $blnTrail ? true : $blnShowClear;
			}
		}

		$this->Template->filters = $arrFilters;
		$this->Template->showClear = $blnShowClear;
	}
}

