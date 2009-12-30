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
 * @copyright  Winans Creative 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 

class IsotopeDatabase
{

	/**
	 * Current object instance (Singleton)
	 * @var object
	 */
	protected static $objInstance;

	/**
	 * Files object
	 * @var object
	 */
	protected $Files;

	/**
	 * Top content
	 * @var string
	 */
	protected $strTop = '';

	/**
	 * Bottom content
	 * @var string
	 */
	protected $strBottom = '';

	/**
	 * Modified
	 * @var boolean
	 */
	protected $blnIsModified = false;

	/**
	 * Data array
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Cache array
	 * @var array
	 */
	protected $arrCache = array();


	/**
	 * Load all configuration files
	 */
	protected function __construct()
	{
		// Read the local configuration file
		$strMode = 'top';
		$resFile = fopen(TL_ROOT . '/system/modules/isotope/config/database.sql', 'rb');

		while (!feof($resFile))
		{
			$strLine = fgets($resFile);
			$strTrim = trim($strLine);

			if ($strTrim == '?>')
			{
				continue;
			}

			if ($strTrim == '-- PRODUCT ATTRIBUTES START --')
			{
				$strMode = 'data';
				continue;
			}

			if ($strTrim == '-- PRODUCT ATTRIBUTES STOP --')
			{
				$strMode = 'bottom';
				continue;
			}
			
			if ($strMode == 'top')
			{
				$this->strTop .= $strLine;
			}
			elseif ($strMode == 'bottom')
			{
				$this->strBottom .= $strLine;
			}
			elseif ($strTrim != '')
			{
				if (preg_match('@^[ ]*`([^`]+)`([^,]*)@i', $strLine, $arrMatch))
				{
					$this->arrData[$arrMatch[1]] = trim($arrMatch[2]);
				}
			}
		}

		fclose($resFile);
	}
	

	/**
	 * Save the local configuration
	 */
	public function __destruct()
	{
		if (!$this->blnIsModified)
		{
			return;
		}

		$strFile  = trim($this->strTop) . "\n\n";
		$strFile .= "-- PRODUCT ATTRIBUTES START --\nCREATE TABLE `tl_product_data` (\n";

		foreach ($this->arrData as $k=>$v)
		{
			$strFile .= "  `$k` $v,\n";
		}

		$strFile .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8;\n-- PRODUCT ATTRIBUTES STOP --\n\n";

		if ($this->strBottom != '')
		{
			$strFile .= trim($this->strBottom) . "\n\n";
		}

		$objFile = new File('/system/modules/isotope/config/database.sql');
		$objFile->write($strFile);
		$objFile->close();
	}


	/**
	 * Prevent cloning of the object (Singleton)
	 */
	final private function __clone() {}


	/**
	 * Return the current object instance (Singleton)
	 * @return object
	 */
	public static function getInstance()
	{
		if (!is_object(self::$objInstance))
		{
			self::$objInstance = new IsotopeDatabase();
		}

		return self::$objInstance;
	}


	/**
	 * Add a configuration variable to the local configuration file
	 * @param string
	 * @param mixed
	 */
	public function add($strKey, $varValue)
	{
		$this->blnIsModified = true;
		$this->arrData[$strKey] = $varValue;
	}


	/**
	 * Alias for Config::add()
	 * @param string
	 * @param mixed
	 */
	public function update($strKey, $varValue)
	{
		$this->add($strKey, $varValue);
	}


	/**
	 * Delete a configuration variable from the local configuration file
	 * @param string
	 * @param mixed
	 */
	public function delete($strKey)
	{
		$this->blnIsModified = true;
		unset($this->arrData[$strKey]);
	}


	/**
	 * Escape a parameter depending on its type and return it
	 * @param mixed
	 * @return mixed
	 */
	protected function escape($varValue)
	{
		if (is_numeric($varValue))
		{
			return $varValue;
		}

		if (is_bool($varValue))
		{
			return $varValue ? 'true' : 'false';
		}

		if ($varValue == 'true')
		{
			return 'true';
		}

		if ($varValue == 'false')
		{
			return 'false';
		}

		$varValue = preg_replace('/[\n\r\t]+/i', ' ', str_replace("'", "\\'", $varValue));
		$varValue = "'" . preg_replace('/ {2,}/i', ' ', $varValue) . "'";

		return $varValue;
	}
}

?>