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
 * Class ModuleShoppingCart
 *
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    Controller
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

			return $objTemplate->parse();
		}

		// Fallback template
		if (!strlen($this->iso_cart_layout))
		{
			$this->iso_cart_layout = 'iso_cart_full';
		}

		$this->strTemplate = $this->iso_cart_layout;

		//BUG TO BE FIXED: A session id combined with a cart type is our Cart ID.  Actual record ID field for now is not necessary unless pulling products.
		//Every time you revisit the page after closign window it is determining that we haven't been here in teh last 30 days but we have.  I need to
		//Correct the code that is not finding the cookie value and using it to grab the cart with its ID.  
		
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
			
			return parent::generate();
			
		}else{
			return parent::generate();
		}
		
		
		
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{		
				
		global $objPage;

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
	
		$strAction = $this->Input->get('action');		
		
		if($this->Input->get('source_cart_id'))
		{
			$intSourceCartId = $this->Input->get('source_cart_id');
		}else{
			$intSourceCartId = 0;
		}
		
		switch($strAction)
		{
			case 'add_to_cart':
				$intAttributeSetId = $this->getAttributeSetId($this->Input->get('aset_id'));
				
				
				
				$this->addToCart($this->Input->get('id'), $intAttributeSetId, $this->Input->get('quantity_requested'), $intSourceCartId);
				$this->blnRecallProductData = true;
				break;
			case 'update_cart':
				$intAttributeSetId = $this->getAttributeSetId($this->Input->get('aset_id'));

				$this->updateCart($this->Input->get('id'), $intAttributeSetId, $this->Input->get('quantity_requested'), $intSourceCartId);
				$this->blnRecallProductData = true;
				break;
			case 'remove_from_cart':
				//$intAttributeSetId = $this->getAttributeSetId($this->Input->get('aset_id'));

				//a new quantity of zero indicates to remove 
				$this->updateCart($this->Input->get('id'), $this->Input->get('attribute_set_id'), 0, $intSourceCartId);
				$this->blnRecallProductData = true;
				break;
			default:
				// Call isotope_shopping_cart_custom_action (e.g. to check permissions)
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
		}				
						
		//Hit the database for the product data for cart  This is what will happen if the user is viewing the cart directly instead of via an action
		//such as adding a product
		//$this->blnRecallProductData = true;
		
		$session = $this->Session->getData();
		
		if(!array_key_exists('cart_data', $session['isotope']) || !sizeof($session['isotope']['cart_data']) < 1 || $this->blnRecallProductData)
		{
			$arrAggregateSetData = $this->getCartProducts();
			
			if(!sizeof($arrAggregateSetData))
			{
				$arrAggregateSetData = array();
			}
				
			$arrProductData = $this->getProductData($arrAggregateSetData, array('product_alias','product_name','product_price', 'product_images'), 'product_name');
			
			
			foreach($arrProductData as $data)
			{
				$arrProductIds[$data['product_id']] = $data['attribute_set_id'];
			}
		}	
	
		if($this->Input->post('form_action')=='cart_update')
		{
					
			foreach($arrProductIds as $k=>$v)
			{
				$this->updateCart($k, $v, $this->Input->post('product_qty_' . $k), $intSourceCartId, true);
					
			}
			
			$this->reload();
		}
		//actions need reload to show updated product info (until ajax comes along)
		
		if(strlen($strAction))
		{
			//referer current breaks if the back button is pressed.  Instead lets take the base of the url (index 0) and tack on .html.  Check with and without rewrites though!!
			
			
			//$arrUrlBits = explode('/', $this->Environment->request);
			
			$strReturnUrl = $_SESSION['FE_DATA']['referer']['current']; //$arrUrlBits[0] . '.html';		
					
			$this->redirect(ampersand($this->Environment->base . ltrim($strReturnUrl, '/')));
		
		}
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
			$floatSubTotalPrice = 0;
		}else{
			$arrFormattedProductData = $this->formatProductData($arrProductData);
			
			$floatSubTotalPrice = $this->getOrderTotal($arrProductData);
			
		}
		
		
		if(!sizeof($arrProductData))
		{
			$arrFormattedProductData = array();
			$floatGrandTotalPrice = 0;
		}else{
			$arrFormattedProductData = $this->formatProductData($arrProductData);
			
			$floatGrandTotalPrice = $this->getOrderTotal($arrProductData);
			
		}
		
		$this->Template->cartJumpTo = $this->getPageData($this->arrJumpToValues['shopping_cart']);
		$this->Template->checkoutJumpTo = $this->getPageData($this->arrJumpToValues['checkout']);
		$this->Template->products = $arrFormattedProductData;
		$this->Template->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$this->Template->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$this->Template->taxLabel = sprintf($GLOBALS['TL_LANG']['MSC']['taxLabel'], 'Sales');
		$this->Template->taxTotal = $this->generatePriceString($taxPriceAdjustment, $this->strCurrency);
		$this->Template->subTotalPrice = $this->generatePriceString($floatSubTotalPrice, $this->strCurrency, 'stpl_total_price');
		$this->Template->grandTotalPrice = $this->generatePriceString($floatGrandTotalPrice, $this->strCurrency, 'stpl_total_price');
		$this->Template->noItemsInCart = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
		
		//$product['name']
		//$product['options']
		//$product['quantity_requested']
		//$product['price']
		
	}
	
		
	protected function formatProductData($arrProductData)
	{
		global $objPage;
		
		
		
 		foreach($arrProductData as $row)
		{
			
			$intTotalPrice = $row['product_price'] * $row['quantity_requested'];
			$arrFormattedProductData[] = array
			(
				'product_id'		=> $row['product_id'],
				'image'				=> $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . substr($row['product_alias'], 0, 1) . '/' . $row['product_alias'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['thumbnail_images_folder'] . '/' . $row['product_images'],
				'name'				=> $row['product_name'],
				'link'				=> $this->generateProductLink($row['product_alias'], $row, $this->arrJumpToValues['product_reader'], $row['attribute_set_id'], 'product_id'),
				'price'				=> $this->generatePriceString($row['product_price'], $this->strCurrency, $this->strPriceTemplate),
				'total_price'		=> $this->generatePriceString($intTotalPrice, $this->strCurrency, 'stpl_total_price'),
				'quantity'			=> $row['quantity_requested'],
				'remove_link'		=> $this->generateActionLinkString('remove_from_cart', $row['product_id'], array('attribute_set_id'=>$row['attribute_set_id'],'quantity'=>0, 'source_cart_id'=>$row['source_cart_id']), $objPage->id),
				'remove_link_title' => sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $row['product_name'])
			
			);

		}
		
		
		return $arrFormattedProductData;
	
	}
	
	
	
	
	
	
	
	/**
	 * Get basic cart data including the corresponding aggregate set IDs for the products in the cart currently. (if any for the customer's cart)
	 * 
	 */
	protected function getCartProducts()
	{		
		//$session = $this->Session->getData();
				
		//$this->strUserId = $this->getCustomerId();
				
		$strFieldClause = $this->determineUserIdType($this->strUserId);
				
		/*if(!array_key_exists('cart_id', $session['isotope']))
		{
			//if the cart Id doesn't exist in the session array, get it from the db based on user information cookie hash or actual user id.
			$this->intCartId = $this->userCartExists($this->strUserId);
			
		}else{
			$this->intCartId = $session['isotope']['cart_id'];
		}*/		
		
		
		//do not query by cart id as it won't ever be stored past session, we only need the session value from the cookie to pull the right cart for the job.
		$objCartData = $this->Database->prepare("SELECT ci.* FROM tl_cart c INNER JOIN tl_cart_items ci ON c.id=ci.pid WHERE ci.pid=? AND c.cart_type_id=? AND c." . $strFieldClause)
										  ->execute($this->intCartId, 1);
										  
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
	

	protected function getTempCartProducts($strExistingCookie)
	{
		
		
		if(is_null($this->sessCartId))
		{
			return array();
		}
		$objCartData = $this->Database->prepare("SELECT ci.* FROM tl_cart c INNER JOIN tl_cart_items ci ON c.id=ci.pid WHERE ci.pid=? AND c.cart_type_id=? AND c.session=?")
										  ->execute($this->sessCartId, 1, $strExistingCookie);
										  
		if($objCartData->numRows < 1)
		{
			return array();
		}
		
		return $objCartData->fetchAllAssoc();
	}

	protected function mergeCartData($arrProductData)
	{
		foreach($arrProductData as $product)
		{
			$this->addToCart($product['product_id'], $product['attribute_set_id'], $product['quantity_requested']);
		}
		$this->cleanTempCart($arrProductData);
		
	}
	
	
	protected function cleanTempCart($arrProductData)
	
	{
		foreach($arrProductData as $product)
		{
			$this->Database->prepare("DELETE FROM tl_cart_items WHERE id=?")
				->execute($product['id']);
		}
	}
	
	/**
	 * Add one or more units of a given product to the cart
	 * @param integer
	 * @param integer
	 * @param array
	 * @return boolean
	 */
	protected function addToCart($intProductId, $intAttributeSetId, $intQuantity, $intSourceCartId = 0)
	{
				
		//Step 1: If the user has an existing cart by session Id or user Id then retrieve the cart Id
		//$this->intCartId = $this->userCartExists($this->strUserId);
		
		if($this->intCartId!==false)
		{
			if($this->productExistsInCart($this->intCartId, $intProductId, $intAttributeSetId))
			{
				$strMethod = 'update';
			}else{
				$strMethod = 'insert';
			}
		
		}else{
			//will this ever happen? it shouldn't.

			//$this->intCartId = $this->createNewCart($strUserId);
			$strMethod = 'insert';
		}
		
		
		//Step 2: Insert or update if the product exists.
		switch($strMethod)
		{
			case 'insert':
				//$objTask = $this->Database->prepare("INSERT INTO tl_task %s")->set($arrSet)->execute();
				//$pid = $objTask->insertId;
				$time = time();
			
				// Insert task
				$arrSet = array
				(
					'pid'					=> $this->intCartId,
					'tstamp' 				=> $time,
					'product_id'			=> $intProductId,
					'attribute_set_id'		=> $intAttributeSetId,
					'quantity_requested'	=> $intQuantity,
					'source_cart_id'		=> $intSourceCartId//,
					//'product_options'		=> serialize($arrProductOptions)
				);
								
				$objCartItem = $this->Database->prepare("INSERT INTO tl_cart_items %s")->set($arrSet)->execute();
				
				break;
				
			case 'update':
				
				$this->Database->prepare("UPDATE tl_cart_items SET quantity_requested=(quantity_requested+" . $intQuantity . ")" . $strAdditionalFields . " WHERE product_id=? AND source_cart_id=? AND attribute_set_id=? AND pid=?")
							   ->execute($intProductId, $intSourceCartId, $intAttributeSetId, $this->intCartId);
				break;
			default:
				break;
		}
				
	}
	
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
	
	/**
	 * Remove one or more units of a given product from the cart
	 * @param integer
	 * @param integer
	 * @param array
	 * @return boolean
	 */
	protected function updateCart($intProductId, $intAttributeSetId, $intQuantity, $intSourceCartId = 0, $blnOverwriteQty = false)
	{
		//Get visitor's cart
		
		//Prepare & execute the query.
		if($intQuantity==0)
		{
			$strQuery = "DELETE FROM tl_cart_items WHERE product_id=? AND attribute_set_id=? AND pid=? AND source_cart_id=?";

		}else{
			if($blnOverwriteQty)
			{
				$strClause = $intQuantity;
			}else{
				$strClause = "(quantity_requested+" . $intQuantity . ")";
			}
			
			$strQuery = "UPDATE tl_cart_items SET quantity_requested=$strClause WHERE product_id=? AND attribute_set_id=? AND pid=? AND source_cart_id=?";			
			
		}
				
		$this->Database->prepare($strQuery)
					   ->execute($intProductId, $intAttributeSetId, $this->intCartId, $intSourceCartId);
	
		$this->blnRecallProductData = true;
	}
	
	protected function productExistsInCart($intCartId, $intProductId, $intAttributeSetId, $intSourceCartId = 0)
	{
		//check session if not then we know we need to add it!
		$session = $this->Session->getData();
		
		//first check the session to save a db call to the cart.  It should always be in here. - future.
		//$session['isotope']['cart_data'][] = array(<product keys and values>);
		
		//query for the product id for the given cart, product and attribute set.
		$objProductExistsInCart = $this->Database->prepare("SELECT COUNT(*) as count FROM tl_cart_items WHERE product_id=? AND pid=? AND attribute_set_id=? AND source_cart_id=?")
												 ->limit(1)
												 ->execute($intProductId, $intCartId, $intAttributeSetId, $intSourceCartId);
	
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
	

	
	
}

?>