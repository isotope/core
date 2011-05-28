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


class IsotopeAutomator extends Controller
{

	/**
	 * Remove carts that have not been accessed for a given number of days (depending on store config).
	 *
	 * @access public
	 * @return void
	 */
	public function deleteOldCarts()
	{
		$this->import('Database');

		$time = time() - $GLOBALS['TL_CONFIG']['iso_cartTimeout'];
		$objCarts = $this->Database->execute("SELECT id FROM tl_iso_cart WHERE tstamp<$time");

		if ($objCarts->numRows)
		{
			$objCart = new IsotopeCart();

			foreach( $objCarts->fetchEach('id') as $id )
			{
				if ($objCart->findBy('id', $id))
				{
					$objOrder = new IsotopeOrder();
					
					if ($objOrder->findBy('cart_id', $objCart->id))
					{
						if ($objOrder->status == '')
						{
							$objOrder->delete();
						}
					}
					
					$objCart->delete();
				}
			}
		}
	}
}

