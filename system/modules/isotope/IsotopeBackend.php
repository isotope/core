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
	 * Disable the edit button for archived records
	 */
	public function disableArchivedRecord($row, $href, $label, $title, $icon, $attributes)
	{
		return $row['archive'] == 0 ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
	}
	
	
	/**
	 * Hide archived records.
	 */
	public function hideArchivedRecords($dc)
	{
		$arrRoot = $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'];
		
		$arrRoot = $this->Database->execute("SELECT id FROM {$dc->table} WHERE archive<2" . ((is_array($arrRoot) && count($arrRoot)) ? " AND id IN (".implode(',', $arrRoot).")" : ''))->fetchEach('id');
		
		$GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'] = count($arrRoot) ? $arrRoot : array(0);
		
		if ($this->Input->get('act') == 'edit')
		{
			$objRecord = $this->Database->execute("SELECT * FROM {$dc->table} WHERE id={$dc->id}");
			
			if ($objRecord->numRows && $objRecord->archive > 0)
			{
				$GLOBALS['TL_DCA'][$dc->table]['config']['notEditable'] = true;
			}
		}
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
	
	
	/**
	 * Format value (based on DC_Table::show(), Contao 2.9.0)
	 * @param  mixed
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatValue($table, $field, $value)
	{
		$value = deserialize($value);
	
		// Get field value
		if (strlen($GLOBALS['TL_DCA'][$table]['fields'][$field]['foreignKey']))
		{
			$temp = array();
			$chunks = explode('.', $GLOBALS['TL_DCA'][$table]['fields'][$field]['foreignKey']);

			foreach ((array) $value as $v)
			{
				$objKey = $this->Database->prepare("SELECT " . $chunks[1] . " AS value FROM " . $chunks[0] . " WHERE id=?")
										 ->limit(1)
										 ->execute($v);

				if ($objKey->numRows)
				{
					$temp[] = $objKey->value;
				}
			}

			return implode(', ', $temp);
		}

		elseif (is_array($value))
		{
			foreach ($value as $kk=>$vv)
			{
				if (is_array($vv))
				{
					$vals = array_values($vv);
					$value[$kk] = $vals[0].' ('.$vals[1].')';
				}
			}

			return implode(', ', $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['rgxp'] == 'date')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['rgxp'] == 'time')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['rgxp'] == 'datim' || in_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['flag'], array(5, 6, 7, 8, 9, 10)) || $field == 'tstamp')
		{
			return $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $value);
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['multiple'])
		{
			return strlen($value) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
		}

		elseif ($GLOBALS['TL_DCA'][$table]['fields'][$field]['inputType'] == 'textarea' && ($GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['allowHtml'] || $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['preserveTags']))
		{
			return specialchars($value);
		}

		elseif (is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['reference']))
		{
			return isset($GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value]) ? ((is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value])) ? $GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value][0] : $GLOBALS['TL_DCA'][$table]['fields'][$field]['reference'][$value]) : $value;
		}
		
		return $value;
	}
	
	
	/**
	 * Format label (based on DC_Table::show(), Contao 2.9.0)
	 * @param  mixed
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public function formatLabel($table, $field)
	{
		// Label
		if (count($GLOBALS['TL_DCA'][$table]['fields'][$field]['label']))
		{
			$label = is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$table]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$table]['fields'][$field]['label'];
		}

		else
		{
			$label = is_array($GLOBALS['TL_LANG']['MSC'][$field]) ? $GLOBALS['TL_LANG']['MSC'][$field][0] : $GLOBALS['TL_LANG']['MSC'][$field];
		}

		if (!strlen($label))
		{
			$label = $field;
		}
		
		return $label;
	}
}

