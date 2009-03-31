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
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Class Order
 *
 * Class for ecommerce order object
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class IsotopeOrder extends System
{

	/**
	 *
	 */
	protected $fltTotal;
	
	/**
	 *
	 */
	protected $intId;

	/**
	 * Current record
	 * @var array
	 */
	protected $arrData = array();
	
	/**
	 *
	 */
	protected $arrProducts = array();

	
	/**
	 * Instantiate and gather order data 
	 */
	public function __construct($arrProducts)
	{
		parent::__construct();

		
	}


	/**
	 * Set an object property
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch($strKey)
		{
			
			case 'total':
				$this->fltTotal = (float)$varValue;
				break;
		
		
			default:
				$this->arrData[$strKey] = $varValue;
				break;
		}
	
	}


	/**
	 * Return an object property
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch($strKey)
		{
			case 'id':
				return $this->intId;
				break;
				
			case 'total':
				return $this->fltTotal;
				break;		
					
			default:
				if (array_key_exists($strKey, $this->arrData))
				{
					return $this->arrData[$strKey];
				}
				break;
		}

	}


	/**
	 * Parse the template
	 * @return string
	 */
	public function generate($intOrderId)
	{
		$objOrder = $this->Database->prepare()
		
		$this->compile();
	}


	/**
	 * Compile the current element
	 */
	abstract protected function compile();
	
	/**
	 *	save the order to the database.
	 */
	public function save()
	{
		
	
	}
	
	/**
	 * remove the order from the database
	 */
	public function delete()
	{
	
	
	}
	
	
	
}

?>