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
	 * get rules whenever IsotopeProductCollection::getProducts is called.
	 */
	public function getRules($arrObjects = array(), $objSource = NULL)
	{		
		if(!count($arrObjects))
			return $arrObjects;
	
		$arrData = array();
			
		if($objSource instanceof IsotopeProductCollection)
		{
			$arrObjects = array($objSource);
			$arrData = $this->getEligibleRules($arrObjects, 'cart');	//allows us to grab all eligible rules skipping coupons.
		}
		else
		{				
			$arrData = $this->getEligibleRules($arrObjects, 'products');	//allows us to grab all eligible rules skipping coupons.
		}
	
		if(!count($arrData))
			return $arrObjects;
				
		$this->applyRules($arrObjects, $arrData);				
	}
	
	
	/**
	 * append rules to the template object if they exist (cached)
	 * @access public
	 * @param object $objTemplate
	 * @param object $objProduct
	 * @return object $objTemplate
	 */
	public function updatePrice($attribute, $varValue, $strBuffer, $objProduct)
	{
		if($attribute!='price')
			return $strBuffer;
		
		if($objProduct->pid && !count($objProduct->rules))
		{
			//get parent rules
			$arrData = $this->getEligibleRules(array($objProduct), 'products');
		
			$this->applyRules(array($objProduct), $arrData);
		}
				
		if(count($objProduct->rules))
		{
			$varNewValue = $varValue;
			
			foreach($objProduct->rules as $rule)
			{
				$shift = pow(10, 2);
				$varNewValue += (-1*round((floor($rule['total_price'] * $shift) / $shift),2));
							
			}
						
			return 'Was: <strike>'.$strBuffer.'</strike><br />Your Price: '.$this->Isotope->formatPriceWithCurrency($varNewValue,false);
		}
		else
		{
		
			return $strBuffer;
		}
	}
	
	
	/** 
	 * load cached rules.  We'll just skip eligibility and application and instead directly load the rule data and reflect the information for each item.
	 * @access public
	 * @param array $arrObjects
	 * @param array $arrCachedRules
	 * @return array $arrObjects
	 */
	public function loadRules($arrObjects, $strTable)
	{
		foreach($arrObjects as $object)
		{
			$arrIds[] = ($object instanceof IsotopeProduct ? $object->cart_id : $object->id);
		}
		
		$strIds = implode(",", $arrIds);
		
		$arrRules = $this->Database->query("SELECT id, rules FROM $strTable WHERE id IN($strIds)")->fetchAllAssoc();

		foreach($arrRules as $rule)
		{
			$arrRulesById[$rule['id']] = deserialize($rule['rules'], true);
		}

		foreach($arrObjects as $object)
		{										
			$intObjectId = ($object instanceof IsotopeProduct ? $object->cart_id : $object->id);
			
			$object->rules = $arrRulesById[$intObjectId];
		
		}
	}
	
	/** 
	 * Returns a rule form if needed
	 * @access public
	 * @param object $objModule
	 * @return string
	 */
	public function getCouponForm($objModule)
	{		
		$arrObjects = $this->Isotope->Cart->getProducts();	
		
		$arrObjects[] = $this->Isotope->Cart;
		
		$arrData = $this->getEligibleRules($arrObjects, 'coupons', true);	//returns a collection of rules and their respective products that are associated.
		
		if(!count($arrData))
			return '';
					
		if($this->Input->post('FORM_SUBMIT')=='iso_cart_coupons')
		{			
			if($this->Input->post('code'))
			{					
				$arrData = $this->getEligibleRules($arrObjects, 'coupons', true);	//we need to pull this again as we are refiguring everything.
				
				$this->applyRules($arrObjects, $arrData, true, $this->Input->post('code'));
															
				foreach($arrObjects as $object)
				{
					if($object instanceof IsotopeProduct)
					{													
						$this->saveRules($object, 'tl_iso_cart_items');
					}
					elseif($object instanceof IsotopeProductCollection)
					{
						$this->saveRules($object, $object->table);
					}
				}

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
	 * Upon adding to cart, we need to somehow store the rule so it can be cached & recalled.
	 * @access public
	 * @param object $objProduct
	 * @param object $objModule
	 */
	public function addToCollection($objProduct, $arrSet, $intInsertId, $objSource=null)
	{			
		foreach($arrSet as $k=>$v)
		{
			switch($k)
			{
				case 'quantity_requested':
					$objProduct->product_quantity = $v;
					break;
				default;
					$objProduct->$k = $v;
					break;
			}	
		}

		$objProduct->cart_id = $intInsertId;				

		$arrData = $this->getEligibleRules(array($objProduct), 'products', true);	//we need to pull this again as we are refiguring everything.
				
		$arrObjects = $this->applyRules(array($objProduct), $arrData, true);
		
		foreach($arrObjects as $object)
		{
			if($object instanceof IsotopeProduct)
			{														
				$this->saveRules($object, 'tl_iso_cart_items');
			}
			elseif($object instanceof IsotopeProductCollection)
			{
				$this->saveRules($object, $object->table);
			}
		}
		
		return $intQuantity;
	}
	
	
	
	/** 
	 * Upon adding to cart, we need to somehow store the rule so it can be cached & recalled.
	 * @access public
	 * @param object $objProduct
	 * @param object $objModule
	 */
	public function updateProductInCollection($objProduct, $arrSet, $objModule=null)
	{			
		foreach($arrSet as $k=>$v)
		{
			switch($k)
			{
				case 'quantity_requested':
					$objProduct->product_quantity = $v;
					break;
				default;
					$objProduct->$k = $v;
					break;
			}	
		}
		
		$arrData = $this->getEligibleRules(array($objProduct), 'rules', true);	//we need to pull this again as we are refiguring everything.
		
		$arrExistingRules = array();
		
		if(count($objProduct->rules))
			$arrExistingRules = $objProduct->rules;
		
		$objProduct->rules = array_merge($arrExistingRules, $arrData);
			
		$arrProducts = $this->applyRules(array($objProduct), $objProduct->rules, true);				
		
		foreach($arrProducts as $object)
		{
			if($object instanceof IsotopeProduct)
			{													
				$this->saveRules($object, 'tl_iso_cart_items');
			}
			elseif($object instanceof IsotopeProductCollection)
			{
				$this->saveRules($object, $object->table);
			}
		}
			
		return $arrSet;
	}
	

	/** 
	 * See what is already applied, in case we need to exclude certain rules.  May not be necessary
	 * @access protected
	 * @object $object
	 * @array $arrRules
	 * @return array (maybe should just reutrn rule id??
	 */
	protected function findAppliedRules($object, $arrRules)
	{
		$intObjectId = $object->id;
		
		if($object instanceof IsotopeProduct)
			$intObjectId = $object->cart_id;
			
		if(count($arrRules[get_class($object)][$intObjectId]))
			return $arrRules[get_class($object)][$intObjectId];
			
		return array();
	}
	
	
	/** 
	 * Match ruleCodes entered against eligible rules in array
	 * 
	 * @access protected
	 * @param string $strCodes
	 * @param array $arrData
	 * @return boolean
	 */
	//!@todo: include an option for caching rules that are applied to items in the cart
	protected function applyRules($arrObjects, $arrData, $blnCartItem=false, $strCodes='', $objModule=NULL)
	{		
		$arrUsedCodes = array();
		$arrCodes = array();
		$arrAppliedRules = array();
					
		if($strCodes)
			$arrCodes = explode(',', $strCodes);

		$arrUsedCodes = array();

		foreach($arrObjects as $i=>$object)
		{			
			$arrRules = array();
			$arrAppliedRules = array();

			$intObjectId = $object->id;	
			
			if($object instanceof IsotopeProduct && $blnCartItem)
				$intObjectId = $object->cart_id;						
						
			if(!count($arrData[get_class($object)][$intObjectId]))
			{
				$arrFinalObjects[] = $object;
				continue;
			}
							
			foreach($arrData[get_class($object)][$intObjectId] as $rule)
			{	
				
				if(($rule['enableCode'] && (count($arrCodes) && in_array($rule['id'], $arrAppliedRules)) || (count($arrCodes) && !in_array($rule['code'], $arrCodes))))
				{						
					continue;
				}
				
				$arrAppliedRules[] = $rule['id'];
				
				$intQuantity = 1;
				
				if($object->quantity_requested)
					$intQuantity = $object->quantity_requested;
				
				if($object->product_quantity)
					$intQuantity = $object->product_quantity;
									
				switch($rule['type'])
				{ 
					case 'product':					
						$fltChange = $this->calculateRuleTotal($object->price, $intQuantity, $rule['discount']);							
						break;
						
					case 'cart':	
						$fltChange = $this->calculateRuleTotal($object->subTotal, $intQuantity, $rule['discount']);
						break;
						
					default:
						//!@todo: Hook for other types of coupons
						continue;
				}
				
				$arrRules[] = array
				(
					'id'			=> $rule['id'],
					'label'			=> $rule['title'],
					'price'			=> $rule['discount'],
					'total_price'	=> $fltChange
				);
											
			}	//end rules for this particular object
							
			if(count($arrRules))
				$object->rules = $arrRules;
			
			$arrFinalObjects[] = $object;
		
		}	//end objects
			
		return $arrFinalObjects;
	}
	
	protected function calculateRuleTotal($fltFieldValue, $intQuantity = 1, $varRuleDiscount = 0)
	{
		$blnPercentage = strpos($varRuleDiscount, '%');
		
		if($blnPercentage)
		{	
			$fltValue = -1*(float)rtrim($varRuleDiscount, '%') / 100;
				
			$fltChange = (round($fltFieldValue *100, 2) / 100 ) * $intQuantity * $fltValue;
		
		}
		else
		{
			$fltChange = $varRuleDiscount;
		}
		
		return $fltChange;			
	
	}
	
	/** 
	 * Save the currently used rules either to the session or else to the usage table in the case of confirmed rules.
	 * @access private
	 * @param array $arrData
	 * @param string $strContainer
	 */
	private function saveRules($object, $strTable)
	{		
		
		switch($strContainer)
		{
			case 'tl_iso_rule_usage':
				//save usage once coupons have been validated. (MOVE TO 
				
					//update the usage table 
					/*$arrSet['tstamp'] 		= time();
					$arrSet['pid'] 			= $row['rule']['id'];
					$arrSet['member_id'] 	= (FE_USER_LOGGED_IN ? $this->User->id : 0);
					//$arrSet['object_type']	= 
					//$arrSet['object_id'] 	= $row['object']['id'];
					
					$this->Database->prepare("INSERT INTO tl_iso_rule_usage %s")
								   ->set($arrSet)
								   ->execute();
				*/
				break;
				
			default:
							
				$intObjectId = $object->id;
			
				if($object instanceof IsotopeProduct)
				{
					$intObjectId = $object->cart_id;
				}
				
				$arrRules['rules'] = $object->rules;			
				
				//$_SESSION['CHECKOUT_DATA']['rules'] = $arrRules;	//alternately, store to cart "coupons" field.
				$this->Database->prepare("UPDATE $strTable %s WHERE id=?")
							   ->set($arrRules)
							   ->executeUncached($intObjectId);
				break;
				
		}
	}
	
	public function calculateProductRulePrice($objProduct, $intQuantity, $objSource = NULL)
	{
		if(count($objProduct->rules))
		{
			foreach($objProduct->rules as $rule)
			{			
					$objProduct->price += -1*$this->calculateRuleTotal($objProduct->price, $intQuantity, $rule['price']);
			}
		}
				
		return $intQuantity;
	}
	
	/** 
	 * surcharge callback to tally each rule as a line item for total discounts
	 * @access public
	 * @param array $arrSurcharges
	 * @return array $arrSurcharges
	 */
	public function calculateRuleTotals($arrSurcharges)
	{
		$arrTotals = array();
		$arrObjects = $this->Isotope->Cart->getProducts();
		
		if(!count($arrObjects))
			return $arrTotals;

		$this->loadRules($arrObjects, 'tl_iso_cart_items');
					
		foreach($arrObjects as $object)
		{				
			$arrRules = deserialize($object->rules, true);
			
			if(!count($arrRules))
				continue;
				
			foreach($arrRules as $rule)
			{
				$shift = pow(10, 2);
				$fltTotalPrice = -1*round((floor($rule['total_price'] * $shift) / $shift),2);								
				
				$arrTotals[$rule['id']]['label'] 		= $rule['label'];
				$arrTotals[$rule['id']]['price'] 		= $rule['price'];
				$arrTotals[$rule['id']]['total_price'] 	+= $fltTotalPrice;			
							
			}
		}			
				
		return $arrTotals;
	}
	
		
	/** 
	 * Hook-callback for rules
	 * 
	 * @access public
	 * @param array
	 * @return array
	 */
	public function getSurcharges($arrSurcharges)
	{
		$this->import('Isotope');
		
		$arrProducts = $this->Isotope->Cart->getProducts();
		
		if(!count($arrProducts))
			return $arrSurcharges;
	
		$this->loadRules($arrProducts, 'tl_iso_cart_items');
		//first get product rules, then get cart rules.  figure out how to lump rules together so that we may calculate total discounts accurately where
		//products share rules.
		foreach($arrProducts as $object)
		{
			$arrRules = deserialize($object->rules, true);
	
			if(!count($arrRules))
				continue;
				
			foreach($arrRules as $rule)
			{
				$shift = pow(10, 2);
				$fltTotalPrice = -1*round((floor($rule['total_price'] * $shift) / $shift),2);
						
				$arrSurcharges[] = array
				(
					'label'			=> $rule['label'],
					'price'			=> $rule['price'],
					'total_price'	=> $fltTotalPrice,
					'tax_class'		=> 0,
					'add_tax'		=> false,
				);
			}
				
		}
			
		return $arrSurcharges;
	}
	
	/** 
	 * Verify that our rules are still in fact, valid just before payment is completed.
	 * @access public
	 * @param object $objModule
	 */
	public function verifyRules()
	{
		$arrProducts = $this->Isotope->Cart->getProducts();
			
		if(count($arrData))
			$this->saveRules($arrProducts, 'tl_iso_rule_usage');
	}
	
	
		/** 
	 * check eligibility for products
	 * @access protected
	 * @param array $arrProducts
	 * @param string $strQueryMode
	 * @return array $arrReturn
	 */ 
	protected function getEligibleRules($arrObjects, $strQueryMode = '', $blnCartItem = false)
	{				
					
		if(!count($arrObjects))
			return array();
		
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
			case 'products':
				$strRulesClause = " AND type='product' AND enableCode=''";
				break;
				
			case 'cart': 
				$strRulesClause = " AND type='cart' AND enableCode=''";
				break;
				
			case 'coupons':
				$strRulesClause = " AND enableCode='1'";
				break;
		}
									
		//determine eligibility for the current shopper. //restrictions either null or not matching
		$objRules = $this->Database->executeUncached("SELECT c.*, (SELECT COUNT(u.id) AS ruleUses FROM tl_iso_rule_usage u WHERE u.pid=c.id) AS uses FROM tl_iso_rules c WHERE c.enabled='1'".$strRulesClause);
	
		if(!$objRules->numRows)
			return array();
						
		$arrRuleIds = array();
		$arrMemberUsesByRule = array();
		
		$arrRuleIds = $objRules->fetchEach('id');
		
		$arrRules = $objRules->fetchAllAssoc();
		
		$strRuleIds = implode(',', $arrRuleIds);
		
		//gather all usage data for the rules we have returned.. if a rule is for non-members, then this query by default is checking usage in terms of global use  				 		//of the rule rather that per user as we haven't a way to verify usage for a non-member.  
		if(FE_USER_LOGGED_IN)
		{
			$objMemberUses = $this->Database->executeUncached("SELECT *, COUNT(id) AS customerUses FROM tl_iso_rule_usage WHERE pid IN($strRuleIds) AND member_id={$this->User->id}");
			
			if($objMemberUses->numRows)		
			{
				while($objMemberUses->next());
				{
					$arrMemberUsesByRule[$objMemberUses->pid] = $objMemberUses->row();
				}
			}
		}
			
		foreach($arrObjects as $i => $object)
		{
			$intObjectId=$object->id; //necessary to check the usage table by product collection class id (for example, cart id)

			if($object instanceof IsotopeProduct)
			{
					$arrObject['pages'] = $object->pages;
					$arrObject['producttypes'] = $object->type;
					$arrObject['products'] = $object->id;
					if($blnCartItem)
						$intObjectId = $object->cart_id; //necessary to check the usage table by product collection class id (for example, cart id)
			}
			elseif($object instanceof IsotopeProductCollection)
			{
					$arrObject = array(); //this is only necessary for product-level rules.
			}
			else
			{
					//!@todo: HOOK THIS for other unanticipated classes that don't fit the two we provide for?
					break;
			}
						
			$arrCustomerMatrix = array_merge($arrCustomer, $arrObject);		
			
			foreach($arrRules as $row)
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
									//if the number of customer uses exceeds this rule in total, or the current product has already had the rule applied to it...					
									if($arrUses['value'] <= $arrMemberUsesByRule[$row['id']]['customerUses'] || $intObjectId==$arrMemberUsesByRule[$row['id']]['object_id'])
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
					if($row['startDate'] > time() || $row['endDate'] < time())
						break;
				}
				
				//check time, will be verified again later.	fix
				
				if($row['timeRestrictions'])
				{
					if($row['startTime'] > time() || $row['endTime'] < time())
						break;				
				}
				
				//exclusion of other rules, all or certain ones
				switch($row['ruleRestrictions'])
				{
					case 'all':
						if(count($object->rules))
							break(2);
					case 'rules':
						$arrExcludedRules = deserialize($row['rules'], true);	//get specific rules for exclusion check
						if(count($arrRules) && count($object->rules) && array_intersect($object->rules, $arrExcludedRules))
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
					case 'product':
						if($row['minItemQuantity'] && $row['minItemQuantity'] > $object->product_quantity)
							break(2);
												
						switch($row['productRestrictions'])
						{
							case 'producttypes':
							case 'pages':
							case 'products':
								if($row[$row['productRestrictions']])
									$arrRestrictions[$row['productRestrictions']] = deserialize($row[$row['productRestrictions']]);
								break;				
							default:
								break;			
						}
						break;
						
					case 'cart':
						if(!($object instanceof IsotopeCart))
							break(2);
								
						if($row['minSubTotal']>0 && $object->subTotal > $row['minSubTotal'])
							break(2);
						
						if($row['minCartQuantity']>0 && $object->totalQuantity > $row['minCartQuantity'])
							break(2);
						
						break;
						
					default:
						//!@todo: Hook for additional types of rule-eligible objects
						break;
				}
				
				if(count($arrRestrictions))
				{														
					$blnLoopBreak = false;									
					foreach($arrRestrictions as $k=>$v) //check each field in the rule row
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
					
				}
				
				$arrReturn[get_class($object)][$intObjectId][] = $row;
				
			} 	//end rules loop
			
		}	//end products loop

		if(!count($arrReturn))
			return array();	
	
		//return an array of eligible rules to each item in the cart.
		return $arrReturn;
	}

}
