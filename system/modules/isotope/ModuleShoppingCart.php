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
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>, Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ModuleShoppingCart extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_cart_full';
	
	/**
	 * Recall product data, if db has been updated with new information.
	 * @param boolean
	 */
	protected $blnRecallProductData = false;

	
	/** 
	 * 
	 * @param boolean
	 */
	
	protected $sessCartId;
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE SHOPPING CART ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Fallback template
		if (strlen($this->iso_cart_layout))
		{
			$this->strTemplate = $this->iso_cart_layout;
		}

		//BUG TO BE FIXED: A session id combined with a cart type is our Cart ID.  Actual record ID field for now is not necessary unless pulling products.
		//Every time you revisit the page after closign window it is determining that we haven't been here in teh last 30 days but we have.  I need to
		//Correct the code that is not finding the cookie value and using it to grab the cart with its ID.  
/*		
		// Get initial values set up
		$this->strUserId = $this->getCustomerId();
		$this->intCartId = $this->userCartExists($this->strUserId);
		
		$strExistingCookie = $this->Input->cookie($this->strCartCookie);
		
		if(FE_USER_LOGGED_IN)
		{
			$this->sessCartId = $this->userCartExists($strExistingCookie, false);		
		}

		$arrTempProducts = $this->getTempCartProducts($strExistingCookie);
		
		if(sizeof($arrTempProducts))
		{
			$this->mergeCartData($arrTempProducts);
		}
		
				
		$this->arrJumpToValues = $this->getStoreJumpToValues($this->store_id);	//Deafult keys are "product_reader", "shopping_cart", and "checkout"
	

		if(!$this->intCartId)
		{
			$this->intCartId = $this->createNewCart($this->strUserId);
		}
*/
		
		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{		
//		global $objPage;

/*
		// Call isotope_shopping_cart_onload_callback (e.g. to check permissions)
		if (is_array($GLOBALS['TL_HOOKS']['isotope_shopping_cart_onload']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isotope_shopping_cart_onload'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($arrCartItems);
				}
			}
		}
*/
	
		$strAction = $this->getRequestData('action');		
		
/*
		if($this->getRequestData('source_cart_id'))
		{
			$intSourceCartId = $this->getRequestData('source_cart_id');
		}else{
			$intSourceCartId = 0;
		}
*/
		if($strAction=='add_to_cart' || $strAction=='update_cart')
		{
			$intAttributeSetId = $this->getAttributeSetId($this->getRequestData('aset_id'));
		
			
			$arrOptionWidgets = explode(',', $this->getRequestData('option_fields'));
			
			if(sizeof($arrOptionWidgets))
			{	
				$this->validateOptionValues($arrOptionWidgets, $intAttributeSetId, $this->getRequestData('FORM_SUBMIT'));
			}	
		}
		
		switch($strAction)
		{
			case 'add_to_cart':
				if(!$this->doNotSubmit)
				{
					$this->addToCart($this->getRequestData('id'), $intAttributeSetId, $this->getRequestData('quantity_requested'), $intSourceCartId, $this->arrProductOptionsData);
					$this->blnRecallProductData = true;
				}
				break;
				
			case 'update_cart':
				if(!$this->doNotSubmit)
				{
					$this->updateCart($this->getRequestData('id'), $intAttributeSetId, $this->getRequestData('quantity_requested'), $intSourceCartId, $this->arrProductOptionsData);
					$this->blnRecallProductData = true;
				}

				break;
				
			case 'remove_from_cart':
				//$intAttributeSetId = $this->getAttributeSetId($this->getRequestData('aset_id'));
				//a new quantity of zero indicates to remove 
				$this->updateCart($this->getRequestData('id'), $this->getRequestData('attribute_set_id'), 0, $intSourceCartId);
				$this->blnRecallProductData = true;
				break;
/*
				
			default:
				// Call isotope_shopping_cart_custom_action
				if (is_array($GLOBALS['TL_HOOKS']['isotope_shopping_cart_custom_action']))
				{
					foreach ($GLOBALS['TL_HOOKS']['isotope_shopping_cart_custom_action'] as $callback)
					{
						if (is_array($callback))
						{
							$this->import($callback[0]);
							$this->$callback[0]->$callback[1]($strAction);
						}
					}
				}
*/
		}				
						
		//Hit the database for the product data for cart  This is what will happen if the user is viewing the cart directly instead of via an action
		//such as adding a product
		//$this->blnRecallProductData = true;
		
		$session = $this->Session->getData();

		if(!is_array($session) || !is_array($session['isotope']) || !array_key_exists('cart_data', $session['isotope']) || !sizeof($session['isotope']['cart_data']) < 1 || $this->blnRecallProductData)
		{		
			//what fields to display out in cart.
			
			$arrDisplayFields = array('alias','name','price', 'main_image');
						
			$arrProductData = $this->Isotope->getProductData($this->Cart->getProducts(), $arrDisplayFields, 'name');
			
			foreach($arrProductData as $k => $data)
			{
				$arrProductIds[$data['cart_item_id']] = $data['attribute_set_id'];
			}
		}	
	
		if($this->getRequestData('form_action')=='cart_update')
		{
					
			foreach($arrProductIds as $k=>$v)
			{
				
				$this->updateCart($k, $v, $this->getRequestData('product_qty_' . $k), $intSourceCartId, $this->arrProductOptionsData, true);
					
			}
			
			$arrProductData = $this->Isotope->getProductData($this->Cart->getProducts(), $arrDisplayFields, 'name');
			
			foreach($arrProductData as $k => $data)
			{
				$arrProductIds[$data['cart_item_id']] = $data['attribute_set_id'];
			}
			//$this->reload();
		}
		//actions need reload to show updated product info (until ajax comes along)
		/*
		if(strlen($strAction))
		{
			//referer current breaks if the back button is pressed.  Instead lets take the base of the url (index 0) and tack on .html.  Check with and without rewrites though!!
			
			
			//$arrUrlBits = explode('/', $this->Environment->request);
			
			$strReturnUrl = $_SESSION['FE_DATA']['referer']['current']; //$arrUrlBits[0] . '.html';		
					
			$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));
		
		}*/
		
		//we can take from session here because getProductData will update the session anyway, so at this point session always has the latest data.
		//$arrProductData = $session['isotope']['cart_data'];
		
		//$arrUserTaxData contains all parameters that are needed to retrieve a given tax rule from the tax rules.  So, if a use is taxed by country, then
		//grab the tax country and get the associated rate
		//if the user is taxed by state, then grab the user's tax based on their given state
		//if the user is taxed by postal code, then grab the user's tax based on their postal code.
		//Of course, you can stack one or more tax rules as well.
		
		//$arrTaxRules = $this->getTaxRules($this->store_id, $arrUserTaxData);
		
		
		if(!sizeof($arrProductData))
		{
			$arrFormattedProductData = array();
		}
		else
		{
			$arrFormattedProductData = $this->formatProductData($arrProductData);
		}

		
		$this->Template->cartJumpTo = $this->getPageData($this->Store->cartJumpTo);
		$this->Template->checkoutJumpTo = $this->getPageData($this->Store->checkoutJumpTo);
		$this->Template->products = $arrFormattedProductData;
		$this->Template->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$this->Template->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$this->Template->taxLabel = $GLOBALS['TL_LANG']['MSC']['shippingLabel'];
		$this->Template->taxTotal = $this->generatePrice($taxPriceAdjustment);
		$this->Template->taxLabel = sprintf($GLOBALS['TL_LANG']['MSC']['taxLabel'], 'Sales');
		$this->Template->taxTotal = $this->generatePrice($this->Cart->taxTotal);
		$this->Template->subTotalPrice = $this->generatePrice($this->Cart->subTotal, 'stpl_total_price');
		$this->Template->grandTotalPrice = $this->generatePrice($this->Cart->subTotal, 'stpl_total_price');		// FIXME
		$this->Template->noItemsInCart = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
		
		//$product['name']
		//$product['options']
		//$product['quantity_requested']
		//$product['price']
		
	}
	
	
	
	
	/**
	 * Add one or more units of a given product to the cart
	 * @param integer
	 * @param integer
	 * @param array
	 * @return boolean
	 */
	protected function addToCart($intProductId, $intAttributeSetId, $intQuantity, $intSourceCartId = 0, $arrProductOptionsData = array())
	{	
		if(sizeof($arrProductOptionsData))
		{
			// we can't assume this product is the same as another, so we add an item.
			//$objTask = $this->Database->prepare("INSERT INTO tl_task %s")->set($arrSet)->execute();
			//$pid = $objTask->insertId;
			$time = time();
		
			// Insert task
			$arrSet = array
			(
				'pid'					=> $this->Cart->id,
				'tstamp' 				=> $time,
				'product_id'			=> $intProductId,
				'attribute_set_id'		=> $intAttributeSetId,
				'quantity_requested'	=> $intQuantity,
				//'source_cart_id'		=> $intSourceCartId//,
				'product_options'		=> serialize($arrProductOptionsData)
			);
			
			$this->Database->prepare("INSERT INTO tl_cart_items %s")->set($arrSet)->execute();
		}
		else
		{
			if($this->Cart->containsProduct($intProductId, $intAttributeSetId))
			{
				
				
					$this->Database->prepare("UPDATE tl_cart_items SET quantity_requested=(quantity_requested+" . $intQuantity . ")" . $strAdditionalFields . " WHERE product_id=? AND attribute_set_id=? AND pid=?")
								   ->execute($intProductId, $intAttributeSetId, $this->Cart->id);
				
			}
			else
			{
				//$objTask = $this->Database->prepare("INSERT INTO tl_task %s")->set($arrSet)->execute();
				//$pid = $objTask->insertId;
				$time = time();
			
				// Insert task
				$arrSet = array
				(
					'pid'					=> $this->Cart->id,
					'tstamp' 				=> $time,
					'product_id'			=> $intProductId,
					'attribute_set_id'		=> $intAttributeSetId,
					'quantity_requested'	=> $intQuantity,
					//'source_cart_id'		=> $intSourceCartId//,
					'product_options'		=> serialize($arrProductOptionsData)
				);
				
				
				
				$this->Database->prepare("INSERT INTO tl_cart_items %s")->set($arrSet)->execute();
			}
		}
		
		if ($this->iso_forward_cart)
		{
			$this->import('IsotopeStore', 'Store');
			$this->jumpToOrReload($this->Store->cartJumpTo);
		}
	}
	
	
	
/*
	protected function productExistsInCart($intCartId, $intProductId, $intAttributeSetId, $intSourceCartId = 0)
	{
		//check session if not then we know we need to add it!
		$session = $this->Session->getData();
		
		//first check the session to save a db call to the cart.  It should always be in here. - future.
		//$session['isotope']['cart_data'][] = array(<product keys and values>);
		
		//query for the product id for the given cart, product and attribute set.
//		$objProductExistsInCart = $this->Database->prepare("SELECT COUNT(*) as count FROM tl_cart_items WHERE product_id=? AND pid=? AND attribute_set_id=? AND source_cart_id=?")
		$objProductExistsInCart = $this->Database->prepare("SELECT id FROM tl_cart_items WHERE product_id=? AND pid=? AND attribute_set_id=?")
												 ->limit(1)
												 ->execute($intProductId, $intCartId, $intAttributeSetId);
	
		if($objProductExistsInCart->numRows < 1)
		{
			return false;
		}
		
		if($objProductExistsInCart->count < 1)
		{
			return false;
		}
		
		
		return true;
		
	}
*/
	
	
	/**
	 * Get basic cart data including the corresponding aggregate set IDs for the products in the cart currently. (if any for the customer's cart)
	 * 
	 *//*
	protected function getCartProducts()
	{		
		//$session = $this->Session->getData();
				
		//$this->strUserId = $this->getCustomerId();
				
		$strFieldClause = $this->determineUserIdType($this->strUserId);
				
//		if(!array_key_exists('cart_id', $session['isotope']))
//		{
//			//if the cart Id doesn't exist in the session array, get it from the db based on user information cookie hash or actual user id.
//			$this->intCartId = $this->userCartExists($this->strUserId);
//			
//		}else{
//			$this->intCartId = $session['isotope']['cart_id'];
//		}	
		
		
		//do not query by cart id as it won't ever be stored past session, we only need the session value from the cookie to pull the right cart for the job.
		$objCartData = $this->Database->prepare("SELECT ci.* FROM tl_cart c INNER JOIN tl_cart_items ci ON c.id=ci.pid WHERE ci.pid=? AND c.cart_type_id=? AND c." . $strFieldClause)
										  ->execute($this->Cart->id, 1);
										  
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
	protected function userCartExists($strUserId, $blnDetermineUserID=true)
	{
		if($blnDetermineUserID)
		{
			$strClause = $this->determineUserIdType($strUserId);
		} else
		{
			$strClause = "session='".$strUserId."'";
		}
		
						
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
	
/*
	protected function createNewCart($strUserId)
	{
		$time = time();
		
		$arrSet = array
		(
			'pid'						=> (FE_USER_LOGGED_IN ? $strUserId : '0'),
			'tstamp'					=> $time,
			'cart_type_id'				=> 1,	//needs to vary later for other cart types!
			'session'					=> (!FE_USER_LOGGED_IN ? $strUserId : ''),
			'last_visit'				=> $time,
			//'source_cart_id'			=> $intSourceCartId,
			'store_id'					=> $this->store_id
		);
		
		
		$objCart = $this->Database->prepare("INSERT INTO tl_cart %s")->set($arrSet)->execute();
		
		return $objCart->insertId;
	}
*/
	
	/**
	 * Remove one or more units of a given product from the cart
	 * @param integer
	 * @param integer
	 * @param array
	 * @return boolean
	 */
	protected function updateCart($intCartItemId, $intAttributeSetId, $intQuantity, $intSourceCartId = 0, $arrProductOptionsData = array(), $blnOverwriteQty = false)
	{
		//Get visitor's cart
		
		//Prepare & execute the query.
		if($intQuantity==0)
		{
//			$strQuery = "DELETE FROM tl_cart_items WHERE product_id=? AND attribute_set_id=? AND pid=? AND source_cart_id=?";
			$strQuery = "DELETE FROM tl_cart_items WHERE product_id=? AND attribute_set_id=? AND pid=?";

		}
		else
		{
			if($blnOverwriteQty)
			{
				$strClause = $intQuantity;
			}
			else
			{
				$strClause = "(quantity_requested+" . $intQuantity . ")";
			}
			
			$strProductOptions = serialize($arrProductOptionsData);
			
//			$strQuery = "UPDATE tl_cart_items SET quantity_requested=$strClause WHERE product_id=? AND attribute_set_id=? AND pid=? AND source_cart_id=?";			
			$strQuery = "UPDATE tl_cart_items SET quantity_requested=$strClause, product_options='$strProductOptions' WHERE id=? AND attribute_set_id=? AND pid=?";			
			
		}

		$this->Database->prepare($strQuery)
					   ->execute($intCartItemId, $intAttributeSetId, $this->Cart->id, $intSourceCartId);
	
		$this->blnRecallProductData = true;
		
		return;
	}
}

