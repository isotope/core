<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleIsotopeProductFilter extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_productfilter';

	protected $categories = array();

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE ECOMMERCE: FILTER MODULE ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = $this->Environment->script.'?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}
		
		$this->iso_filterFields = deserialize($this->iso_filterFields);
		$this->iso_sortingFields = deserialize($this->iso_sortingFields);
		$this->iso_searchFields = deserialize($this->iso_searchFields);

		if (!$this->iso_enableLimit && !is_array($this->iso_filterFields) && !is_array($this->iso_sortingFields) && !is_array($this->iso_searchFields))
		{
			return '';
		}

		return parent::generate();
	}


	protected function compile()
	{
		// used to reduce the list of available options for each filter
		$this->categories = $this->findCategories($this->iso_category_scope);

		$this->loadLanguageFile('tl_iso_products');

		if (is_array($this->iso_filterFields))
		{
			foreach($this->iso_filterFields as $field)
			{
				$data = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field];
	
				if($data['eval']['fe_filter'])
					$arrFilters[] = array('html' => $this->generateFilterWidget($field, $data));
			}
		}
		
		
		
		
		
		
		$blnCacheRequest = $this->Input->post('FORM_SUBMIT') == 'iso_filter_'.$this->id ? true : false;
		list($strUrl) = explode('?', $this->Environment->request, 2);

		$this->Template->hasSorting = false;
		if (is_array($this->iso_sortingFields) && count($this->iso_sortingFields))
		{
			$arrOptions = array();
			
			
			// Cache new request value
			// @todo should support multiple sorting fields
			list($sortingField, $sortingDirection) = explode(':', $this->Input->post('sorting'));
			
			if ($blnCacheRequest && in_array($sortingField, $this->iso_sortingFields))
			{
				$GLOBALS['ISO_SORTING'][$this->id][$sortingField] = array(($sortingDirection=='DESC' ? SORT_DESC : SORT_ASC), SORT_REGULAR);
			}
			
			// Request cache contains wrong value, delete it!
			elseif (is_array($GLOBALS['ISO_SORTING'][$this->id]) && array_diff(array_keys($GLOBALS['ISO_SORTING'][$this->id]), $this->iso_sortingFields))
			{
				$blnCacheRequest = true;
				unset($GLOBALS['ISO_SORTING'][$this->id]);
				
				$this->Database->prepare("DELETE FROM tl_iso_requestcache WHERE id=?")->execute($this->Input->get('isorc'));
			}
			
			// No need to generate options if we reload anyway
			elseif (!$blnCacheRequest)
			{
				foreach( $this->iso_sortingFields as $field )
				{
				
					// @todo this must be dynamic
					switch( $field )
					{
						case 'price':
							$asc = $GLOBALS['TL_LANG']['MSC']['low_to_high'];
							$desc = $GLOBALS['TL_LANG']['MSC']['high_to_low'];
							break;
		
						case 'datetime':
							$asc = $GLOBALS['TL_LANG']['MSC']['old_to_new'];
							$desc = $GLOBALS['TL_LANG']['MSC']['new_to_old'];
							break;
		
						case 'name':
						default:
							$asc = $GLOBALS['TL_LANG']['MSC']['a_to_z'];
							$desc = $GLOBALS['TL_LANG']['MSC']['z_to_a'];
							break;
					}

				
					$arrOptions[] = array
					(
						'label'		=> ($this->Isotope->formatLabel('tl_iso_products', $field) . ' ' . $asc),
						'value'		=> $field.':ASC',
						'default'	=> ((is_array($GLOBALS['ISO_SORTING'][$this->id]) && $GLOBALS['ISO_SORTING'][$this->id][$field][0] == SORT_ASC) ? '1' : ''),
					);
					
					$arrOptions[] = array
					(
						'label'		=> ($this->Isotope->formatLabel('tl_iso_products', $field) . ' ' . $desc),
						'value'		=> $field.':DESC',
						'default'	=> ((is_array($GLOBALS['ISO_SORTING'][$this->id]) && $GLOBALS['ISO_SORTING'][$this->id][$field][0] == SORT_DESC) ? '1' : ''),
					);
				}
			}

			$this->Template->hasSorting = true;
			$this->Template->sortingLabel = $GLOBALS['TL_LANG']['MSC']['orderByLabel'];
			$this->Template->sortingOptions = $arrOptions;
		}


		$this->Template->hasLimit = false;
		if ($this->iso_enableLimit)
		{
			$arrOptions = array();
			$arrLimit = array_map('intval', trimsplit(',', $this->iso_perPage));
			$intLimit = $GLOBALS['ISO_LIMIT'][$this->id] ? $GLOBALS['ISO_LIMIT'][$this->id] : $arrLimit[0];
			$arrLimit = array_unique($arrLimit);
			sort($arrLimit);

			// Cache new request value
			if ($blnCacheRequest && in_array($this->Input->post('limit'), $arrLimit))
			{
				$GLOBALS['ISO_LIMIT'][$this->id] = (int)$this->Input->post('limit');
			}
			
			// Request cache contains wrong value, delete it!
			elseif ($GLOBALS['ISO_LIMIT'][$this->id] && !in_array($GLOBALS['ISO_LIMIT'][$this->id], $arrLimit))
			{
				$blnCacheRequest = true;
				$GLOBALS['ISO_LIMIT'][$this->id] = $intLimit;
				
				$this->Database->prepare("DELETE FROM tl_iso_requestcache WHERE id=?")->execute($this->Input->get('isorc'));
			}
			
			// No need to generate options if we reload anyway
			elseif (!$blnCacheRequest)
			{
				foreach( $arrLimit as $limit )
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


		// Cache request in the database and redirect to the unique requestcache ID
		if ($blnCacheRequest)
		{
			$time = time();
			$varFilter = is_array($GLOBALS['ISO_FILTERS']) ? serialize($GLOBALS['ISO_FILTERS']) : null;
			$varSorting = is_array($GLOBALS['ISO_SORTING']) ? serialize($GLOBALS['ISO_SORTING']) : null;
			$varLimit = is_array($GLOBALS['ISO_LIMIT']) ? serialize($GLOBALS['ISO_LIMIT']) : null;

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
			
			$this->redirect($strUrl . '?isorc=' . $intCacheId);
		}


		$this->Template->id = $this->id;
		$this->Template->formId = 'iso_filter_' . $this->id;
		$this->Template->action = $strUrl;


		$this->Template->searchable = (is_array($this->iso_searchFields) && count($this->iso_searchFields)) ? true : false;
		$this->Template->filters = $arrFilters;
		$this->Template->filterFields = (is_array($this->iso_filterFields) ? implode(',',$this->iso_filterFields) : array());
//		$this->Template->orderBy = $this->getOrderByFields($this->iso_sortingFields);
		$this->Template->order_by = ($this->Input->get('order_by')) ? $this->Input->get('order_by') : '';//$this->getListingModuleSorting($objListingModule);
		$this->Template->page = ($this->Input->get('page') ? $this->Input->get('page') : 1);
		$this->Template->for = $this->Input->get('for');
		$this->Template->defaultSearchText = $GLOBALS['TL_LANG']['MSC']['defaultSearchText'];
		
		
		$this->Template->keywordsLabel = $GLOBALS['TL_LANG']['MSC']['searchTermsLabel'];
		$this->Template->searchLabel = $GLOBALS['TL_LANG']['MSC']['searchLabel'];
		$this->Template->clearLabel = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
	}


	/**
	 * Load filter values from products based on an array of page ids for a given attribute
	 *
	 * @access private
	 * @pararm string $strField
	 * @param array $arrPageIds
	 * @return array
	 */
	private function loadFilterValues($strField, $arrPageIds)
	{
		$strPageIds = implode(',',$arrPageIds);

		$objFilterValues = $this->Database->query("SELECT DISTINCT $strField FROM tl_iso_products WHERE id IN (SELECT pid FROM tl_iso_product_categories WHERE page_id IN ($strPageIds)) AND published='1'");

		if(!$objFilterValues->numRows)
			return array();

		return $objFilterValues->fetchEach($strField);

	}


	/**
	 * Return a widget object based on a product attribute's properties.
	 */
	protected function generateFilterWidget($strField, $arrData, $blnAjax=false)
	{
		$strClass = strlen($GLOBALS['ISO_ATTR'][$arrData['inputType']]['class']) ? $GLOBALS['ISO_ATTR'][$arrData['inputType']]['class'] : $GLOBALS['TL_FFL'][$arrData['inputType']];

		// Continue if the class is not defined
		if (!$this->classFileExists($strClass))
		{
			return '';
		}

		$arrData['eval']['mandatory'] = ($arrData['eval']['mandatory'] && !$blnAjax) ? true : false;
		$arrData['eval']['required'] = $arrData['eval']['mandatory'];

		if ($arrData['inputType'] == 'select')
		{
			$arrData['eval']['includeBlankOption'] = true;
		}

		if (is_array($arrData['options']) || $arrData['foreignKey'])
		{
			$arrField = $this->prepareForWidget($arrData, $strField);

		}
		else
		{
			if (is_array($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']) && count($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']))
			{
				foreach( $GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback'] as $callback )
				{
					$this->import($callback[0]);
					$arrData = $this->{$callback[0]}->{$callback[1]}($strField, $arrData, $this);
				}
			}

			$arrField = $this->prepareForWidget($arrData, $strField);
		}

		$objWidget = new $strClass($arrField);

		//reassign options if foreignKey our own way.
		if($arrData['foreignKey'])
		{
			$arrFK = explode(".", $arrData['foreignKey'], 2);

			//need to gather & reduce options
			$arrOptions = $this->Database->execute("SELECT DISTINCT id, {$arrFK[1]} FROM {$arrFK[0]}")->fetchAllAssoc();

			foreach($arrOptions as $option)
			{

				$arrOptionsAssoc[$option['id']] = $option[$arrFK[1]];
			}

			$arrAssignedOptions = $this->loadFilterValues($strField, $this->categories);

			$arrFinalOptions = array();

			if($arrData['inputType']=='select')
				$arrFinalOptions[] = array('value'=>'','label'=>'-');

			foreach($arrAssignedOptions as $val)
			{
				if($val==0)
					continue;

				$arrFinalOptions[] = array
				(
					'value'	=> $val,
					'label'	=> $arrOptionsAssoc[$val]
				);
			}

			$intCountForDisabling = ($arrData['inputType']=='select' ? 2 : 1); //with a blank option we have no less than two values, implying only one
																		   //value and hence, disable the widget as it has no function in this case;

			if(count($arrFinalOptions)==$intCountForDisabling)
			{
				if($arrData['inputType']=='select')
					array_shift($arrFinalOptions);

				$objWidget->disabled =true;
			}

			$objWidget->options = $arrFinalOptions;
		}

		if($this->Input->get($strField))
			$objWidget->value = $this->Input->get($strField);


		$objWidget->storeValues = true;
		$objWidget->tableless = true;
		$objWidget->id .= "_" . ($this->pid ? $this->pid : $this->id);

		return $objWidget->parse();
	}
}

