<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
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
			case 'billing_fields_raw':
			case 'shipping_fields_raw':
				if (!is_array($this->arrCache[$strKey]))
				{
					$strField = str_replace('_raw', '', $strKey);
					$arrFields = array();

					foreach( $this->$strField as $field )
					{
						if ($field['enabled'])
						{
							$arrFields[] = $field['value'];
						}
					}

					$this->arrCache[$strKey] = $arrFields;
				}

				return $this->arrCache[$strKey];

			default:
				return deserialize(parent::__get($strKey));
		}
	}


	public function __construct()
	{
		return parent::__construct();
	}


	/**
	 * Transparently map calls to core config class, because Isotope->Config has the same name.
	 */
	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->Config, $name), $arguments);
	}
}

