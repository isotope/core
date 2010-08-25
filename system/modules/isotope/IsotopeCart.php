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
 
 
class IsotopeCart extends IsotopeProductCollection
{
	
	/**
	 * Cookie hash value
	 * @var string
	 */
	protected $strHash = '';
	
	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_cart';
	
	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable = 'tl_iso_cart_items';
		
	/**
	 * Name of the temporary cart cookie
	 * @var string
	 */
	protected $strCookie = 'ISOTOPE_TEMP_CART';
	
	/**
	 * Cache cart data
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
	 * Return cart data. All data is cached for speed improvement.
	 * 
	 * @access public
	 * @param string $strKey
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'billing_address':
			case 'billingAddress':
				if ($this->arrCache['billingAddress_id'] > 0)
				{
					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($this->arrCache['billingAddress_id']);
					
					if ($objAddress->numRows)
						return $objAddress->fetchAssoc();
				}
				elseif ($this->arrCache['billingAddress_id'] === 0 && is_array($this->arrCache['billingAddress_data']))
				{
					return $this->arrCache['billingAddress_data'];
				}
				
				$this->import('Isotope');
							
				if (FE_USER_LOGGED_IN)
				{	
					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE pid=? AND isDefaultBilling='1'")->limit(1)->execute($this->User->id);

					if ($objAddress->numRows)
						return $objAddress->fetchAssoc();

					// Return the default user data, but ID should be 0 to know that it is a custom/new address
					// Trying to guess subdivision by country and state
					return array_intersect_key(array_merge($this->User->getData(), array('id'=>0, 'street_1'=>$this->User->street, 'subdivision'=>strtoupper($this->User->country . '-' . $this->User->state))), array_flip($this->Isotope->Config->billing_fields));
				}
				
				return array('postal'=>$this->Isotope->Config->postal, 'subdivision'=>$this->Isotope->Config->subdivision, 'country' => $this->Isotope->Config->country);
				
			case 'shipping_address':
			case 'shippingAddress':
				if ($this->arrCache['shippingAddress_id'] == -1)
				{							
					return array_merge($this->billingAddress, array('id' => -1));
				}
					
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

				if (FE_USER_LOGGED_IN)
				{
					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE pid=? AND isDefaultShipping='1'")->limit(1)->execute($this->User->id);
					
					if ($objAddress->numRows)
						return $objAddress->fetchAssoc();
				}
				
				return array_merge((is_array($this->billingAddress) ? $this->billingAddress : array()), array('id' => -1));
				
			default:
				return parent::__get($strKey);
		}
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
	 */
	public function initializeCart($intConfig, $intStore)
	{
		$time = time();
		$this->strHash = $this->Input->cookie($this->strCookie);
		
		//  Check to see if the user is logged in.
		if (!FE_USER_LOGGED_IN)
		{	
			if (!strlen($this->strHash))	
			{	
				$this->strHash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? $this->Environment->ip : '') . $intConfig . $this->strCookie);
				$this->setCookie($this->strCookie, $this->strHash, $time+$GLOBALS['TL_CONFIG']['iso_cartTimeout'],  $GLOBALS['TL_CONFIG']['websitePath']);
			}

			$objCart = $this->Database->execute("SELECT * FROM tl_iso_cart WHERE session='{$this->strHash}' AND store_id=$intStore");
		}
		else
		{
			$objCart = $this->Database->execute("SELECT * FROM tl_iso_cart WHERE pid={$this->User->id} AND store_id=$intStore");
		}
				
		// Create new cart
		if ($objCart->numRows)
		{
			$this->setFromRow($objCart, $this->strTable, 'id');
			$this->Database->query("UPDATE tl_iso_cart SET tstamp=$time WHERE id={$this->id}");
		}
		else
		{
			$this->setData(array
			(
				'pid'			=> ($this->User->id ? $this->User->id : 0),
				'session'		=> ($this->User->id ? '' : $this->strHash),
				'tstamp'		=> time(),
				'store_id'		=> $intStore,
			));
			
			if (!$this->findBy('id', $this->save(true)))
			{
				throw new Exception('Unable to create shopping cart');
			}
		}
		
		// Temporary cart available, move to this cart. Must be after creating a new cart!
 		if (FE_USER_LOGGED_IN && strlen($this->strHash))
 		{
			$objCart = new IsotopeCart();
			if ($objCart->findBy('session', $this->strHash))
			{
				$this->transferFromCollection($objCart, false);
				$objCart->delete();
			}
			
			// Delete cookie
			$this->setCookie($this->strCookie, '', (time() - 3600), $GLOBALS['TL_CONFIG']['websitePath']);
 		}
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
		$this->import('Isotope');
		
		if ($this->Isotope->Cart->hasShipping && $this->Isotope->Cart->Shipping->price > 0)
		{
			$arrSurcharges[] = array
			(
				'label'			=> ($GLOBALS['TL_LANG']['MSC']['shippingLabel'] . ' (' . $this->Isotope->Cart->Shipping->label . ')'),
				'price'			=> '&nbsp;',
				'total_price'	=> $this->Isotope->Cart->Shipping->price,
				'tax_class'		=> $this->Isotope->Cart->Shipping->tax_class,
				'before_tax'	=> ($this->Isotope->Cart->Shipping->tax_class ? true : false),
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
		$this->import('Isotope');
		
		if ($this->Isotope->Cart->hasPayment && $this->Isotope->Cart->Payment->price > 0)
		{
			$arrSurcharges[] = array
			(
				'label'			=> ($GLOBALS['TL_LANG']['MSC']['paymentLabel'] . ' (' . $this->Isotope->Cart->Payment->label . ')'),
				'price'			=> '&nbsp;',
				'total_price'	=> $this->Isotope->Cart->Payment->price,
				'tax_class'		=> $this->Isotope->Cart->Payment->tax_class,
				'before_tax'	=> ($this->Isotope->Cart->Payment->tax_class ? true : false),
			);
		}
		
		return $arrSurcharges;
	}
	
		
	public function getSurcharges()
	{
		$this->import('Isotope');
		
		$arrPreTax = $arrPostTax = $arrTaxes = array();
		
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
			if ($arrSurcharge['before_tax'])
			{
				$arrPreTax[] = $arrSurcharge;
			}
			else
			{
				$arrPostTax[] = $arrSurcharge;
			}
		}

		$arrProducts = $this->getProducts();
		foreach( $arrProducts as $pid => $objProduct )
		{
			$fltPrice = $objProduct->total_price;
			foreach( $arrPreTax as $tax )
			{
				if (isset($tax['products'][$objProduct->cart_id]))
				{
					$fltPrice += $tax['products'][$objProduct->cart_id];
				}
			}
			
			$arrTaxIds = array();
			$arrTax = $this->Isotope->calculateTax($objProduct->tax_class, $fltPrice);
			
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
		
		
		foreach( $arrPreTax as $arrSurcharge )
		{
			if (!$arrSurcharge['tax_class'])
				continue;
				
			$arrTax = $this->Isotope->calculateTax($arrSurcharge['tax_class'], $arrSurcharge['total_price'], $arrSurcharge['before_tax']);
			
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
}


