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
 

class IsotopeConfig extends Model
{
	
	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_config';
	
	
	/**
	 * Return custom options or table row data.
	 * 
	 * @access public
	 * @param mixed $strKey
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'cookie_timeout':
				return $this->cookie_duration ? (time() + ($this->cookie_duration * 86400)) : 0;
				break;
			
			default:
				return deserialize(parent::__get($strKey));
				break;
		}
	}
	
	
	/**
	 * Fetch the current store configuration.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct($intConfig)
	{
		parent::__construct();
		
		if (!$this->findBy('id', $intConfig))
		{
			throw new Exception('No store configuration available');
		}
	}
}

