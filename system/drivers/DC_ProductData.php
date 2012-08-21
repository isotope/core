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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


require_once(TL_ROOT . '/system/drivers/DC_Table.php');

/**
 * Based on DC_Table from Contao 2.9.2
 */
class DC_ProductData extends DC_Table
{

	/**
	 * True if we are editing a language
	 */
	protected $blnEditLanguage;

	/**
	 * Deferred loading of product data
	 * @var bool
	 */
	protected $blnDeferredLoading = false;

	/**
	 * Array of languages for this product's type
	 * @var array
	 */
	protected $arrLanguages;
	
	/**
	 * Array of language labels
	 * @var array
	 */
	protected $arrLanguageLabels;

	/**
	 * IDs of visible products
	 */
	protected $products = array();


	/**
	 * Initialize the object
	 * @param string
	 */
	public function __construct($strTable)
	{
		$this->import('Environment');

		$this->Environment->request = preg_replace('/&loadDeferredProduct=[^&]*&level=[^&]*/', '', $this->Environment->request);
		$this->Environment->requestUri = preg_replace('/&loadDeferredProduct=[^&]*&level=[^&]*/', '', $this->Environment->requestUri);
		$this->Environment->queryString = preg_replace('/&loadDeferredProduct=[^&]*&level=[^&]*/', '', $this->Environment->queryString);

		parent::__construct($strTable);
	}


	/**
	 * List all records of a particular table
	 * @return string
	 */
	public function showAll()
	{
		$return = '';
		$this->limit = '';
		$this->bid = 'tl_buttons';

		// Clean up old tl_undo and tl_log entries
		if ($this->strTable == 'tl_undo' && strlen($GLOBALS['TL_CONFIG']['undoPeriod']))
		{
			$this->Database->query("DELETE FROM tl_undo WHERE tstamp<".intval(time() - $GLOBALS['TL_CONFIG']['undoPeriod']));
		}

		elseif ($this->strTable == 'tl_log' && strlen($GLOBALS['TL_CONFIG']['logPeriod']))
		{
			$this->Database->query("DELETE FROM tl_log WHERE tstamp<".intval(time() - $GLOBALS['TL_CONFIG']['logPeriod']));
		}

		$this->reviseTable();

		// Add to clipboard
		if ($this->Input->get('act') == 'paste')
		{
			$arrClipboard = $this->Session->get('CLIPBOARD');

			$arrClipboard[$this->strTable] = array
			(
				'id' => $this->Input->get('id'),
				'childs' => $this->Input->get('childs'),
				'mode' => $this->Input->get('mode')
			);

			$this->Session->set('CLIPBOARD', $arrClipboard);
		}

		if ($this->Input->get('table') && $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && $this->Database->fieldExists('pid', $this->strTable))
		{
			$this->procedure[] = 'pid=?';
			$this->values[] = CURRENT_ID;
		}

		// Custom filter
		if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']) && count($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $filter)
			{
				$this->procedure[] = $filter[0];
				$this->values[] = $filter[1];
			}
		}

		$return .= $this->panel();
		$this->fetchProductIds();
		$return .= $this->treeView();

		// Store the current IDs
		$session = $this->Session->getData();
		$session['CURRENT']['IDS'] = $this->current;
		$this->Session->setData($session);

		return $return;
	}


	/**
	 * Insert a new row into a database table
	 * @param array
	 */
	public function create($set=array())
	{
		if (!is_array($set))
		{
			$set = array();
		}

		$set['gid'] = (int) $this->Input->get('gid');

		parent::create($set);
	}


	/**
	 * Assign a new position to an existing record
	 * @param boolean
	 */
	public function cut($blnDoNotRedirect=false)
	{
		// ID and GID tell about paste into a group
		if ($this->intId > 0 && $this->Input->get('gid') != '')
		{
			// Empty clipboard
			$arrClipboard = $this->Session->get('CLIPBOARD');
			$arrClipboard[$this->strTable] = array();
			$this->Session->set('CLIPBOARD', $arrClipboard);

			// Update the record
			$this->Database->prepare("UPDATE {$this->strTable} SET tstamp=?, gid=? WHERE id=?")->execute(time(), $this->Input->get('gid'), $this->intId);

			if (!$blnDoNotRedirect)
			{
				$this->redirect($this->getReferer());
			}

			// Do not call parent function
			return;
		}

		parent::cut($blnDoNotRedirect);
	}


	/**
	 * Move all selected records
	 */
	public function cutAll()
	{
		// GID tells about paste into a group
		if ($this->Input->get('gid') != '')
		{
			$arrClipboard = $this->Session->get('CLIPBOARD');

			if (isset($arrClipboard[$this->strTable]) && is_array($arrClipboard[$this->strTable]['id']))
			{
				foreach ($arrClipboard[$this->strTable]['id'] as $id)
				{
					$this->intId = $id;
					$this->cut(true);
				}
			}

			$this->redirect($this->getReferer());
		}

		return parent::cutAll();
	}


	/**
	 * Duplicate a particular record of the current table
	 * @param boolean
	 */
	public function copy($blnDoNotRedirect=false)
	{
		if ($this->Input->get('gid') != '')
		{
			$this->set['gid'] = (int) $this->Input->get('gid');
		}

		return parent::copy($blnDoNotRedirect);
	}


	/**
	 * Duplicate all child records of a duplicated record
	 * @param string
	 * @param int
	 * @param int
	 * @param int
	 */
	protected function copyChilds($table, $insertID, $id, $parentId)
	{
		$time = time();
		$copy = array();
		$cctable = array();
		$ctable = $GLOBALS['TL_DCA'][$table]['config']['ctable'];

		if (!$GLOBALS['TL_DCA'][$table]['config']['ptable'] && $this->Input->get('childs') != '' && $this->Database->fieldExists('pid', $table))
		{
			$ctable[] = $table;
		}

		if (!is_array($ctable))
		{
			return;
		}

		// Walk through each child table
		foreach ($ctable as $v)
		{
			$this->loadDataContainer($v);
			$cctable[$v] = $GLOBALS['TL_DCA'][$v]['config']['ctable'];

			if (!$GLOBALS['TL_DCA'][$v]['config']['doNotCopyRecords'] && $v != '')
			{
				$objCTable = $this->Database->prepare("SELECT * FROM " . $v . " WHERE pid=?" . ($this->Database->fieldExists('sorting', $v) ? " ORDER BY sorting" : ""))
											->execute($id);

				foreach ($objCTable->fetchAllAssoc() as $row)
				{
					// Exclude the duplicated record itself
					if ($v == $table && $row['id'] == $parentId)
					{
						continue;
					}

					foreach ($row as $kk=>$vv)
					{
						if ($kk == 'id')
						{
							continue;
						}

						// Reset all unique, doNotCopy and fallback fields to their default value
						if ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['unique'] || $GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['doNotCopy'] || $GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['fallback'])
						{
							$vv = $GLOBALS['TL_DCA'][$v]['fields'][$kk]['default'] ? ((is_array($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default'])) ? serialize($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) : $GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) : '';
						}

						$copy[$v][$row['id']][$kk] = $vv;
					}

					$copy[$v][$row['id']]['pid'] = $insertID;
					$copy[$v][$row['id']]['tstamp'] = $time;
				}
			}
		}

		// Duplicate the child records
		foreach ($copy as $k=>$v)
		{
			if (count($v))
			{
				foreach ($v as $kk=>$vv)
				{
					$objInsertStmt = $this->Database->prepare("INSERT INTO " . $k . " %s")
													->set($vv)
													->execute();

					if ($objInsertStmt->affectedRows && (count($cctable[$k]) || $GLOBALS['TL_DCA'][$k]['list']['sorting']['mode'] == 5) && $kk != $parentId)
					{
						$this->copyChilds($k, $objInsertStmt->insertId, $kk, $parentId);
					}
				}
			}
		}
	}


	/**
	 * Move all selected records
	 */
	public function copyAll()
	{
		// GID tells about paste into a group
		if ($this->Input->get('gid') != '')
		{
			$arrClipboard = $this->Session->get('CLIPBOARD');

			if (isset($arrClipboard[$this->strTable]) && is_array($arrClipboard[$this->strTable]['id']))
			{
				$arrIds = array();

				foreach ($arrClipboard[$this->strTable]['id'] as $id)
				{
					$this->intId = $id;
					$arrIds[] = $this->copy(true);
				}

				$this->Database->query("UPDATE {$this->strTable} SET gid=" . (int)$this->Input->get('gid') . " WHERE id IN (" . implode(',', $arrIds) . ")");
			}

			$this->redirect($this->getReferer());
		}

		return parent::copyAll();
	}


	/**
	 * Calculate the new position of a moved or inserted record
	 * @param string
	 * @param integer
	 * @param boolean
	 */
	protected function getNewPosition($mode, $pid=null, $insertInto=false)
	{
		// PID is not set - only valid for duplicated records, as they get the same parent ID as the original record!
		if (is_null($pid) && $this->intId && $mode == 'copy')
		{
			$pid = $this->intId;
		}

		// PID is set (insert after or into the parent record)
		if (is_numeric($pid))
		{
			// Insert the current record into the parent record
			if ($insertInto)
			{
				$this->set['pid'] = $pid;
			}

			// Else insert the current record after the parent record
			elseif ($pid > 0)
			{
				$objParentRecord = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
												  ->limit(1)
												  ->executeUncached($pid);

				if ($objParentRecord->numRows)
				{
					$this->set['pid'] = $objParentRecord->pid;
				}
			}
		}
	}


	/**
	 * Auto-generate a form to edit the current database record
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function edit($intID=false, $ajaxId=false)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			$this->log('Table ' . $this->strTable . ' is not editable', 'DC_ProductData edit()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		if ($intID)
		{
			$this->intId = $intID;
		}

		$return = '';
		$this->values[] = $this->intId;
		$this->procedure[] = 'id=?';
		$this->blnCreateNewVersion = false;
		$this->blnEditLanguage = false;

		// Get the current record
		$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
								 ->limit(1)
								 ->executeUncached($this->intId);

		// Redirect if there is no record with the given ID
		if ($objRow->numRows < 1)
		{
			$this->log('Could not load record ID "'.$this->intId.'" of table "'.$this->strTable.'"!', 'DC_ProductData edit()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		// ID of a language record is not allowed
		elseif ($objRow->language != '')
		{
			$this->log('Cannot edit language record ID "'.$this->intId.'" of table "'.$this->strTable.'"!', 'DC_ProductData edit()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$this->objActiveRecord = $objRow;

		// Load and/or change language
		$arrActiveModules = $this->Config->getActiveModules();

		if (in_array('isotope_multilingual', $arrActiveModules))
		{
			// Add support for i18nl10n extension
			if (in_array('i18nl10n', $arrActiveModules))
			{
				$arrPageLanguages = array_filter(array_unique(deserialize($GLOBALS['TL_CONFIG']['i18nl10n_languages'], true)));
			}
			else
			{
				$arrPageLanguages = $this->Database->execute("SELECT DISTINCT language FROM tl_page")->fetchEach('language');
			}

			$this->arrLanguageLabels = $this->getLanguages();
			$this->arrLanguages = array_intersect(array_keys($this->arrLanguageLabels), $arrPageLanguages);

			if ($this->Input->post('FORM_SUBMIT') == 'tl_language')
			{
				$session = $this->Session->getData();

				if (in_array($this->Input->post('language'), $this->arrLanguages))
				{
					$session['language'][$this->strTable][$this->intId] = $this->Input->post('language');

					if ($this->Input->post('deleteLanguage') != '')
					{
						$this->Database->prepare("DELETE FROM " . $this->strTable . " WHERE pid=? AND language=?")->execute($this->intId, $this->Input->post('language'));
						unset($session['language'][$this->strTable][$this->intId]);
					}
				}
				else
				{
					unset($session['language'][$this->strTable][$this->intId]);
				}

				$this->Session->setData($session);
				$_SESSION['TL_INFO'] = '';
				$this->reload();
			}

			if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] != '' && in_array($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId], $this->arrLanguages))
			{
				$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE pid=? AND language=?")->execute($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);

				if (!$objRow->numRows)
				{
					$intId = $this->Database->prepare("INSERT INTO tl_iso_products (pid,tstamp,language) VALUES (?,?,?)")->execute($this->intId, time(), $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId])->insertId;

					$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")->execute($intId);
				}

				$this->objActiveRecord = $objRow;
				$this->values = array($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);
				$this->procedure = array('pid=?', 'language=?');
				$this->blnEditLanguage = true;
			}
		}

		$this->createInitialVersion($this->strTable, $this->objActiveRecord->id);

		// Change version
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] && $this->Input->post('FORM_SUBMIT') == 'tl_version' && $this->Input->post('version') != '')
		{
			$objData = $this->Database->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? AND version=?")
									  ->limit(1)
									  ->execute($this->strTable, $this->objActiveRecord->id, $this->Input->post('version'));

			if ($objData->numRows)
			{
				$data = deserialize($objData->data);

				if (is_array($data))
				{
					$this->Database->prepare("UPDATE " . $objData->fromTable . " %s WHERE id=?")
								   ->set($data)
								   ->execute($this->objActiveRecord->id);

					$this->Database->prepare("UPDATE tl_version SET active='' WHERE pid=?")
								   ->execute($this->objActiveRecord->id);

					$this->Database->prepare("UPDATE tl_version SET active=1 WHERE pid=? AND version=?")
								   ->execute($this->objActiveRecord->id, $this->Input->post('version'));

					$this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $this->Input->post('version'), $this->objActiveRecord->id, $this->strTable), 'DC_ProductData edit()', TL_GENERAL);

					// Trigger the onrestore_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onrestore_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onrestore_callback'] as $callback)
						{
							if (is_array($callback))
							{
								$this->import($callback[0]);
								$this->$callback[0]->$callback[1]($this->objActiveRecord->id, $this->strTable, $data, $this->Input->post('version'));
							}
						}
					}
				}
			}

			$this->reload();
		}


		// Build an array from boxes and rows
		$this->strPalette = $this->getPalette();
		$boxes = trimsplit(';', $this->strPalette);
		$legends = array();

		if (count($boxes))
		{
			foreach ($boxes as $k=>$v)
			{
				$eCount = 1;
				$boxes[$k] = trimsplit(',', $v);

				foreach ($boxes[$k] as $kk=>$vv)
				{
					if (preg_match('/^\[.*\]$/i', $vv))
					{
						++$eCount;
						continue;
					}

					if (preg_match('/^\{.*\}$/i', $vv))
					{
						$legends[$k] = substr($vv, 1, -1);
						unset($boxes[$k][$kk]);
					}

					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] || !is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]))
					{
						unset($boxes[$k][$kk]);
					}

					elseif ($this->blnEditLanguage && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['attributes']['multilingual'])
					{
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (count($boxes[$k]) < $eCount)
				{
					unset($boxes[$k]);
				}
			}

			$class = 'tl_tbox block';
			$fs = $this->Session->get('fieldset_states');

			// Render boxes
			foreach ($boxes as $k=>$v)
			{
				$strAjax = '';
				$blnAjax = false;
				$legend = '';

				if (isset($legends[$k]))
				{
					list($key, $cls) = explode(':', $legends[$k]);
					$legend = "\n" . '<legend onclick="AjaxRequest.toggleFieldset(this, \'' . $key . '\', \'' . $this->strTable . '\')">' . (isset($GLOBALS['TL_LANG'][$this->strTable][$key]) ? $GLOBALS['TL_LANG'][$this->strTable][$key] : $key) . '</legend>';
				}

				if (!$GLOBALS['TL_CONFIG']['oldBeTheme'])
				{
					if (isset($fs[$this->strTable][$key]))
					{
						$class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
					}
					else
					{
						$class .= (($cls && $legend) ? ' ' . $cls : '');
					}

					$return .= "\n\n" . '<fieldset' . ($key ? ' id="pal_'.$key.'"' : '') . ' class="' . $class . ($legend ? '' : ' nolegend') . '">' . $legend;
				}
				else
				{
					$return .= "\n\n" . '<div class="'.$class.'">';
				}

				// Build rows of the current box
				foreach ($v as $kk=>$vv)
				{
					if ($vv == '[EOF]')
					{
						if ($blnAjax && $this->Environment->isAjaxRequest)
						{
							return $strAjax . '<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'">';
						}

						$blnAjax = false;
						$return .= "\n" . '</div>';

						continue;
					}

					if (preg_match('/^\[.*\]$/i', $vv))
					{
						$thisId = 'sub_' . substr($vv, 1, -1);
						$blnAjax = ($ajaxId == $thisId && $this->Environment->isAjaxRequest) ? true : false;
						$return .= "\n" . '<div id="'.$thisId.'">';

						continue;
					}

					$this->strField = $vv;
					$this->strInputName = $vv;
					$this->varValue = $this->objActiveRecord->$vv;

					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							if (is_array($callback))
							{
								$this->import($callback[0]);
								$this->varValue = $this->$callback[0]->$callback[1]($this->varValue, $this);
							}
						}

						$this->objActiveRecord->{$this->strField} = $this->varValue;
					}

					// Build row
					$blnAjax ? $strAjax .= $this->row() : $return .= $this->row();
				}

				$class = 'tl_box block';

				if (!$GLOBALS['TL_CONFIG']['oldBeTheme'])
				{
					$return .= "\n" . '</fieldset>';
				}
				else
				{
					$return .= "\n" . '</div>';
				}
			}
		}

		$version = '';

		// Check versions
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'])
		{
			$objVersion = $this->Database->prepare("SELECT tstamp, version, username, active FROM tl_version WHERE fromTable=? AND pid=? ORDER BY version DESC")
									     ->execute($this->strTable, $this->objActiveRecord->id);

			if ($objVersion->numRows > 1)
			{
				$versions = '';

				while ($objVersion->next())
				{
					$versions .= '
  <option value="'.$objVersion->version.'"'.($objVersion->active ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['version'].' '.$objVersion->version.' ('.$this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objVersion->tstamp).') '.$objVersion->username.'</option>';
				}

				$version = '<form action="'.ampersand($this->Environment->request, true).'" id="tl_version" class="tl_form" method="post" style="float:right;">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_version">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<select name="version" class="tl_select">'.$versions.'
</select>
<input type="submit" name="showVersion" id="showVersion" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['restore']).'">
</div>
</form>';
			}
		}

		// Check languages
		if (is_array($this->arrLanguages) && !empty($this->arrLanguages))
		{
			$arrAvailableLanguages = $this->Database->prepare("SELECT language FROM " . $this->strTable . " WHERE pid=?")->execute($this->intId)->fetchEach('language');
			$available = '';
			$undefined = '';

			foreach( $this->arrLanguages as $language )
			{
				if (in_array($language, $arrAvailableLanguages))
				{
					if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] == $language)
					{
						$available .= '<option value="' . $language . '" selected="selected">' . $this->arrLanguageLabels[$language] .'</option>';
						$_SESSION['TL_INFO'] = array($GLOBALS['TL_LANG']['MSC']['editingLanguage']);
					}
					else
					{
						$available .= '<option value="' . $language . '">' . $this->arrLanguageLabels[$language] . '</option>';
					}
				}
				else
				{
					$undefined .= '<option value="' . $language . '">' . $this->arrLanguageLabels[$language] . ' ('.$GLOBALS['TL_LANG']['MSC']['undefinedLanguage'].')' . '</option>';
				}
			}

			$version .= '<form action="'.ampersand($this->Environment->request, true).'" id="tl_language" class="tl_form" method="post" style="float:left;margin-left:20px;">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_language">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<select name="language" class="tl_select' . ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] == '' ? '' : ' active') . '">
	<option value="">' . $GLOBALS['TL_LANG']['MSC']['defaultLanguage'] . '</option>'.$available.$undefined.'
</select>
<input type="submit" name="editLanguage" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editLanguage']).'">
<input type="submit" name="deleteLanguage" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteLanguage']).'" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] . '\')">
</div>
</form>';
		}

		if ($version != '')
		{
			$version = '
<div class="tl_version_panel">

'.$version.'
<div class="clear"></div>
</div>';
		}

		// Add some buttons and end the form
		$return .= '
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'">
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'">' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNcreate']).'">' : '') . ($this->Input->get('s2e') ? '
<input type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNedit']).'">' : (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4 || $this->ptable != '' || $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit']) ? '
<input type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNback']).'">' : '')) .'
</div>

</div>
</form>

<script>
window.addEvent(\'domready\', function() {
  var first = $(\''.$this->strTable.'\').getElement(\'input[type="text"]\');
  if (first) first.focus();
});
</script>';

		$copyFallback = $this->blnEditLanguage ? '&nbsp;&nbsp;::&nbsp;&nbsp;<a href="'.$this->addToUrl('act=copyFallback').'" class="header_iso_copy" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['copyFallback']).'" accesskey="d" onclick="Backend.getScrollOffset();">'.($GLOBALS['TL_LANG']['MSC']['copyFallback'] ? $GLOBALS['TL_LANG']['MSC']['copyFallback'] : 'copyFallback').'</a>' : '';

		// Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
		$return = $version . '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' . $copyFallback . '
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], ($this->intId ? 'ID '.$this->intId : '')).'</h2>
'.$this->getMessages().'
<form action="'.ampersand($this->Environment->request, true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"'.(count($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.specialchars($this->strTable).'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return;

		// Reload the page to prevent _POST variables from being sent twice
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
		{
			$arrValues = $this->values;
			array_unshift($arrValues, time());

			// Trigger the onsubmit_callback
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($this);
				}
			}

			// Save the current version
			if ($this->blnCreateNewVersion && $this->Input->post('SUBMIT_TYPE') != 'auto')
			{
				$this->createNewVersion($this->strTable, $this->objActiveRecord->id);

				// Call the onversion_callback
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback']))
				{
					foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback)
					{
						$this->import($callback[0]);
						$this->$callback[0]->$callback[1]($this->strTable, $this->objActiveRecord->id, $this);
					}
				}

				$this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->objActiveRecord->id), 'DC_ProductData edit()', TL_GENERAL);
			}

			// Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
			$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
						   ->execute(time(), $this->activeRecord->id);

			// Redirect
			if (isset($_POST['saveNclose']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$this->redirect($this->getReferer());
			}

			elseif (isset($_POST['saveNedit']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$strUrl = $this->addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href']);

				$strUrl = preg_replace('/(&amp;)?s2e=[^&]*/i', '', $strUrl);
				$strUrl = preg_replace('/(&amp;)?act=[^&]*/i', '', $strUrl);

				$this->redirect($strUrl);
			}

			elseif (isset($_POST['saveNback']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');

				if ($this->ptable == '')
				{
					$this->redirect($this->Environment->script . '?do=' . $this->Input->get('do'));
				}
				elseif ($this->ptable == 'tl_theme' && $this->strTable == 'tl_style_sheet') # TODO: try to abstract this
				{
					$this->redirect($this->getReferer(false, $this->strTable));
				}
				else
				{
					$this->redirect($this->getReferer(false, $this->ptable));
				}
			}

			elseif (isset($_POST['saveNcreate']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$strUrl = $this->Environment->script . '?do=' . $this->Input->get('do');

				if (isset($_GET['table']))
				{
					$strUrl .= '&amp;table=' . $this->Input->get('table');
				}

				// Tree view
				if ($this->treeView)
				{
					$strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
				}

				// Parent view
				elseif ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4)
				{
					$strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->activeRecord->pid : '&amp;act=create&amp;mode=2&amp;pid=' . $this->activeRecord->pid;
				}

				// List view
				else
				{
					$strUrl .= $this->ptable != '' ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';
				}

				$this->redirect($strUrl);
			}

			$this->reload();
		}

		// Set the focus if there is an error
		if ($this->noReload)
		{
			$return .= '

<script>
window.addEvent(\'domready\', function() {
  Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
});
</script>';
		}

		return $return;
	}


	/**
	 * Auto-generate a form to edit all records that are currently shown
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function editAll($intId=false, $ajaxId=false)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			$this->log('Table "'.$this->strTable.'" is not editable', 'DC_Table editAll()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$return = '';
		$this->import('BackendUser', 'User');

		// Get current IDs from session
		$session = $this->Session->getData();
		$ids = $session['CURRENT']['IDS'];

		if ($this->Environment->isAjaxRequest)
		{
			$ids = array($intId);
		}

		// Save field selection in session
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable.'_all' && $this->Input->get('fields'))
		{
			$session['CURRENT'][$this->strTable] = deserialize($this->Input->post('all_fields'));
			$this->Session->setData($session);
		}

		// Add fields
		$fields = $session['CURRENT'][$this->strTable];

		if (is_array($fields) && count($fields) && $this->Input->get('fields'))
		{
			$class = 'tl_tbox block';
			$this->checkForTinyMce();

			// Walk through each record
			foreach ($ids as $id)
			{
				$this->intId = $id;
				$this->procedure = array('id=?');
				$this->values = array($this->intId);
				$this->blnCreateNewVersion = false;
				$this->strPalette = trimsplit('[;,]', $this->getPalette());

				$this->createInitialVersion($this->strTable, $this->intId);

				// Begin current row
				$strAjax = '';
				$blnAjax = false;
				$return .= '
<div class="'.$class.'">';

				$class = 'tl_box block';
				$formFields = array();

				// Get the field values
				$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
										 ->limit(1)
										 ->executeUncached($this->intId);

				// Store the active record
				$this->objActiveRecord = $objRow;

				foreach ($this->strPalette as $v)
				{
					// Check whether field is excluded
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude'])
					{
						continue;
					}

					if ($v == '[EOF]')
					{
						if ($blnAjax && $this->Environment->isAjaxRequest)
						{
							return $strAjax . '<input type="hidden" name="FORM_FIELDS_'.$id.'[]" value="'.specialchars(implode(',', $formFields)).'">';
						}

						$blnAjax = false;
						$return .= "\n  " . '</div>';

						continue;
					}

					if (preg_match('/^\[.*\]$/i', $v))
					{
						$thisId = 'sub_' . substr($v, 1, -1) . '_' . $id;
						$blnAjax = ($ajaxId == $thisId && $this->Environment->isAjaxRequest) ? true : false;
						$return .= "\n  " . '<div id="'.$thisId.'">';

						continue;
					}

					if (!in_array($v, $fields))
					{
						continue;
					}

					$this->strField = $v;
					$this->strInputName = $v.'_'.$this->intId;
					$formFields[] = $v.'_'.$this->intId;

					// Set the default value and try to load the current value from DB
					$this->varValue = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] : '';

					if ($objRow->$v !== false)
					{
						$this->varValue = $objRow->$v;
					}

					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							$this->import($callback[0]);
							$this->varValue = $this->$callback[0]->$callback[1]($this->varValue, $this);
						}
					}

					// Re-set the current value
					$this->objActiveRecord->{$this->strField} = $this->varValue;

					// Build the current row
					$blnAjax ? $strAjax .= $this->row() : $return .= $this->row();
				}

				// Close box
				$return .= '
  <input type="hidden" name="FORM_FIELDS_'.$this->intId.'[]" value="'.specialchars(implode(',', $formFields)).'">
</div>';

				// Save record
				if ($this->Input->post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
				{
					// Call onsubmit_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
						{
							$this->import($callback[0]);
							$this->$callback[0]->$callback[1]($this);
						}
					}

					// Create a new version
					if ($this->blnCreateNewVersion && $this->Input->post('SUBMIT_TYPE') != 'auto')
					{
						$this->createNewVersion($this->strTable, $this->intId);

						// Call the onversion_callback
						if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback']))
						{
							foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback)
							{
								$this->import($callback[0]);
								$this->$callback[0]->$callback[1]($this->strTable, $this->intId, $this);
							}
						}

						$this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table editAll()', TL_GENERAL);
					}

					// Set the current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
					$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
								   ->execute(time(), $this->intId);
				}
			}

			// Add the form
			$return = '

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand($this->Environment->request, true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return.'

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'">
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'">
</div>

</div>
</form>';

			// Set the focus if there is an error
			if ($this->noReload)
			{
				$return .= '

<script>
window.addEvent(\'domready\', function() {
  Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
});
</script>';
			}

			// Reload the page to prevent _POST variables from being sent twice
			if ($this->Input->post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
			{
				if ($this->Input->post('saveNclose'))
				{
					setcookie('BE_PAGE_OFFSET', 0, 0, '/');
					$this->redirect($this->getReferer());
				}

				$this->reload();
			}
		}

		// Else show a form to select the fields
		else
		{
			$options = '';
			$fields = array();

			// Add fields of the current table
			$fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));

			// Show all non-excluded fields
			foreach ($fields as $field)
			{
				if ($field == 'pid' || $field == 'sorting' || (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] != '' || is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback']))))
				{
					$options .= '
  <input type="checkbox" name="all_fields[]" id="all_'.$field.'" class="tl_checkbox" value="'.specialchars($field).'"> <label for="all_'.$field.'" class="tl_checkbox_label">'.($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] != '' ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field][0]).'</label><br>';
				}
			}

			$blnIsError = ($_POST && !count($_POST['all_fields']));

			// Return the select menu
			$return .= '

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand($this->Environment->request, true).'&amp;fields=1" id="'.$this->strTable.'_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'_all">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($blnIsError ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').'

<div class="tl_tbox block">
<fieldset class="tl_checkbox_container">
  <legend'.($blnIsError ? ' class="error"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['all_fields'][0].'</legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6;"><em>'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</em></label><br>'.$options.'
</fieldset>'.($blnIsError ? '
<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['all_fields'].'</p>' : (($GLOBALS['TL_CONFIG']['showHelp'] && $GLOBALS['TL_LANG']['MSC']['all_fields'][1] != '') ? '
<p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['all_fields'][1].'</p>' : '')).'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['continue']).'">
</div>

</div>
</form>';
		}

		// Return
		return '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>'.$return;
	}


	/**
	 * Auto-generate a form to override all records that are currently shown
	 * @author Based on a patch by Andreas Schempp
	 * @return string
	 */
	public function overrideAll()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			$this->log('Table ' . $this->strTable . ' is not editable', 'DC_Table overrideAll()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$return = '';
		$this->import('BackendUser', 'User');

		// Get current IDs from session
		$session = $this->Session->getData();
		$ids = $session['CURRENT']['IDS'];

		// Save field selection in session
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable.'_all' && $this->Input->get('fields'))
		{
			$session['CURRENT'][$this->strTable] = deserialize($this->Input->post('all_fields'));
			$this->Session->setData($session);
		}

		// Add fields
		$fields = $session['CURRENT'][$this->strTable];

		if (is_array($fields) && count($fields) && $this->Input->get('fields'))
		{
			$class = 'tl_tbox block';
			$formFields = array();
			$this->checkForTinyMce();

			// Save record
			if ($this->Input->post('FORM_SUBMIT') == $this->strTable)
			{
				foreach ($ids as $id)
				{
					$this->intId = $id;
					$this->procedure = array('id=?');
					$this->values = array($this->intId);
					$this->blnCreateNewVersion = false;

					$this->createInitialVersion($this->strTable, $this->intId);

					$this->strPalette = trimsplit('[;,]', $this->getPalette());

					// Store all fields
					foreach ($fields as $v)
					{
						// Check whether field is excluded or not in palette
						if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude'] || !in_array($v, $this->strPalette))
						{
							continue;
						}

						$this->strField = $v;
						$this->strInputName = $v;
						$this->varValue = '';

						// Make sure the new value is applied
						$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['alwaysSave'] = true;

						// Store value
						$this->row();
					}

					// Post processing
					if (!$this->noReload)
					{
						// Call onsubmit_callback
						if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
						{
							foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
							{
								$this->import($callback[0]);
								$this->$callback[0]->$callback[1]($this);
							}
						}

						// Create a new version
						if ($this->blnCreateNewVersion)
						{
							$this->createNewVersion($this->strTable, $this->intId);
							$this->log(sprintf('A new version of record ID %s (table %s) has been created', $this->intId, $this->strTable), 'DC_Table editAll()', TL_GENERAL);
						}

						// Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
						$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
									   ->execute(time(), $this->intId);
					}
				}
			}

			// Begin current row
			$return .= '
<div class="'.$class.'">';

			foreach ($fields as $v)
			{
				// Check whether field is excluded
				if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude'])
				{
					continue;
				}

				$formFields[] = $v;

				$this->intId = 0;
				$this->procedure = array('id=?');
				$this->values = array($this->intId);
				$this->strField = $v;
				$this->strInputName = $v;
				$this->varValue = '';

				// Disable auto-submit
				$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['submitOnChange'] = false;
				$return .= $this->row();
			}

			// Close box
			$return .= '
<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars(implode(',', $formFields)).'">
</div>';

			// Add the form
			$return = '

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand($this->Environment->request, true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return.'

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'">
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'">
</div>

</div>
</form>';

			// Set the focus if there is an error
			if ($this->noReload)
			{
				$return .= '

<script>
window.addEvent(\'domready\', function() {
  Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
});
</script>';
			}

			// Reload the page to prevent _POST variables from being sent twice
			if ($this->Input->post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
			{
				if ($this->Input->post('saveNclose'))
				{
					setcookie('BE_PAGE_OFFSET', 0, 0, '/');
					$this->redirect($this->getReferer());
				}

				$this->reload();
			}
		}

		// Else show a form to select the fields
		else
		{
			$options = '';
			$fields = array();

			// Add fields of the current table
			$fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));

			// Add meta fields if the current user is an administrator
			if ($this->User->isAdmin)
			{
				if ($this->Database->fieldExists('sorting', $this->strTable) && !in_array('sorting', $fields))
				{
					array_unshift($fields, 'sorting');
				}

				if ($this->Database->fieldExists('pid', $this->strTable) && !in_array('pid', $fields))
				{
					array_unshift($fields, 'pid');
				}
			}

			// Show all non-excluded fields
			foreach ($fields as $field)
			{
				if ($field == 'pid' || $field == 'sorting' || (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] != '' || is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback']))))
				{
					$options .= '
  <input type="checkbox" name="all_fields[]" id="all_'.$field.'" class="tl_checkbox" value="'.specialchars($field).'"> <label for="all_'.$field.'" class="tl_checkbox_label">'.($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] != '' ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field][0]).'</label><br>';
				}
			}

			$blnIsError = ($_POST && !count($_POST['all_fields']));

			// Return the select menu
			$return .= '

<h2 class="sub_headline_all">'.sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable).'</h2>

<form action="'.ampersand($this->Environment->request, true).'&amp;fields=1" id="'.$this->strTable.'_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'_all">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($blnIsError ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').'

<div class="tl_tbox block">
<fieldset class="tl_checkbox_container">
  <legend'.($blnIsError ? ' class="error"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['all_fields'][0].'</legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6;"><em>'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</em></label><br>'.$options.'
</fieldset>'.($blnIsError ? '
<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['all_fields'].'</p>' : (($GLOBALS['TL_CONFIG']['showHelp'] && $GLOBALS['TL_LANG']['MSC']['all_fields'][1] != '') ? '
<p class="tl_help tl_tip">'.$GLOBALS['TL_LANG']['MSC']['all_fields'][1].'</p>' : '')).'
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['continue']).'">
</div>

</div>
</form>';
		}

		// Return
		return '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>'.$return;
	}


	/**
	 * List all records of the current table as tree and return them as HTML string
	 * @return string
	 */
	protected function treeView()
	{
		// Return if there is no parent table
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] != 5)
		{
			return '<p class="tl_empty">DC_ProductData does only support sorting mode 5!</p>';
		}

		if ($this->Input->get('loadDeferredProduct') > 0)
		{
			$this->intId = (int) $this->Input->get('loadDeferredProduct');
			$level = (int) $this->Input->get('level');
			$this->blnDeferredLoading = true;
			$this->Input->setGet('loadDeferredProduct', null);
			$this->Input->setGet('level', null);

			while(ob_end_clean());
			echo json_encode(array
			(
				'content'	=> $this->ajaxTreeView($this->intId, $level),
				'token'		=> REQUEST_TOKEN,
			));
			exit;
		}

		$table = $this->strTable;
		$treeClass = 'tl_tree tl_productdata';
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['gtable'] != '')
		{
			$gtable = $GLOBALS['TL_DCA'][$this->strTable]['config']['gtable'];
			$treeClass = 'tl_tree_xtnd tl_productdata';

			$this->loadLanguageFile($gtable);
			$this->loadDataContainer($gtable);
		}

		// Return if a mandatory field (id, pid) is missing
		if (!$this->Database->fieldExists('id', $table) || !$this->Database->fieldExists('pid', $table))
		{
			return '<p class="tl_empty">strTable "'.$table.'" can not be shown as tree!</p>';
		}

		// Return if a mandatory field (id, pid) is missing in group table
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['gtable'] != '' && (!$this->Database->fieldExists('id', $gtable) || !$this->Database->fieldExists('pid', $gtable) || !$this->Database->fieldExists('sorting', $gtable)))
		{
			return '<p class="tl_empty">strTable "'.$gtable.'" can not be shown as grouped tree!</p>';
		}

		// Get session data and toggle nodes
		if ($this->Input->get('ptg') == 'all')
		{
			$session = $this->Session->getData();
			$node = $this->strTable.'_tree';

			// Expand tree
			if (!is_array($session[$node]) || count($session[$node]) < 1 || current($session[$node]) != 1)
			{
				$session[$node] = array();

				$objNodes = $this->Database->execute("SELECT DISTINCT pid FROM " . $table . " WHERE pid>0");
				while ($objNodes->next())
				{
					$session[$node][$objNodes->pid] = 1;
				}
			}

			// Collapse tree
			else
			{
				$session[$node] = array();
			}

			$this->Session->setData($session);
			$this->redirect(preg_replace('/(&(amp;)?|\?)(ptg)=[^& ]*/i', '', $this->Environment->request));
		}

		// Get session data and toggle nodes
		if ($this->Input->get('gtg') == 'all')
		{
			$session = $this->Session->getData();
			$node = $this->strTable.'_'.$gtable.'_tree';

			// Expand tree
			if (!is_array($session[$node]) || count($session[$node]) < 1 || current($session[$node]) != 1)
			{
				$session[$node] = array();

				$objNodes = $this->Database->execute("SELECT id FROM " . $gtable);
				while ($objNodes->next())
				{
					$session[$node][$objNodes->id] = 1;
				}
			}

			// Collapse tree
			else
			{
				$session[$node] = array();
			}

			$this->Session->setData($session);
			$this->redirect(preg_replace('/(&(amp;)?|\?)(gtg)=[^& ]*/i', '', $this->Environment->request));
		}

		// Handle overload detection. This variable is only true if the previous rendering was not successful
		if ($this->Session->get('PRODUCTDATA_OVERLOAD'))
		{
			// From now on we defer loading to prevent another overload
			$GLOBALS['TL_CONFIG']['iso_deferProductLoading'] = true;
			$this->Config->add('$GLOBALS[\'TL_CONFIG\'][\'iso_deferProductLoading\']', true);

			// Close all groups
			$session = $this->Session->getData();
			$node = $this->strTable.'_'.$gtable.'_tree';
			$session[$node] = array();
			$this->Session->setData($session);
		}

		$blnClipboard = false;
		$arrClipboard = $this->Session->get('CLIPBOARD');

		// Check clipboard
		if (isset($arrClipboard[$this->strTable]) && count($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		$label = $GLOBALS['TL_DCA'][$table]['config']['label'];
		$icon = $GLOBALS['TL_DCA'][$table]['list']['sorting']['icon'] ? $GLOBALS['TL_DCA'][$table]['list']['sorting']['icon'] : 'pagemounts.gif';
		$label = $this->generateImage($icon).' <label>'.$label.'</label>';

		// Begin buttons container
		$return = '
<div id="tl_buttons">'.(($this->Input->get('act') == 'select') ? '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' : '') . (($this->Input->get('act') != 'select' && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed']) ? '
<a href="'.$this->addToUrl('act=paste&amp;mode=create').'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a>' : '') . (($this->Input->get('act') != 'select') ? $this->generateGlobalButtons() . ($blnClipboard ? ' &nbsp; :: &nbsp; <a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="x">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a>' : '') : '') . '
</div>' . $this->getMessages(true);

		$tree = '';

		// Start the overload detection
		$this->Session->set('PRODUCTDATA_OVERLOAD', true);

		// Call a recursive function that builds the tree including groups
		$this->root = $this->Database->query("SELECT id FROM $gtable WHERE pid=0 ORDER BY sorting")->fetchEach('id');
		for ($i=0, $count=count($this->root); $i<$count; $i++)
		{
			$tree .= $this->generateProductTree($gtable, $this->root[$i], array('p'=>$this->root[($i-1)], 'n'=>$this->root[($i+1)]), -20, ($blnClipboard ? $arrClipboard : false));
		}

		// Generate all products not in a group
		if ($GLOBALS['TL_CONFIG']['iso_deferProductLoading'])
		{
			$root = $this->Database->query("SELECT id FROM $table WHERE pid=0 AND gid=0")->fetchEach('id');
			$this->root = array_values(array_intersect($this->products, $root));
		}
		else
		{
			$this->root = $this->Database->query("SELECT id FROM $table WHERE pid=0 AND gid=0 AND id IN (" . implode(',', $this->products) . ") ORDER BY id=" . implode(' DESC, id=', $this->products) . " DESC")->fetchEach('id');
		}

		for ($i=0, $count=count($this->root); $i<$count; $i++)
		{
			$tree .= $this->generateProductTree($table, $this->root[$i], array('p'=>$this->root[($i-1)], 'n'=>$this->root[($i+1)]), -20, ($blnClipboard ? $arrClipboard : false));
		}

		// Stop the overload detection, everything went smoothly
		$this->Session->set('PRODUCTDATA_OVERLOAD', false);

		// Return if there are no records
		if ($tree == '' && $this->Input->get('act') != 'paste')
		{
			return $return . '
<p class="tl_empty">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

		$return .= (($this->Input->get('act') == 'select') ? '

<form action="'.ampersand($this->Environment->request, true).'" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">' : '').'

<div class="tl_listing_container tree_view" id="tl_listing">'.(isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb']) ? $GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb'] : '').(($this->Input->get('act') == 'select') ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '').'

<ul class="tl_listing ' . $treeClass . '">
  <li class="tl_folder_top"><div class="tl_left">'.$label.'</div> <div class="tl_right">';

		$_buttons = '&nbsp;';

		// Show paste button only if there are no root records specified
		if ($this->Input->get('act') != 'select' && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $blnClipboard && ((!count($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) && $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'] !== false) || $GLOBALS['TL_DCA'][$table]['list']['sorting']['rootPaste']))
		{
			// Call paste_button_callback (&$dc, $row, $table, $cr, $childs, $previous, $next)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][1];

				$this->import($strClass);
				$_buttons = $this->$strClass->$strMethod($this, array('id'=>0), $table, false, $arrClipboard);
			}
			else
			{
				$imagePasteInto = $this->generateImage('pasteinto.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0], 'class="blink"');
				$_buttons = '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid=0'.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][0]).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
			}
		}

		// End table
		$return .= $_buttons . '</div><div style="clear:both;"></div></li>'.$tree.'
</ul>

</div>';

		if ($GLOBALS['TL_CONFIG']['iso_deferProductLoading'])
		{
			$return .= "
<script>
function loadDeferredProducts() {
	var scroll = window.getScroll().y + window.getSize().y;
	$$('.deferred_product').each( function(el) {
		if (scroll - el.getPosition().y > 0)
		{
			el.removeClass('deferred_product');
			var productId = el.get('id').replace('product_', '');
			var level = (el.getParent('ul').get('class').match(/level_/) ? el.getParent('ul').get('class').replace('level_', '').toInt() : -1);
			new Request.Contao({
				method: 'get',
				url: (window.location.href+'&loadDeferredProduct='+productId+'&level='+level),
				onComplete: function(html, text) {
					var temp = new Element('div').set('html', html);
					temp.getChildren().each( function(li) { li.inject(el.getParent('li'), 'before') });
					el.getParent('li').destroy();
					window.fireEvent('structure');
				}
			}).send();
		}
	});
}
$(window).addEvent('scroll', loadDeferredProducts).addEvent('domready', loadDeferredProducts).addEvent('ajax_change', loadDeferredProducts);
</script>";
		}

		// Close form
		if ($this->Input->get('act') == 'select')
		{
			$return .= '

<div class="tl_formbody_submit" style="text-align:right;">

<div class="tl_submit_container">' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ? '
  <input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\');" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'"> ' : '') . '
  <input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['moveSelected']).'">
  <input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['copySelected']).'"> ' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']).'">
  <input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'"> ' : '') . '
</div>

</div>
</div>
</form>';
		}

		return $return;
	}


	/**
	 * Generate a particular subpart of the tree and return it as HTML string
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function ajaxTreeView($id, $level)
	{
		if (!$this->Environment->isAjaxRequest && !$this->blnDeferredLoading)
		{
			return '';
		}

		$this->panel();
		$this->fetchProductIds();

		$return = '';
		$table = $this->strTable;
		$blnPtable = false;
		$margin = ($level * 20);

		$blnClipboard = false;
		$arrClipboard = $this->Session->get('CLIPBOARD');

		// Check clipboard
		if (isset($arrClipboard[$this->strTable]) && count($arrClipboard[$this->strTable]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$this->strTable];
		}

		// Load single product
		if ($this->blnDeferredLoading)
		{
			$blnPtable = true;

			return ' ' . trim($this->generateProductTree($this->strTable, $id, array(), $margin, ($blnClipboard ? $arrClipboard : false), ($id == $arrClipboard ['id'] || (is_array($arrClipboard ['id']) && in_array($id, $arrClipboard ['id'])) || (!$blnPtable && !is_array($arrClipboard['id']) && in_array($id, $this->getChildRecords($arrClipboard['id'], $table))))));
		}

		// Load groups and products
		elseif ($GLOBALS['TL_DCA'][$this->strTable]['config']['gtable'] != '' && $this->Input->post('id') != ($table.'_tree_'.$id))
		{
			$table = $GLOBALS['TL_DCA'][$this->strTable]['config']['gtable'];

			$this->loadLanguageFile($table);
			$this->loadDataContainer($table);

			$blnPtable = true;

			// Load products in the current group
			$this->root = $this->Database->query("SELECT id FROM {$this->strTable} WHERE pid=0 AND gid=$id")->fetchEach('id');
			for ($i=0; $i<count($this->root); $i++)
			{
				$return .= ' ' . trim($this->generateProductTree($this->strTable, $this->root[$i], array('p'=>$this->root[($i-1)], 'n'=>$this->root[($i+1)]), $margin, ($blnClipboard ? $arrClipboard : false), ($id == $arrClipboard['id'] || (is_array($arrClipboard ['id']) && in_array($id, $arrClipboard ['id'])) || (!$blnPtable && !is_array($arrClipboard['id']) && in_array($id, $this->getChildRecords($arrClipboard['id'], $table))))));
			}

			// Load subgroups in the current group
			$this->root = $this->Database->query("SELECT id FROM $table WHERE pid=$id ORDER BY sorting")->fetchEach('id');
			for ($i=0; $i<count($this->root); $i++)
			{
				$return .= ' ' . trim($this->generateProductTree($table, $this->root[$i], array('p'=>$this->root[($i-1)], 'n'=>$this->root[($i+1)]), $margin, ($blnClipboard ? $arrClipboard : false)));
			}
		}

		// Load variant
		else
		{
			$this->root = $this->Database->query("SELECT id FROM {$this->strTable} WHERE pid=$id AND gid=0")->fetchEach('id');
			for ($i=0; $i<count($this->root); $i++)
			{
				$return .= ' ' . trim($this->generateProductTree($this->strTable, $this->root[$i], array('p'=>$this->root[($i-1)], 'n'=>$this->root[($i+1)]), $margin, ($blnClipboard ? $arrClipboard : false), ($id == $arrClipboard['id'] || (is_array($arrClipboard ['id']) && in_array($id, $arrClipboard ['id'])) || (!$blnPtable && !is_array($arrClipboard['id']) && in_array($id, $this->getChildRecords($arrClipboard['id'], $table))))));
			}
		}

		return $return;
	}


	/**
	 * Recursively generate the tree and return it as HTML string
	 * @param string
	 * @param integer
	 * @param array
	 * @param boolean
	 * @param integer
	 * @param array
	 * @param boolean
	 * @param boolean
	 * @return string
	 */
	protected function generateProductTree($table, $id, $arrPrevNext, $intMargin=0, $arrClipboard=false, $blnCircularReference=false)
	{
		// Only list products & variants matched by the search & filters
		if ($table == $this->strTable && !in_array($id, $this->products))
		{
			return '';
		}

		static $session;

		$session = $this->Session->getData();
		$node = ($this->strTable != $table) ? $this->strTable.'_'.$table.'_tree' : $this->strTable.'_tree';
		$toggle = ($this->strTable != $table) ? 'gtg' : 'ptg';

		// Toggle nodes
		if ($this->Input->get($toggle))
		{
			$session[$node][$this->Input->get($toggle)] = (isset($session[$node][$this->Input->get($toggle)]) && $session[$node][$this->Input->get($toggle)] == 1) ? 0 : 1;
			$this->Session->setData($session);

			$this->redirect(preg_replace('/(&(amp;)?|\?)'.$toggle.'=[^& ]*/i', '', $this->Environment->request));
		}

		$intSpacing = 20;
		$return = "\n  " . '<li class="'.(($table != $this->strTable) ? 'tl_folder' : 'tl_file').'" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';;

		$session[$node][$id] = (is_int($session[$node][$id])) ? $session[$node][$id] : 0;

		if ($GLOBALS['TL_CONFIG']['iso_deferProductLoading'] && $table == $this->strTable && !$this->Environment->isAjaxRequest)
		{
			return $return . '<div class="iso_product deferred_product" id="product_' . $id . '"><div class="thumbnail"><img src="system/themes/default/images/loading.gif" alt=""></div><p>&nbsp;</p></div></div></li>';
		}

		$objRow = $this->Database->query("SELECT * FROM " . $table . " WHERE id=" . (int)$id);

		// Return if there is no result
		if ($objRow->numRows < 1)
		{
			return '';
		}

		// Add the ID to the list of current IDs
		if ($this->strTable == $table)
		{
			$this->current[] = $objRow->id;
		}

		// Check whether there are child records
		if ($GLOBALS['TL_CONFIG']['iso_deferProductLoading'] && $this->strTable == $table)
		{
			$childs = $this->Database->query("SELECT id FROM " . $table . " WHERE pid=$id AND language=''")->fetchEach('id');
			$childs = array_values(array_intersect($this->products, $childs));
		}
		else
		{
			$objChilds = $this->Database->query("SELECT id FROM " . $table . " WHERE pid=$id" . ($this->strTable == $table ? " AND language='' AND id IN (" . implode(',', $this->products) . ") ORDER BY id=" . implode(' DESC, id=', $this->products) . " DESC" : " ORDER BY sorting"));

			if ($objChilds->numRows)
			{
				$childs = $objChilds->fetchEach('id');
			}
		}

		// Check wether there are group child records
		if ($table != $this->strTable)
		{
			if ($GLOBALS['TL_CONFIG']['iso_deferProductLoading'])
			{
				$gchilds = $this->Database->query("SELECT id FROM " . $this->strTable . " WHERE gid=$id")->fetchEach('id');
				$gchilds = array_values(array_intersect($this->products, $gchilds));

				if (empty($gchilds) && empty($childs) && $arrClipboard === false)
				{
					return '';
				}
			}
			else
			{
				$objChilds = $this->Database->query("SELECT id FROM " . $this->strTable . " WHERE gid=$id AND id IN (" . implode(',', $this->products) . ") ORDER BY id=" . implode(' DESC, id=', $this->products) . " DESC");

				if ($objChilds->numRows)
				{
					$gchilds = $objChilds->fetchEach('id');
				}
				elseif (empty($childs) && $arrClipboard === false)
				{
					return '';
				}
			}
		}

		// Calculate label and add a toggle button
		$args = array();
		$folderAttribute = 'style="margin-left:20px;"';
		$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];
		$level = ($intMargin / $intSpacing + 1);

		if (!empty($childs) || !empty($gchilds))
		{
			$folderAttribute = '';
			$img = ($session[$node][$id] == 1) ? 'folMinus.gif' : 'folPlus.gif';
			$alt = ($session[$node][$id] == 1) ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
			$return .= '<a href="'.$this->addToUrl($toggle.'='.$id).'" title="'.specialchars($alt).'" onclick="Backend.getScrollOffset(); AjaxRequest.toggleStructure(this, \''.$node.'_'.$id.'\', '.$level.', '.$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'].'); window.fireEvent(\'ajax_change\'); return false">'.$this->generateImage($img, '', 'style="margin-right:2px;"').'</a>';
		}

		foreach ($showFields as $k=>$v)
		{
			// Decrypt the value
			if ($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['encrypt'])
			{
				$objRow->$v = deserialize($objRow->$v);

				$this->import('Encryption');
				$objRow->$v = $this->Encryption->decrypt($objRow->$v);
			}

			if (strpos($v, ':') !== false)
			{
				list($strKey, $strTable) = explode(':', $v);
				list($strTable, $strField) = explode('.', $strTable);

				$objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
										 ->limit(1)
										 ->execute($objRow->$strKey);

				$args[$k] = $objRef->numRows ? $objRef->$strField : '';
			}
			elseif (in_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
			{
				$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objRow->$v);
			}
			elseif ($GLOBALS['TL_DCA'][$table]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['multiple'])
			{
				$args[$k] = $objRow->$v != '' ? ($GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] != '' ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : $v) : '';
			}
			else
			{
				$args[$k] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$objRow->$v] != '' ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$objRow->$v] : $objRow->$v;
			}
		}

		$label = vsprintf(($GLOBALS['TL_DCA'][$table]['list']['label']['format'] != '' ? $GLOBALS['TL_DCA'][$table]['list']['label']['format'] : '%s'), $args);

		// Shorten label it if it is too long
		if ($GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
		{
			$this->import('String');
			$label = trim($this->String->substrHtml($label, $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'])) . ' …';
		}

		$label = preg_replace('/\(\) ?|\[\] ?|\{\} ?|<> ?/i', '', $label);

		// Call label_callback ($row, $label, $this)
		if (is_array($GLOBALS['TL_DCA'][$table]['list']['label']['label_callback']))
		{
			$strClass = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][0];
			$strMethod = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][1];

			$this->import($strClass);
			$return .= $this->$strClass->$strMethod($objRow->row(), $label, $this, $folderAttribute);
		}
		else
		{
			$return .= $this->generateImage('system/modules/isotope/html/folder-network.png', '', $folderAttribute) . ' ' . $label;
		}

		$return .= '</div> <div class="tl_right">';
		$previous = ($GLOBALS['TL_DCA'][$this->strTable]['config']['gtable'] == $table) ? $arrPrevNext['pp'] : $arrPrevNext['p'];
		$next = ($GLOBALS['TL_DCA'][$this->strTable]['config']['gtable'] == $table) ? $arrPrevNext['nn'] : $arrPrevNext['n'];
		$_buttons = '';

		if ($this->strTable == $table)
		{
			// Regular buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
			$_buttons .= $this->Input->get('act') == 'select' ? ($objRow->pid == 0 ? '<input type="checkbox" name="IDS[]" id="ids_'.$id.'" class="tl_tree_checkbox" value="'.$id.'">' : '') : $this->generateButtons($objRow->row(), $table, $this->root, $blnCircularReference, $childs, $previous, $next);
		}

		// Paste buttons
		if ($arrClipboard !== false && $this->Input->get('act') != 'select')
		{
			$_buttons .= ' ';

			// Call paste_button_callback(&$dc, $row, $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][1];

				$this->import($strClass);
				$_buttons .= $this->$strClass->$strMethod($this, $objRow->row(), $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next);
			}
		}

		$return .= ($_buttons != '' ? $_buttons : '&nbsp;') . '</div><div style="clear:both;"></div></li>';
		$group = '';
		$rows = '';

		// Add records of the table itself
		if ($table != $this->strTable && count($gchilds) && $session[$node][$id] == 1)
		{
			for ($j=0; $j<count($gchilds); $j++)
			{
				$group .= $this->generateProductTree($this->strTable, $gchilds[$j], array('pp'=>$gchilds[($j-1)], 'nn'=>$gchilds[($j+1)]), ($intMargin + $intSpacing), $arrClipboard, false, ($j<(count($gchilds)-1) || count($childs)));
			}
		}

		// Add records of the parent table
		if ($session[$node][$id] == 1)
		{
			if (is_array($childs))
			{
				for ($k=0; $k<count($childs); $k++)
				{
					$rows .= $this->generateProductTree($table, $childs[$k], array('p'=>$childs[($k-1)], 'n'=>$childs[($k+1)]), ($intMargin + $intSpacing), $arrClipboard, ((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $childs[$k] == $arrClipboard['id']) || $blnCircularReference) ? true : false));
				}
			}
		}



		// Begin new submenu
		if (($group != '' || $rows != '') && $session[$node][$id] == 1)
		{
			$group = '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">' . $group . $rows;
		}

		// Close submenu
		if ($group != '' && $session[$node][$id] == 1)
		{
			$group .= '</ul></li>';
		}

		if ($group == '' && $table != $this->strTable && !count($childs) && !count($gchilds) && $arrClipboard === false)
		{
			return '';
		}

		$this->Session->setData($session);
		return $return . $group;
	}


	/**
	 * Build the sort panel and return it as string
	 * @return string
	 */
	protected function panel()
	{
		$filter = $this->filterMenu();
		$search = $this->searchMenu();
		$sort = $this->sortMenu();

		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'] == '' || ($filter == '' && $search == '' && $sort == ''))
		{
			return '';
		}

		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
		{
			$this->reload();
		}

		$return = '';
		$panelLayout = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'];
		$arrPanels = trimsplit(';', $panelLayout);
		$intLast = count($arrPanels) - 1;

		for ($i=0; $i<count($arrPanels); $i++)
		{
			$panels = '';
			$submit = '';
			$arrSubPanels = trimsplit(',', $arrPanels[$i]);

			foreach ($arrSubPanels as $strSubPanel)
			{
				if (strlen($$strSubPanel))
				{
					$panels = $$strSubPanel . $panels;
				}
			}

			if ($i == $intLast)
			{
				$submit = '

<div class="tl_submit_panel tl_subpanel">
<input type="image" name="filter" id="filter" src="' . TL_FILES_URL . 'system/themes/' . $this->getTheme() . '/images/reload.gif" class="tl_img_submit" title="' . $GLOBALS['TL_LANG']['MSC']['apply'] . '" alt="' . $GLOBALS['TL_LANG']['MSC']['apply'] . '">
</div>';
			}

			if ($panels != '')
			{
				$return .= '
<div class="tl_panel">'.$submit.$panels.'

<div class="clear"></div>

</div>';
			}
		}

		$return = '
<form action="'.ampersand($this->Environment->request, true).'" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_filters">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
' . $return . '
</div>
</form>
';

		return $return;
	}


	/**
	 * Return a select menu that allows to sort results by a particular field
	 * @return string
	 */
	protected function sortMenu()
	{
		$sortingFields = array();

		// Get sorting fields
		foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
		{
			if ($v['sorting'])
			{
				$sortingFields[] = $k;
			}
		}

		// Return if there are no sorting fields
		if (!count($sortingFields))
		{
			return '';
		}

		$this->bid = 'tl_buttons_a';
		$session = $this->Session->getData();
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

		// Set sorting from user input
		if ($this->Input->post('FORM_SUBMIT') == 'tl_filters')
		{
			$session['sorting'][$this->strTable] = in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->Input->post('tl_sort')]['flag'], array(2, 4, 6, 8, 10, 12)) ? $this->Input->post('tl_sort').' DESC' : $this->Input->post('tl_sort');
			$this->Session->setData($session);
		}

		// Overwrite the "orderBy" value with the session value
		elseif ($session['sorting'][$this->strTable] != '')
		{
			$overwrite = preg_quote(preg_replace('/\s+.*$/i', '', $session['sorting'][$this->strTable]), '/');
			$orderBy = array_diff($orderBy, preg_grep('/^'.$overwrite.'/i', $orderBy));

			array_unshift($orderBy, $session['sorting'][$this->strTable]);

			$this->firstOrderBy = $overwrite;
			$this->orderBy = $orderBy;
		}

		$options_sorter = array();

		// Sorting fields
		foreach ($sortingFields as $field)
		{
			$options_label = strlen(($lbl = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'])) ? $lbl : $GLOBALS['TL_LANG']['MSC'][$field];

			if (is_array($options_label))
			{
				$options_label = $options_label[0];
			}

			$options_sorter[$options_label] = '  <option value="'.specialchars($field).'"'.(($session['sorting'][$this->strTable] == '' && $field == $firstOrderBy || $field == str_replace(' DESC', '', $session['sorting'][$this->strTable])) ? ' selected="selected"' : '').'>'.$options_label.'</option>';
		}

		// Sort by option values
		uksort($options_sorter, 'strcasecmp');

		return '

<div class="tl_sorting tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['sortBy'] . ':</strong>
<select name="tl_sort" id="tl_sort" class="tl_select">
'.implode("\n", $options_sorter).'
</select>
</div>';
	}


	/**
	 * Copy multilingual fields from fallback to current language
	 */
	public function copyFallback()
	{
		$session = $this->Session->getData();

		$strLanguage = $session['language'][$this->strTable][$this->intId];
		$this->strPalette = trimsplit('[;,]', $this->getPalette());

		$arrDuplicate = array();

		foreach( $this->strPalette as $field )
		{
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]) && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['attributes']['multilingual'])
			{
				$arrDuplicate[] = $field;
			}
		}

		if (count($arrDuplicate))
		{
			$intLanguageId = $this->Database->execute("SELECT id FROM {$this->strTable} WHERE pid={$this->intId} AND language='$strLanguage'")->id;

			$this->createInitialVersion($this->strTable, $intLanguageId);

			$arrRow = $this->Database->execute("SELECT " . implode(',', $arrDuplicate) . " FROM {$this->strTable} WHERE id={$this->intId}")->fetchAssoc();
			$this->Database->prepare("UPDATE {$this->strTable} %s WHERE id=$intLanguageId")->set($arrRow)->executeUncached();

			$this->createNewVersion($this->strTable, $intLanguageId);
			$this->log(sprintf('A new version of record ID %s (table %s) has been created', $intLanguageId, $this->strTable), 'DC_ProductData copyFallback()', TL_GENERAL);
		}

		$this->redirect($this->addToUrl('act=edit'));
	}


	/**
	 * Query available product IDs
	 */
	private function fetchProductIds()
	{
		$arrProducts = array();
		$arrVariants = array();

		// No products available
		if (!is_array($this->root) || !count($this->root))
		{
			$this->root = array(0);
		}

		// Get root IDs matching search & filters
		$query = "SELECT id FROM {$this->strTable} p1 WHERE id IN(" . implode(',', array_map('intval', $this->root)) . ")";

		if (count($this->procedure))
		{
			$query .= ' AND ' . implode(' AND ', $this->procedure);
		}

		$objIds = $this->Database->prepare($query)->execute($this->values);

		if ($objIds->numRows)
		{
			$arrProducts = $objIds->fetchEach('id');
		}


		// Get variant IDs matching search & filters
		$query = "SELECT id, language, pid AS pid1, (SELECT pid FROM {$this->strTable} WHERE id=p1.pid) AS pid2 FROM {$this->strTable} p1 WHERE pid>0";

		if (count($this->procedure))
		{
			$query .= " AND " . implode(' AND ', $this->procedure);
		}

		$objChilds = $this->Database->prepare($query)->execute($this->values);

		while( $objChilds->next() )
		{
			if ($objChilds->pid2 > 0)
			{
				// Skip this variant because it is not allowed (not in root IDs)
				if (!in_array($objChilds->pid2, $this->root))
				{
					continue;
				}
				elseif (!in_array($objChilds->pid2, $arrProducts))
				{
					$arrProducts[] = $objChilds->pid2;
				}

				$arrVariants[] = $objChilds->pid2;
			}

			// Skip this variant because it is not allowed (not in root IDs)
			elseif (!in_array($objChilds->pid1, $this->root))
			{
				continue;
			}

			elseif (!in_array($objChilds->pid1, $arrProducts))
			{
				$arrProducts[] = $objChilds->pid1;
			}

			$arrVariants[] = $objChilds->pid1;

			if ($objChilds->language == '')
			{
				$arrVariants[] = $objChilds->id;
			}
		}


		// Fetch all variants of matching products
		$arrMissing = array_diff($arrProducts, $arrVariants);
		if (count($arrMissing) > 0)
		{
			$objChilds = $this->Database->execute("SELECT id, pid AS pid1, (SELECT pid FROM {$this->strTable} WHERE id=p1.pid) AS pid2 FROM {$this->strTable} p1 HAVING pid1 IN (" . implode(',', $arrMissing) . ") OR pid2 IN (" . implode(',', $arrMissing) . ")");

			$arrVariants = array_merge($arrVariants, $objChilds->fetchEach('id'), $objChilds->fetchEach('pid1'), $objChilds->fetchEach('pid2'));
		}


		$this->products = array_unique(array_merge($arrProducts, $arrVariants));

		// Order IDs by session value
		if (is_array($this->orderBy) && $this->orderBy[0] != '' && !empty($this->products))
		{
			$query = "SELECT id FROM {$this->strTable} WHERE id IN (" . implode(',', $this->products) . ")";
			$orderBy = $this->orderBy;

			foreach ($orderBy as $k=>$v)
			{
				if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['findInSet'])
				{
					$keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'];

					if (array_is_assoc($keys))
					{
						$keys = array_keys($keys);
					}

					$orderBy[$k] = "FIND_IN_SET(" . $v . ", '" . implode(',', $keys) . "')";
				}
			}

			$query .= " ORDER BY " . implode(', ', $orderBy);

			if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0)
			{
				$query .= " DESC";
			}

			$objIds = $this->Database->query($query);

			if ($objIds->numRows)
			{
				$this->products = $objIds->fetchEach('id');
			}
		}

		if (!count($this->products))
		{
			$this->products = array(0);
		}
	}
}

