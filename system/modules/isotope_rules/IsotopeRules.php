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
			$arrProcedures = array("type='product'", "enabled='1'");
						
			// Member restrictions
			if (FE_USER_LOGGED_IN)
			{
				$arrProcedures[] = "(memberRestrictions='none'
									OR (memberRestrictions='members' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='members' AND object_id={$this->User->id})>0)
									" . (count($this->User->groups) ? " OR (memberRestrictions='groups' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='groups' AND object_id IN (" . implode(',', $this->User->groups) . "))>0)" : '') . ")";
			}
			else
			{
				$arrProcedures[] = "memberRestrictions='none'";
			}
			
			// Product restrictions
			$arrProcedures[] = "(productRestrictions='none'
								OR (productRestrictions='producttypes' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='producttypes' AND object_id={$objSource->type})>0)
								OR (productRestrictions='products' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='products' AND object_id=" . ($objSource->pid ? $objSource->pid : $objSource->id) . ")>0)
								OR (productRestrictions='pages' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='pages' AND object_id IN (SELECT page_id FROM tl_iso_product_categories WHERE pid=" . ($objSource->pid ? $objSource->pid : $objSource->id) . "))))";
			
			
			// Fetch and process rules
			$objRules = $this->Database->execute("SELECT * FROM tl_iso_rules r WHERE " . implode(' AND ', $arrProcedures) . " ORDER BY sorting");
			
			while( $objRules->next() )
			{
				if (strpos($objRules->discount, '%') !== false)
				{
					$fltDiscount = 100 + rtrim($objRules->discount, '%');
					$fltPrice = $fltPrice / 100 * $fltDiscount;
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
		$arrProducts = $this->Isotope->Cart->getProducts();
		
		if (!count($arrProducts))
			return $arrSurcharges;
	
		$arrProcedures = array("type='cart'", "enabled='1'", "enableCode=''");
								
		// Member restrictions
		if (FE_USER_LOGGED_IN)
		{
			$arrProcedures[] = "(memberRestrictions='none'
								OR (memberRestrictions='members' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='members' AND object_id={$this->User->id})>0)
								" . (count($this->User->groups) ? " OR (memberRestrictions='groups' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='groups' AND object_id IN (" . implode(',', $this->User->groups) . "))>0)" : '') . ")";
		}
		else
		{
			$arrProcedures[] = "memberRestrictions='none'";
		}
		
		// Product restrictions
		$arrIds = array();
		$arrTypes = array();
		foreach( $arrProducts as $objProduct )
		{
			$arrIds[] = $objProduct->pid ? $objProduct->pid : $objProduct->id;
			$arrTypes[] = $objProduct->type;
		}
		
		$arrProcedures[] = "(productRestrictions='none'
							OR (productRestrictions='producttypes' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='producttypes' AND object_id IN (" . implode(',', $arrTypes) . "))>0)
							OR (productRestrictions='products' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='products' AND object_id IN (" . implode(',', $arrIds) . "))>0)
							OR (productRestrictions='pages' AND (SELECT COUNT(*) FROM tl_iso_rule_restrictions WHERE pid=r.id AND type='pages' AND object_id IN (SELECT page_id FROM tl_iso_product_categories WHERE pid IN (" . implode(',', $arrIds) . ")))))";
		
		
		// Fetch and process rules
		$objRules = $this->Database->execute("SELECT * FROM tl_iso_rules r WHERE " . implode(' AND ', $arrProcedures) . " ORDER BY sorting");
		
		while( $objRules->next() )
		{
			$arrSurcharges[] = $this->calculateProductSurcharge($objRules->row(), $arrProducts);
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
					$arrSurcharges[] = $this->calculateProductSurcharge($arrRule, $arrProducts);
				}
			}
			
			if (count($arrDropped))
			{
				//!@todo show dropped coupons
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
				$arrCoupons = is_array(deserialize($this->Isotope->Cart->coupons)) ? deserialize($this->Isotope->Cart->coupons) : array();
				
				if (in_array($strCoupon, $arrCoupons))
				{
					$_SESSION['COUPON_FAILED'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponDuplicate'], $strCoupon);
				}
				else
				{
					$arrCoupons[] = $arrRule['code'];
					
					$this->Database->query("UPDATE tl_iso_cart SET coupons='" . serialize($arrCoupons) . "' WHERE id={$this->Isotope->Cart->id}");
					
					$_SESSION['COUPON_SUCCESS'][$objModule->id] = sprintf($GLOBALS['TL_LANG']['MSC']['couponApplied'], $arrRule['code']);
				}
			}
			
			$this->redirect(preg_replace('@[?&]coupon(_[0-9]+)?=[^&]*@', '', $this->Environment->request));
		}
		
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
	 * Find coupon matching a code
	 */
	protected function findCoupon($strCode, $arrProducts)
	{
		$arrRule = false;
		$objRules = $this->Database->prepare("SELECT * FROM tl_iso_rules WHERE enableCode='1' AND code=? AND enabled='1'")->execute($strCode);
		
		while( $objRules->next() )
		{
			// Member restrictions
			if (($objRules->memberRestrictsion != 'none' && !FE_USER_LOGGED_IN)
				|| ($objRules->memberRestrictions == 'members' && !$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$objRules->id} AND type='members' AND object_id={$this->User->id}")->numRows)
				|| ($objRules->memberRestrictions == 'groups' && count($this->User->groups) && !$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$objRules->id} AND type='groups' AND object_id IN (" . implode(',', $this->User->groups) . ")")->numRows))
			{
				continue;
			}

			// Product restrictions
			if ($objRules->productRestrictions == 'products')
			{
				$arrIds = array();
				foreach( $arrProducts as $objProduct )
				{
					$arrIds[] = ($objProduct->pid ? $objProduct->pid : $objProduct->id);
				}
				
				if (!$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$objRules->id} AND type='products' AND object_id IN (" . implode(',', $arrIds) . ")")->numRows)
					continue;
			}
			elseif ($objRules->productRestrictions == 'producttypes')
			{
				$arrIds = array();
				foreach( $arrProducts as $objProduct )
				{
					$arrIds[] = $objProduct->type;
				}
				
				if (!$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$objRules->id} AND type='producttypes' AND object_id IN (" . implode(',', $arrIds) . ")")->numRows)
					continue;
			}
			elseif ($objRules->productRestrictions == 'pages')
			{
				$arrIds = array();
				foreach( $arrProducts as $objProduct )
				{
					$arrIds[] = ($objProduct->pid ? $objProduct->pid : $objProduct->id);
				}
				
				if (!$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$objRules->id} AND type='pages' AND object_id IN (SELECT page_id FROM tl_iso_product_categories WHERE pid IN (" . implode(',', $arrIds) . "))")->numRows)
					continue;
			}
			
			$arrRule = $objRules->row();
			break;
		}
		
		return $arrRule;
	}
	
	
	/**
	 * Calculate the total of all products to which apply a rule to
	 */
	protected function calculateProductSurcharge($arrRule, $arrProducts)
	{
		$blnDiscount = false;
		if (strpos($arrRule['discount'], '%') !== false)
		{
			$blnDiscount = true;
			$fltDiscount = rtrim($arrRule['discount'], '%');
		}
		
		$arrSurcharge = array
		(
			'label'			=> $arrRule['title'],
			'price'			=> ($blnDiscount ? $fltDiscount.'%' : ''),
			'total_price'	=> 0,
			'tax_class'		=> 0,
			'before_tax'	=> true,
			'products'		=> array(),
		);
		
		foreach( $arrProducts as $objProduct )
		{
			// Product restrictions
			if ($arrRule['productRestrictions'] == 'products')
			{
				if (!$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$arrRule['id']} AND type='products' AND object_id=" . ($objProduct->pid ? $objProduct->pid : $objProduct->id))->numRows)
					continue;
			}
			elseif ($arrRule['productRestrictions'] == 'producttypes')
			{
				if (!$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$arrRule['id']} AND type='producttypes' AND object_id=" . $objProduct->type)->numRows)
					continue;
			}
			elseif ($arrRule['productRestrictions'] == 'pages')
			{
				if (!$this->Database->execute("SELECT * FROM tl_iso_rule_restrictions WHERE pid={$arrRule['id']} AND type='pages' AND object_id IN (SELECT page_id FROM tl_iso_product_categories WHERE pid=" . ($objProduct->pid ? $objProduct->pid : $objProduct->id) . ")")->numRows)
					continue;
			}
			
			$fltPrice = $blnDiscount ? ($objProduct->total_price / 100 * $fltDiscount) : $arrRule['discount'];
			$fltPrice = $fltPrice > 0 ? (floor($fltPrice * 100) / 100) : (ceil($fltPrice * 100) / 100);
			
			$arrSurcharge['total_price'] += $fltPrice;
			$arrSurcharge['products'][$objProduct->cart_id] = $fltPrice;
		}
		
		return $arrSurcharge;
	}
}

