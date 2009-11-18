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
	 * Cart type. Can be "2" for gift registry.
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
	protected $arrProducts = array();
	
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
					$this->arrCache[$strKey] = $this->calculateTotal($this->Isotope->getProductData($this->getProducts(), array($this->Isotope->Store->priceField), $this->Isotope->Store->priceField));
					break;
					
				case 'taxTotal':
					// FIXME: currently rounds to 0.05 (swiss francs)
					$this->arrCache[$strKey] = (float)$this->calculateTax($this->Isotope->getProductData($this->getProducts(), array($this->Isotope->Store->priceField, 'tax_class'), $this->Isotope->Store->priceField));
					break;
					
				case 'taxTotalWithShipping':
					// FIXME: currently rounds to 0.05 (swiss francs)
					return (float)$this->calculateTax($this->Isotope->getProductData($this->getProducts(), array($this->Isotope->Store->priceField, 'tax_class'), $this->Isotope->Store->priceField)) + $this->shippingTotal;
					break;
				
				case 'shippingTotal':
					return $this->hasShipping ? (float)$this->Shipping->price : 0.00;
					break;
					
				case 'grandTotal':
					//return ($this->subTotal + $this->taxTotalWithShipping);
					return ($this->subTotal + $this->taxTotal + $this->shippingTotal);
					break;
					
				case 'hasShipping':
					return is_object($this->Shipping) ? true : false;
					break;
					
				case 'hasPayment':
					return is_object($this->Payment) ? true : false;
					break;
			}
		}
		
		return $this->arrCache[$strKey];
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
		
		//  Check to see if the user is logged in.  If not, cart data should be found in session data. - THIS IS CURRENTLY STORED IN THE DB - the cart is identified by session ID at the moment.
		
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
				
				$objExistingMemberCartData = $this->Database->prepare("SELECT * FROM tl_cart_items WHERE product_id=? AND pid=?")
											->limit(1)
											->execute($objCartData->product_id, $this->id);
									
				//Nothing existing in member's cart, just add items to it.		 
				if($objExistingMemberCartData->numRows < 1)
				{
					$this->Database->prepare("UPDATE tl_cart_items SET pid=? WHERE id=?")->execute($this->id, $objCartData->id);					
				
				}else{
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
				
				
				$this->import('Isotope');
								
				$fltNewProductPrice = $this->Isotope->applyRules($objCartData->price, $objCartData->product_id);
				
				$this->Database->prepare("UPDATE tl_cart_items SET price=? WHERE id=?")->execute($fltNewProductPrice, $objCartData->id);
			}
			
			// Delete cookie
			$this->setCookie($this->strCookie, '', (time() - 3600), $GLOBALS['TL_CONFIG']['websitePath']);
			
			// Delete cart
			$this->Database->prepare("DELETE FROM tl_cart WHERE session=? AND pid=0")->execute($this->strHash);
 		}
 		
 		// Load shipping object
 		if ($_SESSION['FORM_DATA']['shipping']['module'])
 		{
 			$objShipping = $this->Database->prepare("SELECT * FROM tl_shipping_modules WHERE id=?")->limit(1)->execute($_SESSION['FORM_DATA']['shipping']['module']);
 			if ($objShipping->numRows)
 			{
 				$strClass = $GLOBALS['ISO_SHIP'][$objShipping->type];
 				$this->Shipping = new $strClass($objShipping->row());
 			}
 		}
 		
 		
 		// Load payment object
 		if ($_SESSION['FORM_DATA']['payment']['module'])
 		{
 			$objPayment = $this->Database->prepare("SELECT * FROM tl_payment_modules WHERE id=?")->limit(1)->execute($_SESSION['FORM_DATA']['payment']['module']);
 			if ($objPayment->numRows)
 			{
 				$strClass = $GLOBALS['ISO_PAY'][$objPayment->type];
 				$this->Payment = new $strClass($objPayment->row());
 			}else{
 				$this->Payment = null;
 			}
 		}
	}
	
	
	/**
	 * Auto-Save to database
	 *//*
	public function __destruct()
	{
		// Update timestamp
		$this->tstamp = time();
		$this->last_visit = time();
		
		$this->save();
	}*/
	
	
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

		$resResult = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE " . $this->strRefField . "=? AND cart_type_id=?")
									->execute($this->varRefId, $this->intType);

		if ($resResult->numRows == 1)
		{
			$this->arrData = $resResult->fetchAssoc();
			$this->blnRecordExists = true;

			return true;
		}

		return false;
	}
	

	/**
	 * Fetch products from database.
	 * 
	 * @access public
	 * @return array
	 */
	public function getProducts()
	{
		return $this->Database->prepare("SELECT * FROM tl_cart_items WHERE tl_cart_items.pid=?")->execute($this->id)->fetchAllAssoc();
	}
	
	
	public function getProductsAsHtml()
	{
		$this->import('Isotope');
		
		$arrProducts = $this->Isotope->getProductData($this->getProducts(), array('alias','name'), 'name');
		
		if (!count($arrProducts))
			return '';
		
		$strBuffer  = "<table class='products'>\n";
		$strBuffer .= "<tr><td class='name'>" . $GLOBALS['TL_LANG']['MSC']['iso_order_items'] ."</td><td class='quantity'>" . $GLOBALS['TL_LANG']['MSC']['iso_quantity_header'] ."</td><td class='price'>". $GLOBALS['TL_LANG']['MSC']['iso_price_header']  ."</td><td class='subtotal'>". $GLOBALS['TL_LANG']['MSC']['iso_subtotal_header'] ."</td></tr>\n";
		
		foreach( $arrProducts as $product )
		{
			$strBuffer .= '<tr>';
			$strBuffer .= '<td>' . $product['name'] . '</td>';
			$strBuffer .= '<td>' . $product['quantity_requested'] . ' x </td>';
			$strBuffer .= '<td>' . $this->Isotope->formatPriceWithCurrency($product[$this->Isotope->Store->priceField]) . '</td>';
			$strBuffer .= '<td>' . $this->Isotope->formatPriceWithCurrency($product['quantity_requested'] * $product[$this->Isotope->Store->priceField]) . '</td>';
			$strBuffer .= "</tr>\n";
		}
		
		return $strBuffer . '</table>';
	}
	
	
	public function getProductsAsString()
	{
		$this->import('Isotope');
		
		$arrProducts = $this->Isotope->getProductData($this->getProducts(), array('alias','name'), 'name');
		
		if (!count($arrProducts))
			return 'Keine Produkte';
		
//		$strBuffer = "Name    Anzahl</td><td>Preis</td><td>Betrag</td></tr>\n";
		
		foreach( $arrProducts as $product )
		{
			$strBuffer .= $product['name'] . ': ';
			$strBuffer .= $product['quantity_requested'] . ' x ';
			$strBuffer .= $this->Isotope->formatPriceWithCurrency($product[$this->Isotope->Store->priceField]) . ' = ';
			$strBuffer .= $this->Isotope->formatPriceWithCurrency($product['quantity_requested'] * $product[$this->Isotope->Store->priceField]);
		}
		
		return $strBuffer;
	}
	

	/**
	 * Calculate total price for products.
	 * 
	 * @access protected
	 * @param array $arrProductData
	 * @return float
	 */
	protected function calculateTotal($arrProductData)
	{
		$fltTotal = 0;
		
		if(is_array($arrProductData) && sizeof($arrProductData))
		{
			
			foreach($arrProductData as $data)
			{
				$fltTotal += ((float)$data[$this->Isotope->Store->priceField] * (int)$data['quantity_requested']);
			}
			
			$taxPriceAdjustment = 0; // $this->getTax($floatSubTotalPrice, $arrTaxRules, 'MULTIPLY');
		}
		else
		{
			return 0.00;
		}
		
		return (float)$fltTotal + (float)$taxPriceAdjustment;
	}

	
	/**
	 * Calculate Tax per product, considering product tax class.
	 * 
	 * @access protected
	 * @param array $arrProductData
	 * @return array
	 */
	protected function calculateTax($arrProductData)
	{
		$this->import('FrontendUser','User');
		
		if($arrProductData)
		{	
			foreach($arrProductData as $row)
			{
				if(strlen($row['tax_class']))
					$arrTaxClasses[] = $row['tax_class'];	
			}
			
			if(is_array($arrTaxClassRecords))
			{
				//Get the tax rates for the given class.
				$arrTaxClassRecords = array_unique($arrTaxClasses);
			}else{
				return array();
			}
			
					
			if(sizeof($arrTaxClassRecords))
			{		
				$strTaxRates = join(',', $arrTaxClassRecords);
			}
			
			if(strlen(trim($strTaxRates)) < 1)
			{
				return array();
			}
			
			
			$objTaxRates = $this->Database->prepare("SELECT r.pid, r.country_id, r.region_id, r.postcode, r.rate, (SELECT name FROM tl_tax_class c WHERE c.id=r.pid) AS class_name FROM tl_tax_rate r WHERE r.pid IN(" . $strTaxRates . ")")
										  ->execute();
			
			if($objTaxRates->numRows < 1)
			{
				return 0.00;
			}
			
			$arrTaxRates = $objTaxRates->fetchAllAssoc();
			
			foreach($arrTaxRates as $rate)
			{
				//eventually this will also contain the formula or calc rule for the given tax rate.
				$arrRates[$rate['pid']] = array
				(
					'rate'			=> $rate['rate'],
					'country_id'	=> $rate['country_id'],
					'region_id'		=> $rate['region_id'],
					'postal_code'	=> $rate['postcode'],
					'class_name'	=> $rate['class_name']	//we need to output this to template for customers.
				);
			}
			
			
			
			$arrBillingAddress = $this->Isotope->getAddress('billing'); //Tax calculated based on billing address.
			$arrShippingAddress = $this->Isotope->getAddress('shipping');
			
			$arrAddresses[] = $arrBillingAddress;
			$arrAddresses[] = $arrShippingAddress;
			
			//the calculation logic for tax rates will need to be something we can set in the backend eventually.  This is specific to Kolbo right now
			//as tax class 3 = luxury tax.
			foreach($arrProductData as $product)
			{
				$blnAlreadyCalculatedTax = false;
				$blnCalculate = false; 
				
						
				foreach($arrAddresses as $address)
				{		
					if(is_null($address['country']) || strlen($address['country']) < 1)
					{
						$address['country'] = 'us';	//Default
					}
					
					if($product['tax_class']!=0)
					{
					
						//only check what we need to.  There may be a better logic gate to express this but I haven't figured out what it is yet. ;)
						if(strlen($arrRates[$product['tax_class']]['postalcode']))
						{
							if($address['postal']==$arrRates[$product['tax_class']]['postal_code'] && $address['state']==$arrRates[$product['tax_class']]['region_id'] && $address['country']==$arrRates[$product['tax_class']]['country_id'])
							{
								
								$blnCalculate = true;
							}
						}
						elseif(strlen($arrRates[$product['tax_class']]['region_id']) && strlen($arrRates[$product['tax_class']]['country_id']))
						{
							
							
							if($address['state']==$arrRates[$product['tax_class']]['region_id'] && $address['country']==$arrRates[$product['tax_class']]['country_id'])
							{
									
								$blnCalculate = true;
							}
						}
						/*elseif(strlen($rate['country_id']))
						{
							if($address['country']==$rate['country_id'])
							{
								$blnCalculate = true;
							}	
						}*/		
						
				
					
						if($blnCalculate && !$blnAlreadyCalculatedTax)
						{
							//This needs to be database-driven.  We know what these tax values are right now and later it must not assume anything obviously.
							switch($product['tax_class'])
							{
								case '1':
										//if(strlen($rate['region_id']) > 0 && $this->User->state==$rate['region_id'])
										$fltSalesTax += (((float)$product[$this->Isotope->Store->priceField] * $arrRates[$product['tax_class']]['rate'] / 100) * $product['quantity_requested']);
										
										//$arrTaxInfo['code'] = $
									break;
									
								/*case '2':	//Luxury tax.  5% of the difference over $175.00  this trumps standard sales tax.
									if((float)$product[$this->Isotope->Store->priceField] >= 175)
									{
										$fltTaxableAmount = (float)$product[$this->Isotope->Store->priceField] - 175;
										$fltSalesTax += (($fltTaxableAmount * $arrRates[$product['tax_class']]['rate'] / 100) * $product['quantity_requested']);
									}else{
										//fallback if the price is below to standard sales tax.
										$fltTaxableAmount = (float)$product[$this->Isotope->Store->priceField] - 175;
										$fltSalesTax += (($fltTaxableAmount * $arrRates[$product['tax_class']]['rate'] / 100) * $product['quantity_requested']);
									}
															
									break;
									
								case '3':	//because tax class 2 is exempt in Kolbo.*/
								default:
									break;			
							}
							
							$blnAlreadyCalculatedTax = true;
						}
					} //end if($product['tax_class'])
				} //end foreach($arrAddresses)
			}
			
			return $fltSalesTax;
			/*$this->arrTaxInfo[] = array
			(
				'class'			=> 'Sales Tax',
				'total'			=> number_format($fltSalesTax, 2)
			);
			
			return $arrTaxInfo;*/
		}
	}
	
	/**
	 * Check if a product is already in cart.
	 * 
	 * @todo use cache data
	 * @access public
	 * @param int $intProductId
	 * @return bool
	 */
	public function containsProduct($intProductId)
	{
		return ($this->Database->prepare("SELECT * FROM tl_cart_items WHERE pid=? AND product_id=?")
							   ->limit(1)
							   ->execute($this->id, $intProductId)
							   ->numRows ? true : false);
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
		
		//echo $intProductId . '<br /><br />';
		
		//var_dump($arrOptions);
		
		if(is_array($arrOptions) && strlen(implode('',$arrOptions)))
		{	
			return true;
		}
		
		return false;
					
	}
	
	
	
	
	
	
	
	
	
	
	
	
	public function addProduct($intId, $arrProduct=null)
	{
		$objProduct = $this->Database->prepare("SELECT * FROM tl_product_data WHERE id=?")->limit(1)->execute($intId);
		
		if (!$objProduct->numRows)
			return false;
			
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")->limit(1)->execute($objProduct->type);
		
		if (!$objType->numRows)
			return false;
			
		$arrAttributeIds = deserialize($objType->attributes);
		
		if (!is_array($arrAttributeIds) || !count($arrAttributeIds))
			return;
			
		$objAttributes = $this->Database->execute("SELECT * FROM tl_product_attributes");
		
		$arrSet = array('pid'=>$this->id, 'tstamp'=>time(), 'product_id'=>$objProduct->id);
		
		if (is_array($arrProduct) && strlen($arrProduct['href_reader']))
		{
			$arrSet['href_reader'] = $arrProduct['href_reader'];
		}
		
		$arrProductData = $objProduct->row();
		while( $objAttributes->next() )
		{
			// Drop disabled attribute data
			if ($objAttributes->disabled || !in_array($objAttributes->id, $arrAttributeIds))
			{
				unset($arrProductData[$objAttributes->field_name]);
				continue;
			}
			
			$varValue = $objAttributes->is_customer_defined ? $this->Input->post($objAttributes->field_name) : $objProduct->{$objAttributes->field_name};
			
			switch( $objAttributes->field_name )
			{					
				case $this->Isotope->Store->priceField:
					$arrSet['price'] = $varValue;
					break;
				
				default:
					$arrProductData[$objAttributes->field_name] = $varValue;
					break;
			}
		}
		
		$arrSet['quantity_requested'] = $this->Input->post('quantity_requested') ? $this->Input->post('quantity_requested') : 1;
				
		$strProductData = serialize($arrProductData);
		
		$arrSet['product_data'] = $strProductData;
		
		if (!$this->Database->prepare("UPDATE tl_cart_items SET tstamp=?, quantity_requested=quantity_requested+" . $arrSet['quantity_requested'] . " WHERE pid=? AND product_id=? AND product_data=?")->execute($arrSet['tstamp'], $this->id, $objProduct->id, $strProductData)->affectedRows)
		{
			$this->Database->prepare("INSERT INTO tl_cart_items %s")->set($arrSet)->execute();
		}
	}
}

