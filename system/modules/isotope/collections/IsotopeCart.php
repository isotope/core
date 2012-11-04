<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope;


/**
 * Class IsotopeCart
 *
 * Provide methods to handle Isotope cart.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
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

				return array('id'=>-1, 'country' => $this->Isotope->Config->billing_country);

			case 'shipping_address':
				if ($this->arrSettings['shippingAddress_id'] == -1)
				{
					return array_merge($this->billing_address, array('id' => -1));
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

				$arrBilling = $this->billing_address;

				if ($arrBilling['id'] != -1)
				{
					return $arrBilling;
				}

				return array('id'=>-1, 'country' => $this->Isotope->Config->shipping_country);

			case 'billingAddress':
				$objAddress = new IsotopeAddressModel();
				$objAddress->setData($this->billing_address);
				return $objAddress;

			case 'shippingAddress':
				$objAddress = new IsotopeAddressModel();
				$objAddress->setData($this->shipping_address);
				return $objAddress;

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

				$this->blnModified = true;
				$this->arrCache = array();
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

				$this->blnModified = true;
				$this->arrCache = array();
				break;

			default:
				parent::__set($strKey, $varValue);
		}
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
			$this->setRow($objCart->row());
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
 			$blnMerge = $this->products ? true : false;
			$objCart = new IsotopeCart();

			if ($objCart->findBy('session', $this->strHash))
			{
				$arrIds = $this->transferFromCollection($objCart, false);

				if ($blnMerge && !empty($arrIds))
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

		$arrPreTax = array();
		$arrPostTax = array();
		$arrTaxes = array();
		$arrSurcharges = array();

		// !HOOK: get checkout surcharges
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


