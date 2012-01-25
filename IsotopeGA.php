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
 
 
class IsotopeGA extends Frontend
{
	
	public function postCheckout($objOrder, $arrItemIds, $arrData)
	{
		require_once('system/modules/isotope_ga/php-ga-1.0/src/GoogleAnalytics/GoogleAnalyticsTracker.php');
		require_once('system/modules/isotope_ga/php-ga-1.0/src/GoogleAnalytics/GoogleAnalyticsSession.php');
		require_once('system/modules/isotope_ga/php-ga-1.0/src/GoogleAnalytics/GoogleAnalyticsVisitor.php');
		require_once('system/modules/isotope_ga/php-ga-1.0/src/GoogleAnalytics/GoogleAnalyticsTransaction.php');
		require_once('system/modules/isotope_ga/php-ga-1.0/src/GoogleAnalytics/GoogleAnalyticsItem.php');
		// Initilize GA Tracker
		$tracker = new GoogleAnalyticsTracker($this->Isotope->Config->ga_account, $this->Environment->base);
		
		// Assemble Visitor information
		// (could also get unserialized from database)
		$visitor = new GoogleAnalyticsVisitor();
		$visitor->setIpAddress($this->Environment->ip);
		$visitor->setUserAgent($this->Environment->httpUserAgent);
			
		$transaction = new GoogleAnalyticsTransaction();
		
		$transaction->setOrderId($objOrder->order_id);
		$transaction->setAffiliation($this->Isotope->Config->name);
		$transaction->setTotal($objOrder->grand_total);
		$transaction->setTax($objOrder->taxTotal);
		$transaction->setCity($objOrder->billingAddress['city']);
		
		if($objOrder->billingAddress['subdivision'])
		{
			$arrSub = explode("-",$objOrder->billingAddress['subdivision']);
			$transaction->setState($arrSub[1]);
		}
		
		$transaction->setCountry($objOrder->billingAddress['country']);
		
		$arrProducts = $objOrder->getProducts();
			
		$item = new GoogleAnalyticsItem();
		
		foreach($arrProducts as $i=>$objProduct)
		{	
			$arrOptions = array();
			$arrOptionValues = array();
	
			if($objProduct->sku)
				$item->setSku = $objProduct->sku;
	
			$item->setName = $objProduct->name;
			$item->setPrice = $objProduct->price;
			$item->setQuantity = $objProduct->quantity_requested;
			
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
		$session = $_SESSION;
		
		$transaction->trackTransaction($transaction, $session, $visitor);
	}
	
}