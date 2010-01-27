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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
class IsotopeCart extends Model
{
	
	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;

	/**
	 * Cookie hash value
	 * @var string
	 */
	protected $strHash = '';
	
	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_cart';
	
	/**
	 * Cart type. Example: can be "2" for gift registry.
	 * @var int
	 */
	protected $intType = 1;
	
	/**
	 * Name of the temporary cart cookie
	 * @var string
	 */
	protected $strCookie = 'ISOTOPE_TEMP_CART';
	
	/**
	 * Cache get requests to improve speed. Cart data cannot change without reload...
	 * @var array
	 */
	protected $arrCache = array();
	
	/**
	 * Cache all products for speed improvements
	 * @var array
	 */
	protected $arrProducts;
	
	protected $arrSurcharges;
	
	/**
	 * Shipping object if shipping module is set in session
	 * @var object
	 */
	public $Shipping;
	
	/**
	 * Payment object if payment module is set in session
	 * @var object
	 */
	public $Payment;
	
	
	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}
	
	
	/**
	 * Return the current object instance (Singleton)
	 * @return object
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new IsotopeCart();
		}

		return self::$objInstance;
	}
	
	
	/**
	 * Return cart data. All data is cached for speed improvement.
	 * 
	 * @access public
	 * @param string $strKey
	 * @return mixed
	 */
	public function __get($strKey)
	{
		// Return from database result
		if (array_key_exists($strKey, $this->arrData))
		{
			return $this->arrData[$strKey];
		}
		
		$this->import('Isotope');
		
		// Add to cache if not available
		if (!array_key_exists($strKey, $this->arrCache))
		{
			switch( $strKey )
			{
				case 'items':
					$this->arrCache[$strKey] = $this->Database->prepare("SELECT SUM(quantity_requested) AS items FROM tl_cart_items LEFT OUTER JOIN tl_cart ON tl_cart_items.pid=tl_cart.id WHERE tl_cart_items.pid=? AND tl_cart.cart_type_id=?")->execute($this->id, $this->intType)->items;
					break;
					
				case 'products':
					$this->arrCache[$strKey] = $this->Database->prepare("SELECT COUNT(*) AS items FROM tl_cart_items LEFT OUTER JOIN tl_cart ON tl_cart_items.pid=tl_cart.id WHERE tl_cart_items.pid=? AND tl_cart.cart_type_id=?")->execute($this->id, $this->intType)->items;
					break;
					
				case 'subTotal':
					return $this->calculateTotal($this->getProducts());
					
				case 'taxTotal':
					$intTaxTotal = 0;
					$arrSurcharges = $this->getSurcharges();
					
					foreach( $arrSurcharges as $arrSurcharge )
					{
						if ($arrSurcharge['add'])
							$intTaxTotal += $arrSurcharge['total_price'];
					}
					
					$this->arrCache[$strKey] = $intTaxTotal;
					break;
					
				case 'taxTotalWithShipping':
					$this->arrCache[$strKey] =  $this->taxTotal + $this->shippingTotal;
					break;
				
				case 'shippingTotal':
					$this->arrCache[$strKey] = $this->hasShipping ? (float)$this->Shipping->price : 0.00;
					break;
					
				case 'grandTotal':
					return ($this->subTotal + $this->taxTotal + $this->shippingTotal);
					
				case 'requiresShipping':
					$this->arrCache[$strKey] = false;
					$arrProducts = $this->getProducts();
					foreach( $arrProducts as $objProduct )
					{
						if (!$objProduct->shipping_exempt)
						{
							$this->arrCache[$strKey] = true;
							break;
						}
					}
					break;
					
				case 'totalWeight':
				
					$arrProducts = $this->getProducts();
					
					foreach($arrProducts as $objProduct)
					{						
						$fltShippingWeight += $objProduct->weight * $objProduct->quantity_requested;
					}
										
					return $fltShippingWeight;
					
				case 'hasShipping':
					return is_object($this->Shipping) ? true : false;
					
				case 'hasPayment':
					return is_object($this->Payment) ? true : false;
					
				case 'billingAddress':
			
					if ($this->arrCache['billingAddress_id'] > 0)
					{
						$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=?")->limit(1)->execute($this->arrCache['billingAddress_id']);
						
						if ($objAddress->numRows)
							return $objAddress->fetchAssoc();
					}
					elseif ($this->arrCache['billingAddress_id'] === 0 && is_array($this->arrCache['billingAddress_data']))
					{
						return $this->arrCache['billingAddress_data'];
					}
								
					if (FE_USER_LOGGED_IN)
					{					
						$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=? AND isDefaultBilling='1'")->limit(1)->execute($this->User->id);
						
						if ($objAddress->numRows)
							return $objAddress->fetchAssoc();
							
						// Return the default user data, but ID should be 0 to know that it is a custom/new address
						return array_merge($this->User->getData(), array('id'=>0));
					}
					else
					{
						return $_SESSION['CHECKOUT_DATA']['billing_address'];
					}					
					
					return $this->arrCache['billingAddress_data'];
					
				case 'shippingAddress':
				
					if ($this->arrCache['shippingAddress_id'] == -1)
					{							
						return array_merge($this->billingAddress, array('id' => -1));
					}
						
					if ($this->arrCache['shippingAddress_id'] > 0)
					{
						$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE id=?")->limit(1)->execute($this->arrCache['shippingAddress_id']);
						
						if ($objAddress->numRows)
							return $objAddress->fetchAssoc();
					}
					
					if ($this->arrCache['shippingAddress_id'] == 0 && count($this->arrCache['shippingAddress_data']))
					{
						return $this->arrCache['shippingAddress_data'];
					}

					if (FE_USER_LOGGED_IN)
					{
						$objAddress = $this->Database->prepare("SELECT * FROM tl_address_book WHERE pid=? AND isDefaultShipping='1'")->limit(1)->execute($this->User->id);
						
						if ($objAddress->numRows)
							return $objAddress->fetchAssoc();
					}
					
					return array_merge((is_array($this->billingAddress) ? $this->billingAddress : array()), array('id' => -1));
			}
		}
		
		return $this->arrCache[$strKey];
	}
	
	
	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{
			case 'billingAddress':
			case 'billing_address':			
				if (is_array($varValue))
				{
					$this->arrCache['billingAddress_id'] = 0;
					$this->arrCache['billingAddress_data'] = $varValue;
				}
				else
				{
					$this->arrCache['billingAddress_id'] = $varValue;
				}
				break;

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
	 * Load current cart
	 *
	 * @todo why do we need store_id in tl_cart?
	 */
	public function __construct()
	{
		$this->import('Isotope');
		
		parent::__construct();
		
		$this->strHash = $this->Input->cookie($this->strCookie);
		
		//  Check to see if the user is logged in.
		if (!FE_USER_LOGGED_IN)
		{	
			if(!strlen($this->strHash))	
			{	
				$this->strHash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? $this->Environment->ip : '') . $this->Isotope->Store->id . $this->strCookie);
				
				$this->setCookie($this->strCookie, $this->strHash, $this->Isotope->Store->cookie_timeout,  $GLOBALS['TL_CONFIG']['websitePath']);
			}

			$this->findBy('session', $this->strHash);
		}
		else
		{
			$this->import('FrontendUser', 'User');
	 		$this->findBy('pid', $this->User->id);
		}
				
		// Create new cart
		if (!$this->blnRecordExists)
		{
			$this->setData(array
			(
				'pid'			=> (FE_USER_LOGGED_IN ? $this->User->id : 0),
				'session'		=> $this->strHash,
				'tstamp'		=> time(),
				'last_visit'	=> time(),
				'cart_type_id'	=> $this->intType,
				'store_id'		=> $this->Isotope->Store->id,
			));
			
			if (!$this->findBy('id', $this->save(true)))
			{
				throw new Exception('Unable to create shopping cart');
			}
		}
				
		// Temporary cart available, move to this cart. Must be after creating a new cart!
 		if (FE_USER_LOGGED_IN && strlen($this->strHash))
 		{
 			$objCartData = $this->Database->prepare("SELECT ci.* FROM tl_cart c INNER JOIN tl_cart_items ci ON c.id=ci.pid WHERE c.session=? AND c.cart_type_id=?")->execute($this->strHash, $this->intType);
										  
			while( $objCartData->next() )
			{
				$objExistingMemberCartData = $this->Database->prepare("SELECT * FROM tl_cart_items WHERE product_id=? AND pid=?")->limit(1)->execute($objCartData->product_id, $this->id);
									
				//Nothing existing in member's cart, just add items to it.		 
				if($objExistingMemberCartData->numRows < 1)
				{
					$this->Database->prepare("UPDATE tl_cart_items SET pid=? WHERE id=?")->execute($this->id, $objCartData->id);					
				
				}
				else
				{
					// Do something to cart data when merging products into a member cart.  Good for applying rules.
					if (isset($GLOBALS['TL_HOOKS']['isoOnCartMerge']) && is_array($GLOBALS['TL_HOOKS']['isoOnCartMerge']))
					{
						foreach ($GLOBALS['TL_HOOKS']['isoOnCartMerge'] as $callback)
						{
							$this->import($callback[0]);
							$objCartData = $this->{$callback[0]}->{$callback[1]}($objCartData);
						}
					}

					while( $objExistingMemberCartData->next() )
					{
						// Only sum quantity if two products with same ids have no product options.
						if($objExistingMemberCartData->numRows > 0)
						{
							if(sizeof(deserialize($objExistingMemberCartData->product_options))<1 && sizeof(deserialize($objCartData->product_options))<1)
							{
								$this->Database->prepare("UPDATE tl_cart_items SET quantity_requested=(quantity_requested+" . $objCartData->quantity_requested . ") WHERE product_id=? AND pid=?")
											   ->execute($objCartData->product_id, $this->id);							
							}else{
								$this->Database->prepare("UPDATE tl_cart_items SET pid=? WHERE id=?")->execute($this->id, $objCartData->id);
							}				   
							//$this->Database->prepare("DELETE FROM tl_cart_items WHERE id=?")->execute($objCartData->id);
						}						
						// Simply move item to this cart
						else
						{
							$this->Database->prepare("UPDATE tl_cart_items SET pid=? WHERE id=?")->execute($this->id, $objCartData->id);
						}
					}
				}
			}
			
			// Delete cookie
			$this->setCookie($this->strCookie, '', (time() - 3600), $GLOBALS['TL_CONFIG']['websitePath']);
			
			// Delete cart
			$this->Database->prepare("DELETE FROM tl_cart WHERE session=? AND pid=0")->execute($this->strHash);
 		}
	}
	
	
	/**
	 * Find a record by its reference field and return true if it has been found. Include cart type id.
	 * @param  int
	 * @return boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		$this->blnRecordExists = false;
		$this->strRefField = $strRefField;
		$this->varRefId = $varRefId;

		$resResult = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE " . $this->strRefField . "=? AND cart_type_id=?")->execute($this->varRefId, $this->intType);

		if ($resResult->numRows == 1)
		{
			$this->arrData = $resResult->fetchAssoc();
			$this->blnRecordExists = true;

			return true;
		}

		return false;
	}
	
	
	/**
	 * Also delete cart items when dropping this cart.
	 */
	public function delete()
	{
		$this->Database->prepare("DELETE FROM tl_cart_items WHERE pid=?")->execute($this->id);
		
		return parent::delete();
	}
	

	/**
	 * Fetch products from database.
	 * 
	 * @access public
	 * @return array
	 */
	public function getProducts($strTemplate='')
	{
		if (!is_array($this->arrProducts))
		{
			$this->arrProducts = array();
			$objProducts = $this->Database->prepare("SELECT * FROM tl_cart_items WHERE pid=?")->execute($this->id);
	
			while( $objProducts->next() )
			{
				// Do not use the TYPOlight function deserialize() cause it handles arrays not objects
				$objProduct = unserialize($objProducts->product_data);
								
				$objProduct->quantity_requested = $objProducts->quantity_requested;
				$objProduct->product_options = deserialize($objProducts->product_options);
				$objProduct->cart_id = $objProducts->id;
	
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
	 * Callback for add_to_cart button
	 *
	 * @todo	fetch data of custom fields
	 * @access	public
	 * @param	object
	 * @return	void
	 */
	public function addProduct($objProduct, $objModule=null)
	{		
		$arrAllOptionValues = array();
		$arrOptionValues = array();
		$arrVariantOptions = array();
		
		
		if($this->Input->post('product_variants'))
		{			
			$objProduct->setVariant($this->Input->post('product_variants'), $this->Input->post('variant_options'));	
			
			$arrVariantOptions = explode(',', $this->Input->post('variant_options'));
			
			//cycle through each product object's set variant option.
			foreach($arrVariantOptions as $option)
			{
				$arrAttributeData = $this->getProductAttributeData($option);
				
				$arrVariantOptionValues[$option] = array
				(
					'name'		=> $arrAttributeData['name'],
					'values'	=> array($objProduct->$option)
				);
			}
			
			
			//$arrAllOptionValues = array_merge(deserialize($this->getProductOptionValues($this->Input->post('product_options'))), $arrVariantOptionValues);
		
		}
		
		if($this->Input->post('product_options'))
		{
			
			$arrOptionValues = deserialize($this->getProductOptionValues($this->Input->post('product_options')));

		}
		
		$arrAllOptionValues = array_merge($arrOptionValues, $arrVariantOptions);
		
		if(count($arrAllOptionValues))
		{
			$strAllOptionValues = serialize($arrAllOptionValues);
		}
				
		$arrSet = array
		(
			'pid'					=> $this->id,
			'tstamp'				=> time(),
			'quantity_requested'	=> ((is_object($objModule) && $objModule->iso_use_quantity && intval($this->Input->post('quantity_requested')) > 0) ? intval($this->Input->post('quantity_requested')) : 1),
			'price'					=> $objProduct->price,	//NOTE: Won't reference the variable unless $ precedes curly brackets!
			'price_override'		=> $objProduct->price_override,
			'href_reader'			=> $objProduct->href_reader,
			'product_id'			=> ($objProduct->subId ? $objProduct->subId : $objProduct->id),
			'product_data'			=> serialize($objProduct),
			'product_options'		=> $strAllOptionValues 
		);

		if (!$this->Database->prepare("UPDATE tl_cart_items SET tstamp=?, quantity_requested=quantity_requested+" . $arrSet['quantity_requested'] . " WHERE pid=? AND product_id=? AND product_data=? AND product_options=?")->execute($arrSet['tstamp'], $this->id, $arrSet['product_id'], $arrSet['product_data'], $arrSet['product_options'])->affectedRows)
		{
			$this->Database->prepare("INSERT INTO tl_cart_items %s")->set($arrSet)->execute();
		}
	}
	
	
	public function deleteProduct($intId)
	{
		$this->Database->prepare("DELETE FROM tl_cart_items WHERE id=?")->execute($intId);					   
	}
	
	
	/** 
	 * Need to grab the corresponding data from the subproduct if product_variants is being called!
	 */
	private function getProductOptionValues($strProductOptions)
	{	
		if (!strlen($strProductOptions))
			return '';
			
		$arrProductOptions = explode(',', $strProductOptions);
		
		foreach($arrProductOptions as $option)
		{	
			$arrAttributeData = $this->getProductAttributeData($option); //1 will eventually be irrelevant but for now just going with it...
			
			$varValue = $this->Input->post($option);
			
			switch($arrAttributeData['type'])
			{
				case 'radio':
				case 'checkbox':
				case 'select':
					
					//get the actual labels, not the key reference values.
					$arrOptions = $this->getOptionList($arrAttributeData);
					
					if(is_array($varValue))
					{
						foreach($varValue as $value)
						{
							foreach($arrOptions as $optionRow)
							{
								if($optionRow['value']==$value)
								{
									$varOptionValues[] = $optionRow['label'];
									break;
								}
							}
						}	
					}
					else
					{
						foreach($arrOptions as $optionRow)
						{
							if($optionRow['value']==$varValue)
							{
								$varOptionValues[] = $optionRow['label'];
								break;
							}
						}
					}				
					break;
					
				default:
					//these values are not by reference - they were directly entered.  
					if (is_array($varValue))
					{
						foreach($varValue as $value)
						{
							$varOptionValues[] = $value;
						}
					}
					else
					{
						$varOptionValues[] = $varValue;
					}
					break;
			}		
		
			$arrValues[$option] = array
			(
				'name'		=> $arrAttributeData['name'],
				'values'	=> $varOptionValues			
			);
		}
		
		return serialize($arrValues);
	}
	
	
	
	protected function getOptionList($arrAttributeData)
	{
		if ($arrAttributeData['use_alternate_source']==1)
		{
			if(strlen($arrAttributeData['list_source_table']) > 0 && strlen($arrAttributeData['list_source_field']) > 0)
			{
				$strForeignKey = $arrAttributeData['list_source_table'] . '.' . $arrAttributeData['list_source_field'];
			}
		}
		else
		{
			$arrValues = deserialize($arrAttributeData['option_list']);
		}
	
		return $arrValues;
	}


	protected function getProductAttributeData($strFieldName)
	{		
		
		$objAttributeData = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE field_name=?")->limit(1)->execute($strFieldName);

		if (!$objAttributeData->numRows)
		{
			return array();
		}
		
		return $objAttributeData->fetchAssoc();
	}


	/**
	 * Hook-callback for isoCheckoutSurcharge. Accesses the shipping module to get a shipping surcharge.
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 */
	public function getShippingSurcharge($arrSurcharges)
	{
		if ($this->hasShipping && $this->Shipping->price > 0)
		{
			$arrSurcharges[] = array
			(
				'label'			=> ($GLOBALS['TL_LANG']['MSC']['shippingLabel'] . ' (' . $this->Shipping->label . ')'),
				'price'			=> '&nbsp;',
				'total_price'	=> $this->Shipping->price,
				'tax_class'		=> 0,
				'add_tax'		=> false,
			);
		}
		
		return $arrSurcharges;
	}
	
	
	/**
	 * Hook-callback for isoCheckoutSurcharge.
	 *
	 * @todo	Accesses the payment module to get a payment surcharge.
	 * @access	public
	 * @param	array
	 * @return	array
	 */
	public function getPaymentSurcharge($arrSurcharges)
	{
		return $arrSurcharges;
	}
	
	
	
	
	
	
	
	public function getSurcharges()
	{
		if (!is_array($this->arrSurcharges))
		{
			$arrPreTax = $arrPostTax = $arrTaxes = array();
			$arrProducts = $this->getProducts();
			
			foreach( $arrProducts as $pid => $objProduct )
			{
				$arrTaxIds = array();
				$arrTax = $this->Isotope->calculateTax($objProduct->tax_class, $objProduct->total_price);
				
				if (is_array($arrTax))
				{
					foreach ($arrTax as $k => $tax)
					{
						if (array_key_exists($k, $arrTaxes))
						{
							$arrTaxes[$k]['total_price'] += $tax['total_price'];
							
							if (is_numeric($arrTaxes[$k]['price']) && is_numeric($tax['price']))
							{
								$arrTaxes[$k]['price'] += $tax['price'];
							}
						}
						else
						{
							$arrTaxes[$k] = $tax;
						}
						
						$arrTaxes[$k]['tax_id'] = array_search($k, array_keys($arrTaxes)) + 1;
						$arrTaxIds[] = array_search($k, array_keys($arrTaxes)) + 1;
					}
				}
				
				$this->arrProducts[$pid]->tax_id = implode(',', $arrTaxIds);
			}
			
			$arrSurcharges = array();
			if (isset($GLOBALS['TL_HOOKS']['isoCheckoutSurcharge']) && is_array($GLOBALS['TL_HOOKS']['isoCheckoutSurcharge']))
			{
				foreach ($GLOBALS['TL_HOOKS']['isoCheckoutSurcharge'] as $callback)
				{
					$this->import($callback[0]);
					$arrSurcharges = $this->{$callback[0]}->{$callback[1]}($arrSurcharges);
				}
			}
			
			foreach( $arrSurcharges as $arrSurcharge )
			{
				if ($arrSurcharge['tax_class'] > 0)
				{
					$arrPreTax[] = $arrSurcharge;
				}
				else
				{
					$arrPostTax[] = $arrSurcharge;
				}
			}
			
			foreach( $arrPreTax as $arrSurcharge )
			{
				$arrTax = $this->Isotope->calculateTax($arrSurcharge['tax_class'], $arrSurcharge['total_price'], $arrSurcharge['add_tax']);
				
				foreach ($arrTax as $k => $tax)
				{
					if (array_key_exists($k, $arrTaxes))
					{
						$arrTaxes[$k]['total_price'] += $tax['total_price'];
						
						if (is_numeric($arrTaxes[$k]['price']) && is_numeric($tax['price']))
						{
							$arrTaxes[$k]['price'] += $tax['price'];
						}
					}
					else
					{
						$arrTaxes[$k] = $tax;
					}
					
					$arrTaxes[$k]['tax_id'] = array_search($k, array_keys($arrTaxes)) + 1;
				}
			}
			
			$this->arrSurcharges = array_merge($arrPreTax, $arrTaxes, $arrPostTax);
		}
		
		return $this->arrSurcharges;
	}
	
	
	public function useTaxRate($objRate, $fltPrice)
	{
		$arrAddresses = deserialize($objRate->address);
		
		if (is_array($arrAddresses) && count($arrAddresses))
		{
			foreach( $arrAddresses as $address )
			{
				$arrAddress = $this->{$address . 'Address'};
				
				if (strlen($objRate->country) && $objRate->country != $arrAddress['country'])
					return false;
					
				if (strlen($objRate->subdivision) && $objRate->subdivision != $arrAddress['subdivision'])
					return false;
					
				$arrPostal = deserialize($objRate->postal);
				if (is_array($arrPostal) && count($arrPostal) && strlen($arrPostal[0]))
				{
					if (strlen($arrPostal[1]))
					{
						if ($arrPostal[0] > $arrAddress['postal'] || $arrPostal[1] < $arrAddress['postal'])
							return false;
					}
					else
					{
						if ($arrPostal[0] != $arrAddress['postal'])
							return false;
					}
				}
				
				$arrPrice = deserialize($objRate->amount);
				if (is_array($arrPrice) && count($arrPrice) && strlen($arrPrice[0]))
				{
					if (strlen($arrPrice[1]))
					{
						if ($arrPrice[0] > $fltPrice || $arrPrice[1] < $fltPrice)
							return false;
					}
					else
					{
						if ($arrPrice[0] != $fltPrice)
							return false;
					}
				}
			}
		}
			
		return true;
	}
	
	
	/**
	 * Calculate total price for products.
	 * 
	 * @access protected
	 * @param array $arrProductData
	 * @return float
	 */
	protected function calculateTotal($arrProducts)
	{
		if (!is_array($arrProducts) || !count($arrProducts))
			return 0;
			
		$fltTotal = 0;
		
		foreach($arrProducts as $objProduct)
		{
			$fltTotal += ((float)$objProduct->price * (int)$objProduct->quantity_requested);
		}
			
		$taxPriceAdjustment = 0; // $this->getTax($floatSubTotalPrice, $arrTaxRules, 'MULTIPLY');
		
		return (float)$fltTotal + (float)$taxPriceAdjustment;
	}
	
	
	/**
	 * Check if a product has any options associated with it.
	 * 
	 * @todo use cache data
	 * @access public
	 * @param int $intProductId
	 * @return bool
	 */
	public function hasOptions($intProductId)
	{
		$objProductOptions = $this->Database->prepare("SELECT product_options FROM tl_cart_items WHERE pid=? AND product_id=?")
							   ->limit(1)
							   ->execute($this->id, $intProductId);
		
		$arrOptions = deserialize($objProductOptions->product_options);
		
		if(is_array($arrOptions) && strlen(implode('',$arrOptions)))
		{	
			return true;
		}
		
		return false;
	}
}

