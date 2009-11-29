<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleProductLister extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_productlist';
	
	protected $strFormId = 'iso_product_list';

	
	protected $strPriceOverrideTemplate = 'stpl_price_override';
       
	
	/**
	 * The ids of all pages we take care of. this is what should later be used eg. for filter data.
	 */
	protected $arrCategories;
        
        
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
		
		//Determine category scope
		switch($this->iso_category_scope)
		{
			case 'global':
				 $this->arrCategories = array_merge($this->getChildRecords($objPage->rootId, 'tl_page'), array($objPage->rootId));
				break;
				
			case 'parent_and_first_child':
				$this->arrCategories = array_merge($this->Database->prepare("SELECT id FROM tl_page WHERE pid=?")->execute($objPage->id)->fetchEach('id'), array($objPage->id));
				break;
				
			case 'parent_and_all_children':
				$this->arrCategories = array_merge($this->getChildRecords($objPage->id, 'tl_page'), array($objPage->id));				
				break;
				
			default:
			case 'current_category':
				$this->arrCategories = array($objPage->id);
				break;		
		}
		
		if (!count($this->arrCategories))
			return '';

		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		
		$this->perPage = ($this->Input->get('per_page') ? $this->Input->get('per_page') : 10);
		$this->orderBy = $this->Input->get('order_by');
		$this->searchTerms = $this->Input->get('for');
		
		$objProductIds = $this->Database->prepare("SELECT * FROM tl_product_categories c, tl_product_data p WHERE c.pid=p.id AND c.page_id IN (" . implode(',', $this->arrCategories) . ")");
		
		// Add pagination
		if ($this->perPage > 0)
		{
			$total = $objProductIds->execute()->numRows;
			$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
			$offset = ($page - 1) * $this->perPage;

			$objPagination = new Pagination($total, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
			
			$objProductIds->limit($this->perPage, $offset);
		}
		
		$arrProducts = $this->getProducts($objProductIds->execute()->fetchEach('id'));
				
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['noProducts'];
			return;
		}
		
		// Buttons
		$arrButtons = array
		(
			'add_to_cart'		=> array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('IsotopeCart', 'addProduct')),
		);
		
		if (isset($GLOBALS['TL_HOOKS']['isoListButtons']) && is_array($GLOBALS['TL_HOOKS']['isoReaderButtons']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isoListButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}
		}
		
		
		$arrTemplateData = array
		(
			'buttons'		=> $arrButtons,
			'quantityLabel'	=> $GLOBALS['TL_LANG']['MSC']['quantity'],
			'useQuantity'	=> $this->iso_use_quantity,
		);
		
		$arrBuffer = array();
		
		foreach( $arrProducts as $i => $objProduct )
		{
			if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && $this->Input->post('product_id') == $objProduct->id)
			{
				foreach( $arrButtons as $button => $data )
				{
					if (strlen($this->Input->post($button)))
					{
						if (is_array($data['callback']) && count($data['callback']) == 2)
						{
							$this->import($data['callback'][0]);
							$this->{$data['callback'][0]}->{$data['callback'][1]}($objProduct, $this);
						}
						
						break;
					}
				}
				
				$this->reload();
			}				
		
			$arrBuffer[] = array
			(
				'raw'		=> $objProduct,
				'clear'	    => ($this->iso_list_format=='grid' && $blnSetClear ? true : false),
				'class'		=> ('product' . ($i == 0 ? ' product_first' : '')),
				'html'		=> $objProduct->generate($this->iso_list_layout, $arrTemplateData),
			);

			$blnSetClear = (($i+1) % $this->columns==0 ? true : false);

		}
		
		// Add "product_last" css class
		if (count($arrBuffer))
		{
			$arrBuffer[count($arrBuffer)-1]['class'] .= ' product_last';
		}

		$this->Template->action = ampersand($this->Environment->request, true);
		$this->Template->formId = $this->strFormId;
		$this->Template->buttons = $arrButtons;
		$this->Template->products = $arrBuffer;
			
}
	
	
	/**
	 *  Get listing filter data from the cache (tl_filter_values_to_categories)
	 *
	 *  @param integer
	 *  @param integer
	 *  @param boolean
	 *  @return array
	 */
	private function getListingFilterData($intAttributeId, $arrClauses = array(), $blnUseCache = true)
	{
		$objListingFilterData = $this->Database->prepare("SELECT value_collection FROM tl_filter_values_to_categories WHERE attribute_id=? AND pid IN (" . implode($this->arrCategories) . ")")->execute($intAttributeId);
			
		if($objListingFilterData->numRows < 1)
		{
			return array();
		}
						
		$arrListingFilterData = $objListingFilterData->fetchEach('value_collection');
		
				
		foreach($arrListingFilterData as $listingDataCollection)
		{
			$arrValuesReturned = deserialize($listingDataCollection); 
						
			foreach($arrValuesReturned as $value)
			{	
				$arrValues[] = $value;
			}
		}
		
		$arrUniqueValues = array_unique($arrValues);
				
		$arrRefinedValues = $this->getFilterListData($intAttributeId, $arrUniqueValues);
				
		return $arrRefinedValues;
	}
	
	
	
	/**
	 *	Calculate the per-page options based on the number of product columns specified.  The first option is always * 4 rows
	 *  for example, 5 wide * 4 rows = default option of 20 per page.
	 *
	 *	@param integer
	 *	@return array
	 */
	private function getPerPageOptions($intColumns)
	{
		//
		$arrPerPageOptions[] = ($intColumns * 4) * 1;
		$arrPerPageOptions[] = ($intColumns * 4) * 2;
		$arrPerPageOptions[] = ($intColumns * 4) * 3;
		$arrPerPageOptions[] = ($intColumns * 4) * 5;
		$arrPerPageOptions[] = ($intColumns * 4) * 10;
	
		return $arrPerPageOptions;
	}
	/**
	 * Future feature - allows admin to display a message within a template to appear if an announcement is to be made for example, a promotion for a certain category - this could be used to display that promotion easily.
	 * 
	 * @param string
	 * @return string (formatted html)
	 *
	 */
	private function getAdditionalMessages($intPageId)
	{
		
		return;
	
	}
	
	private function getOrderByOptions()
	{
		$objOrderByAttributes = $this->Database->prepare("SELECT name, field_name, type FROM tl_product_attributes WHERE is_visible_on_front=? AND is_order_by_enabled=?")
											   ->execute(1, 1);
	
		if($objOrderByAttributes->numRows < 1)
		{
			return array();
		}
		
		$arrAttributes = $objOrderByAttributes->fetchAllAssoc();
		
		foreach($arrAttributes as $attribute)
		{
			$arrSortingDirections = $this->generateSortingDirections($attribute['type']);
			
			$arrOptions[] = array
			(
				'value'		=> $attribute['field_name'] . '-ASC',
				'label'		=> $attribute['name'] . ' ' . $arrSortingDirections['ASC']
			);
			
			$arrOptions[] = array
			(
				'value'		=> $attribute['field_name'] . '-DESC',
				'label'		=> $attribute['name'] . ' ' . $arrSortingDirections['DESC']
			);
		}
		
		return $arrOptions;
	}
	
	private function generateSortingDirections($strType)
	{
		switch($strType)
		{
			case 'integer':
			case 'decimal':
			
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['low_to_high'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['high_to_low']);
				break;
			
			case 'text':
			case 'longtext':
			
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['a_to_z'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['z_to_a']);
				break;
			case 'datetime':
				
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['old_to_new'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['new_to_old']);
				break;
			default:
				return;
				break;
		}
	
	}
	
	/* NOTE - THIS FUNCTION IS DUPED IN SEVERAL PLACES (CART, REGISTRY)*/
	// FIXME
	protected function userRegistryExists($strUserId)
	{
		return false;
		
		$strClause = $this->determineUserIdType($strUserId);
						
		$objUserCart = $this->Database->prepare("SELECT id FROM tl_cart WHERE cart_type_id=? AND " . $strClause)
									  ->limit(1)
									  ->execute(2);	//again this will vary later.
		
		if($objUserCart->numRows > 0)
		{
			return true;
			
		}
				
		return false;
	
	}
	
	public function generateAjax()
	{
		
		 
		//get the default params
		$arrFilters = array('for'=>$this->Input->get('for'),'per_page'=>$this->Input->get('per_page'),'page'=>$this->Input->get('page'),'order_by'=>$this->Input->get('order_by'));	


		/*$arrFilterFields = implode(',', $this->Input->get('filters'));	//get the names of filters we are using

		foreach($arrFilterFields as $field)
		{
			if($this->Input->get($field))
			{
				$arrFilters[$field] = $this->Input->get($field);
			}
		}*/	

		$strHtml = $this->generateListing($arrFilters);

		return $strHtml;
	}

	/**
	 * Generate the listing template in html to update the listing results
	 * @var array $arrFilters
	 * @return string
	 */
	protected function generateListing($arrFilters)
	{				
		$arrFilterClauses = array();
		$arrSearchClauses = array();
		$arrOrderByClauses = array();
		$arrFilterChunks = array();
		$arrParams = NULL;

		$objTemplate = new FrontendTemplate($this->strTemplate);
				
		//Determine category scope
		switch($this->iso_category_scope)
		{
			case 'global':
				 $this->arrCategories = array_merge($this->getChildRecords($this->Input->get('rid'), 'tl_page'), array($this->Input->get('rid')));
				break;
				
			case 'parent_and_first_child':
				$this->arrCategories = array_merge($this->Database->prepare("SELECT id FROM tl_page WHERE pid=?")->execute($this->Input->get('pid'))->fetchEach('id'), array($this->Input->get('pid')));
				break;
				
			case 'parent_and_all_children':
				$this->arrCategories = array_merge($this->getChildRecords($this->Input->get('pid'), 'tl_page'), array($this->Input->get('pid')));				
				break;
				
			default:
			case 'current_category':
				$this->arrCategories = array($this->Input->get('pid'));
				break;		
		}
		
		foreach($arrFilters as $filter=>$value)
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
					//prepare clause for text search. TODO:  need to add filter for each std. search field plus any additional user-defined.
					$arrSearchFields = array('name','description');
					
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
		
		if(count($arrFilterClauses[0]))
		{			
			foreach($arrFilterClauses as $param)
			{
				$arrFilterChunks[] = $param['sql'];
				$arrParams[] = $param['value'];
			}
		}	

		if(count($arrSearchClauses[0]))
		{
			foreach($arrSearchClauses as $param)
			{
				$arrSearchChunks[] = $param['sql'];
				$arrParams[] = $param['value'];
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
			
			$strOrderBySQL = implode(', ', $arrOrderBySQLWithParentTable);
		}
		
		$strFilterSQL = (count($arrFilterChunks) ? implode(" AND ", $arrFilterChunks) : NULL);
		$strSearchSQL = (count($arrSearchChunks) ? implode(" OR ", $arrSearchChunks) : NULL);
		
		//$strParams = (count($arrParams) ? implode(",", $arrParams) : NULL);
		
		//echo "SELECT p.* FROM tl_product_categories c, tl_product_data p WHERE p.id=c.pid" . ($strFilterSQL ? " AND (" . $strFilterSQL . ")" : "") . " AND c.page_id IN (" . implode(',', $this->arrCategories) . ")" . ($strSearchSQL ? " AND (" . $strSearchSQL . ")" : "") . ($strOrderBySQL ? " ORDER BY " . $strOrderBySQL : "");
		$objProductIds = $this->Database->prepare("SELECT p.* FROM tl_product_categories c, tl_product_data p WHERE p.id=c.pid" . ($strFilterSQL ? " AND (" . $strFilterSQL . ")" : "") . " AND c.page_id IN (" . implode(',', $this->arrCategories) . ")" . ($strSearchSQL ? " AND (" . $strSearchSQL . ")" : "") . ($strOrderBySQL ? " ORDER BY " . $strOrderBySQL : ""));
		
		// Add pagination
		if ($this->perPage > 0)
		{
			$total = $objProductIds->execute($arrParams)->numRows;
			$page = $this->currentPage ? $this->currentPage : 1;
			$offset = ($page - 1) * $this->perPage;

			$objPagination = new Pagination($total, $this->perPage);
			$objTemplate->pagination = $objPagination->generate("\n  ");
			
			$objProductIds->limit($this->perPage, $offset);
		}
		
		$arrProducts = $this->getProducts($objProductIds->execute($arrParams)->fetchEach('id'));
				
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$objTemplate = new FrontendTemplate('mod_message');
			$objTemplate->type = 'empty';
			$objTemplate->message = $GLOBALS['TL_LANG']['MSC']['noProducts'];
			return;
		}
		
		// Buttons
		$arrButtons = array
		(
			'add_to_cart'		=> array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('IsotopeCart', 'addProduct')),
		);
		
		if (isset($GLOBALS['TL_HOOKS']['isoListButtons']) && is_array($GLOBALS['TL_HOOKS']['isoReaderButtons']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isoListButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}
		}
		
		$arrBuffer = array();
		
		foreach( $arrProducts as $i => $objProduct )
		{			
		
			$arrBuffer[] = array
			(
				'raw'		=> $objProduct,
				'clear'	    => ($this->iso_list_format=='grid' && $blnSetClear ? true : false),
				'class'		=> ('product' . ($i == 0 ? ' product_first' : '')),
				'html'		=> $objProduct->generate($this->iso_list_layout),
			);

			$blnSetClear = (($i+1) % $this->columns==0 ? true : false);

		}
		
		// Add "product_last" css class
		if (count($arrBuffer))
		{
			$arrBuffer[count($arrBuffer)-1]['class'] .= ' product_last';
		}
		
		$objTemplate->action = $this->Environment->base;
		$objTemplate->formId = $this->strFormId;
		$objTemplate->buttons = $arrButtons;
		$objTemplate->products = $arrBuffer;
		
		return $objTemplate->parse();
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
	
}