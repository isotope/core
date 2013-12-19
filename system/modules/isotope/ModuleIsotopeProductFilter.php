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
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class ModuleIsotopeProductFilter
 * Front end module Isotope "product filter".
 */
class ModuleIsotopeProductFilter extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_filter_default';

	/**
	 * Cache request
	 * @var boolean
	 */
	protected $blnCacheRequest = false;


	/**
	 * Generate ajax
	 * @return mixed
	 */
	public function generateAjax()
	{
		if ($this->iso_searchAutocomplete && $this->Input->get('autocomplete'))
		{
			$time = time();
			$arrCategories = $this->findCategories($this->iso_category_scope);

			$objProductData = $this->Database->execute(IsotopeProduct::getSelectStatement(array('p1.'.$this->iso_searchAutocomplete)) . "
													WHERE p1.language=''"
				. (BE_USER_LOGGED_IN === true ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)")
				. " AND c.page_id IN (" . implode(',', $arrCategories) . ")"
				. " GROUP BY p1.id ORDER BY c.sorting");

			return $objProductData->fetchEach($this->iso_searchAutocomplete);
		}

		return '';
	}


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: PRODUCT FILTERS ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Initialize module data.
		if (!$this->initializeFilters())
		{
			return '';
		}

		// Hide product list in reader mode if the respective setting is enabled
		if ($this->iso_hide_list && $this->Input->get('product') != '')
		{
			return '';
		}

		$strBuffer = parent::generate();

		// Cache request in the database and redirect to the unique requestcache ID
		if ($this->blnCacheRequest)
		{
			$time = time();
			$varFilter = (is_array($GLOBALS['ISO_FILTERS']) && !empty($GLOBALS['ISO_FILTERS'])) ? serialize($GLOBALS['ISO_FILTERS']) : null;
			$varSorting = (is_array($GLOBALS['ISO_SORTING']) && !empty($GLOBALS['ISO_SORTING'])) ? serialize($GLOBALS['ISO_SORTING']) : null;
			$varLimit = (is_array($GLOBALS['ISO_LIMIT']) && !empty($GLOBALS['ISO_LIMIT'])) ? serialize($GLOBALS['ISO_LIMIT']) : null;

			// if all filters are null we don't have to cache (this will prevent useless isorc params from being generated)
			if ($varFilter !== null || $varLimit !== null || $varSorting !== null)
			{
				$intCacheId = $this->Database->prepare("SELECT id FROM tl_iso_requestcache WHERE store_id={$this->Isotope->Config->store_id} AND filters" . ($varFilter ? '=' : ' IS ') . "? AND sorting" . ($varSorting ? '=' : ' IS ') . "? AND limits" . ($varLimit ? '=' : ' IS ') . "?")
											 ->execute($varFilter, $varSorting, $varLimit)
											 ->id;

				if ($intCacheId)
				{
					$this->Database->query("UPDATE tl_iso_requestcache SET tstamp=$time WHERE id=$intCacheId");
				}
				else
				{
					$intCacheId = $this->Database->prepare("INSERT INTO tl_iso_requestcache (tstamp,store_id,filters,sorting,limits) VALUES ($time, {$this->Isotope->Config->store_id}, ?, ?, ?)")
												 ->execute($varFilter, $varSorting, $varLimit)
												 ->insertId;
				}

				$this->Input->setGet('isorc', $intCacheId);
			}

			// Include Environment::base or the URL would not work on the index page
			$this->redirect($this->Environment->base . $this->generateRequestUrl());
		}

		return $strBuffer;
	}


	/**
	 * Initialize module data. You can override this function in a child class
	 * @return boolean
	 */
	protected function initializeFilters()
	{
		$this->iso_filterFields = deserialize($this->iso_filterFields);
		$this->iso_sortingFields = deserialize($this->iso_sortingFields);
		$this->iso_searchFields = deserialize($this->iso_searchFields);

		if (!$this->iso_enableLimit && !is_array($this->iso_filterFields) && !is_array($this->iso_sortingFields) && !is_array($this->iso_searchFields))
		{
			return false;
		}

		if ($this->iso_filterTpl)
		{
			$this->strTemplate = $this->iso_filterTpl;
		}

		return true;
	}


	/**
	 * Generate the module
	 * @return void
	 */
	protected function compile()
	{
		$this->blnCacheRequest = $this->Input->post('FORM_SUBMIT') == 'iso_filter_'.$this->id ? true : false;

		$this->generateFilters();
		$this->generateSorting();
		$this->generateLimit();

		if (!$this->blnCacheRequest)
		{
			// Search does not affect request cache
			$this->generateSearch();

			$this->Template->id = $this->id;
			$this->Template->formId = 'iso_filter_' . $this->id;
			$this->Template->actionFilter = ampersand($this->Environment->request);
			$this->Template->actionSearch = ampersand($this->Environment->request);
			$this->Template->actionClear = ampersand(preg_replace('/\?.*/', '', $this->Environment->request));
			$this->Template->clearLabel = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
			$this->Template->slabel = $GLOBALS['TL_LANG']['MSC']['submitLabel'];
		}
	}


	/**
	 * Generate a search form
	 * @return void
	 */
	protected function generateSearch()
	{
		$this->Template->hasSearch = false;
		$this->Template->hasAutocomplete = ($this->iso_searchAutocomplete) ? true : false;

		if (is_array($this->iso_searchFields) && count($this->iso_searchFields)) // Can't use empty() because its an object property (using __get)
		{
			if ($this->Input->get('keywords') != '' && $this->Input->get('keywords') != $GLOBALS['TL_LANG']['MSC']['defaultSearchText'])
			{
				$arrKeywords = trimsplit(' |-', $this->Input->get('keywords'));
				$arrKeywords = array_filter(array_unique($arrKeywords));

				foreach ($arrKeywords as $keyword)
				{
					foreach ($this->iso_searchFields as $field)
					{
						$GLOBALS['ISO_FILTERS'][$this->id][] = array
						(
							'group'		=> ('keyword: '.$keyword), // Must create an OR group because multiple fields can be searched for
							'operator'	=> 'search',
							'attribute'	=> $field,
							'value'		=> $keyword,
						);
					}
				}
			}

			$this->Template->hasSearch = true;
			$this->Template->keywordsLabel = $GLOBALS['TL_LANG']['MSC']['searchTermsLabel'];
			$this->Template->keywords = $this->Input->get('keywords');
			$this->Template->searchLabel = $GLOBALS['TL_LANG']['MSC']['searchLabel'];
			$this->Template->defaultSearchText = $GLOBALS['TL_LANG']['MSC']['defaultSearchText'];
		}
	}


	/**
	 * Generate a filter form
	 * @return void
	 */
	protected function generateFilters()
	{
		$this->Template->hasFilters = false;

		if (is_array($this->iso_filterFields) && count($this->iso_filterFields)) // Can't use empty() because its an object property (using __get)
		{
			$time = time();
			$arrFilters = array();
			$arrInput = $this->Input->post('filter');
			$arrCategories = $this->findCategories($this->iso_category_scope);

			foreach ($this->iso_filterFields as $strField)
			{
				$arrValues = array();
				$objValues = $this->Database->execute("SELECT DISTINCT p1.$strField FROM tl_iso_products p1
				                                        LEFT OUTER JOIN tl_iso_products p2 ON p1.pid=p2.id
														WHERE p1.language='' AND p1.$strField!=''"
														. (BE_USER_LOGGED_IN === true ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)")
														. "AND (p1.id IN (SELECT pid FROM tl_iso_product_categories WHERE page_id IN (" . implode(',', $arrCategories) . "))
														   OR p1.pid IN (SELECT pid FROM tl_iso_product_categories WHERE page_id IN (" . implode(',', $arrCategories) . ")))"
														. (BE_USER_LOGGED_IN === true ? '' : " AND (p1.pid=0 OR (p2.published='1' AND (p2.start='' OR p2.start<$time) AND (p2.stop='' OR p2.stop>$time)))")
														. ($this->iso_list_where == '' ? '' : " AND ".$this->replaceInsertTags($this->iso_list_where)));

				while ($objValues->next())
				{
					$arrValues = array_merge($arrValues, deserialize($objValues->$strField, true));
				}

				if ($this->blnCacheRequest && in_array($arrInput[$strField], $arrValues))
				{
					$GLOBALS['ISO_FILTERS'][$this->id][$strField] = array
					(
						'operator'		=> '==',
						'attribute'		=> $strField,
						'value'			=> $arrInput[$strField],
					);
				}

				// Request cache contains wrong value, delete it!
				elseif (is_array($GLOBALS['ISO_FILTERS'][$this->id][$strField]) && !in_array($GLOBALS['ISO_FILTERS'][$this->id][$strField]['value'], $arrValues))
				{
					$this->blnCacheRequest = true;
					unset($GLOBALS['ISO_FILTERS'][$this->id][$strField]);

					$this->Database->prepare("DELETE FROM tl_iso_requestcache WHERE id=?")->execute($this->Input->get('isorc'));
				}

				// No need to generate options if we reload anyway
				elseif (!$this->blnCacheRequest)
				{
					if (empty($arrValues))
					{
						continue;
					}

					$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField];

					if (is_array($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback']) && !empty($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback']))
					{
						foreach ($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback'] as $callback)
						{
							$this->import($callback[0]);
							$arrData = $this->{$callback[0]}->{$callback[1]}($strField, $arrData, $this);
						}
					}

					// Use the default routine to initialize options data
					$arrWidget = $this->prepareForWidget($arrData, $strField);

					// Must have options to apply the filter
					if (!is_array($arrWidget['options']))
					{
						continue;
					}

					foreach ($arrWidget['options'] as $k => $option)
					{
					    if ($option['value'] == '')
					    {
    					    $arrWidget['blankOptionLabel'] = $option['label'];
    					    unset($arrWidget['options'][$k]);
    					    continue;
					    }
						elseif (!in_array($option['value'], $arrValues) || $option['value'] == '-')
						{
							unset($arrWidget['options'][$k]);
							continue;
						}

						$arrWidget['options'][$k]['default'] = $option['value'] == $GLOBALS['ISO_FILTERS'][$this->id][$strField]['value'] ? '1' : '';
					}

					// Hide fields with just one option (if enabled)
					if ($this->iso_filterHideSingle && count($arrWidget['options']) < 2)
					{
						continue;
					}

					$arrFilters[$strField] = $arrWidget;
				}
			}

			// !HOOK: alter the filters
			if (isset($GLOBALS['ISO_HOOKS']['generateFilters']) && is_array($GLOBALS['ISO_HOOKS']['generateFilters']))
			{
				foreach ($GLOBALS['ISO_HOOKS']['generateFilters'] as $callback)
				{
					$this->import($callback[0]);
					$arrFilters = $this->$callback[0]->$callback[1]($arrFilters);
				}
			}

			if (!empty($arrFilters))
			{
				$this->Template->hasFilters = true;
				$this->Template->filterOptions = $arrFilters;
			}
		}
	}


	/**
	 * Generate a sorting form
	 * @return void
	 */
	protected function generateSorting()
	{
		$this->Template->hasSorting = false;

		if (is_array($this->iso_sortingFields) && count($this->iso_sortingFields)) // Can't use empty() because its an object property (using __get)
		{
			$arrOptions = array();

			// Cache new request value
			list($sortingField, $sortingDirection) = explode(':', $this->Input->post('sorting'));

            // First delete an active item, then add as first so the new selection takes priority over previous ones
			if ($this->blnCacheRequest && in_array($sortingField, $this->iso_sortingFields))
			{
			    unset($GLOBALS['ISO_SORTING'][$this->id][$sortingField]);
				array_insert($GLOBALS['ISO_SORTING'][$this->id], 0, array($sortingField => array(($sortingDirection=='DESC' ? SORT_DESC : SORT_ASC), SORT_REGULAR)));
			}

			// Request cache contains wrong value, delete it!
			elseif (is_array($GLOBALS['ISO_SORTING'][$this->id]) && array_diff(array_keys($GLOBALS['ISO_SORTING'][$this->id]), $this->iso_sortingFields))
			{
				$this->blnCacheRequest = true;
				unset($GLOBALS['ISO_SORTING'][$this->id]);

				$this->Database->prepare("DELETE FROM tl_iso_requestcache WHERE id=?")->execute($this->Input->get('isorc'));
			}

			// No need to generate options if we reload anyway
			elseif (!$this->blnCacheRequest)
			{
			    // Find (default) sorting of the select menu
			    $sortingValue = array(($this->iso_listingSortDirection == 'DESC' ? SORT_DESC : SORT_ASC));
			    $sortingField = $this->iso_listingSortField;

			    if (!empty($GLOBALS['ISO_SORTING'][$this->id]) && is_array($GLOBALS['ISO_SORTING'][$this->id])) {
    			    $sortingValue = reset($GLOBALS['ISO_SORTING'][$this->id]);
    			    $sortingField = key($GLOBALS['ISO_SORTING'][$this->id]);
    			}

				foreach ($this->iso_sortingFields as $field)
				{
					list($asc, $desc) = $this->getSortingLabels($field);

					$arrOptions[] = array
					(
						'label'		=> ($this->Isotope->formatLabel('tl_iso_products', $field) . ', ' . $asc),
						'value'		=> $field.':ASC',
						'default'	=> (($field == $sortingField && $sortingValue[0] == SORT_ASC) ? '1' : ''),
					);

					$arrOptions[] = array
					(
						'label'		=> ($this->Isotope->formatLabel('tl_iso_products', $field) . ', ' . $desc),
						'value'		=> $field.':DESC',
						'default'	=> (($field == $sortingField && $sortingValue[0] == SORT_DESC) ? '1' : ''),
					);
				}
			}

			$this->Template->hasSorting = true;
			$this->Template->sortingLabel = $GLOBALS['TL_LANG']['MSC']['orderByLabel'];
			$this->Template->sortingOptions = $arrOptions;
		}
	}


	/**
	 * Generate a limit form
	 * @return void
	 */
	protected function generateLimit()
	{
		$this->Template->hasLimit = false;

		if ($this->iso_enableLimit)
		{
			$arrOptions = array();
			$arrLimit = array_map('intval', trimsplit(',', $this->iso_perPage));
			$intLimit = $GLOBALS['ISO_LIMIT'][$this->id] ? $GLOBALS['ISO_LIMIT'][$this->id] : $arrLimit[0];
			$arrLimit = array_unique($arrLimit);
			sort($arrLimit);

			// Cache new request value
			if ($this->blnCacheRequest && in_array($this->Input->post('limit'), $arrLimit))
			{
				$GLOBALS['ISO_LIMIT'][$this->id] = (int)$this->Input->post('limit');
			}

			// Request cache contains wrong value, delete it!
			elseif ($GLOBALS['ISO_LIMIT'][$this->id] && !in_array($GLOBALS['ISO_LIMIT'][$this->id], $arrLimit))
			{
				$this->blnCacheRequest = true;
				$GLOBALS['ISO_LIMIT'][$this->id] = $intLimit;

				$this->Database->prepare("DELETE FROM tl_iso_requestcache WHERE id=?")->execute($this->Input->get('isorc'));
			}

			// No need to generate options if we reload anyway
			elseif (!$this->blnCacheRequest)
			{
				foreach ($arrLimit as $limit)
				{
					$arrOptions[] = array
					(
						'label'		=> $limit,
						'value'		=> $limit,
						'default'	=> ($intLimit == $limit ? '1' : ''),
					);
				}

				$this->Template->hasLimit = true;
				$this->Template->limitLabel = $GLOBALS['TL_LANG']['MSC']['perPageLabel'];
				$this->Template->limitOptions = $arrOptions;
			}
		}
	}


	/**
	 * Get the sorting labels (asc/desc) for an attribute
	 * @param string
	 * @return array
	 */
	protected function getSortingLabels($field)
	{
		$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field];

		switch ($arrData['eval']['rgxp'])
		{
			case 'price':
			case 'digit':
				return array($GLOBALS['TL_LANG']['MSC']['low_to_high'], $GLOBALS['TL_LANG']['MSC']['high_to_low']);

			case 'date':
			case 'time':
			case 'datim':
				return array($GLOBALS['TL_LANG']['MSC']['old_to_new'], $GLOBALS['TL_LANG']['MSC']['new_to_old']);
		}

		return array($GLOBALS['TL_LANG']['MSC']['a_to_z'], $GLOBALS['TL_LANG']['MSC']['z_to_a']);
	}
}

