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
	 * Add downloads to this order
	 */
	public function transferFromCollection(IsotopeProductCollection $objCollection, $blnDuplicate=true)
	{
		$arrIds = parent::transferFromCollection($objCollection, $blnDuplicate);
		
		foreach( $arrIds as $id )
		{
			$objDownloads = $this->Database->execute("SELECT * FROM tl_iso_downloads WHERE pid=(SELECT product_id FROM {$this->ctable} WHERE id=$id)");
			
			while( $objDownloads->next() )
			{
				$arrSet = array
				(
					'pid'					=> $id,
					'tstamp'				=> time(),
					'download_id'			=> $objDownloads->id,
					'downloads_remaining'	=> ($objDownloads->downloads_allowed > 0 ? $objDownloads->downloads_allowed : ''),
				);
				
				$this->Database->prepare("INSERT INTO tl_iso_order_downloads %s")->set($arrSet)->executeUncached();
			}
		}
	}
	
	
	/**
	 * Remove downloads when removing a product
	 */
	public function deleteProduct($intId)
	{
		$this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid=$intId");
		
		return parent::deleteProuct($intId);
	}

	
	/**
	 * Also delete downloads when deleting this order.
	 */
	public function delete()
	{
		$this->Database->query("DELETE FROM tl_iso_order_downloads WHERE pid IN (SELECT id FROM {$this->ctable} WHERE pid={$this->id})");
		
		return parent::delete();
	}

}

