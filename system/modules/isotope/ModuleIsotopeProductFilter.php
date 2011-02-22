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

	protected $strFormId = 'iso_filters';

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

		if(!$this->iso_filterFields && !$this->iso_orderByFields && !$this->iso_searchFields)
			return '';

		return parent::generate();
	}

	/**
	 * Compile module
	 *
	 * @todo generate() should confirm that data is set and hide otherwise
	 */
	protected function compile()
	{
		global $objPage;

		$arrFilterFields = deserialize($this->iso_filterFields);
		$arrOrderByFields = deserialize($this->iso_orderByFields);
		$arrSearchFields = deserialize($this->iso_searchFields);
		$objListingModule = $this->Database->prepare("SELECT * FROM tl_module WHERE id=?")->limit(1)->execute($this->iso_listingModule);

		//used to reduce the list of available options for each filter
		$this->categories = $this->findCategories($objListingModule->iso_category_scope);

		$arrLimit = array();
		$arrOrderByOptions = array();

		$this->loadLanguageFile('tl_iso_products');

		foreach($arrFilterFields as $field)
		{
			$data = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field];

			if($data['eval']['is_filterable'])
				$arrFilters[] = array('html' => $this->generateFilterWidget($field, $data));
		}

		$arrOrderByOptions = $this->getOrderByOptions($this->getOrderByFields($arrOrderByFields));

		if($this->iso_enableSearch)
		{
			$arrSearchFields = array('name','description');

			if(count($arrSearchFields))
			{
				foreach($arrSearchFields as $field)
				{
					$arrSearchFieldNames[] = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['eval']['field_name'];
				}

			}
		}

		//Set the default per page limit if one exists from the listing module,
		//and also add it to the default array if it not there already
		$strPerPageDefault = '';
		if($this->iso_enableLimit)
		{
			//Generate the limits per page... used to be derived from the number of columns in grid format, but not in list format.  For now, just a standard array.
			$arrLimit = $GLOBALS['ISO_PERPAGE'];

			if ($this->iso_listingModule)
			{
				$intModuleLimit = intval($objListingModule->perPage);

				if($intModuleLimit > 0)
				{
					$strPerPageDefault = $intModuleLimit;
					if(!in_array($intModuleLimit,$arrLimit))
					{
						array_push($arrLimit,$intModuleLimit);
						//Sort the array
						sort($arrLimit);
					}
				}
			}
		}

		$arrCleanUrl = explode('?', $this->Environment->request);

		$this->Template->searchable = $this->iso_enableSearch;
		$this->Template->perPage = $this->iso_enableLimit;
		$this->Template->limit = $arrLimit;
		$this->Template->filters = $arrFilters;
		$this->Template->filterFields = (count($arrFieldNames) ? implode(',',$arrFieldNames) : array());
		$this->Template->action = $this->Environment->request;
		$this->Template->baseUrl = $arrCleanUrl[0];
		$this->Template->orderBy = $arrOrderByOptions;
		$this->Template->order_by = ($this->Input->get('order_by')) ? $this->Input->get('order_by') : $this->getListingModuleSorting($objListingModule);
		$this->Template->per_page = ($this->Input->get('per_page') ? $this->Input->get('per_page') : $strPerPageDefault);
		$this->Template->page = ($this->Input->get('page') ? $this->Input->get('page') : 1);
		$this->Template->for = $this->Input->get('for');
		$this->Template->defaultSearchText = $GLOBALS['TL_LANG']['MSC']['defaultSearchText'];
		$this->Template->orderByLabel = $GLOBALS['TL_LANG']['MSC']['orderByLabel'];
		$this->Template->perPageLabel = $GLOBALS['TL_LANG']['MSC']['perPageLabel'];
		$this->Template->keywordsLabel = $GLOBALS['TL_LANG']['MSC']['searchTermsLabel'];
		$this->Template->searchLabel = $GLOBALS['TL_LANG']['MSC']['searchLabel'];
		$this->Template->clearLabel = $GLOBALS['TL_LANG']['MSC']['clearFiltersLabel'];
	}


	private function getOrderByOptions(array $arrAttributes)
	{
		$arrOptions[''] = '-';

		foreach($arrAttributes as $attribute)
		{
			$arrSortingDirections = $this->generateSortingDirections($attribute['type']);

			$arrOptions[$attribute['field_name'] . '-ASC'] = $attribute['label'] . ' ' . $arrSortingDirections['ASC'];
			$arrOptions[$attribute['field_name'] . '-DESC'] = $attribute['label'] . ' ' . $arrSortingDirections['DESC'];

		}

		return $arrOptions;
	}


	/**
	 * Automate the generation of sorting options for one or more order by-enabled attributes
	 *
	 * @access public
	 * @param array $arrFields
	 * @return array
	 */
	public function getOrderByFields($arrFields)
	{
		if($arrFields)
		{
			foreach($arrFields as $field)
			{
				switch($field)
				{
					case 'name':
						//Add default name field
						$arrAttributeData[] = array
						(
							'type'			=> 'text',
							'field_name'	=> 'name',
							'label'			=> $GLOBALS['TL_LANG']['tl_iso_products']['name'][0]
						);
						break;
					case 'price':
						$arrAttributeData[] = array
						(
							'type'			=> 'decimal',
							'field_name'	=> 'price',
							'label'			=> $GLOBALS['TL_LANG']['tl_iso_products']['price'][0]
						);
						break;
					default:
						$arrAttributeData[] = array
						(
							'type'			=> $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['eval']['type'],
							'field_name'    => $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['eval']['field_name'],
							'label'			=> $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$field]['eval']['name']
						);
						break;
				}
			}
		}

		return $arrAttributeData;
	}


	/**
	 * Get the per page option limits from corresponding listing module
	 *
	 * @param int $intListingModule
	 * @return integer
	 */
	private function getListingModuleLimit($intListingModule)
	{
		$objLimit = $this->Database->prepare("SELECT perPage FROM tl_module WHERE id=?")->limit(1)->execute($intListingModule);

		if(!$objLimit->numRows)
		{
			return;
		}

		if($objLimit->perPage > 0)
		{
			$intLimit = $objLimit->perPage;
		}

		return $intLimit ;
	}


	/**
	 * Get the initial sorting field and direction from corresponding listing module
	 */
	private function getListingModuleSorting($objModule)
	{
		$strSorting = '';

		if(strlen($objModule->iso_listingSortField))
		{
			$strSorting = $objModule->iso_listingSortField . '-' . $objModule->iso_listingSortDirection;
		}

		return $strSorting;
	}

	/**
	 * Generates sorting directions based upon data type
	 *
	 * @todo "integer", "decimal" and "datetime" are no longer available
	 *
	 * @access private
	 * @param string $strType
	 * @return array
	 */
	private function generateSortingDirections($strType)
	{
		switch($strType)
		{
			case 'integer':
			case 'decimal':
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['low_to_high'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['high_to_low']);

			case 'text':
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['a_to_z'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['z_to_a']);

			case 'datetime':
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['old_to_new'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['new_to_old']);
		}
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

	/**
	 * Find categories based on the category scope of the listing module that corresponds to this filter module instance
	 *
	 * @todo: this is replicated from ModuleIsotopeProductListing until we can determine if appropriate to move this to ModuleIsotope for any module needing this data.
	 * @access protected
	 * @param string $strCategoryScope
	 * @return array
	 */
	protected function findCategories($strCategoryScope)
	{
		global $objPage;

		switch($strCategoryScope)
		{
			case 'global':
				return array_merge($this->getChildRecords($objPage->rootId, 'tl_page', true), array($objPage->rootId));

			case 'current_and_first_child':
				return array_merge($this->Database->execute("SELECT id FROM tl_page WHERE pid={$objPage->id}")->fetchEach('id'), array($objPage->id));

			case 'current_and_all_children':
				return array_merge($this->getChildRecords($objPage->id, 'tl_page', true), array($objPage->id));

			case 'parent':
				return array($objPage->pid);

			case 'product':
				$objProduct = $this->getProductByAlias($this->Input->get('product'));

				if (!$objProduct)
					return array(0);

				return $objProduct->categories;

			default:
			case 'current_category':
				return array($objPage->id);
		}

	}
}

