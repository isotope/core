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


class IsotopeBackend extends Backend
{
		
	/**
	 * Hide archived records.
	 */
	public function hideArchivedRecords($dc)
	{
		$arrRoot = $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'];
		
		$arrRoot = $this->Database->execute("SELECT id FROM {$dc->table} WHERE archive<2" . ((is_array($arrRoot) && count($arrRoot)) ? " AND id IN (".implode(',', $arrRoot).")" : ''))->fetchEach('id');
		
		$GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'] = count($arrRoot) ? $arrRoot : array(0);
	}
	
	
	/**
	 * Archive a database record.
	 *
	 * @access	public
	 * @param	object
	 * @return	void
	 */
	public function archiveRecord($dc)
	{
		$objRecord = $this->Database->execute("SELECT * FROM {$dc->table} WHERE id={$dc->id}");
		
		if ($objRecord->archive > 0)
		{
			$this->Database->execute("UPDATE {$dc->table} SET archive=2 WHERE id={$dc->id}");
			$this->redirect($this->getReferer());
		}
		else
		{
			$this->redirect(str_replace('key=delete', 'act=delete', $this->Environment->request));
		}
	}
}

