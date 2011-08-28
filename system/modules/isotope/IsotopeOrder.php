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


class IsotopeOrder extends IsotopeProductCollection
{

	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_orders';
	
	/**
	 * Name of the child table
	 * @var string
	 */
	protected $ctable = 'tl_iso_order_items';
	
	/**
	 * Lock products from apply rule prices
	 */
	protected $blnLocked = true;

				
	public function __get($strKey)
	{
		switch($strKey)
		{
			case 'surcharges':
				return $this->arrData['surcharges'] ? deserialize($this->arrData['surcharges']) : array();
				break;
				
			case 'billingAddress':
				return deserialize($this->arrData['billing_address'], true);
				
			case 'shippingAddress':
				return deserialize($this->arrData['shipping_address'], true);
				
			default:
				return parent::__get($strKey);
		}
	}
	
	
	/**
	 * Add downloads to this order
	 */
	public function transferFromCollection(IsotopeProductCollection $objCollection, $blnDuplicate=true)
	{
		$arrIds = parent::transferFromCollection($objCollection, $blnDuplicate);
		
		foreach( $arrIds as $id )
		{
			$objDownloads = $this->Database->execute("SELECT *, (SELECT product_quantity FROM {$this->ctable} WHERE id=$id) AS product_quantity FROM tl_iso_downloads WHERE pid=(SELECT product_id FROM {$this->ctable} WHERE id=$id)");
			
			while( $objDownloads->next() )
			{
				$arrSet = array
				(
					'pid'					=> $id,
					'tstamp'				=> time(),
					'download_id'			=> $objDownloads->id,
					'downloads_remaining'	=> ($objDownloads->downloads_allowed > 0 ? ($objDownloads->downloads_allowed * $objDownloads->product_quantity) : ''),
				);
				
				$this->Database->prepare("INSERT INTO tl_iso_order_downloads %s")->set($arrSet)->executeUncached();
			}
		}
		
		return $arrIds;
	}
	
	
	/**
	 * Find a record by its reference field and return true if it has been found
	 * @param  int
	 * @return boolean
	 */
	public function findBy($strRefField, $varRefId)
	{
		if (parent::findBy($strRefField, $varRefId))
		{
			$this->Shipping = null;
			$this->Payment = null;
			
			$objPayment = $this->Database->execute("SELECT * FROM tl_iso_payment_modules WHERE id=" . $this->payment_id);
			
			if ($objPayment->numRows)
			{
				$strClass = $GLOBALS['ISO_PAY'][$objPayment->type];
				
				try
				{
					$this->Payment = new $strClass($objPayment->row());
				}
				catch (Exception $e) {}
			}
			
			if ($this->shipping_id > 0)
			{		
				$objShipping = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id=" . $this->shipping_id);
				
				if ($objShipping->numRows)
				{
					$strClass = $GLOBALS['ISO_SHIP'][$objShipping->type];
					
					try
					{
						$this->Shipping = new $strClass($objShipping->row());
					}
					catch (Exception $e) {}
				}
			}
			
			return true;
		}
		
		return false;
	}


	/**
	 * Remove downloads when removing a product
	 */
	public function deleteProduct(IsotopeProduct $objProduct)
	{
		if (parent::deleteProduct($objProduct))
		{
			$this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid={$objProduct->cart_id}");
		}
		
		return false;
	}


	/**
	 * Also delete downloads when deleting this order.
	 */
	public function delete()
	{
		$this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid IN (SELECT id FROM {$this->ctable} WHERE pid={$this->id})");
		
		return parent::delete();
	}


	public function getSurcharges()
	{
		$arrSurcharges = deserialize($this->surcharges);
		return is_array($arrSurcharges) ? $arrSurcharges : array();
	}
}

