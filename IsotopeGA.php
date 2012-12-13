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

	/**
	 * Trigger the correct function based on Isotope version
	 * @params mixed
	 * @return mixed
	 */
	public function triggerAction()
	{
		$this->import('Isotope');

		$blnCompatible = version_compare(ISO_VERSION, '1.3', '<');
		$arrParam = func_get_args();

		if($blnCompatible)
		{
			return call_user_func_array(array($this, 'postCheckoutCompatible'), $arrParam);
		}
		else
		{
			return call_user_func_array(array($this, 'postCheckout'), $arrParam);
		}
	}


	/**
	 * Process checkout in Isotope 1.3+
	 */
	public function postCheckout($objOrder, $arrItemIds, $arrData)
	{
		$objConfig = new IsotopeConfig();

		if ($objConfig->findBy('id', $objOrder->config_id))
		{
			if ($objConfig->ga_enable)
			{
				$this->trackGATransaction($objConfig,$objOrder);
			}
		}

		return true;
	}


	/**
	 * Process checkout in Isotope 0.2
	 */
	public function postCheckoutCompatible($orderId, $blnCheckout, $objModule)
	{
		$objOrder = new IsotopeOrder();

		if ($objOrder->findBy('id', $orderId))
		{
			$objConfig = new IsotopeConfig();

			if ($objConfig->findBy('id', $objOrder->config_id))
			{
				if ($objConfig->ga_enable)
				{
					$this->trackGATransaction($objConfig, $objOrder);
				}
			}
		}

		return $blnCheckout;
	}


	/**
	 * Actually execute the GoogleAnalytics tracking
	 * @param Database_Result
	 * @param IsotopeProductCollection
	 */
	protected function trackGATransaction($objConfig, $objOrder)
	{
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
		$transaction->setCity($objOrder->billing_address['city']);

		if($objOrder->billing_address['subdivision'])
		{
			$arrSub = explode("-",$objOrder->billing_address['subdivision']);
			$transaction->setRegion($arrSub[1]);
		}

		$transaction->setCountry($objOrder->billing_address['country']);

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

		// Track logged-in member as custom variable
		if ($objConfig->ga_trackMember && FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');

			$customVar = new GoogleAnalyticsCustomVariable(1, 'Member', $this->User->username, GoogleAnalyticsCustomVariable::SCOPE_VISITOR);

			$tracker->addCustomVariable($customVar);
		}

		// Assemble Session information
		// (could also get unserialized from PHP session)
		$session = new GoogleAnalyticsSession();

		$tracker->trackTransaction($transaction, $session, $visitor);
	}
}
