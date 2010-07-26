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
		$this->import('FrontendUser','User');
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
	 * Returns a coupon form if needed
	 * @access public
	 * @param object $objModule
	 * @return string
	 */
	public function getForm($objModule)
	{		
		$arrProducts = $this->Isotope->Cart->getProducts();	
		
		$arrData = $this->getEligibleRules($arrProducts,'coupons');	//returns a collection of rules and their respective products that are associated.
		
		if(!count($arrData))
			return '';
					
		if($this->Input->post('FORM_SUBMIT')=='iso_cart_coupons')
		{			
			if($this->Input->post('code'))
			{
				$arrAppliedRules = $this->applyRules($arrData,$this->Input->post('code'));
				
				$this->saveRules($arrAppliedRules);
			}
		}
					
		//build template
		$objTemplate = new FrontendTemplate('iso_coupons');
		
		$objTemplate->action = $this->Environment->request;
		$objTemplate->formId = 'iso_cart_coupons';
		$objTemplate->formSubmit = 'iso_cart_coupons';
		$objTemplate->headline = $GLOBALS['TL_LANG']['ISO']['couponsHeadline'];
		$objTemplate->message = NULL;
		$objTemplate->inputLabel = $GLOBALS['TL_LANG']['ISO']['couponsInputLabel'];
		$objTemplate->sLabel = $GLOBALS['TL_LANG']['ISO']['couponsSubmitLabel'];
		$objTemplate->error = ($blnResult ? $GLOBALS['TL_LANG']['ERR']['invalidCoupon'] : NULL);
	
		return $objTemplate->parse();
	}
	
	
	/** 
	 * get any rule pricing
	 */
	public function getRules($arrObjects, $objSource)
	{
		$arrReturn = array();
		$arrData = array();
		
		if($objSource instanceof IsotopeProductCollection)	//@TODO Make space for additional custom class rule eligibility hooking
			$arrObjects[] = $objSource;
					
		$arrData = $this->getEligibleRules($arrObjects, 'rules');
		
		if(!count($arrData))
			return array();
			
		$arrAppliedRuleData = $this->applyRules($arrData);
		
		foreach($arrObject
	}
	
	/** 
	 * calculatePrice hook callback for items
	 * allows us to reflect the application of a rule upon a product price
	 * @access public
	 * @param float $fltPrice
	 * @param object $objSource
	 * @param string $strField
	 * @param integer $intTaxClass
	 * @return float $fltReturn
	 */
	/*public function calculateItemPrice($fltPrice, $objSource, $strField, $intTaxClass)
	{		
		if($objSource instanceof IsotopeProduct) //var set to indicate these do not reside within the cart and therefore aren't being double checked.)
		{
			switch($strField)
			{
				case 'price':
					$arrProducts[] = $objSource;	//Get the current product
					$arrData = $this->getEligibleRules($arrProducts, 'rules');
					return $this->applyRules($arrData);
					break;
				default:
					return $fltPrice;
			}
		}
	}*/
	
	/*public function addToCart($objProduct, $objModule=null)
	{
		$arrProducts[] = $objProduct;	//Get the current product
		$arrData = $this->getEligibleRules($arrProducts, 'rules');
		
		$arrAppliedRules = $this->applyRules($arrData);
		
		$this->saveRules($arrAppliedRules);	//session save by default	
	}*/
	
	/** 
	 * check eligibility for products
	 * @access protected
	 * @param array $arrProducts
	 * @param string $strQueryMode
	 * @return array $arrReturn
	 */ 
	protected function getEligibleRules($arrObjects, $strQueryMode = '')
	{							
		if(!count($arrProducts))
			return '';
		
		$intToday = time();
		
		if(FE_USER_LOGGED_IN)
		{
			$arrCustomer['members'] 		= $this->User->id;
			$arrCustomer['countries'] 		= $this->User->country;
			$arrCustomer['subdivisions'] 	= $this->User->state;
			$arrCustomer['groups']			= deserialize($this->User->groups, true);
		}
		else
		{
			$arrCustomer['members'] = 0;
			$arrCustomer['groups'] = 0;
			$arrCustomer['countries'] = '';
			$arrCustomer['subdivisions'] = '';
		}
	
		switch($strQueryMode)
		{
			case 'coupons':
				$strCouponsClause = " AND enableCode='1'";
				break;
			case 'rules':
				$strCouponsClause = " AND enableCode=''";
			default:
				break;
		}
									
		//determine eligibility for the current shopper. //restrictions either null or not matching
		$objCoupons = $this->Database->executeUncached("SELECT c.*, (SELECT COUNT(u.id) AS couponUses FROM tl_iso_rule_usage u WHERE u.pid=c.id) AS uses FROM tl_iso_rule c WHERE c.enabled='1'".$strCouponsClause);
		
		if(!$objCoupons->numRows)
			return '';
						
		$arrCouponIds = array();
		$arrMemberUsesByCoupon = array();
		
		$arrCouponIds = $objCoupons->fetchEach('id');
		
		$arrCoupons = $objCoupons->fetchAllAssoc();
		
		$strCouponIds = implode(',', $arrCouponIds);
		
		//gather all usage data for the coupons we have returned.. if a coupon is for non-members, then this query by default is checking usage in terms of global use  		//of the coupon rather that per user as we haven't a way to verify usage for a non-member.  
		if(FE_USER_LOGGED_IN)
		{
			$objMemberUses = $this->Database->executeUncached("SELECT *, COUNT(id) AS customerUses FROM tl_iso_rule_usage WHERE pid IN($strCouponIds) AND member_id={$this->User->id}");
			
			if($objMemberUses->numRows)		
			{
				while($objMemberUses->next());
				{
					$arrMemberUsesByCoupon[$objMemberUses->pid] = $objMemberUses->row();
				}
			}
		}
					
		foreach($arrObjects as $i => $object)
		{			
			$strParentClass = get_parent_class($object);
			
			switch($strParentClass)
			{
			
				case 'IsotopeProduct':
					$arrObject['pages'] = $object->pages;
					$arrObject['productTypes'] = $object->type;
					$arrObject['products'] = $object->id;
					$object->container_id = $object->cart_id; //necessary to check the usage table by product collection class id (for example, cart id)
					$object->coupons = array();	//@TODO: get coupons for this item from the container or else reinstate the coupons field for items.
					break;
				case 'IsotopeProductCollection':
					$object->container_id=$object->id; //necessary to check the usage table by product collection class id (for example, cart id)
					$arrObject = array(); //this is only necessary for product-level rules.
					break;
				default:
					//@TODO: HOOK THIS for other unanticipated classes that don't fit the two we provide for?
					break(2);
			}
			
			$arrCustomerMatrix = array_merge($arrCustomer, $arrObject);		
					
			foreach($arrCoupons as $row)
			{				
				//Check existing usage
				if($row['uses'])
				{
					$arrUses = deserialize($row['numUses'], true);
			
					if(count($arrUses) && $arrUses['value']>0)
					{
						switch($arrUses['unit'])
						{
							case 'customer':
								if(FE_USER_LOGGED_IN)
								{																		
									//if the number of customer uses exceeds this coupon in total, or the current product has already had the coupon applied to it...					
									if($arrUses['value'] <= $arrMemberUsesByCoupon[$row['id']]['customerUses'] || $object->container_id==$arrMemberUsesByCoupon[$row['id']]['object_id'])
									{	
										break(2);	//don't allow
									}
								}							
								break;
							case 'store':
								if($arrUses['value'] <= $row['uses'])
								{
									break(2);	//don't allow
								}							
								break;	
							default:
								break;				
						}
					}
				}
				
				if($row['dateRestrictions'])
				{
					if($row['startDate']>time() || $row['endDate']<time())
						break;
				}
				
				//check time, will be verified again later.	//fix
				
				if($row['timeRestrictions'])
				{
					if($row['startTime']>time() || $row['endTime']<time())
						break;				
				}
				
				//exclusion of other coupons, all or certain ones
				switch($row['couponRestrictions'])
				{
					case 'all':
						if(count($object->coupons))
							break(2);
					case 'coupons':
						$arrExcludedCoupons = deserialize($row['coupons'], true);	//get specific coupons for exclusion check
						if(count($arrCoupons) && array_intersect($object->coupons, $arrExcludedCoupons))
							break(2);
					default:
						break;
				}
				
								
				//Usage didn't stop us, let's further check for member restrictions
				switch($row['memberRestrictions'])
				{
					case 'groups':
					case 'members':
						if($row[$row['memberRestrictions']])
							$arrRestrictions[$row['memberRestrictions']] = deserialize($row[$row['memberRestrictions']]);
						break;
					default:
						break;			
				}
				
				switch($row['type'])
				{
					case 'cart_item':
						if($row['minItemQuantity'] && $row['minItemQuantity'] > $object->quantity_requested)
							break(2);
													
						switch($row['productRestrictions'])
						{
							case 'productTypes':
							case 'pages':
							case 'products':
								if($row[$row['productRestrictions']])
									$arrRestrictions[$row['productRestrictions']] = deserialize($row[$row['productRestrictions']]);
								break;				
							default:
								break;			
						}
						
					case 'cart':
						if($row['minSubTotal']>0 && $object->subTotal > $row['minSubTotal'])
							break(2);
						
						if($row['minCartQuantity']>0 && $object->totalQuantity > $row['minCartQuantity'])
							break(2);
						break;
					default:
						//@TODO: Hook for additional types of rule-eligible objects
						break;
				}
											
				if(count($arrRestrictions))
				{														
						$blnLoopBreak = false;									
						foreach($arrRestrictions as $k=>$v) //check each field in the coupon row
						{											
							if(is_array($arrCustomerMatrix[$k]) && is_array($v))	//mismatch! break to next row.
							{										
								$cRow[$k] = array_map('strval', $arrCustomerMatrix[$k]);
								$v = array_map('strval', $v);
																
								if(!count(array_intersect($arrCustomerMatrix[$k], $v)))																				
									$blnLoopBreak = true;
							}
							elseif(!in_array($arrCustomerMatrix[$k], $v))
							{
								$blnLoopBreak = true;
							}									
							
							if($blnLoopBreak)
								break(2);
						}
						
						$arrReturn[$row['id']] = array
						(
							'coupon'		=> $row,
							'object'		=> $object	//assumes only product right now
						);
					
				}
			} 	//end coupons loop
		}	//end products loop
		
		if(!count($arrReturn))
			return array();	
	
		//return an array of eligible coupons to each item in the cart.
		return $arrReturn;
	}
	
	
	/** 
	 * Match couponCodes entered against eligible coupons in array
	 * 
	 * @access protected
	 * @param string $strCodes
	 * @param array $arrData
	 * @return boolean
	 */
	protected function applyRules($arrData,$strCodes='')
	{		
		$arrAppliedRules = array();
		$arrUsedCodes = array();
		$arrCodes = array();
					
		if($strCodes)
			$arrCodes = explode(',', $strCodes);

		$arrUsedCodes = array();

		foreach($arrData as $row)
		{	
			//if we have codes and they don't match, skip this rule, also if they are already applied, skip them.  The rest of the code
			//can safely assume valid coupon usage and simply apply them.
			if(count($arrCodes) && (!in_array($row['coupon']['code'], $arrCodes) || in_array($row['coupon']['code'], $arrUsedCodes)))
			{
						continue;
			}
			elseif(count($arrCodes))
			{
				//add to used codes as it will be used now.
				$arrUsedCodes[] = $row['coupon']['code'];
			}
										
			$blnPercentage = strpos($row['coupon']['discount'], '%');
					
			switch($row['coupon']['type'])
			{ 
				case 'product':					
					if($blnPercentage)
					{	
						$intValue = (float)rtrim($row['coupon']['discount'], '%') / 100;	
						$fltChange = ($row['object']->price * $intValue);
					}
					else
					{
						$fltChange = (float)$row['coupon']['discount'];
					}
				
					$arrCouponData['object_id']		= $row['object']->cart_id;
					$arrCouponData['total_price']	= $row['object']->price - $fltChange;
					
					//simply update the price on the existing product as-is
					$arrUpdate['price'] = $row['product']->price - $fltChange;
					$arrUpdate['coupons'][] = serialize($arrCouponData);
					
					//yes, we need to reflect this coupon in the cart, arrCache will be used to preserve the discount info, when hitting "calculatePrice"
					//$varReturn = $this->Isotope->Cart->updateProduct($row['product'], $arrUpdate);
					break;
				case 'product_collection':
					if($blnPercentage)
					{	
						$fltValue = (float)rtrim($row['coupon']['discount'], '%') / 100;	
						
						$fltChange = $this->Isotope->Cart->subTotal * $fltValue;
					}
					else
					{
						$fltChange = (float)$row['coupon']['discount'];
					}	
														
					break;
			}
				
			$arrRules[$row['object']->id]['rules'][] = array
			(
				'label'			=> $row['coupon']['title'],
				'price'			=> ($blnPercentage ? $row['coupon']['discount'] : $this->Isotope->formatPriceWithCurrency($row['coupon']['discount'])),
				'total_price'	=> $this->Isotope->formatPriceWithCurrency($fltChange,false),
			);										
			
		}  //end $arrData foreach
			
		return $arrRules;
	}
	
	/** 
	 * Save the currently used rules either to the session or else to the usage table in the case of confirmed coupons.
	 * @access private
	 * @param array $arrData
	 * @param string $strContainer
	 */
	private function saveRules($arrData = array(), $strContainer = '')
	{
		if(!count($arrData))
			return;
		
		switch($strContainer)
		{
			case 'table':
				foreach($arrData as $row)
				{
					//update the usage table 
					$arrSet['tstamp'] 		= time();
					$arrSet['pid'] 			= $row['coupon']['id'];
					$arrSet['member_id'] 	= (FE_USER_LOGGED_IN ? $this->User->id : 0);
					$arrSet['object_type']	= 
					$arrSet['object_id'] 	= $row['object']['id'];
					
					$this->Database->prepare("INSERT INTO tl_iso_rule_usage %s")
								   ->set($arrSet)
								   ->execute();
				}
				break;
			default:
				foreach($arrData as $coupon)
				{
					$_SESSION['CHECKOUT_DATA']['coupons'][] = $coupon;
				}
				break;
		}
	}
	
	/** 
	 * Verify that our coupons are still in fact, valid just before payment is completed.
	 * @access public
	 * @param object $objModule
	 */
	public function verifyCoupons()
	{
		$arrProducts = $this->Isotope->Cart->getProducts();
	
		$arrData = $this->getEligibleRules($arrProducts);	//returns a collection of rules and their respective products that are associated.
		
		if(count($arrData))
			$this->saveRules($arrData, 'table');
	}
	
	
	/** 
	 * Hook-callback for coupons @TODO - determine if needed
	 * 
	 * @access public
	 * @param array
	 * @return array
	 */
	public function getRulesSurcharges($arrSurcharges)
	{
		$objCoupons = $this->Database->query("SELECT coupons FROM tl_iso_cart WHERE id={$this->id}");
		
		if(!$objCoupons->numRows)
			return $arrSurcharges;
		
		$arrCoupons = deserialize($objCoupons->coupons, true);
		
		foreach($arrCoupons as $coupon)
		{
			$arrSurcharges[] = array
			(
				'label'			=> $coupon['title'],
				'price'			=> $coupon['price'],
				'total_price'	=> $coupon['total_price'],
				'tax_class'		=> 0,
				'add_tax'		=> false,
			);
		}
						
		return $arrSurcharges;
	}
}