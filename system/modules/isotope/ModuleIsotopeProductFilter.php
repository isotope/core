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
			$objTemplate->wildcard = '### ISOTOPE FILTER MODULE ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}		
		
		return parent::generate();
	}
	
	/**
	 * Compile module
	 */
	protected function compile()
	{
		global $objPage;
		
		$arrFilterFields = deserialize($this->iso_filterFields);
		$arrOrderByFieldIds = deserialize($this->iso_orderByFields);
		$arrSearchFields = deserialize($this->iso_searchFields);
		$arrListingModule = deserialize($this->iso_listingModule);
		
		$arrLimit = array();	

		$this->loadLanguageFile('tl_product_data');
		
		$arrOrderByFields = $this->getOrderByFields($arrOrderByFieldIds);
		
		$arrSearchFields = array('name','description');
		
		if(count($arrFilterFields))
		{
			foreach($arrFilterFields as $field)
			{
					
				//Render as a select widget, for now.  Perhaps make flexible in the future.
				$arrFilters[] = array
				(
					'html'		=> ''	//render filter widget
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
			$arrLimit = $GLOBALS['TL_LANG']['MSC']['perPageOptions'];
			if($arrListingModule)
			{
				$intModuleLimit = $this->getListingModuleLimit($arrListingModule);
				if($intModuleLimit)
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
		$this->Template->action = $this->Environment->request;
		$this->Template->baseUrl = $arrCleanUrl[0];
		$this->Template->orderBy = $arrOrderByOptions;
		$this->Template->order_by = ($this->getRequestData('order_by')) ? $this->getRequestData('order_by') : $this->getListingModuleSorting($arrListingModule);
		$this->Template->per_page = ($this->getRequestData('per_page') ? $this->getRequestData('per_page') : $strPerPageDefault);
		$this->Template->page = ($this->getRequestData('page') ? $this->getRequestData('page') : 1);
		$this->Template->for = $this->getRequestData('for');
		$this->Template->perPageLabel = $GLOBALS['TL_LANG']['MSC']['perPage'];
		$this->Template->keywordsLabel = $GLOBALS['TL_LANG']['MSC']['searchTerms'];
		$this->Template->searchLabel = $GLOBALS['TL_LANG']['MSC']['search'];
		$this->Template->submitLabel = $GLOBALS['TL_LANG']['MSC']['labelSubmit'];
		$this->Template->clearLabel = $GLOBALS['TL_LANG']['MSC']['clearFilters'];
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
				$objAttribute = $this->Database->prepare("SELECT name, type, field_name FROM tl_product_attributes WHERE id=?")
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
			'label'			=> $GLOBALS['TL_LANG']['tl_product_data']['name'][0]
		);
		//Add default price field
		$arrAttributeData[] = array
		(
			'type'			=> 'decimal',
			'field_name'	=> 'price',
			'label'			=> $GLOBALS['TL_LANG']['tl_product_data']['price'][0]
		);
		

		return $arrAttributeData;
	}
	
	/** 
	 * Get the per page option limits from corresponding listing module
	 *
	 * @param array $arrListingModule
	 * @return integer
	 */
	private function getListingModuleLimit($arrListingModule)
	{
			$objLimit = $this->Database->prepare("SELECT perPage FROM tl_module WHERE id=?")
						       ->limit(1)
						       ->execute($arrListingModule[0]);
						       
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
	 *
	 * @param array $arrListingModule
	 * @return string
	 */
	private function getListingModuleSorting($arrListingModule)
	{
			$objSorting = $this->Database->prepare("SELECT iso_listingSortField, iso_listingSortDirection FROM tl_module WHERE id=?")
						       ->limit(1)
						       ->execute($arrListingModule[0]);
						       
			if(!$objSorting->numRows)
			{
				return;
			}
			if(strlen($objSorting->iso_listingSortField))
			{
				$strSorting = $objSorting->iso_listingSortField . '-' . $objSorting->iso_listingSortDirection;
			}
		
		return $strSorting;
	}
	
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
}

