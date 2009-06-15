<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Initialize the system
 */
define('TL_MODE', 'FE');
require('system/initialize.php');


class PostSale extends Frontend
{

	/**
	 * Run the controller
	 */
	public function run()
	{
		if (!strlen($this->Input->get('do')) || !strlen($this->Input->get('id')))
		{
			$this->log('Invalid post-sale requests without parameters.', 'PostSale run()', TL_ERROR);
			return '';
		}
		
		switch( $this->Input->post('do'))
		{
			case 'pay':
				$objModule = $this->Database->prepare("SELECT * FROM tl_payment_modules WHERE id=?")->limit(1)->execute($this->Input->get('id'));
				break;
				
			case 'ship':
				$objModule = $this->Database->prepare("SELECT * FROM tl_shipping_modules WHERE id=?")->limit(1)->execute($this->Input->get('id'));
				break;
		}
		
						
		if (!$objModule->numRows)
			return '';
			
		$strClass = $GLOBALS['ISO_PAY'][$objModule->type];
		if (!strlen($strClass) || !$this->classFileExists($strClass))
			return '';
			
		try 
		{
			$objModule = new $strClass($objModule->row());
			return $objModule->processPostSale();
		}
		catch (Exception $e) {}
		
		return '';
	}
}


/**
 * Instantiate controller
 */
$objPostSale = new PostSale();
$objPostSale->run();

