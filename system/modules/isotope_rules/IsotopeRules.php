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

class IsotopeRules extends Controller
{

	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;

	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}


	/**
	 * Prevent direct instantiation (Singleton)
	 */
	protected function __construct()
	{
		parent::__construct();

		$this->import('Database');
		$this->import('FrontendUser', 'User');
		$this->import('Isotope');
	}


	/**
	 * Instantiate a database driver object and return it (Factory)
	 *
	 * @return object
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new IsotopeRules();
		}

		return self::$objInstance;
	}


	/**
	 * Calculate the price for a product, applying rules and coupons
	 */
	public function calculatePrice($fltPrice, $objSource, $strField, $intTaxClass)
	{
		if ($objSource instanceof IsotopeProduct && ($strField == 'price' || $strField == 'low_price'))
		{
			$objRules = $this->findRules(array("type='product'"), array(), array($objSource), ($strField == 'low_price' ? true : false));

			while( $objRules->next() )
			{
				if (strpos($objRules->discount, '%') !== false)
				{
					$fltDiscount = 100 + rtrim($objRules->discount, '%');
					$fltDiscount = round($fltPrice - ($fltPrice / 100 * $fltDiscount), 10);
					$fltDiscount = $fltDiscount > 0 ? (floor($fltDiscount * 100) / 100) : (ceil($fltDiscount * 100) / 100);

					$fltPrice = $fltPrice - $fltDiscount;
				}
				else
				{
					$fltPrice = $fltPrice + $objRules->discount;
				}
			}
		}

		return $fltPrice;
	}


	/**
	 * Add cart rules to surcharges
	 */
	public function getSurcharges($arrSurcharges)
	{
		$objRules = $this->findRules(array("type='cart'", "enableCode=''"));

		while( $objRules->next() )
		{
			$arrSurcharge = $this->calculateProductSurcharge($objRules->row());

			if (is_array($arrSurcharge))
				$arrSurcharges[] = $arrSurcharge;
		}

		$arrCoupons = deserialize($this->Isotope->Cart->coupons);
		if (is_array($arrCoupons) && count($arrCoupons))
		{
			$arrDropped = array();

			foreach( $arrCoupons as $code )
			{
				$arrRule = $this->findCoupon($code, $arrProducts);

				if ($arrRule === false)
				{
					$arrDropped[] = $code;
				}
				else
				{
					//cart rules should total all eligible products for the cart discount and apply the discount to that amount rather than individual products.
					$arrSurcharge = $this->calculateProductSurcharge($arrRule);

					if (is_array($arrSurcharge))
						$arrSurcharges[] = $arrSurcharge;
				}
			}

			if (count($arrDropped))
			{
				// @todo show dropped coupons
				$arrCoupons = array_diff($arrCoupons, $arrDropped);
				$this->Database->query("UPDATE tl_iso_cart SET coupons='" . serialize($arrCoupons) . "' WHERE id={$this->Isotope->Cart->id}");
			}
		}

		return $arrSurcharges;
	}


	/**
	 * Returns a rule form if needed
	 * @access public
	 * @param object $objModule
	 * @return string
	 */
	public function getCouponForm($objModule)
	{
		$arrCoupons = is_array(deserialize($this->Isotope->Cart->coupons)) ? deserialize($this->Isotope->Cart->coupons) : array();
		$strCoupon = $this->Input->get('coupon_'.$objModule->id);

		if ($strCoupon == '')
			$strCoupon = $this->Input->get('coupon');

		if ($strCoupon != '')
		{
			$arrRule = $this->findCoupon($strCoupon, $this->Isotope->Cart->getProducts());

			if ($arrRule === false)
			{
				$_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponInvalid'], $strCoupon);
			}
			else
			{
				if (in_array($strCoupon, $arrCoupons))
				{
					$_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponDuplicate'], $strCoupon);
				}
				else
				{
					$arrCoupons[] = $arrRule['code'];

					$this->Isotope->Cart->coupons = serialize($arrCoupons);
					$this->Isotope->Cart->save();

					$_SESSION['COUPON_SUCCESS'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponApplied'], $arrRule['code']);
				}
			}

			$this->redirect(preg_replace('@[?&]coupon(_[0-9]+)?=[^&]*@', '', $this->Environment->request));
		}


		$objRules = $this->findRules(array("type='cart'", "enableCode='1'"));

		if (!$objRules->numRows || !count(array_diff($objRules->fetchEach('code'), $arrCoupons)))
			return '';


		//build template
		$objTemplate = new FrontendTemplate('iso_coupons');

		$objTemplate->id = $objModule->id;
		$objTemplate->action = $this->Environment->request;
		$objTemplate->headline = $GLOBALS['TL_LANG']['MSC']['couponHeadline'];
		$objTemplate->inputLabel = $GLOBALS['TL_LANG']['MSC']['couponLabel'];
		$objTemplate->sLabel = $GLOBALS['TL_LANG']['MSC']['couponApply'];

		if ($_SESSION['COUPON_FAILED'][$objModule->id] != '')
		{
			$objTemplate->message = $_SESSION['COUPON_FAILED'][$objModule->id];
			$objTemplate->mclass = 'failed';
			unset($_SESSION['COUPON_FAILED']);
		}
		elseif ($_SESSION['COUPON_SUCCESS'][$objModule->id] != '')
		{
			$objTemplate->message = $_SESSION['COUPON_SUCCESS'][$objModule->id];
			$objTemplate->mclass = 'success';
			unset($_SESSION['COUPON_SUCCESS']);
		}

		return $objTemplate->parse();
	}


	/**
	 * Callback for checkout Hook. Transfer active rules to usage table.
	 */
	public function writeRuleUsages($objOrder, $objCart)
	{
		$objRules = $this->findRules(array("(type='product' OR (type='cart' AND enableCode=''))"));
		$arrRules = $objRules->fetchEach('id');

		$arrCoupons = deserialize($objCart->coupons);
		if (is_array($arrCoupons) && count($arrCoupons))
		{
			$arrDropped = array();

			foreach( $arrCoupons as $code )
			{
				$arrRule = $this->findCoupon($code, $objCart->getProducts());

				if ($arrRule === false)
				{
					$arrDropped[] = $code;
				}
				else
				{
					$arrRules[] = $arrRule['id'];
				}
			}

			if (count($arrDropped))
			{
				// @todo show dropped coupons
				return false;
			}
		}

		if (count($arrRules))
		{
			$time = time();

			$this->Database->query("INSERT INTO tl_iso_rule_usage (pid,tstamp,order_id,config_id,member_id) VALUES (" . implode(", $time, {$objOrder->id}, ".(int)$this->Isotope->Config->id.", {$objOrder->pid}), (", $arrRules) . ", $time, {$objOrder->id}, ".(int)$this->Isotope->Config->id.", {$objOrder->pid})");

			$this->Database->query("UPDATE tl_iso_rules SET archive=1 WHERE id IN (" . implode(',', $arrRules) . ")");
		}

		return true;
	}

	/**
	 * Callback for checkout step "review". Remove rule usages if an order failed.
	 */
	public function cleanRuleUsages(&$objModule)
	{
		$this->Database->query("DELETE FROM tl_iso_rule_usage WHERE pid=(SELECT id FROM tl_iso_orders WHERE cart_id={$this->Isotope->Cart->id})");

		return '';
	}


	/**
	 * Fetch rules
	 */
	protected function findRules($arrProcedures, $arrValues=array(), $arrProducts=null, $blnIncludeVariants=false)
	{
		if (!is_array($arrProducts))
		{
			$arrProducts = $this->Isotope->Cart->getProducts();
		}

		// Only enabled and not deleted/archived rules
		$arrProcedures[] = "enabled='1'";
		$arrProcedures[] = "archive<2";


		// Date & Time restrictions
		$arrProcedures[] = "(startDate='' OR FROM_UNIXTIME(startDate,GET_FORMAT(DATE,'INTERNAL')) <= FROM_UNIXTIME(UNIX_TIMESTAMP(),GET_FORMAT(DATE,'INTERNAL')))";
		$arrProcedures[] = "(endDate='' OR FROM_UNIXTIME(endDate,GET_FORMAT(DATE,'INTERNAL')) >= FROM_UNIXTIME(UNIX_TIMESTAMP(),GET_FORMAT(DATE,'INTERNAL')))";
		$arrProcedures[] = "(startTime='' OR FROM_UNIXTIME(startTime,GET_FORMAT(TIME,'INTERNAL')) <= FROM_UNIXTIME(UNIX_TIMESTAMP(),GET_FORMAT(TIME,'INTERNAL')))";
		$arrProcedures[] = "(endTime='' OR FROM_UNIXTIME(endTime,GET_FORMAT(TIME,'INTERNAL')) >= FROM_UNIXTIME(UNIX_TIMESTAMP(),GET_FORMAT(TIME,'INTERNAL')))";


		// Limits
		$arrProcedures[] = "(limitPerConfig=0 OR limitPerConfig>(SELECT COUNT(*) FROM tl_iso_rule_usage WHERE pid=r.id AND config_id=".(int)$this->Isotope->Config->id." AND order_id NOT IN (SELECT id FROM tl_iso_orders WHERE cart_id=".$this->Isotope->Cart->id.")))";

		if (FE_USER_LOGGED_IN && TL_MODE=='FE')
		{
			$arrProcedures[] = "(limitPerMember=0 OR limitPerMember>(SELECT COUNT(*) FROM tl_iso_rule_usage WHERE pid=r.id AND member_id=".(int)$this->User->id." AND order_id NOT IN (SELECT id FROM tl_iso_orders WHERE cart_id=".$this->Isotope->Cart->id.")))";
		}


		// Store config restrictions
		$arrProcedures[] = "(configRestrictions='' OR (configRestrictions='1' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='configs' AND object_id=".(int)$this->Isotope->Config->id.")>0))";


		// Member restrictions
		if (FE_USER_LOGGED_IN && TL_MODE=='FE')
		{
			$arrProcedures[] = "(memberRestrictions='none'
								OR (memberRestrictions='members' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='members' AND object_id={$this->User->id})>0)
								" . (count($this->User->groups) ? " OR (memberRestrictions='groups' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='groups' AND object_id IN (" . implode(',', $this->User->groups) . "))>0)" : '') . ")";
		}
		else
		{
			$arrProcedures[] = "(memberRestrictions='none' OR memberRestrictions='guests')";
		}


		// Product restrictions
		$arrIds = array();
		$arrTypes = array();
		foreach( $arrProducts as $objProduct )
		{
			$arrIds[] = $objProduct->id;
			$arrTypes[] = $objProduct->type;
			
			if ($objProduct->pid > 0)
			{
				$arrIds[] = $objProduct->pid;
			}
			
			if ($blnIncludeVariants)
			{
				$arrIds = array_merge($arrIds, $objProduct->variant_ids);
			}
		}
		
		$arrRestrictions = array("productRestrictions='none'");
		
		if (count($arrTypes))
		{
			$arrRestrictions[] = "(productRestrictions='producttypes' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='producttypes' AND object_id IN (" . implode(',', $arrTypes) . "))>0)";
		}
		
		if (count($arrIds))
		{
			$arrIds = array_unique($arrIds);
			
			$arrRestrictions[] = "(productRestrictions='products' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='products' AND object_id IN (" . implode(',', $arrIds) . "))>0)";
			$arrRestrictions[] = "(productRestrictions='pages' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='pages' AND object_id IN (SELECT page_id FROM tl_iso_product_categories WHERE pid IN (" . implode(',', $arrIds) . "))))";
		}

		$arrProcedures[] = '(' . implode(' OR ', $arrRestrictions) . ')';


		// Fetch and process rules
		return $this->Database->prepare("SELECT * FROM tl_iso_rules r WHERE " . implode(' AND ', $arrProcedures) . " ORDER BY sorting")->execute($arrValues);
	}


	/**
	 * Find coupon matching a code
	 */
	protected function findCoupon($strCode, $arrProducts)
	{
		$objRules = $this->findRules(array("type='cart'", "enableCode='1'", "code=?"), array($strCode), $arrProducts);
		return $objRules->numRows ? $objRules->row() : false;
	}


	/**
	 * Calculate the total of all products to which apply a rule to
	 */
	protected function calculateProductSurcharge($arrRule)
	{
		$arrProducts = $this->Isotope->Cart->getProducts();

		$blnMatch = false;
		$blnDiscount = false;
		if (strpos($arrRule['discount'], '%') !== false)
		{
			$blnDiscount = true;
			$fltDiscount = rtrim($arrRule['discount'], '%');
		}

		$arrSurcharge = array
		(
			'label'			=> ($arrRule['label'] ? $arrRule['label'] : $arrRule['name']),
			'price'			=> ($blnDiscount ? $fltDiscount.'%' : ''),
			'total_price'	=> 0,
			'tax_class'		=> 0,
			'before_tax'	=> true,
			'products'		=> array(),
		);

		// Product or producttype restrictions
		if ($arrRule['productRestrictions'] != '' && $arrRule['productRestrictions'] != 'none')
		{
			$arrLimit = $this->Database->execute("SELECT object_id FROM tl_iso_rule_restrictions WHERE pid={$arrRule['id']} AND type='{$arrRule['productRestrictions']}'")->fetchEach('object_id');

			if ($arrRule['productRestrictions'] == 'pages' && count($arrLimit))
			{
				$arrLimit = $this->Database->execute("SELECT pid FROM tl_iso_product_categories WHERE page_id IN (" . implode(',', $arrLimit) . ")")->fetchEach('pid');
			}

			if ($arrRule['quantityMode'] == 'cart_products' || $arrRule['quantityMode'] == 'cart_items')
			{
				$intTotal = 0;
				foreach( $arrProducts as $objProduct )
				{
					if ((($arrRule['productRestrictions'] == 'products' || $arrRule['productRestrictions'] == 'pages')
						&& (in_array($objProduct->id, $arrLimit) || ($objProduct->pid > 0 && in_array($objProduct->pid, $arrLimit))))
					|| ($arrRule['productRestrictions'] == 'producttypes' && in_array($objProduct->type, $arrLimit)))
					{
						$intTotal += $arrRule['quantityMode']=='cart_items' ? $objProduct->quantity_requested : 1;
					}
				}
			}
		}
		else
		{
			switch( $arrRule['quantityMode'] )
			{
				case 'cart_products':
					$intTotal = $this->Isotope->Cart->products;
					break;

				case 'cart_items':
					$intTotal = $this->Isotope->Cart->items;
					break;
			}
		}

		foreach( $arrProducts as $objProduct )
		{
			// Product restrictions
			if ((($arrRule['productRestrictions'] == 'products' || $arrRule['productRestrictions'] == 'pages')
				&& (!in_array($objProduct->id, $arrLimit) && ($objProduct->pid == 0 || !in_array($objProduct->pid, $arrLimit))))
			|| ($arrRule['productRestrictions'] == 'producttypes' && !in_array($objProduct->type, $arrLimit)))
			{
				continue;
			}

			// Cart item quantity
			if ($arrRule['quantityMode'] != 'cart_products' && $arrRule['quantityMode'] != 'cart_items')
			{
				$intTotal = $objProduct->quantity_requested;
			}

			if (($arrRule['minItemQuantity'] > 0 && $arrRule['minItemQuantity'] > $intTotal) || ($arrRule['maxItemQuantity'] > 0 && $arrRule['maxItemQuantity'] < $intTotal))
			{
				continue;
			}

			// Apply To
			switch( $arrRule['applyTo'] )
			{
				case 'product':
					$fltPrice = $blnDiscount ? ($objProduct->total_price / 100 * $fltDiscount) : $arrRule['discount'];
					$fltPrice = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
					$arrSurcharge['total_price'] += $fltPrice;

					$fltPrice = $blnDiscount ? ($objProduct->tax_free_total_price / 100 * $fltDiscount) : $arrRule['discount'];
					$fltPrice = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
					$arrSurcharge['products'][$objProduct->cart_id] = $fltPrice;
					break;

				case 'item':
					$fltPrice = ($blnDiscount ? ($objProduct->price / 100 * $fltDiscount) : $arrRule['discount']) * $objProduct->quantity_requested;
					$fltPrice = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
					$arrSurcharge['total_price'] += $fltPrice;

					$fltPrice = ($blnDiscount ? ($objProduct->tax_free_price / 100 * $fltDiscount) : $arrRule['discount']) * $objProduct->quantity_requested;
					$fltPrice = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
					$arrSurcharge['products'][$objProduct->cart_id] = $fltPrice;
					break;

				case 'cart':
					$blnMatch = true;
					$arrSurcharge['total_price'] += $objProduct->total_price;
					break;
			}
		}

		if ($arrRule['applyTo'] == 'cart' && $blnMatch)
		{
			$fltPrice = $blnDiscount ? ($arrSurcharge['total_price'] / 100 * $fltDiscount) : $arrRule['discount'];
			$arrSurcharge['total_price'] = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
			$arrSurcharge['before_tax'] = false;
		}

		return $arrSurcharge['total_price'] == 0 ? false : $arrSurcharge;
	}
}

