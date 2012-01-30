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
 
 
class IsotopeGA extends IsotopeFrontend
{
	
	public function postCheckout($objOrder, $arrItemIds, $arrData)
	{		
		/*$this->import('GoogleAnalyticsTracker');
		$this->import('GoogleAnalyticsSession');
		$this->import('GoogleAnalyticsVisitor');
		$this->import('GoogleAnalyticsTransaction');
		$this->import('GoogleAnalyticsItem');
		*/
		
		$objConfig = new IsotopeConfig();
		
		$objConfig->findBy('id',$objOrder->config_id);
		
		if(!$objConfig->ga_enable)
			return;
		
		// Initilize GA Tracker
		$tracker = new GoogleAnalyticsTracker($objConfig->ga_account, $this->Environment->base);
		
		// Assemble Visitor information
		// (could also get unserialized from database)
		$visitor = new GoogleAnalyticsVisitor();
		$visitor->setIpAddress($this->Environment->ip);
		$visitor->setUserAgent($this->Environment->httpUserAgent);
			
		$transaction = new GoogleAnalyticsTransaction();
		
		$transaction->setOrderId($objOrder->order_id);
		$transaction->setAffiliation($objConfig->name);
		$transaction->setTotal($objOrder->grandTotal);
		$transaction->setTax($objOrder->taxTotal);
		$transaction->setShipping($objOrder->shippingTotal);
		$transaction->setCity($objOrder->billingAddress['city']);
		
		if($objOrder->billingAddress['subdivision'])
		{
			$arrSub = explode("-",$objOrder->billingAddress['subdivision']);
			$transaction->setRegion($arrSub[1]);
		}
		
		$transaction->setCountry($objOrder->billingAddress['country']);
		
		$arrProducts = $objOrder->getProducts();
			
		
		
		foreach($arrProducts as $i=>$objProduct)
		{	
			$item = new GoogleAnalyticsItem();
		
			$arrOptions = array();
			$arrOptionValues = array();
	
			if($objProduct->sku)
				$item->setSku($objProduct->sku);
	
			$item->setName($objProduct->name);
			$item->setPrice($objProduct->price);
			$item->setQuantity($objProduct->quantity_requested);
			
			//Do we also potentially have options?
			$arrOptions = $objProduct->getOptions(true);
									
			foreach ($arrOptions as $field => $value)
			{
				if ($value == '')
					continue;
				
				$arrOptionValues[] = $this->Isotope->formatValue('tl_iso_products', $field, $value);
				
			}
			
			if(count($arrOptionValues))
				$item->setVariation(implode(' ',$arrOptionValues));
				
			$transaction->addItem($item);
		}
		
		// Assemble Session information
		// (could also get unserialized from PHP session)
		$session = new GoogleAnalyticsSession();
		
		$tracker->trackTransaction($transaction, $session, $visitor);
	}
	
}