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


abstract class ModuleIsotopeBase extends Module
{

	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();
	
	/**
	 * Template
	 * @var string
	 */
	protected $strPriceTemplate = 'stpl_price';
	
	/**
	 * for widgets, don't submit if certain validation(s) fail
	 * @var boolean;
	 */
	protected $doNotSubmit = false;
	
	
	public function __construct(Database_Result $objModule, $strColumn='main')
	{
		parent::__construct($objModule, $strColumn);
	
		if (TL_MODE == 'FE')
		{	
			$this->import('Isotope');
			$this->import('IsotopeCart', 'Cart');
			
			if (FE_USER_LOGGED_IN)
			{
				$this->import('FrontendUser', 'User');
			}
			
			// Load isotope javascript class
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/isotope_frontend.js';
			
			// Make sure field data is available
			if (!is_array($GLOBALS['TL_DCA']['tl_product_data']['fields']))
			{
				$this->loadDataContainer('tl_product_data');
				$this->loadLanguageFile('tl_product_data');
			}
		}
	}
	
	
	/**
	 * Generate a link string for various actions such as adding a product to the cart, removing from, or updating.
	 * @param string
	 * @param array
	 * @return string
	 */
	protected function generateActionLinkString($strAction, $intProductId, $arrParams, $pageId)
	{
		$strCacheKeyParams = 'action_' . $strAction . '_';
		$strParams = 'action/' . $strAction . '/';
		
		foreach($arrParams as $k=>$v)
		{
			if(array_key_exists('exclude', $arrParams))
			{
				if(!in_array($k, $arrParams['exclude']))
				{
					$strCacheKeyParams .= $k . '_' . $v . '_';
					$strParams .= $k . '/' . $v . '/';
				}
			}
			else
			{
					$strCacheKeyParams .= $k . '_' . $v . '_';
					$strParams .= $k . '/' . $v . '/';
			}
		}

		$strParams .= 'id/' . $intProductId;
		$strCacheKey = 'id_' . $intProductId . '_' . $strCacheKeyParams . $arrProduct['tstamp'];


		// Load URL from cache
		if (array_key_exists($strCacheKey, self::$arrUrlCache))
		{			
			return self::$arrUrlCache[$strCacheKey];
		}
		
		
		$strUrl = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		
		$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
								  ->limit(1)
								  ->execute($pageId);

		if ($objPage->numRows)
		{
			$strUrl = ampersand($this->generateFrontendUrl($objPage->fetchAssoc(), '/' . $strParams));			
		}
				
		self::$arrUrlCache[$strCacheKey] = $strUrl;

		return self::$arrUrlCache[$strCacheKey];
	}
	
			
	/**
	 * Generate a URL and return it as string
	 * @param object
	 * @param boolean
	 * @return string
	 */
	protected function generateProductUrl($arrProduct, $intJumpTo, $strProductIdKey = 'id', $blnAddArchive=false)
	{
		global $objPage;
		$strCacheKey = $strProductIdKey . '_' . $arrProduct[$strProductIdKey] . '_' . $arrProduct['tstamp'];

		// Load URL from cache
		if (array_key_exists($strCacheKey, self::$arrUrlCache))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		$strUrl = ampersand($this->Environment->request, ENCODE_AMPERSANDS);

		// Get target page
		$objJump = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")->limit(1)->execute($intJumpTo);
	
		if ($objJump->numRows > 0)
		{
			$strUrl = ampersand($this->generateFrontendUrl($objJump->fetchAssoc(), '/product/' . $arrProduct['alias']));
		}
		else
		{
			$strUrl = ampersand($this->generateFrontendUrl(array('id'=>$objPage->id, 'alias'=>$objPage->alias), '/details/product/' . $arrProduct['alias']));
		}

		self::$arrUrlCache[$strCacheKey] = $strUrl;
			
		return self::$arrUrlCache[$strCacheKey];
	}

	

	/**
	 * Generate a link and return it as string
	 * @param string
	 * @param object
	 * @param boolean
	 * @return string
	 */
	protected function generateProductLink($strLink, $arrProduct, $intJumpTo, $strProductIdKey = 'id', $blnAddArchive=false)
	{
		return 	$this->generateProductUrl($arrProduct, $intJumpTo, $strProductIdKey, $blnAddArchive);
	}
		
	
	protected function generatePrice($fltPrice, $strTemplate='stpl_price')
	{
		$objTemplate = new FrontendTemplate($strTemplate);
		
		$objTemplate->price = $this->Isotope->formatPriceWithCurrency($fltPrice, null, true);
		
		return $objTemplate->parse();
	}
	
	
	/**
	 *	getFilterListData - Grab a list of values and labels for a filter by attribute Id and by a list of eligible values.  If the array is empty, grab all values. 
	 *	@param integer
	 *  @param array
	 *  @return array
	 */
	protected function getFilterListData($intAttributeId, $arrValues = array())
	{
		if(count($arrValues) < 1)
		{
			$blnGrabAll = true;
		}
				
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field, field_name FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($intAttributeId);
									  
		if(!$objAttributeData->numRows)
		{
			return array();
		}
		
		$arrListData[] = array
		(
			'value'		=> NULL,
			'label'		=> $GLOBALS['TL_LANG']['MSC']['selectItemPrompt']
		);
		
		if($objAttributeData->use_alternate_source==1)
		{

			$objLinkData = $this->Database->prepare("SELECT id, " . $objAttributeData->list_source_field . " FROM " . $objAttributeData->list_source_table)
										  ->execute();
			
			if($objLinkData->numRows < 1)
			{
				return array();
			}
			
			$arrLinkValues = $objLinkData->fetchAllAssoc();
						
			$filter_name = $objAttributeData->list_source_field;
									
			if($blnGrabAll)
			{
				foreach($arrLinkValues as $value)
				{
					$arrListData[] = array
					(
						'value'		=> $value[$objAttributeData->id],
						'label'		=> $value[$objAttributeData->list_source_field]
					);
				}
			}
			else
			{
				foreach($arrLinkValues as $value)
				{
					if(in_array($value['id'], $arrValues))
					{
						$arrListData[] = array
						(
							'value'		=> $value['id'],
							'label'		=> $value[$objAttributeData->list_source_field]
						);
					}			
				}
			}
		}
		else
		{
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			if($blnGrabAll)
			{
				foreach($arrLinkValues as $value)
				{
					$arrListData[] = array
					(			
						'value'		=> $value['value'],
						'label'		=> $value['label']
					);
				}
			}
			else
			{
				foreach($arrLinkValues as $value)
				{
					if(in_array($value['value'], $arrValues))
					{
						$arrListData[] = array
						(			
							'value'		=> $value['value'],
							'label'		=> $value['label']
						);
					}
				}
			}
		}
		
		usort($arrListData, array($this, "sortArrayAsc"));
		
		return $arrListData;
	}
	
	protected function sortArrayAsc($a, $b)
	{
		return strcasecmp($a['label'], $b['label']);
	}
	
	
	protected function getCookieTimeWindow($intStoreId)
	{
		$objCookieTimeWindow = $this->Database->prepare("SELECT cookie_duration FROM tl_store WHERE id=?")->limit(1)->execute($intStoreId);
		
		if (!$objCookieTimeWindow->numRows)
		{
			return 0;
		}
				
		return $objCookieTimeWindow->cookie_duration;
	}
	
	
	/**
	 * determine the form's action method.
	 * @access protected
	 * @param string $strKey
	 * @return string
	 */
	protected function getRequestData($strKey)
	{
		return strlen($this->Input->post($strKey)) ? $this->Input->post($strKey) : $this->Input->get($strKey);
	}
		
	
	/**
	 * Shortcut for a single product by ID
	 */
	protected function getProduct($intId)
	{
		$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_product_types WHERE tl_product_data.type=tl_product_types.id) AS type_class FROM tl_product_data WHERE id=?")
										 ->limit(1)
										 ->executeUncached($intId);
									 
		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->type_class]['class'];
		
		if (!$this->classFileExists($strClass))
		{
			return null;
		}
									
		$objProduct = new $strClass($objProductData->row());
		
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo;
			
		return $objProduct;
	}
	
	
	/**
	 * Shortcut for a single product by alias (from url?)
	 */
	protected function getProductByAlias($strAlias)
	{
		$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_product_types WHERE tl_product_data.type=tl_product_types.id) AS type_class FROM tl_product_data WHERE alias=?")
										 ->limit(1)
										 ->executeUncached($strAlias);
									 
		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->type_class]['class'];
		
		if (!$this->classFileExists($strClass))
		{
			return null;
		}
									
		$objProduct = new $strClass($objProductData->row());
		
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo;
			
		return $objProduct;
	}
	
	
	/**
	 * Retrieve multiple products by ID.
	 */
	protected function getProducts($arrIds)
	{
		if (!is_array($arrIds) || !count($arrIds))
			return array();
		
		$arrProducts = array();
		
		foreach( $arrIds as $intId )
		{
			$objProduct = $this->getProduct($intId);
		
			if (is_object($objProduct))
				$arrProducts[] = $objProduct;
		}
		
		return $arrProducts;
	}
}

