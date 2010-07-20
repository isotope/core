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


class ModuleIsotopeCart extends ModuleIsotope
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_iso_cart';

	/** 
	 * Coupons applied
	 * @var boolean
	 */
	protected $couponsApplied = false;
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE CART ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		$arrProducts = $this->Isotope->Cart->getProducts();
		
		if (!count($arrProducts))
		{
		   $this->Template = new FrontendTemplate('mod_message');
		   $this->Template->type = 'empty';
		   $this->Template->message = $GLOBALS['TL_LANG']['MSC']['noItemsInCart'];
		   return;
		}
		
		$arrEligibleIds = $this->getEligibleCoupons($arrProducts);

		$objTemplate = new FrontendTemplate($this->iso_cart_layout);

		$objTemplate->couponsForm = ($this->iso_enableCoupons && count($arrEligibleIds) ? $this->getCouponInterface($arrProducts, $arrEligibleIds) : NULL);
	
		if($this->couponsApplied)
			$this->reload();
	
		global $objPage;
		$strUrl = $this->generateFrontendUrl($objPage->row());
		
		$blnReload = false;
		$arrQuantity = $this->Input->post('quantity');
		$arrProductData = array();
		
		foreach( $arrProducts as $i => $objProduct )
		{
			if ($this->Input->get('remove') == $objProduct->cart_id)
			{
				$this->Database->query("DELETE FROM tl_iso_cart_items WHERE id={$objProduct->cart_id}");
				//@TODO: doesn't cover order discounts... they won't be removable for now.
				$this->Database->query("DELETE FROM tl_iso_coupon_usage WHERE object_id={$objProduct->cart_id} AND cart_id={$this->Isotope->Cart->id}");
				$this->redirect((strlen($this->Input->get('referer')) ? base64_decode($this->Input->get('referer', true)) : $strUrl));
			}
			elseif ($this->Input->post('FORM_SUBMIT') == 'iso_cart_update' && is_array($arrQuantity) && $objProduct->cart_id)
			{
				$blnReload = true;
				if (!$arrQuantity[$objProduct->cart_id])
				{
					$this->Database->query("DELETE FROM tl_iso_cart_items WHERE id={$objProduct->cart_id}");
				}
				else
				{
					
					$this->Database->prepare("UPDATE tl_iso_cart_items SET product_quantity=? WHERE id={$objProduct->cart_id}")->executeUncached($arrQuantity[$objProduct->cart_id]);
				}
			}
			
			$arrProductData[] = array_merge($objProduct->getAttributes(), array
			(
				'id'				=> $objProduct->id,
				'image'				=> $objProduct->images->main_image,
				'link'				=> $objProduct->href_reader,
				'price'				=> $this->Isotope->formatPriceWithCurrency($objProduct->price),
				'total_price'		=> $this->Isotope->formatPriceWithCurrency($objProduct->total_price),
				'original_price'	=> $this->Isotope->formatPriceWithCurrency($objProduct->original_price),
				'tax_id'			=> $objProduct->tax_id,
				'quantity'			=> $objProduct->quantity_requested,
				'cart_item_id'		=> $objProduct->cart_id,
				'product_options'	=> $objProduct->getOptions(),
				'rules'				=> $objProduct->rules,
				'coupons'			=> $objProduct->coupons,
				'remove_link'		=> ampersand($strUrl . ($GLOBALS['TL_CONFIG']['disableAlias'] ? '&' : '?') . 'remove='.$objProduct->cart_id.'&referer='.base64_encode($this->Environment->request)),
				'remove_link_text'  => $GLOBALS['TL_LANG']['MSC']['removeProductLinkText'],
				'remove_link_title' => sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $objProduct->name),
				'class'				=> 'row_' . $i . ($i%2 ? ' even' : ' odd') . ($i==0 ? ' row_first' : ''),
			));
		}

		if ($blnReload)
		{
			$this->reload();
		}
		
		if (count($arrProductData))
		{
			$arrProductData[count($arrProductData)-1]['class'] .= ' row_last';
		}
		
		$arrSurcharges = array();
		foreach( $this->Isotope->Cart->getSurcharges() as $arrSurcharge )
		{
			$arrSurcharges[] = array
			(
			   'label'				=> $arrSurcharge['label'],
			   'price'				=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['price']),
			   'total_price'		=> $this->Isotope->formatPriceWithCurrency($arrSurcharge['total_price']),
			   'tax_id'				=> $arrSurcharge['tax_id'],
			);
		}
		
		$arrCoupons = array();
		
		
		/*foreach( $this->Isotope->Cart->getCouponSurcharges($arrSurcharges) as $arrCoupon)  @TODO - determine if needed
		{
			$arrSurcharges[] = array
			(
				'label'				=> $arrCoupon['label'],
				'price'				=> $this->Isotope->formatPriceWithCurrency($arrCoupon['price']),
				'total_price'		=> $this->Isotope->formatPriceWithCurrency($arrCoupon['price']),
				'tax_id'			=> NULL
			);
		}*/
		
		$objTemplate->formId = 'iso_cart_update';
		$objTemplate->formSubmit = 'iso_cart_update';
		$objTemplate->action = $this->Environment->request;
		$objTemplate->products = $arrProductData;
		$objTemplate->cartJumpTo = $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id={$this->iso_cart_jumpTo}")->fetchAssoc());
		$objTemplate->cartLabel = $GLOBALS['TL_LANG']['MSC']['cartBT'];
		$objTemplate->checkoutJumpToLabel = $GLOBALS['TL_LANG']['MSC']['checkoutBT'];
		$objTemplate->checkoutJumpTo = $this->generateFrontendUrl($this->Database->execute("SELECT * FROM tl_page WHERE id={$this->iso_checkout_jumpTo}")->fetchAssoc());
		
		$objTemplate->subTotalLabel = $GLOBALS['TL_LANG']['MSC']['subTotalLabel'];
		$objTemplate->grandTotalLabel = $GLOBALS['TL_LANG']['MSC']['grandTotalLabel'];
		$objTemplate->subTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal);
		$objTemplate->grandTotalPrice = $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal);
		$objTemplate->showOptions = false;	//!@todo make a module option.
		
		$objTemplate->surcharges = $arrSurcharges;

		$this->Template->cart = $objTemplate->parse();
	}
	
	protected function getCouponInterface($arrProducts, $arrData)
	{				
		if($this->Input->post('FORM_SUBMIT')=='iso_cart_coupons')
		{			
			if($this->Input->post('code'))
				$blnResult = $this->matchCoupon($this->Input->post('code'), $arrData);
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
	
	protected function matchCoupon($strCodes, $arrData)
	{			
		$arrCodes = explode(',', $strCodes);

		$arrUsedCodes = array();

		foreach($arrData as $row)
		{	

			foreach($arrCodes as $code)
			{
				if(in_array($code, $arrUsedCodes))
					continue;
					
				if($row['coupon']['code']==$code)
				{
					$arrUsedCodes[] = $code;	//only use a code once per postback
																			
					if(strpos($row['coupon']['discount'], '%'))
					{	
						$intValue = (float)rtrim($row['coupon']['discount'], '%') / 100;
												
						$fltDiscount = ($row['product']->price * $intValue);			
					}
					else
					{
						$intValue = (float)$row['coupon']['discount'];
						$fltDiscount = $intValue;
					}		
														
					$arrCouponData['id']			= $row['coupon']['id'];
					$arrCouponData['type']			= $row['coupon']['type'];
					$arrCouponData['object_id']		= $row['product']->cart_id;
					$arrCouponData['label']			= $row['coupon']['title'];
					$arrCouponData['price']			= $row['coupon']['discount'];
					$arrCouponData['total_price']	= $row['product']->price - $fltDiscount;
					
					//simply update the price on the existing product as-is
					$arrUpdate['price'] = $row['product']->price - $fltDiscount;
										
					$arrUpdate['coupons'][] = serialize($arrCouponData);
					
					$varReturn = $this->Isotope->Cart->updateProduct($row['product'], $arrUpdate);
					
					if($varReturn)
						$blnApplied = true;
							
					if($blnApplied)
					{	
						//update the usage table 
						$arrSet['tstamp'] = time();
						$arrSet['pid'] = $row['coupon']['id'];
						$arrSet['member_id'] = (FE_USER_LOGGED_IN ? $this->User->id : 0);
						$arrSet['cart_id']	 = $this->Isotope->Cart->id;
						$arrSet['object_id'] = $row['product']->cart_id;
						
						$this->Database->prepare("INSERT INTO tl_iso_coupon_usage %s")
									   ->set($arrSet)
									   ->execute();		
					}
				}	//end if($row['coupon']['code]...
			}	//end $arrCodes foreach
		}  //end $arrData foreach
		
		$this->couponsApplied = (count($arrUsedCodes) ? true : false);
	}
	
	protected function getEligibleCoupons($arrProducts = array())
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
									
		//determine eligibility for the current shopper. //restrictions either null or not matching
		$objCoupons = $this->Database->executeUncached("SELECT c.*, (SELECT COUNT(u.id) AS couponUses FROM tl_iso_coupon_usage u WHERE u.pid=c.id) AS uses FROM tl_iso_coupon c WHERE enabled='1' AND (startDate IS NULL OR startDate<=$intToday) AND (endDate IS NULL OR endDate>=$intToday)");
		
		if(!$objCoupons->numRows)
			return '';
						
		$arrCouponIds = array();
		$arrMemberUsesByCoupon = array();
		
		$arrCouponIds = $objCoupons->fetchEach('id');
		
		$arrCoupons = $objCoupons->fetchAllAssoc();
		
		$strCouponIds = implode(',', $arrCouponIds);
		
		//gather all usage data for the coupons we have returned.. if a coupon is for non-members, then this query by default is checking usage in terms of global use  		//of the coupon rather that per user as we haven't a way to verify usage for a non-member.  
		$objMemberUses = $this->Database->executeUncached("SELECT *, COUNT(id) AS customerUses FROM tl_iso_coupon_usage WHERE pid IN($strCouponIds) AND member_id={$this->User->id}");
		
		if($objMemberUses->numRows)		
		{
			while($objMemberUses->next());
			{
				$arrMemberUsesByCoupon[$objMemberUses->pid] = $objMemberUses->row();
			}
		}
		
		foreach($arrProducts as $i => $objProduct)
		{						
			$arrProduct['pages'] = $objProduct->pages;
			$arrProduct['productTypes'] = $objProduct->type;
			$arrProduct['products'] = $objProduct->id;
		
			$arrCustomerMatrix = array_merge($arrCustomer, $arrProduct);		
					
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
									if($arrUses['value'] <= $arrMemberUsesByCoupon[$row['id']]['customerUses'] || $objProduct->cart_id==$arrMemberUsesByCoupon[$row['id']]['object_id'])
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
						}
					}
				}
				
				//exclusion of other coupons, all or certain ones
				switch($row['couponRestrictions'])
				{
					case 'all':
						if(count($objProduct->coupons))
							break(2);
					case 'coupons':
						$arrExcludedCoupons = deserialize($row['coupons'], true);	//get specific coupons for exclusion check
						if(count($arrCoupons) && array_intersect($objProduct->coupons, $arrExcludedCoupons))
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
						if($row['minItemQuantity'] && $row['minItemQuantity'] > $objProduct->quantity_requested)
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
						
					case 'order':
						if($row['minSubTotal']>0 && $this->Isotope->Cart->subTotal > $row['minSubTotal'])
							break(2);
						
						if($row['minCartQuantity']>0 && $this->Isotope->Cart->totalQuantity > $row['minCartQuantity'])
							break(2);
						break;
					default:
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
							'product'		=> $objProduct	//assumes only product right now
						);
					
				}
			} 	//end coupons loop
		}	//end products loop
		
		if(!count($arrReturn))
			return array();	
	
		//return an array of eligible coupons to each item in the cart.
		return $arrReturn;
	}
}

