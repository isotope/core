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
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    IsotopeBase 
 * @license    Commercial 
 * @filesource
 */


/**
 * Class IsotopeBase 
 *
 * Parent class for Isotope Modules
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    Controller
 */

abstract class ModuleIsotopeBase extends Module
{

	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();
	
	
	/**
	 * Shopping Cart Cookie
	 * @var string
	 */
//	protected $strCartCookie = 'ISOTOPE_TEMP_CART';
	
	/**
	 * 
	/**
	 * Template
	 * @var string
	 */
	protected $strPriceTemplate = 'stpl_price';
	
	/**
	 * Price String Override Template
	 * @var string
	 */
	protected $strPriceOverrideTemplate = 'stpl_price_override';
	
	/**
	 * current attribute set storage table
	 * @var string
	 */
	protected $strCurrentStoreTable;
	
	/**
	 * IP Address
	 * @var string
	 */
//	protected $strIp = '';
	
	/**
	 * Hash value of cookie
	 * @var string
	 */
//	protected $strCartHash = '';
	
//	protected $intStoreId;
	
//	protected $intCartId;
	
//	protected $strUserId;

	/**
	 * Jump to page id for the product reader.  Standard keys are 'product_reader', 'shopping_cart', and 'checkout'.
	 * @var array
	 */
//	protected $arrJumpToValues = array();
	
	/**
	 
	 */
//	protected $strCurrency;


	public function __construct(Database_Result $objModule, $strColumn='main')
	{
		parent::__construct($objModule, $strColumn);
		
		$_SESSION['isotope']['store_id'] = $this->store_id;
		
		$this->import('Isotope');
		$this->import('IsotopeStore', 'Store');
		$this->import('IsotopeCart', 'Cart');
		
		// Load isotope javascript class
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/isotope.js';
	}
	
	
//	public function generate()
//	{
/*
		//Check and set currency 	
		if($this->Input->post('currency'))
		{
			$session['isotope']['currency'] = $this->Input->post('currency');
			
			setlocale(LC_MONETARY, $GLOBALS['TL_LANG']['MSC']['isotopeLocale'][$this->Input->post('currency')]);

			//Commit the data to the session
		}else{
			setlocale(LC_MONETARY, $GLOBALS['TL_LANG']['MSC']['isotopeLocale'][$GLOBALS['TL_LANG']['MSC']['defaultCurrency']]);		
		}
*/
	
		
/*
		if(empty($session['isotope']['currency']))
		{
			$this->strCurrency = 'USD';
		}else{
			$this->strCurrency = $session['isotope']['currency'];
		}
*/
		
//		$this->strIp = $this->Environment->ip;
//		$this->intStoreId = (isset($session['isotope']['store_id']) ? $session['isotope']['store_id'] : 1);
		
//		$session['isotope']['store_id'] = $this->intStoreId;
			
//		$this->Session->setData($session);			
						
//		return parent::generate();	
//	}
	
	
	/**
	 * Generate a button for a given function such as add to cart buttons
	 * @param array
	 * @param string
	 * @return array
	 */
	protected function generateButtons($arrButtonData, $pageId, $strReturnUrl = NULL)
	{
		//$arrButtonTypes = array_keys($arrButtonData);
		
		foreach($arrButtonData as $buttonProperties)
		{									
				if(!strlen($buttonProperties['button_template']))
				{
					throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['missingButtonTemplate'], $buttonProperties['button_template']));
				}else{
					$objTemplate = new FrontendTemplate($buttonProperties['button_template']); //creating one base template object and cloning for specific products
				}
				
				$objTemplate->buttonType = 'link';
				$objTemplate->isAjaxEnabledButton = false;	
				$objTemplate->buttonLabelOrImage = $buttonProperties['button_label'];
				
				//Get list of product ids for the current button
				
				if($buttonProperties['params'])
				{
				
					$arrProductIds = array_keys($buttonProperties['params']);
					
					foreach($arrProductIds as $productId)
					{
								
						//Get the current product Id's params & process
						$objTemplate->buttonId = $buttonProperties['button_id'] . $productId;
						$objTemplate->actionTitle = sprintf($buttonProperties['action_string'], $buttonProperties['params'][$productId]['product_name']);
						$objTemplate->actionLink = $this->generateActionLinkString($buttonProperties['button_type'], $productId, $buttonProperties['params'][$productId], $pageId);
						$arrButtonHTML[$buttonProperties['button_type']][$productId] = $objTemplate->parse();
						
						
					}
				}
		}	
					
											
			return $arrButtonHTML;
			
			/*		
			Button Model Properties Not yet used - BEGIN

			--------------
			$objTemplate->buttonName = '';						//prefix "button_", NOT USED YET
			$objTemplate->buttonTabIndex = 0;						//tab index (optional)
			$objTemplate->buttonClickEvent = '';					//click event to invoke the button's script.  May be to an AJAX handler or just to a form submit.
			--------------

			Button Model Properties Not yet used - END
		*/	
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
			}else{
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

		/*
		switch($strLinkType)
		{
			case 'add_to_cart':
				//specific actions required to generate an add to cart link
				break;
			
			case 'remove_from_cart':
				//specific actions required to generate a remove from cart link (used in mini-cart template as an X)
				break;
			
			case 'update_cart':
				//specific actions require to generate an update cart link.
				break;
			
			default:
				// Call isotope_generate_custom_link_string for an action that hasn't been accounted for.
				if (is_array($GLOBALS['TL_HOOKS']['isotope_generate_custom_link_string']))
				{
					foreach ($GLOBALS['TL_HOOKS']['isotope_generate_custom_link_string'] as $callback)
					{
						if (is_array($callback))
						{
							$this->import($callback[0]);
							$this->$callback[0]->$callback[1]($strLinkType, $arrParams);
						}
					}
				}
				break;	
		}*/
		
		//return $strURL;
	
	}
	
	
	/**
	 * Get product data for the shopping cart.  In the future to save load time, store the data for each product as an array in the session after
	 * storing in the database so that we may quickly grab the session data instead, saving database calls.
	 * @param array
	 * @param array
	 * @return array
	 *//*
	protected function getProductData($arrAggregateSetData, $arrFieldNames, $strOrderByField)
	{					
		$strFieldList = join(',', $arrFieldNames);

		foreach($arrAggregateSetData as $data)
		{
			$arrProductsAndTables[$data['storeTable']][] = array($data['product_id'], $data['quantity_requested']); //Allows us to cycle thru the correct table and product ids collections.
			
			//The productID list for this storetable, used to build the IN clause for the product gathering.
			$arrProductIds[$data['storeTable']][] = $data['product_id'];
			
			//This is used to gather extra fields for a given product by store table.
			$arrProductExtraFields[$data['storeTable']][$data['product_id']]['attribute_set_id'] = $data['attribute_set_id'];
			
			$arrProductExtraFields[$data['storeTable']][$data['product_id']]['source_cart_id'] = $data['source_cart_id'];
			
			//Aggregate full product quantity all into one product line item for now.
			if($arrProductExtraFields[$data['storeTable']][$data['product_id']]['quantity_requested']<1)
			{
				$arrProductExtraFields[$data['storeTable']][$data['product_id']]['quantity_requested'] = $data['quantity_requested'];
			}else{
				$arrProductExtraFields[$data['storeTable']][$data['product_id']]['quantity_requested'] += $data['quantity_requested'];
			}
		}
		
		if(!sizeof($arrProductsAndTables))
		{
			$arrProductsAndTables = array();
		}
						
		$arrTotalProductsInCart = array();
					
		foreach($arrProductsAndTables as $k=>$v)
		{
							
			$strCurrentProductList = join(',', $arrProductIds[$k]);
						
			$objProducts = $this->Database->prepare("SELECT id, " . $strFieldList . " FROM " . $k . " WHERE id IN(" . $strCurrentProductList . ") ORDER BY " . $strOrderByField . " ASC")
										  ->execute();
			
			if($objProducts->numRows < 1)
			{
				return array();
			}
			
			$arrProductsInCart = $objProducts->fetchAllAssoc();
						
			foreach($arrProductsInCart as $product)
			{
				$arrProducts[$product['id']]['product_id'] = $product['id'];
				
				foreach($arrFieldNames as $field)
				{
					
					$arrProducts[$product['id']][$field] = $product[$field];		
				}
				
				$arrProducts[$product['id']]['attribute_set_id'] = $arrProductExtraFields[$k][$product['id']]['attribute_set_id'];
				$arrProducts[$product['id']]['source_cart_id'] = $arrProductExtraFields[$k][$product['id']]['source_cart_id'];
				$arrProducts[$product['id']]['quantity_requested'] = $arrProductExtraFields[$k][$product['id']]['quantity_requested'];
			}
	
								
			$arrTotalProductsInCart = array_merge($arrTotalProductsInCart, $arrProducts);
		}
		
		//Retrieve current session data, only if a new product has been added or else the cart updated in some way, and reassign the cart product data
		$session = $this->Session->getData();
		
		//clean old cart data
		unset($session['isotope']['cart_data']);
		
		//set new cart data
		$session['isotope']['cart_data'] = $arrTotalProductsInCart;
		
		
//		$session['isotope']['cart_id'] = $this->userCartExists($this->strUserId);
		
		
		$this->Session->setData($session);
				
		return $arrTotalProductsInCart;
	}*/
	
	
	/**
	 * Grab one or more products from a given attribute set by Id or Alias
	 * @param array
	 * @param array
	 * @return array
	 */
	/*
	protected function getProductData($arrAsetData, $arrFieldList = '')
	{

		if(is_array($arrFieldList))
		{
			$strFieldList = join(',', $arrFieldList);
		}else{
			$strFieldList = '*';
		}
		
		$strAsetKeys = join(',', array_keys($arrAsetIds));	//Get all attribute set numbers which are keys in the top level
		
		//Get the corresponding data for each record id.  We can then cycle through each record below
		$objCAPRecord = $this->Database->prepare("SELECT id, storeTable FROM tl_cap_aggregate WHERE id IN (" . $strAsetKeys . ")")
									   ->execute();
											   
		if($objCAPRecord->numRows < 1)
		{
			$isError = true;
			$errKey[] = 'invalidProductInformation';			
			
		}

		//Cycle through each record returned based on the list provided of aggregate record Ids.  With that information, we can 				
		while($objCAPRecord->next())
		{
			$strProductKeys = join(',', $arrAsetIds[$objCAPRecord->id]);
								
			$objProductData = $this->Database->prepare("SELECT " . $strFieldList . " FROM " . $objCAPRecord->storeTable . " WHERE product_visibility=1 AND id IN (" . $strProductKeys . ")")
											->execute();
							
			if($objProductData->numRows > 0)
			{
				$arrNewData = $objProductData->fetchAllAssoc();
				
				$arrProductData = array_merge($arrProductData, $arrNewData);
			}
		
		}
		
		return $arrProductData;
	} */
	 
	/** 
	 * Recursively get child pages associated with a given page id.
	 *
	 * @param integer
	 * @return array
	 */
	protected function getChildPages($intPageId)
	{
		
		$objChildPages = $this->Database->prepare("SELECT DISTINCT id FROM tl_page WHERE pid=?")->execute($intPageId);
		
						
		if($objChildPages->numRows < 1)
		{
			return;
		}	
			
		$arrChildPages[] = $objChildPages->fetchEach('id');
											
		foreach($arrChildPages as $page)
		{	
			$arrNewChildPages = $this->getChildPages($page);
			
			if(sizeof($arrNewChildPages))
			{
				$arrChildPages = array_merge($arrNewChildPages, $arrChildPages);
			}
			
		}
						
		return $arrChildPages;
				
	}
	
	
	/**
	 * Generate the required buttons for a products within the current store configuration
	 * @param integer
	 * @return string
	 */
	public function generateButtonsWidget($intStoreConfigurationId, $strFormTemplate, $strButtonTemplate)
	{
		//Get the form template
		$objTemplate = new FrontendTemplate($strFormTemplate);
		
		//Get the button template
		$objButtonTemplate = new FrontendTemplate($strButtonTemplate);
		
		$objButtonTemplate->buttonClickEvent = '.submit(); return false;';
		//$objButtonTemplate->buttonId = '';
		$objButtonTemplate->buttonLabel = '';
		$objButtonTemplate->buttonTabIndex = '';
		
		$objTemplate->action = '';
		$objTemplate->formId = '';
		$objTemplate->method = '';
		$objTemplate->enctype = '';
		$objTemplate->attributes = '';
		$objTemplate->formSubmit  = ''; //(unique form value)
		$objTemplate->hidden  = ''; //(collection of hidden fields)
		$objTemplate->buttons  = ''; //(collection of buttons)
	
		return $strHTML;
	}
	
	
	public function getPageData($id)
	{
		// Get target page
		$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
								  ->limit(1)
								  ->execute($id);
		
		
		if ($objPage->numRows > 0)
		{
				$strUrl = $this->generateFrontendUrl($objPage->fetchAssoc());
			
		}
		
		return $strUrl;
	}
			
	/**
	 * Generate a URL and return it as string
	 * @param object
	 * @param boolean
	 * @return string
	 */
	protected function generateProductUrl($arrProduct, $intJumpTo, $intAttributeSetId, $strProductIdKey = 'id', $blnAddArchive=false)
	{
		$strCacheKey = $strProductIdKey . '_' . $arrProduct[$strProductIdKey] . '_' . $intAttributeSetId . '_' . $arrProduct['tstamp'];

		// Load URL from cache
		if (array_key_exists($strCacheKey, self::$arrUrlCache))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		$strUrl = ampersand($this->Environment->request, ENCODE_AMPERSANDS);

		// Get target page
		$objJump = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
								  ->limit(1)
								  ->execute($intJumpTo);
		
		
		if ($objJump->numRows > 0)
		{
			$strUrl = ampersand($this->generateFrontendUrl($objJump->fetchAssoc(), '/asetid/' . $intAttributeSetId . '/product/' . $arrProduct['product_alias']));
		}
		else
		{
			global $objPage;
			$strUrl = ampersand($this->generateFrontendUrl(array('id'=>$objPage->id, 'alias'=>$objPage->alias), '/asetid/' . $intAttributeSetId . '/product/' . $arrProduct['product_alias']));
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
	protected function generateProductLink($strLink, $arrProduct, $intJumpTo, $intAttributeSetId, $strProductIdKey = 'id', $blnAddArchive=false)
	{
		// Internal link
		return 	$this->generateProductUrl($arrProduct, $intJumpTo, $intAttributeSetId, $strProductIdKey, $blnAddArchive);
	}
	
	/**
	 * Sort out protected archives
	 * @param array
	 * @return array
	 */
	protected function sortOutProtected($arrArchives)
	{
		if (BE_USER_LOGGED_IN)
		{
			return $arrArchives;
		}

		$this->import('FrontendUser', 'User');
		$objArchive = $this->Database->execute("SELECT id, protected, groups FROM tl_news_archive WHERE id IN(" . implode(',', $arrArchives) . ")");
		$arrArchives = array();

		while ($objArchive->next())
		{
			if ($objArchive->protected)
			{
				$groups = deserialize($objArchive->groups, true);

				if (!is_array($this->User->groups) || count($this->User->groups) < 1 || !is_array($groups) || count($groups) < 1)
				{
					continue;
				}

				if (count(array_intersect($groups, $this->User->groups)) < 1)
				{
					continue;
				}
			}

			$arrArchives[] = $objArchive->id;
		}

		return $arrArchives;
	}
	
	
	/**
	 * Generate a price string based on an product price, a template, and any pricing rules that apply.
	 *
	 * @param integer
	 * @return string (formatted html)
	 *
	 *//*
	protected function generatePriceString($intProductPrice, $currentCurrency, $strPriceTemplate = 'stpl_price')
	{
			
		$objPriceTemplate = new FrontendTemplate($strPriceTemplate);
		
		//$objPriceTemplate->currency = $GLOBALS['TL_LANG']['MSC']['CURRENCY'][$currentCurrency];
				
		$objPriceTemplate->price = money_format('%n', $intProductPrice);
						
		return $objPriceTemplate->parse();
		
	}*/
	
	/**
	 * Generate a price string based on an product price, a template, and any pricing rules that apply.
	 *
	 * @param integer
	 * @return string (formatted html)
	 *
	 *//*
	protected function generatePriceStringOverride($strPriceOverrideTemplate, $varValue)
	{
			
		$objPriceTemplate = new FrontendTemplate($strPriceOverrideTemplate);
				
		$objPriceTemplate->price = $varValue;
		
		
		//$objPriceTemplate->priceNote = $this->getPriceNote($intProductId); - Additional note to appear below the price itself, perhaps indicating what price includes?
				
		return $objPriceTemplate->parse();
		
	}*/
	
	
	protected function generatePrice($fltPrice, $strTemplate='stpl_price')
	{
		$objTemplate = new FrontendTemplate($strTemplate);
		
		$objTemplate->price = $this->Isotope->formatPriceWithCurrency($fltPrice, true);
		
		return $objTemplate->parse();
	}
	
	
	/**
	 * Get the final price including related price rules
	 *
	 */
	protected function getFinalPrice($intProductId, $strStoreTable)
	{
		$objProductPrice = $this->Database->prepare("SELECT product_price FROM " . $strStoreTable . " WHERE id=?")
										  ->limit(1)
										  ->execute($intProductId);
		
		if($objProductPrice->numRows < 1)
		{
			return '';
		}
		
		return $objProductPrice->product_price;
	}
	
	/**
	 * Get any messages relevant to a particular product
	 *
	 * @param integer
	 * @param string
	 * return array
	 */
	protected function getProductMessages($intProductId)
	{
		return array();
	
	}

	protected function getMissingImagePlaceholder($intStoreSettingsId)
	{
		$objMissingImage = $this->Database->prepare("SELECT missing_image_placeholder FROM tl_store WHERE id=?")
										  ->limit(1)
										  ->execute($intStoreSettingsId);
		
		if($objMissingImage->numRows < 1)
		{
			return '';
		}
		
		$strFilePath = $objMissingImage->missing_image_placeholder;
	
	
		return $strFilePath;
		
	}
	
	protected function getProductReaderJumpTo($intStoreSettingsId)
	{
		
		$objJumpTo = $this->Database->prepare("SELECT productReaderJumpTo FROM tl_store WHERE id=?")
										  ->limit(1)
										  ->execute($intStoreSettingsId);
		
		if($objJumpTo->numRows < 1)
		{
			return '';
		}
		
		$intPageId = $objJumpTo->productReaderJumpTo;

		return $intPageId;
	
	}
	

	protected function getOrderTotal($arrProductData)
	{
		foreach($arrProductData as $data)
		{
			$arrPrices[] = (float)$data['product_price'];
			$arrQuantities[] = (int)$data['quantity_requested'];
		}
			
		for($i=0;$i<count($arrPrices);$i++)
		{
			$floatSubTotalPrice += $arrPrices[$i] * $arrQuantities[$i];
		}				
					
		$taxPriceAdjustment = 0; // $this->getTax($floatSubTotalPrice, $arrTaxRules, 'MULTIPLY');
				
		return (float)$floatSubTotalPrice + (float)$taxPriceAdjustment;	
	
	}

	/*

	protected function getCartProductsByCartId($intCartId, $strUserId)
	{
		//do not query by cart id as it won't ever be stored past session, we only need the session value from the cookie to pull the right cart for the job.
		$objCartData = $this->Database->prepare("SELECT ci.* FROM tl_cart c INNER JOIN tl_cart_items ci ON c.id=ci.pid WHERE ci.pid=? AND c.cart_type_id=? AND c.pid=?")
										  ->execute($intCartId, 1, $strUserId);
										  
		if($objCartData->numRows < 1)
		{
			//Create a new cart for the user.
			//$this->intCartId = $this->createNewCart($strUserId);
		}else{
			
			$arrCartData = $objCartData->fetchAllAssoc();
			
			//Get all store tables for each given attribute_set_id record;
			foreach($arrCartData as $data)
			{
				$arrAsetIds[] = $data['attribute_set_id'];
			}
											
			$arrTableInfo = $this->getStoreTables($arrAsetIds);
							
			$i = 0;

			foreach($arrCartData as $row)
			{							
				
				$arrCartData[$i]['storeTable'] = $arrTableInfo[$row['attribute_set_id']];
				$i++;
			}
			
		}
				
		return $arrCartData;
	
	}
	
*/
	
/*
	protected function getStoreTables($arrAsetIds)
	{
		$strAsetIds = join(',', $arrAsetIds);
				
		$objStoreTables = $this->Database->prepare("SELECT id, storeTable FROM tl_product_attribute_sets WHERE id IN (" . $strAsetIds . ")")
										 ->execute();
		
		if($objStoreTables->numRows < 1)
		{
			return array();
		}
		
		//return array('id' => value, 'storeTable' => value);
		
		$arrStoreTables = $objStoreTables->fetchAllAssoc();
		
		foreach($arrStoreTables as $row)
		{
			$arrTableInfo[$row['id']] = $row['storeTable'];
		}
			
		return $arrTableInfo;
		
	}
*/
	
	
/*	
	protected function getStoreJumpToValues($intStoreSettingsId, $arrAdditionalKeys = '')
	{
		if(!is_array($arrAdditionalKeys))	//Additional jumpTo fields that may come later.
		{
			$arrAdditionalKeys = array();  //reset as array if the param is not a valid array.
		}
		
		if(sizeof($arrAdditionalKeys))
		{
			$i = 0;
			
			foreach($arrAdditionalKeys as $key)
			{
				//remove from array if not a valid field, but notify as well 
				if(!$this->Database->fieldExists($key, 'tl_store'))
				{
					unset($arrAdditionalKeys[$i]);
					//Notify user here of invalid field
				}
				
				$i++;
			}
			
			$strAdditionalJumpToFields = join(',', $arrAdditionalKeys);	//This is the final list of extra jump to fields.
		}	
		
		$objJumpTo = $this->Database->prepare("SELECT productReaderJumpTo, cartJumpTo, checkoutJumpTo " . (strlen($strAdditionalJumpToFields) > 0 ? ", " . $strAdditionalJumpToFields . " " : "") . "FROM tl_store WHERE id=?")
										  ->limit(1)
										  ->execute($intStoreSettingsId);
		
		if($objJumpTo->numRows < 1)
		{
			return '';
		}
		
		$arrJumpToValues['product_reader'] = $objJumpTo->productReaderJumpTo;
		$arrJumpToValues['shopping_cart'] = $objJumpTo->cartJumpTo;
		$arrJumpToValues['checkout'] = $objJumpTo->checkoutJumpTo;
		
		if(sizeof($arrAdditionalKeys))
		{
			//Grab any additional keys here.
			foreach($arrAdditionalKeys as $key)
			{
				$arrJumpToValues[$key] = $objJumpTo->$key;
			}
		}
		
		return $arrJumpToValues;
	}*/
	/*
	public function getCurrentStoreConfigById($intStoreId)
	{
		if(!$intStoreId)
		{
			return array();
		}
		
		$objStoreConfig = $this->Database->prepare("SELECT * FROM tl_store WHERE id=?")
										 ->limit(1)
										 ->execute($intStoreId);
		
		if($objStoreConfig->numRows < 1)
		{
			return array();
		}
		
		return $objStoreConfig->fetchAssoc();
	}
	*/
	protected function getRootAssetImportPath($intStoreSettingsId)
	{
		$objPath = $this->Database->prepare("SELECT root_asset_import_path FROM tl_store WHERE id=?")
										  ->limit(1)
										  ->execute($intStoreSettingsId);
		
		if($objPath->numRows < 1)
		{
			return '';
		}
		
		$strFilePath = $objPath->root_asset_import_path;
	
	
		return $strFilePath;
		
	}
	
	
	protected function getAttributeSetId($intAsetId)
	{
		$objAttributeSetId = $this->Database->prepare("SELECT attribute_set_id FROM tl_cap_aggregate WHERE id=?")
											->limit(1)
											->execute($intAsetId);
		if($objAttributeSetId->numRows < 1)
		{
			return false;
		}	
		
		return $objAttributeSetId->attribute_set_id;
	}
	/**
	 * Generate a Teaser text that terminates at the end of the closest sentence to the teaser length value.
	 *
	 * @param mixed
	 * @param object
	 * @return string
	 */
	protected function generateTeaser($varValue, $intLength=0)
	{
		if($intLength == 0 || empty($intLength))
		{
			$intLength = $GLOBALS['TL_LANG']['MSC']['teaserLength'];
		}
		
		$string = substr($varValue, 0, $intLength);
		
		
		if(!strpos($string, "."))
		{
			//Get the position of the first period after the first X number of characters
			$intFirstPeriod = strpos($varValue, ".", $intLength);
			
			$intFirstPeriod++;
			
			$string = substr($varValue, 0, $intFirstPeriod);
		}
			
		$char = strtolower(strlen($string));
								
		while ($char > 0)
		{
			if ($string{$char} == ".")
			{
				break;
			}else{
			
				$char = $char - 1;
			}
		}
		
		$char++;
		$string = substr($string, 0, $char); 
		
		return $string;
	}
	
	/**
	 * Clean a query string of any invalid characters to prevent XSS attacks, etc.
	 *
	 * @param string
	 * @return string
	 */
	protected function sanitizeQueryString($strValue) 
	{  
    	return ereg_replace("[^A-Za-z0-9-]", "", $strValue);  
	}  
	
	protected function verifyFilter($key, $value, $storeTable)
	{
		
		$objSampleData = $this->Database->prepare("SELECT " . $key . " FROM " . $storeTable . " WHERE " . $key . " IS NOT NULL")
										->limit(1)
										->execute();
				
		if(!is_int($objSampleData->$key) && !is_string($value))
		{
			return false;
		}elseif(!is_int((int)$value)){
			return false;
		}
				
		return true;
	}
	
	public function getFilterData($intAttributeId, $strAttributeType)
	{
		//global $objPage;
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($intAttributeId);
		
		
		if($objAttributeData->numRows < 1)
		{
			return '';
		}
		
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
						
			foreach($arrLinkValues as $value)
			{
				$arrLinkData[] = array
				(
					'value'		=> $value[$objAttributeData->id],
					'label'		=> $value[$objAttributeData->list_source_field]
				);
			
			}
			
		}else{
		
			$this->import('ProductCatalog');
			
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			$filter_name = strtolower($this->ProductCatalog->mysqlStandardize($objAttributeData->name));
			
			foreach($arrLinkValues as $value)
			{
				$arrLinkData[] = array
				(
					'value'		=> $value['value'],
					'label'		=> $value['label']
				);
			
			}
		}
		
		return $arrLinkData;
			
	}
	
	/**
	 *	getFilterListData - Grab a list of values and labels for a filter by attribute Id and by a list of eligible values.  If the array is empty, grab all values. 
	 *	@param integer
	 *  @param array
	 *  @return array
	 */
	protected function getFilterListData($intAttributeId, $arrValues = array())
	{
		if(sizeof($arrValues) < 1)
		{
			$blnGrabAll = true;
		}
				
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field, field_name FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($intAttributeId);
									  
		if($objAttributeData->numRows < 1)
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
				
			}else{
				
							
				
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
				
		}else{
					
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
									
			}else{
				
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
		
		usort($arrListData, array("ModuleIsotopeBase", "sortArrayAsc"));
		
				
		return $arrListData;
	
	
	}
	
	protected function sortArrayAsc($a, $b)
	{
			return strcasecmp($a['label'], $b['label']);
	}
	
	
	/**
	 * Get the customer's id whic is either a user Id or a session Id.
	 * 
	 * @return string
	 *//*
	protected function getCustomerId()
	{		
	
		$this->import('FrontendUser', 'User');
		//Check to see if the user is logged in.  If not, cart data should be found in session data.
		if (!FE_USER_LOGGED_IN)
		{	
			
			if(!strlen($this->Input->cookie($this->strCartCookie)))	
			{	
				//problem #1 - not retrieving the cookie!
				$intCookieDuration = $this->getCookieTimeWindow($this->store_id);
						
				$this->strCartHash = sha1(session_id().$this->strIp.$this->store_id.$this->strCartCookie);
				
				setcookie($this->strCartCookie, $this->strCartHash, (time() + ($intCookieDuration * 86400)),  $GLOBALS['TL_CONFIG']['websitePath']);
							
				return $this->strCartHash;
				//$strReturnURL = ltrim($session['referer']['current'], '/');
				//Have to set the cookie with a reload.
				// 
				//$this->reload();
				
			}else{
				return $this->Input->cookie($this->strCartCookie);
			}	
		}else{
	 		return $this->User->id;
		}
		
	}*/
	
	
/*
	protected function userCartExists($strUserId)
	{
		$strClause = $this->determineUserIdType($strUserId);
						
		$objUserCart = $this->Database->prepare("SELECT id FROM tl_cart WHERE cart_type_id=? AND " . $strClause)
									  ->limit(1)
									  ->execute(1);	//again this will vary later.
		
		if($objUserCart->numRows < 1)
		{
			return false;
			
		}
				
		return $objUserCart->id;
	
	}
*/

	
	protected function getCookieTimeWindow($intStoreId)
	{
		$objCookieTimeWindow = $this->Database->prepare("SELECT cookie_duration FROM tl_store WHERE id=?")	
											  ->limit(1)
											  ->execute($intStoreId);
		
		if($objCookieTimeWindow->numRows < 1)
		{
			return 0;
		}
				
		return $objCookieTimeWindow->cookie_duration;
	}
	
	/**
	 * User to determine by which method we will search for the user (user Id or session)
	 * @param string
	 * @return string
	 *//*
	protected function determineUserIdType($strUserId)
	{
		
		if(FE_USER_LOGGED_IN)
		{
			return "pid=" . $strUserId;
		}else{
			return "session='" . $strUserId . "'";
		}
	
	}*/
	
	/**
	 * User to determine by which method we will search for the user (user Id or session)
	 * @param string
	 * @return string
	 *//*
	protected function determineUserIdTypeSerialized($strUserId)
	{
		if(FE_USER_LOGGED_IN)
		{
			return "pid=" . $strUserId;
		}else{
			return "session='" . $strUserId . "'";
		}
	
	}*/
	
	/*
	 * Not necessary
	public function serialize($arrValues)
	{
		return base64_encode(@serialize($arrValues));
	}
	
	public function unserialize($varValue)
	{
		return @unserialize(base64_decode($varValue));	
	}
	*/
	
	/*
	protected getFilterListData($intAttributeId)
	{
		if(empty($intAttributeId))
		{
			return array();
		}
		
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($intAttributeId);
		
		
		if($objAttributeData->numRows < 1)
		{
			return '';
		}
		
		if($objAttributeData->use_alternate_source==1)
		{
			$objLinkData = $this->Database->prepare("SELECT id, " . $objAttributeData->list_source_field . " FROM " . $objAttributeData->list_source_table)
										  ->execute();
			
			if($objLinkData->numRows < 1)
			{
				return array();
			}
			
			$arrListValues = $objLinkData->fetchAllAssoc();
			
			$filter_name = $objAttributeData->list_source_field;
						
			foreach($arrLinkValues as $value)
			{
				$arrListData[] = array
				(
					'value'		=> $value[$objAttributeData->id],
					'title'		=> $value[$objAttributeData->list_source_field]
				);
			
			}
			
		}else{
		
			$this->import('ProductCatalog');
			
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			$filter_name = strtolower($this->ProductCatalog->mysqlStandardize($objAttributeData->name));
			
			foreach($arrLinkValues as $value)
			{
				$arrListData[] = array
				(
					'value'		=> $value['value'],
					'title'		=> $value['label']
				);
			
			}
		}
		
		return $arrListData;
	}*/

	
}

?>