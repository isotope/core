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
 
 
class IsotopeOrder extends Model
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
	protected $strTable = 'tl_iso_orders';
		
	/**
	 * Cache get requests to improve speed. Order data cannot change without reload...
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
	/*public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new IsotopeOrder();
		}

		return self::$objInstance;
	}*/
	
	
	/**
	 * Return order data. All data is cached for speed improvement.
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
					$this->arrCache[$strKey] = $this->Database->prepare("SELECT SUM(quantity_sold) AS items FROM tl_iso_order_items LEFT OUTER JOIN tl_iso_orders ON tl_iso_order_items.pid=tl_iso_orders.id WHERE tl_iso_order_items.pid=?")->execute($this->id)->items;
					break;
					
				case 'products':
					$this->arrCache[$strKey] = $this->Database->prepare("SELECT COUNT(*) AS items FROM tl_iso_order_items LEFT OUTER JOIN tl_iso_orders ON tl_iso_order_items.pid=tl_iso_orders.id WHERE tl_iso_order_items.pid=?")->execute($this->id)->items;
					break;
				case 'order_checkoutData':
					return deserialize($this->checkout_info);
						
				case 'order_subTotal':
					return $this->calculateTotal($this->getItems());
					
				case 'order_taxTotal':
					$intTaxTotal = 0;
					$arrSurcharges = $this->getSurcharges();
					
					foreach( $arrSurcharges as $arrSurcharge )
					{
						if ($arrSurcharge['add'])
							$intTaxTotal += $arrSurcharge['total_price'];
					}
					
					$this->arrCache[$strKey] = $intTaxTotal;
					break;
					
				case 'order_taxTotalWithShipping':
					$this->arrCache[$strKey] =  (float)$this->taxTotal + (float)$this->shippingTotal;
					break;
				
				case 'order_shippingTotal':
					$this->arrCache[$strKey] = (float)$this->shippingTotal;
					break;
					
				case 'order_grandTotal':
					$intTotal = $this->calculateTotal($this->getItems());
					
					$arrSurcharges = deserialize($this->surcharges);
					
					$arrSurcharges = $this->getSurcharges();
					
					foreach( $arrSurcharges as $arrSurcharge )
					{
						
						if ($arrSurcharge['add'])
							$intTotal += round($arrSurcharge['total_price'], 2);
					}
			
					return $intTotal;
					
				case 'order_requiresShipping':
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
					
				case 'order_totalWeight':
					$arrProducts = $this->getProducts();
					
					foreach($arrProducts as $objProduct)
					{						
						$fltShippingWeight += $objProduct->weight * $objProduct->quantity_sold;
					}
										
					return $fltShippingWeight;
					
				case 'order_hasShipping':
					return is_object($this->Shipping) ? true : false;
					
				case 'order_hasPayment':
					return is_object($this->Payment) ? true : false;
					
				case 'order_billingAddress':
					return deserialize($this->billing_address);
					
				case 'order_shippingAddress':
					return deserialize($this->shipping_address);
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
	 * Load current order
	 */
	//!@todo why do we need store_id in tl_iso_orders?
	public function __construct($intId = 0)
	{
		parent::__construct();
		
		$this->import('Isotope');
				
					
		// Create new order
		//if (!$this->blnRecordExists)
		//{						
		if (!$this->findBy('id', $intId))
		{
			throw new Exception('Unable to create order');
		}
		//}
		
	}
	
	
	/**
	 * Find a record by its reference field and return true if it has been found. Include order type id.
	 * @param  int
	 * @return boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		$this->blnRecordExists = false;
		$this->strRefField = $strRefField;
		$this->varRefId = $varRefId;

		$resResult = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE " . $this->strRefField . "=?")->execute($this->varRefId);

		if ($resResult->numRows == 1)
		{
			$this->arrData = $resResult->fetchAssoc();
			$this->blnRecordExists = true;

			return true;
		}

		return false;
	}
	
	
	/**
	 * Also delete order items when dropping this order.
	 */
	public function delete()
	{
		$this->Database->prepare("DELETE FROM tl_iso_order_items WHERE pid=?")->execute($this->id);
		
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
			$objProducts = $this->Database->prepare("SELECT * FROM tl_iso_order_items WHERE pid=?")->execute($this->id);
	
			while( $objProducts->next() )
			{
				// Do not use the TYPOlight function deserialize() cause it handles arrays not objects
				$objProduct = unserialize($objProducts->product_data);
								
				$objProduct->quantity_sold = $objProducts->quantity_sold;
				$objProduct->order_id = $objProducts->id;
	
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
	 * Fetch products from database.
	 * 
	 * @access public
	 * @return array
	 */
	public function getItems($strTemplate='')
	{
		if (!is_array($this->arrProducts))
		{
			$this->arrProducts = array();
			$objProducts = $this->Database->prepare("SELECT * FROM tl_iso_order_items WHERE pid=?")->execute($this->id);
	
			while( $objProducts->next() )
			{
				// Do not use the TYPOlight function deserialize() cause it handles arrays not objects
											
								
				$this->arrProducts[$objProducts->product_id] = $objProducts->row();
			}
		}

		/*if (strlen($strTemplate))
		{
			$objTemplate = new FrontendTemplate($strTemplate);
			$objTemplate->products = $this->arrProducts;
			return $objTemplate->parse();
		}*/

		return $this->arrProducts;
	}
	
	/**
	 * Callback for add_to_order button
	 *
	 * @access	public
	 * @param	object
	 * @return	void
	 */
	//!@todo fetch data of custom fields
	//!@todo $objModule is always defined, rework to use it and make sure the module config field is in palettes
	public function addProduct($objProduct, $objModule=null)
	{
		$arrSet = array
		(
			'pid'					=> $this->id,
			'tstamp'				=> time(),
			'quantity_sold'	=> ((is_object($objModule) && $objModule->iso_use_quantity && intval($this->Input->post('quantity_sold')) > 0) ? intval($this->Input->post('quantity_sold')) : 1),
			'price'					=> $objProduct->price,	//!NOTE: Won't reference the variable unless $ precedes curly brackets!
			'price_override'		=> $objProduct->price_override,
			'href_reader'			=> $objProduct->href_reader,
			'product_id'			=> $objProduct->id,
			'product_data'			=> serialize($objProduct),
		);

		if (!$this->Database->prepare("UPDATE tl_iso_order_items SET tstamp=?, quantity_sold=quantity_sold+" . $arrSet['quantity_sold'] . " WHERE pid=? AND product_id=? AND product_data=?")->execute($arrSet['tstamp'], $this->id, $arrSet['product_id'], $arrSet['product_data'])->affectedRows)
		{
			$this->Database->prepare("INSERT INTO tl_iso_order_items %s")->set($arrSet)->execute();
		}
		
		//!@todo redirect to order if checked in module config
		$this->reload();
	}
	
	public function editProduct($intId, $strAttribute, $varValue)
	{
		$arrSet = array
		(
			'pid'					=> $this->id,
			'tstamp'				=> time(),
			$strAttribute			=> $varValue
		);
		
		$this->Database->prepare("UPDATE tl_iso_order_items %s WHERE id=?")
						->set($arrSet)
						->execute($intId);
	}
	
	public function deleteProduct($intId)
	{
		$this->Database->prepare("DELETE FROM tl_iso_order_items WHERE id=?")->execute($intId);					   
	}
	
	
	public function getAttributeName($strField)
	{
		$objName = $this->Database->prepare("SELECT name FROM tl_iso_attributes WHERE field_name=?")
								  ->limit(1)
								  ->execute($strField);
		
		if(!$objName->numRows)
		{
			return false;
		}
		
		return $objName->name;
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
				'tax_class'		=> $this->Shipping->tax_class,
				'add_tax'		=> ($this->Shipping->tax_class ? true : false),
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
		if ($this->hasPayment && $this->Payment->price > 0)
		{
			$arrSurcharges[] = array
			(
				'label'			=> ($GLOBALS['TL_LANG']['MSC']['paymentLabel'] . ' (' . $this->Payment->label . ')'),
				'price'			=> '&nbsp;',
				'total_price'	=> $this->Payment->price,
				'tax_class'		=> $this->Payment->tax_class,
				'add_tax'		=> ($this->Payment->tax_class ? true : false),
			);
		}
		
		return $arrSurcharges;
	}
	
		
	public function getSurcharges()
	{
		$this->import('Isotope');
		
		$arrPreTax = $arrPostTax = $arrTaxes = array();
		$arrProducts = $this->getProducts();
		$arrItems = $this->getItems();
		
		foreach( $arrProducts as $pid => $product )
		{
			$arrTaxIds = array();
			
			$objProduct = unserialize($product['product_data']);
						
			$arrTax = $this->calculateTax($objProduct->tax_class, ((float)$arrItems[$objProduct->id]['price'] * (integer)$arrItems[$objProduct->id]['quantity_sold']));
			
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
			
			$this->arrProducts[$pid]['tax_id'] = implode(',', $arrTaxIds);
		}
		
		$arrSurcharges = array();
		
		
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
			$arrTax = $this->calculateTax($arrSurcharge['tax_class'], $arrSurcharge['total_price'], $arrSurcharge['add_tax']);
			
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
				}
			}
		}
		
		return array_merge($arrPreTax, $arrTaxes, $arrPostTax);
	}
	
	
	/**
	 * Calculate total price for products.
	 * 
	 * @access protected
	 * @param array $arrProductData
	 * @return float
	 */
	/*protected function calculateTotal($arrProducts)
	{
		if (!is_array($arrProducts) || !count($arrProducts))
			return 0;
			
		$fltTotal = 0;
		
		foreach($arrProducts as $objProduct)
		{
			$fltTotal += ((float)$objProduct->price * (int)$objProduct->quantity_sold);
		}
			
		$taxPriceAdjustment = 0; // $this->getTax($floatSubTotalPrice, $arrTaxRules, 'MULTIPLY');
		
		return (float)$fltTotal + (float)$taxPriceAdjustment;
	}*/
	
	protected function calculateTotal($arrProducts)
	{		
		
		if (!is_array($arrProducts) || !count($arrProducts))
			return 0;
			
		$fltTotal = 0;
		
		foreach($arrProducts as $row)
		{
			$fltTotal += ((float)$row['price'] * (int)$row['quantity_sold']);
		}
			
		$taxPriceAdjustment = 0; // $this->getTax($floatSubTotalPrice, $arrTaxRules, 'MULTIPLY');
		
		return (float)$fltTotal + (float)$taxPriceAdjustment;
	}
	
	
	public function calculateTax($intTaxClass, $fltPrice, $blnAdd=true, $arrAddresses=null)
	{
			
		if (!is_array($arrAddresses))
		{
			$arrAddresses = array('billing'=>$this->order_billingAddress, 'shipping'=>$this->order_shippingAddress);
		}
		
		$objTaxClass = $this->Database->prepare("SELECT * FROM tl_iso_tax_class WHERE id=?")->limit(1)->execute($intTaxClass);
		
		if (!$objTaxClass->numRows)
			return $fltPrice;
			
		$arrTaxes = array();
		$objIncludes = $this->Database->prepare("SELECT * FROM tl_iso_tax_rate WHERE id=?")->limit(1)->execute($objTaxClass->includes);
		
		if ($objIncludes->numRows)
		{
			$arrTaxRate = deserialize($objIncludes->rate);
			
			// final price / (1 + (tax / 100)
			if (strlen($arrTaxRate['unit']))
			{
				$fltTax = $fltPrice - ($fltPrice / (1 + (floatval($arrTaxRate['value']) / 100)));
			}
			// Full amount
			else
			{
				$fltTax = floatval($arrTaxRate['value']);
			}
			
			if (!$this->useTaxRate($objIncludes, $fltPrice, $arrAddresses))
			{
				$fltPrice -= $fltTax;
			}
			else
			{
				$arrTaxes[$objTaxClass->id.'_'.$objIncludes->id] = array
				(
					'label'			=> (strlen($objTaxClass->label) ? $objTaxClass->label : $objIncludes->label),
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $fltTax,
					'add'			=> false,
				);
			}
		}

		if (!$blnAdd)
		{
			return $fltPrice;
		}
		
		$arrRates = deserialize($objTaxClass->rates);
		if (!is_array($arrRates) || !count($arrRates))
			return $arrTaxes;
		
		$objRates = $this->Database->execute("SELECT * FROM tl_iso_tax_rate WHERE id IN (" . implode(',', $arrRates) . ") ORDER BY id=" . implode(" DESC, id=", $arrRates) . " DESC");
		
		while( $objRates->next() )
		{
			if ($this->useTaxRate($objRates, $fltPrice, $arrAddresses))
			{
				$arrTaxRate = deserialize($objRates->rate);
				
				// final price * (1 + (tax / 100)
				if (strlen($arrTaxRate['unit']))
				{
					$fltTax = ($fltPrice * (1 + (floatval($arrTaxRate['value']) / 100))) - $fltPrice;
				}
				// Full amount
				else
				{
					$fltTax = floatval($arrTaxRate['value']);
				}
				
				$arrTaxes[$objRates->id] = array
				(
					'label'			=> $objRates->label,
					'price'			=> $arrTaxRate['value'] . $arrTaxRate['unit'],
					'total_price'	=> $fltTax,
					'add'			=> true,
				);
				
				if ($objRates->stop)
					break;
			}
		}
		
		return $arrTaxes;
	}
	
	public function useTaxRate($objRate, $fltPrice, $arrAddresses)
	{
		$objRate->address = deserialize($objRate->address);
		
		if (is_array($objRate->address) && count($objRate->address))
		{
			foreach( $arrAddresses as $name => $arrAddress )
			{
				if (!in_array($name, $objRate->address))
					continue;
				
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
}

