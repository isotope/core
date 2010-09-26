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
		
		return parent::generate();
	}
	
	/**
	 * Compile module
	 */
	//!@todo generate() should confirm that data is set and hide otherwise
	protected function compile()
	{
		global $objPage;
		
		$arrFilterFields = deserialize($this->iso_filterFields);
		$arrOrderByFieldIds = deserialize($this->iso_orderByFields);
		$arrSearchFieldIds = deserialize($this->iso_searchFields);
		$objListingModule = $this->Database->prepare("SELECT * FROM tl_module WHERE id=?")->limit(1)->execute($this->iso_listingModule);
		
		$arrLimit = array();	

		$this->loadLanguageFile('tl_iso_products');
		
		$arrOrderByFields = $this->getOrderByFields($arrOrderByFieldIds);
		
		//$arrSearchFields = array('name','description');
		
		if(count($arrSearchFieldIds))
		{
			foreach($arrSearchFieldIds as $field)
			{
				$arrAttributeData = $this->getProductAttributeData($field);
				$arrSearchFieldNames[] = $arrAttributeData['field_name'];
			}
			$arrSearchFieldNames[] = 'name';
			$arrSearchFieldNames[] = 'description';
			
		}
	
		if(count($arrFilterFields))
		{
			foreach($arrFilterFields as $field)
			{
					
				//Render as a select widget, for now.  Perhaps make flexible in the future.
				/* Added by Blair */
				if(!$objWidget = $this->generateSelectWidget($field))
					break;
					
				$arrAttributeData = $this->getProductAttributeData($field);
				$arrFieldNames[] = $arrAttributeData['field_name'];
				/* End added by Blair */
				
				$arrFilters[] = array
				(
					'html'		=> $objWidget->parse()	//render filter widget
				);
			}
		}
		
				
		if($arrOrderByFields)
		{
			$arrOrderByOptions = $this->getOrderByOptions($arrOrderByFields);
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
	
	
	private function getOrderByOptions($arrAttributes)
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
	
	
	public function getOrderByFields($arrFieldIds)
	{
		if($arrFieldIds)
		{
			foreach($arrFieldIds as $field)
			{
				$objAttribute = $this->Database->prepare("SELECT name, type, field_name FROM tl_iso_attributes WHERE id=?")
							       ->limit(1)
							       ->execute($field);
				if(!$objAttribute->numRows)
				{
					continue;
				}
				
				$arrAttributeData[] = array
				(
					'type'			=> $objAttribute->type,
					'field_name'    => $objAttribute->field_name,
					'label'			=> $objAttribute->name
				);
			}
		}
		//Add default name field
		$arrAttributeData[] = array
		(
			'type'			=> 'text',
			'field_name'	=> 'name',
			'label'			=> $GLOBALS['TL_LANG']['tl_iso_products']['name'][0]
		);
		//Add default price field
		$arrAttributeData[] = array
		(
			'type'			=> 'decimal',
			'field_name'	=> 'price',
			'label'			=> $GLOBALS['TL_LANG']['tl_iso_products']['price'][0]
		);
		

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
	 *  Just to clean up main code, wrapped a reused piece of code in this function
	 * @access private
	 * @param integer $intFieldID
	 * @return object
	 */
	private function generateSelectWidget($intFieldID)
	{		
		
		$arrAttributeData = $this->getProductAttributeData($intFieldID);
	
		$arrOptionList = deserialize($arrAttributeData['option_list']);
		
		if(!is_array($arrOptionList) || !count($arrOptionList))
		{
			return false;
		}		
		
		array_unshift($arrOptionList, array('value'=>'','label'=>&$GLOBALS['TL_LANG']['MSC']['blankSelectOptionLabel']));
	
		$arrData = array
		(
			'label'			=> array($arrAttributeData['name'],$arrAttributeData['name']),
			'inputType'		=> 'select',
			'eval'			=> array('includeBlankOption'=>false, 'tableless'=>true)
		);

		$objWidget = new FormSelectMenu($this->prepareForWidget($arrData, $arrAttributeData['field_name'], $this->Input->get($arrAttributeData['field_name'])));
		
		$objWidget->options = $arrOptionList;
		$objWidget->onchange = "filterForm.submit();";
	
		return $objWidget;

	}
	
	/**
	 * Get attribute data and do something with it based on the properties of the attribute.
	 *
	 * @access private
	 * @param integer
	 * @return array
	 */
	private function getProductAttributeData($intFieldID)
	{		
		
		$objAttributeData = $this->Database->prepare("SELECT * FROM tl_iso_attributes WHERE id=?")
										   ->limit(1)
										   ->execute($intFieldID);

		if($objAttributeData->numRows < 1)
		{			
			return array();
		}
		
		return $objAttributeData->fetchAssoc();
	}
}

