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
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class DC_DynamicTable
 *
 * Provide methods to modify the database.
 * @copyright  John Brand 2008
 * @author     John Brand <john@thyonmedia.com>
 */
require_once 'DC_Table.php';

class DC_DynamicTable extends DC_Table
{

	public function __construct($strTable)
	{
		// HOOK: add custom create function
		if (array_key_exists('oncreate_callback', $GLOBALS['TL_DCA'][$strTable]['config']) && is_array($GLOBALS['TL_DCA'][$strTable]['config']['oncreate_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$strTable]['config']['oncreate_callback'] as $callback)
			{
				$this->import($callback[0]);
				$strTable = $this->$callback[0]->$callback[1]($strTable);
			}
		}

		parent::__construct($strTable);
	}
}

