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
 
 
class IsotopeRegistry extends IsotopeProductCollection
{
		
	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_registry';
	
	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable = 'tl_iso_registry_items';
		
	
	/**
	 * Cache Registry data
	 * @var array
	 */
	protected $arrCache = array();
	
	
	protected $arrSurcharges;
	
	
	public function __construct()
	{
		parent::__construct();
		
		if (FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');
		}
	}
	
	
	/**
	 * Return Registry data. All data is cached for speed improvement.
	 * 
	 * @access public
	 * @param string $strKey
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{				
							
			case 'shipping_address':
			case 'shippingAddress':
					
				if ($this->arrCache['shippingAddress_id'] > 0)
				{
					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($this->arrCache['shippingAddress_id']);
					
					if ($objAddress->numRows)
						return $objAddress->fetchAssoc();
				}
				
				if ($this->arrCache['shippingAddress_id'] == 0 && count($this->arrCache['shippingAddress_data']))
				{
					return $this->arrCache['shippingAddress_data'];
				}
				
				$this->import('Isotope');
				
				$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE pid=? AND isDefaultShipping='1'")->limit(1)->execute($this->pid);
					
				if ($objAddress->numRows)
					return $objAddress->fetchAssoc();
					
				// Return the default user data, but ID should be 0 to know that it is a custom/new address
				// Trying to guess subdivision by country and state
				
				$objUser = $this->Database->prepare("SELECT * FROM tl_member WHERE id=?")->limit(1)->execute($this->pid);
				
				return array_intersect_key(array_merge($objUser->getData(), array('id'=>0, 'street_1'=>$objUser->street, 'subdivision'=>strtoupper($objUser->country . '-' . $objUser->User->state))), array_flip($this->Isotope->Config->billing_fields));
						
				
				return array('postal'=>$this->Isotope->Config->postal, 'subdivision'=>$this->Isotope->Config->subdivision, 'country' => $this->Isotope->Config->country);
				
			default:
				return parent::__get($strKey);
		}
	}
	
	
	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{

			case 'shippingAddress':
			case 'shipping_address':
				if (is_array($varValue))
				{
					$this->arrCache['shippingAddress_id'] = 0;
					$this->arrCache['shippingAddress_data'] = $varValue;
				}
				else
				{
					$this->arrCache['shippingAddress_id'] = $varValue;
				}
				break;
			
			default:
				parent::__set($strKey, $varValue);
		}
	}
	
	
	/**
	 * Load current user's registry
	 */
	public function initializeRegistry($arrData=array())
	{
		
		$this->findBy('pid', $this->User->id);
		$this->updateSold();
				
		// Create new registry
		if (!$this->blnRecordExists)
		{
			$this->setData($arrData);
			
			if (!$this->findBy('id', $this->save(true)))
			{
				throw new Exception('Unable to create gift registry');
			}
		}		
	}
	
	
	/**
	 * Update the products sold count for a given registry
	 */
	public function updateSold()
	{
		$objItems = $this->Database->prepare("SELECT pid, product_id, SUM(product_quantity) as quantity, (SELECT status FROM tl_iso_orders WHERE tl_iso_orders.id=tl_iso_order_items.pid) as status FROM tl_iso_order_items WHERE registry_id=? GROUP BY product_id")->execute($this->id);
		
		while($objItems->next())
		{
			if($objItems->status != 'cancelled')
				$this->Database->prepare("UPDATE {$this->ctable} SET quantity_sold=? WHERE product_id=? AND pid=?")->execute($objItems->quantity, $objItems->product_id, $this->id);
		}

	}
	
	
	/**
	 * Return array of info about a specific product in the registry (quantity, options, etc)
	 */
	public function getProductInfo($objProduct)
	{
		$arrInfo = array();
		$arrInfo = $this->Database->prepare("SELECT * FROM tl_iso_registry_items WHERE pid={$this->id} and product_id=?")->limit(1)->execute($objProduct->id)->fetchAllAssoc();
		
		return $arrInfo;
	}
	
	
	/**
	 * Transfer product from Registry to Cart
	 * Modified from parent class to take specific product, quantity parameter and registry id instead of entire transfer
	 */
	public function transferToCart(IsotopeProductCollection $objCollection, $objProduct, $intQuantity, $blnDuplicate=true)
	{
		if (!$this->blnRecordExists)
			return array();
			
		$time = time();
		$arrIds = array();
	 	$objOldItem = $this->Database->execute("SELECT * FROM {$this->ctable} WHERE pid={$this->id} AND product_id={$objProduct->id}");
									  
		while( $objOldItem->next() )
		{
			$objNewItem = $this->Database->execute("SELECT * FROM {$objCollection->ctable} WHERE pid={$objCollection->id} AND product_id={$objOldItem->product_id} AND product_options='{$objOldItem->product_options}' AND registry_id='{$this->id}'");
			
			// Product exists in target table. Increase amount.
			if ($objNewItem->numRows)
			{	
				$this->Database->query("UPDATE {$objCollection->ctable} SET tstamp=$time, product_quantity=($intQuantity+{$objNewItem->product_quantity}) WHERE id={$objNewItem->id}");
				$arrIds[] = $objNewItem->id;
			}
								
			// Product does not exist in this collection, we don't duplicate and are on the same table. Simply change parent id.
			elseif (!$objNewItem->numRows && !$blnDuplicate && $this->ctable == $objCollection->ctable)
			{
				$this->Database->query("UPDATE {$objCollection->ctable} SET tstamp=$time, pid={$objCollection->id} WHERE id={$objOldItem->id}");
				$arrIds[] = $objOldItem->id;
			}
			
			// Duplicate existing row to target table
			else
			{
				$arrSet = array('pid'=>$objCollection->id, 'tstamp'=>$time, 'product_quantity'=>$intQuantity, 'registry_id'=>$this->id);
				
				foreach( $objOldItem->row() as $k=>$v )
				{
					if (in_array($k, array('id', 'pid', 'tstamp', 'product_quantity', 'registry_id')))
						continue;
						
					if ($this->Database->fieldExists($k, $objCollection->ctable))
					{
						$arrSet[$k] = $v;
					}
				}
				
				$arrIds[] = $this->Database->prepare("INSERT INTO {$objCollection->ctable} %s")->set($arrSet)->executeUncached()->insertId;
			}
			
		}
		
		return $arrIds;
	}
	
	
	/**
	 * Fetch products from database. Modified from parent to convert product objects to RegistryProducts so we don't need to validate options
	 * 
	 * @access public
	 * @return array
	 */
	public function getProducts($strTemplate='', $blnNoCache=false)
	{
		if (!is_array($this->arrProducts) || $blnNoCache)
		{
			$this->arrProducts = array();
			$objItems = $this->Database->prepare("SELECT * FROM " . $this->ctable . " WHERE pid=?")->execute($this->id);
	
			while( $objItems->next() )
			{	
				//we can possibly simplify this if we have access to a the product's PID but as we're dealing with cart items, we don't by default.			
				$objVariantData = $this->Database->query("SELECT * FROM tl_iso_products WHERE id={$objItems->product_id}");
								
				$intProductId = ($objVariantData->pid ? $objVariantData->pid : $objVariantData->id);
				
				$objProductData = $this->Database->prepare("SELECT *, (SELECT class FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id) AS product_class FROM tl_iso_products WHERE id={$intProductId} ORDER BY pid ASC")->limit(1)->execute();
			
				//since this only pulls a variant with incomplete data we need to start with the parent product and then pull the child data in.
				if($objVariantData->numRows)
				{
					$arrVariantData = $objVariantData->row();
				
					//merge the product data
					foreach($arrVariantData as $k=>$v)
					{						
						if($v)
							$objProductData->$k = $v;
					}
				}
				
				$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];
				
				try
				{
					$objProduct = new RegistryProduct($objProductData->row());
				}
				catch (Exception $e)
				{
					$objProduct = new RegistryProduct(array('id'=>$objItems->product_id, 'sku'=>$objItems->product_sku, 'name'=>$objItems->product_name, 'price'=>$objItems->price));
				}
			
				$arrRules = array();
				$arrCoupons = array();
				
				if($objItems->rules)
				{
					$arrRuleData = deserialize($objItems->coupons, true);
					
					foreach($arrRuleData as $rule)
					{
						$arrRules[] = deserialize($rule, true);
					}

				}
				
				if($objItems->coupons)
				{							
					$arrCouponData = deserialize($objItems->coupons, true);
					
					foreach($arrCouponData as $coupon)
					{
						$arrCoupons[] = deserialize($coupon, true);	
					}
				}	
				
				$objProduct->rules = $arrRules;
				$objProduct->coupons = $arrCoupons;
												
				$objProduct->quantity_requested = $objItems->product_quantity;
				$objProduct->cart_id = $objItems->id;
				$objProduct->reader_jumpTo_Override = $objItems->href_reader;
				
				if($objProduct->price!==$objItems->price)
					$objProduct->price = $objItems->price;
				
				$objProduct->setOptions(deserialize($objItems->product_options, true));
			
				$this->arrProducts[] = $objProduct;
			}
		}
		
		if (strlen($strTemplate))
		{
			$objTemplate = new FrontendTemplate($strTemplate);
			$objTemplate->products = $this->arrProducts;
			return $objTemplate->parse();
		}

		return $this->arrProducts;
	}
	
	
	
	/**
	 * Must be implemented by child class
	 */
	protected function getSurcharges()
	{
		return array();
	}


}


