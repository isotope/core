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
		$blnUpdateCartQuantities = false;
		

		if($strAction=='add_to_cart' || $strAction=='update_cart')
		{			
			//$arrOptionWidgets = explode(',', $this->getRequestData('option_fields'));
			
			/*if(sizeof($arrOptionWidgets))
			{	
				$this->validateOptionValues($arrOptionWidgets, $this->getRequestData('FORM_SUBMIT'));
			}*/
			
			if($this->Input->post('product_variants'))
			{
				$intVariantId = $this->Input->post('product_variants');
			}else{
				$intVariantId = 0;
			}
		}
		
		switch($strAction)
		{
			case 'add_to_cart':
				if(!$this->doNotSubmit)
				{
					if($intVariantId!=0)
					{
						$intId = $intVariantId;
					}else{
						$intId = $this->getRequestData('id');
					}
					$this->addToCart($intId, (int)$this->getRequestData('quantity_requested'), $intSourceCartId, $this->arrProductOptionsData);
					$this->blnRecallProductData = true;
				}
				break;
				
			case 'update_cart':
				if(!$this->doNotSubmit)
				{
					$this->updateCart($this->getRequestData('id'), (int)$this->getRequestData('quantity_requested'), $intSourceCartId, false, $this->arrProductOptionsData);
					$this->blnRecallProductData = true;
				}

				break;
			
			case 'cart_quantity_update':
				$blnUpdateCartQuantities = true;	//Requires a different method for updating cart.  Looks for QTY values in an INPUT field which only 		
				break;								//exists in the full cart template.
			case 'remove_from_cart':
				//a new quantity of zero indicates to remove 
				$this->updateCart($this->getRequestData('id'), 0, $intSourceCartId);
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
			break;
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
			
			if(is_array($arrProductData) && sizeof($arrProductData))
			{
				foreach($arrProductData as $k => $data)
				{
					$arrCartItemIds[] = $data['cart_item_id'];
				}
			}
		}	
	
		//Only happens in the full cart interface - updating cart quantities on items already in the cart.  Needs to happen after getting product data.
		if(strlen($blnUpdateCartQuantities))
		{
				
			foreach($arrCartItemIds as $row)
			{				
				$this->updateCart($row, $this->getRequestData('product_qty_' . $row), $intSourceCartId, true);		
			}
			
			$arrProductData = $this->Isotope->getProductData($this->Cart->getProducts(), $arrDisplayFields, 'name');
						
		}
		
			
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
		
		
		if(strlen($strAction))
		{
			$strReturnUrl = $_SESSION['FE_DATA']['referer']['current'];
			
			$this->redirect(ampersand($this->Environment->url . $strReturnUrl));
					
		}
		
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
	protected function addToCart($intProductId, $intQuantity, $intSourceCartId = 0, $arrProductOptionsData = array())
	{	
		$fltProductBasePrice = $this->getRequestData('price') ? $this->getRequestData('price') : $this->Isotope->getProductPrice($intProductId);
		
		$fltProductPrice = $this->Isotope->applyRules($fltProductBasePrice, $intProductId);
		//$fltProductPrice = $fltProductBasePrice;
			
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
				'quantity_requested'	=> $intQuantity,
				'price'					=> $fltProductPrice,
				//'source_cart_id'		=> $intSourceCartId//,
				'product_options'		=> serialize($arrProductOptionsData)
			);
			
			$this->Database->prepare("INSERT INTO tl_cart_items %s")->set($arrSet)->execute();
		}
		else
		{
			if($this->Cart->containsProduct($intProductId) && !$this->Cart->hasOptions($intProductId))
			{
				
				
					$this->Database->prepare("UPDATE tl_cart_items SET quantity_requested=(quantity_requested+" . $intQuantity . ")" . $strAdditionalFields . " WHERE product_id=? AND pid=?")
								   ->execute($intProductId, $this->Cart->id);
				
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
					'quantity_requested'	=> $intQuantity,
					'price'					=> $fltProductPrice,
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
	
	/**
	 * Remove one or more units of a given product from the cart
	 * @param integer
	 * @param integer
	 * @param array
	 * @return boolean
	 */
	protected function updateCart($intCartItemId, $intQuantity, $intSourceCartId = 0, $blnOverwriteQty = false, $arrProductOptionsData = array())
	{
		//Get visitor's cart
		
		//Prepare & execute the query.
		if(!is_null($intQuantity) && $intQuantity==0)
		{
			
			//Some sort of confirm maybe?
			$strQuery = "DELETE FROM tl_cart_items WHERE id=? AND pid=?";

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
			
			$strQuery = "UPDATE tl_cart_items SET quantity_requested=$strClause WHERE id=? AND pid=?";			
			
		}

		$this->Database->prepare($strQuery)
					   ->execute($intCartItemId, $this->Cart->id, $intSourceCartId);
	
		$this->blnRecallProductData = true;
		
		return;
	}
}

