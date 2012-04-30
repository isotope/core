<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class IsotopeCart
 * 
 * Provide methods to handle Isotope cart.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
	 * Import a front end user
	 */
	public function __construct()
	{
		parent::__construct();

		if (FE_USER_LOGGED_IN === true)
		{
			$this->import('FrontendUser', 'User');
		}
	}


	/**
	 * Return the cart data
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'billing_address':
			case 'billingAddress':
				if ($this->arrSettings['billingAddress_id'] > 0)
				{
					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($this->arrSettings['billingAddress_id']);

					if ($objAddress->numRows)
					{
						return $objAddress->fetchAssoc();
					}
				}
				elseif ($this->arrSettings['billingAddress_id'] === 0 && is_array($this->arrSettings['billingAddress_data']))
				{
					return $this->arrSettings['billingAddress_data'];
				}

				$this->import('Isotope');

				if (FE_USER_LOGGED_IN === true)
				{
					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE pid={$this->User->id} AND store_id={$this->Isotope->Config->store_id} AND isDefaultBilling='1'")->limit(1)->execute();

					if ($objAddress->numRows)
					{
						return $objAddress->fetchAssoc();
					}

					// Return the default user data, but ID should be 0 to know that it is a custom/new address
					// Trying to guess subdivision by country and state
					return array_intersect_key(array_merge($this->User->getData(), array('id'=>0, 'street_1'=>$this->User->street, 'subdivision'=>strtoupper($this->User->country . '-' . $this->User->state))), array_flip($this->Isotope->Config->billing_fields_raw));
				}

				return array('id'=>-1, 'postal'=>$this->Isotope->Config->postal, 'subdivision'=>$this->Isotope->Config->subdivision, 'country' => $this->Isotope->Config->country);

			case 'shipping_address':
			case 'shippingAddress':
				if ($this->arrSettings['shippingAddress_id'] == -1)
				{
					return array_merge($this->billingAddress, array('id' => -1));
				}

				if ($this->arrSettings['shippingAddress_id'] > 0)
				{
					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE id=?")->limit(1)->execute($this->arrSettings['shippingAddress_id']);

					if ($objAddress->numRows)
					{
						return $objAddress->fetchAssoc();
					}
				}

				if ($this->arrSettings['shippingAddress_id'] == 0 && count($this->arrSettings['shippingAddress_data']))
				{
					return $this->arrSettings['shippingAddress_data'];
				}

				if (FE_USER_LOGGED_IN === true)
				{
					$this->import('Isotope');

					$objAddress = $this->Database->prepare("SELECT * FROM tl_iso_addresses WHERE pid={$this->User->id} AND store_id={$this->Isotope->Config->store_id} AND isDefaultShipping='1'")->limit(1)->execute();

					if ($objAddress->numRows)
					{
						return $objAddress->fetchAssoc();
					}
				}

				return array_merge((is_array($this->billingAddress) ? $this->billingAddress : array()), array('id' => -1));

			default:
				return parent::__get($strKey);
		}
	}


	/**
	 * Set the cart data
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'billingAddress':
			case 'billing_address':
				if (is_array($varValue))
				{
					$this->arrSettings['billingAddress_id'] = 0;
					$this->arrSettings['billingAddress_data'] = $varValue;
				}
				else
				{
					$this->arrSettings['billingAddress_id'] = $varValue;
				}
				break;

			case 'shippingAddress':
			case 'shipping_address':
				if (is_array($varValue))
				{
					$this->arrSettings['shippingAddress_id'] = 0;
					$this->arrSettings['shippingAddress_data'] = $varValue;
				}
				else
				{
					$this->arrSettings['shippingAddress_id'] = $varValue;
				}
				break;

			default:
				parent::__set($strKey, $varValue);
		}

		$this->blnModified = true;
		$this->arrCache = array();
	}


	/**
	 * Load the current cart
	 * @param integer
	 * @param integer
	 */
	public function initializeCart($intConfig, $intStore)
	{
		$time = time();
		$this->strHash = $this->Input->cookie($this->strCookie);

		//  Check to see if the user is logged in.
		if (FE_USER_LOGGED_IN !== true)
		{
			if (!strlen($this->strHash))
			{
				$this->strHash = sha1(session_id() . (!$GLOBALS['TL_CONFIG']['disableIpCheck'] ? $this->Environment->ip : '') . $intConfig . $this->strCookie);
				$this->setCookie($this->strCookie, $this->strHash, $time+$GLOBALS['TL_CONFIG']['iso_cartTimeout'], $GLOBALS['TL_CONFIG']['websitePath']);
			}

			$objCart = $this->Database->execute("SELECT * FROM tl_iso_cart WHERE session='{$this->strHash}' AND store_id=" . (int) $intStore);
		}
		else
		{
			$objCart = $this->Database->execute("SELECT * FROM tl_iso_cart WHERE pid=" . (int) $this->User->id . " AND store_id=" . (int) $intStore);
		}

		// Create new cart
		if ($objCart->numRows)
		{
			$this->setFromRow($objCart, $this->strTable, 'id');
			$this->tstamp = $time;
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
		}

		// Temporary cart available, move to this cart. Must be after creating a new cart!
 		if (FE_USER_LOGGED_IN === true && $this->strHash != '')
 		{
			$objCart = new IsotopeCart();

			if ($objCart->findBy('session', $this->strHash))
			{
				$arrIds = $this->transferFromCollection($objCart, false);

				if (!empty($arrIds))
				{
					$_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['cartMerged'];
				}

				$objCart->delete();
			}

			// Delete cookie
			$this->setCookie($this->strCookie, '', ($time - 3600), $GLOBALS['TL_CONFIG']['websitePath']);
			$this->reload();
 		}
	}


	/**
	 * Return current surcharges as array
	 * @return array
	 */
	public function getSurcharges()
	{
		if (isset($this->arrCache['surcharges']))
		{
			return $this->arrCache['surcharges'];
		}

		$this->import('Isotope');
		$arrPreTax = array();
		$arrPostTax = array();
		$arrTaxes = array();
		$arrSurcharges = array();

		if (isset($GLOBALS['ISO_HOOKS']['checkoutSurcharge']) && is_array($GLOBALS['ISO_HOOKS']['checkoutSurcharge']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['checkoutSurcharge'] as $callback)
			{
				if ($callback[0] == 'IsotopeCart')
				{
					$arrSurcharges = $this->{$callback[1]}($arrSurcharges);
				}
				else
				{
					$this->import($callback[0]);
					$arrSurcharges = $this->{$callback[0]}->{$callback[1]}($arrSurcharges);
				}
			}
		}

		foreach ($arrSurcharges as $arrSurcharge)
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

		foreach ($arrProducts as $objProduct)
		{
			$fltPrice = $objProduct->total_price;

			foreach ($arrPreTax as $tax)
			{
				if (isset($tax['products'][$objProduct->cart_id]))
				{
					$fltPrice += $tax['products'][$objProduct->cart_id];
				}
			}

			$arrTaxIds = array();
			$arrTax = $this->Isotope->calculateTax($objProduct->tax_class, $fltPrice, true, null, false);

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

					$taxId = array_search($k, array_keys($arrTaxes)) + 1;
					$arrTaxes[$k]['tax_id'] = $taxId;
					$arrTaxIds[] = $taxId;
				}
			}

			$strTaxId = implode(',', $arrTaxIds);

			if ($objProduct->tax_id != $strTaxId)
			{
				$this->updateProduct($objProduct, array('tax_id'=>$strTaxId));
			}
		}

		foreach ($arrPreTax as $i => $arrSurcharge)
		{
			if (!$arrSurcharge['tax_class'])
			{
				continue;
			}

			$arrTaxIds = array();
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

					$taxId = array_search($k, array_keys($arrTaxes)) + 1;
					$arrTaxes[$k]['tax_id'] = $taxId;
					$arrTaxIds[] = $taxId;
				}
			}

			$arrPreTax[$i]['tax_id'] = implode(',', $arrTaxIds);
		}

		$this->arrCache['surcharges'] = array_merge($arrPreTax, $arrTaxes, $arrPostTax);
		return $this->arrCache['surcharges'];
	}
}


