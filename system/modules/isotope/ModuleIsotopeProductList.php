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


class ModuleIsotopeProductList extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_productlist';

	protected $strOrderBySQL = 'c.sorting';
	
	protected $strFilterSQL;
	
	protected $strSearchSQL;
	
	protected $arrParams;		     
	
	/**
	 * The ids of all pages we take care of. this is what should later be used eg. for filter data.
	 */
	protected $arrCategories = array();
        
        
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{		
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE PRODUCT LIST ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;
			
			return $objTemplate->parse();
		}

		global $objPage;
		
	 	// Determine category scope
		switch($this->iso_category_scope)
		{
			case 'global':
				$this->arrCategories = array_merge($this->getChildRecords($objPage->rootId, 'tl_page', true), array($objPage->rootId));
				break;
				
			case 'parent_and_first_child':
				$this->arrCategories = array_merge($this->Database->execute("SELECT id FROM tl_page WHERE pid={$objPage->id}")->fetchEach('id'), array($objPage->id));
				break;
				
			case 'parent_and_all_children':
				$this->arrCategories = array_merge($this->getChildRecords($objPage->id, 'tl_page', true), array($objPage->id));				
				break;
				
			default:
			case 'current_category':
				$this->arrCategories = array($objPage->id);
				break;		
		}

		return parent::generate();
	}
	
	
	public function generateAjax()
	{		
		$objProduct = $this->getProduct($this->Input->get('product'));
		
		if ($objProduct)
		{
			return $objProduct->generateAjax();
		}
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{	
		global $objPage;
			
		if($this->Input->get('clear'))
		{
			$arrFilters = array();
		}
		else
		{
			$arrFilters = array('for'=>$this->Input->get('for'),'per_page'=>$this->Input->get('per_page'),'page'=>$this->Input->get('page'),'order_by'=>$this->Input->get('order_by'));	
		
			$arrFilterFields = explode(',', $this->Input->get('filters'));	//get the names of filters we are using
	
			foreach($arrFilterFields as $field)
			{
				if($this->Input->get($field))
				{
					$arrFilters[$field] = $this->Input->get($field);
				}
			}
						
			$this->perPage = ($this->Input->get('per_page') ? $this->Input->get('per_page') : $this->perPage);
							
			$this->setFilterSQL($arrFilters);
		}

		if($this->strOrderBySQL=='c.sorting')
		{

			if($this->iso_listingSortField)
			{
				$this->setFilterSQL(array('order_by' => ($this->iso_listingSortField.'-'.$this->iso_listingSortDirection)));
		    }
		}
				
		$objProductIds = $this->Database->prepare("SELECT DISTINCT p.* FROM tl_iso_product_categories c, tl_iso_products p WHERE p.id=c.pid AND published='1'" . ($this->strFilterSQL ? " AND (" . $this->strFilterSQL . ")" : "") . " AND c.page_id IN (" . implode(',', $this->arrCategories) . ")" . ($this->strSearchSQL ? " AND (" . $this->strSearchSQL . ")" : "") . ($this->strOrderBySQL ? " ORDER BY " . $this->strOrderBySQL : ""));
		
		
		// Add pagination
		if ($this->perPage > 0)
		{
			$total = $objProductIds->execute($this->arrParams)->numRows;
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
			$offset = ($page - 1) * $this->perPage;

			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
			
			$objProductIds->limit($this->perPage, $offset);
		}
		
		$arrProducts = $this->getProducts($objProductIds->execute($this->arrParams)->fetchEach('id'));
			
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noProducts'];
			return;
		}
		
		$arrBuffer = array();
		
		if (isset($GLOBALS['TL_HOOKS']['iso_getProductUpdates']) && is_array($GLOBALS['TL_HOOKS']['iso_getProductUpdates']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_getProductUpdates'] as $callback)
			{				
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($arrProducts, $this);
			}
		}
		
		
		foreach( $arrProducts as $i => $objProduct )
		{
			$arrBuffer[] = array
			(
				'clear'	    => ($this->iso_list_format=='grid' && $blnSetClear ? true : false),
				'class'		=> ('product' . ($i == 0 ? ' product_first' : '')),
				'html'		=> $objProduct->generate((strlen($this->iso_list_layout) ? $this->iso_list_layout : $objProduct->list_template), $this),
			);
			
			$blnSetClear = (($i+1) % $this->columns==0 ? true : false);
		}
	
		// Add "product_last" css class
		if (count($arrBuffer))
		{
			$arrBuffer[count($arrBuffer)-1]['class'] .= ' product_last';
		}
		
		$this->Template->listformat = $this->iso_list_format;
		$this->Template->products = $arrBuffer;
	}
	
	
	protected function setFilterSQL($arrFilters)
	{
		$arrFilterClauses = array();
		$arrSearchClauses = array();
		$arrOrderByClauses = array();
		$arrFilterChunks = array();
		$arrOrderBySQLWithParentTable = array();
		
		foreach($arrFilters as $filter=>$value)
		{
			if($value)
			{
				switch($filter)
				{
					case 'order_by':
						$arrOrderByClauses[] = explode('-', $value);
						break;
						
					case 'per_page':
						//prepare per-page limit
						$this->perPage = $value;
						break;
						
					case 'page':
						$this->currentPage = $value;
						break;
						
					case 'for':
						//prepare clause for text search. //!@todo:  need to add filter for each std. search field plus any additional user-defined.
						$arrSearchFields = $this->getSearchFields();
												
						foreach($arrSearchFields as $field)
						{
							$arrSearchClauses[] = $this->addFilter($value, $field, 'search');
						}
						break;
						
					default:
						$arrFilterClauses[] = $this->addFilter($value, $filter, 'filter');
						break;
				}
			}						
		}
		
		if(count($arrFilterClauses[0]))
		{			
			foreach($arrFilterClauses as $param)
			{
				$arrFilterChunks[] = $param['sql'];
				$this->arrParams[] = $param['value'];
			}
		}	

		if(count($arrSearchClauses[0]))
		{
			foreach($arrSearchClauses as $param)
			{
				$arrSearchChunks[] = $param['sql'];
				$this->arrParams[] = $param['value'];
			}
		}	

		if(count($arrOrderByClauses[0]))
		{
			foreach($arrOrderByClauses as $row)
			{
				$arrOrderBySQL[] = implode(" ", $row);
			}
			
			foreach($arrOrderBySQL as $row)
			{
				if(strlen($row))
				{
					$arrRow = explode(" ", $row);
					
					switch($arrRow[0])
					{
						case 'price':		//Workaround to deal with price field being VARCHAR... check on this with Andreas... should be field type decimal.
							$arrOrderBySQLWithParentTable[] = "CAST(p." . $arrRow[0] . " AS decimal) " . $arrRow[1];
							break;
							
						default:
							$arrOrderBySQLWithParentTable[] = "p." . $row;
							break;
					}
				}
			}
			
			$this->strOrderBySQL = implode(', ', $arrOrderBySQLWithParentTable);
		}
		
		$this->strFilterSQL = (count($arrFilterChunks) ? implode(" AND ", $arrFilterChunks) : NULL);
		$this->strSearchSQL = (count($arrSearchChunks) ? implode(" OR ", $arrSearchChunks) : NULL);
	}
	
		
	/** 
	 * Gather SQL clause components to be added into the sql query for pulling product data
	 *
	 * @param variant $varValue
	 * @param string $strKey
	 * @param string $strType
	 * @return array
	 */
	protected function addFilter($varValue, $strKey, $strType)
	{
		$arrReturn = array();
		
		if($varValue)
		{
			switch($strType)
			{
				case 'search':
					$arrReturn['sql'] 		= "p." . $strKey . " LIKE ?";
					$strValue = str_replace('%', '', $varValue);
		
					$arrReturn['value'] 	= "%%" . $strValue . "%";	//double wildcard necessary to get around vsprintf bug.				
					break;
				case 'filter':
					$arrReturn['sql']		= "p." . $strKey . "=?";
					$arrReturn['value']		= $varValue;
					break;
				default:
					break;
			}
		}		
		
		return $arrReturn;
	}
	
	
	/** 
	 * Get the search fields used by any corresponding filter - add defaults plus user defined
	 *
	 * @return array
	 */
	//!@todo: I don't know where exactly, but this should use the DCA not tl_iso_attributes
	protected function getSearchFields()
	{
		$arrSearchFields = array('name','description');
		
		$arrFieldData = array();
		
		$objFilter = $this->Database->prepare("SELECT * FROM tl_module WHERE type='iso_productfilter' AND iso_listingModule=?")
										   ->execute($this->id);
			
		if($objFilter->numRows > 0)
		{	
			$arrFieldData = deserialize($objFilter->iso_searchFields);
			
			if(is_array($arrFieldData) && count($arrFieldData))
			{
				foreach($arrFieldData as $intFieldID)
				{
					$objAttributeData = $this->Database->prepare("SELECT * FROM tl_iso_attributes WHERE id=?")
													   ->limit(1)
													   ->execute($intFieldID);
			
					if($objAttributeData->numRows < 1)
					{			
						continue;
					}
					$arrSearchFields[] = $objAttributeData->field_name;
				}
			}
		}
		return $arrSearchFields;
	}

}

