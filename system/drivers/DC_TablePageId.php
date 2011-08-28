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


require_once(TL_ROOT . '/system/drivers/DC_Table.php');

class DC_TablePageId extends DC_Table
{

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
			$this->Database->prepare("DELETE FROM tl_undo WHERE tstamp<?")
						   ->execute(intval(time() - $GLOBALS['TL_CONFIG']['undoPeriod']));
		}

		elseif ($this->strTable == 'tl_log' && strlen($GLOBALS['TL_CONFIG']['logPeriod']))
		{
			$this->Database->prepare("DELETE FROM tl_log WHERE tstamp<?")
						   ->execute(intval(time() - $GLOBALS['TL_CONFIG']['logPeriod']));
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

		if ($this->treeView)
		{
			$return .= $this->treeView();
		}

		else
		{
			if ($this->Input->get('table') && $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && $this->Database->fieldExists('page_id', $this->strTable))
			{
				$this->procedure[] = 'page_id=?';
				$this->values[] = CURRENT_ID;
			}

			$return .= $this->panel();
			$return .= ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->parentView() : $this->listView();

			// Add another panel at the end of the page
			if (strpos($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'], 'limit') !== false && ($strLimit = $this->limitMenu(true)) != false)
			{
				$return .= '

<form action="'.ampersand($this->Environment->request, true).'" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_filters_limit" />

<div class="tl_panel_bottom">

<div class="tl_submit_panel tl_subpanel">
<input type="image" name="btfilter" id="btfilter" src="system/themes/' . $this->getTheme() . '/images/ok.gif" class="tl_img_submit" alt="apply changes" value="apply changes" />
</div>' . $strLimit . '

<div class="clear"></div>

</div>

</div>
</form>
';
			}
		}

		// Store the current IDs
		$session = $this->Session->getData();
		$session['CURRENT']['IDS'] = $this->current;
		$this->Session->setData($session);

		return $return;
	}



	/**
	 * Assign a new position to an existing record
	 * @param boolean
	 */
	public function cut($blnDoNotRedirect=false)
	{
		$cr = array();

		// ID and page_id are mandatory
		if (!$this->intId || !strlen($this->Input->get('page_id')))
		{
			$this->redirect($this->getReferer());
		}

		// Get the new position
		$this->getNewPosition('cut', $this->Input->get('page_id'), ($this->Input->get('mode') == '2' ? true : false));

		// Avoid circular references when there is no parent table
		if ($this->Database->fieldExists('page_id', $this->strTable) && !strlen($this->ptable))
		{
			$cr = $this->getChildRecords($this->intId, $this->strTable);
			$cr[] = $this->intId;
		}

		// Empty clipboard
		$arrClipboard = $this->Session->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$this->Session->set('CLIPBOARD', $arrClipboard);

		// Update the record
		if (in_array($this->set['page_id'], $cr))
		{
			$this->log('Attempt to relate record "'.$this->intId.'" of table "'.$this->strTable.'" to its child record "'.$this->Input->get('page_id').'" (circular reference)', 'DC_Table cut()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		$this->set['tstamp'] = time();

		$this->Database->prepare("UPDATE " . $this->strTable . " %s WHERE id=?")
					   ->set($this->set)
					   ->execute($this->intId);

		if (!$blnDoNotRedirect)
		{
			$this->redirect($this->getReferer());
		}
	}


	/**
	 * Move all selected records
	 */
	public function cutAll()
	{
		// page_id is mandatory
		if (!strlen($this->Input->get('page_id')))
		{
			$this->redirect($this->getReferer());
		}

		$arrClipboard = $this->Session->get('CLIPBOARD');

		if (isset($arrClipboard[$this->strTable]) && is_array($arrClipboard[$this->strTable]['id']))
		{
			foreach ($arrClipboard[$this->strTable]['id'] as $id)
			{
				$this->intId = $id;
				$this->cut(true);
				$this->Input->setGet('page_id', $id);
				$this->Input->setGet('mode', 1);
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Calculate the new position of a moved or inserted record
	 * @param string
	 * @param integer
	 * @param boolean
	 */
	protected function getNewPosition($mode, $page_id=null, $insertInto=false)
	{
		// If there is page_id and sorting
		if ($this->Database->fieldExists('page_id', $this->strTable) && $this->Database->fieldExists('sorting', $this->strTable))
		{
			// page_id is not set - only valid for duplicated records, as they get the same parent ID as the original record!
			if (is_null($page_id) && $this->intId && $mode == 'copy')
			{
				$page_id = $this->intId;
			}

			// page_id is set (insert after or into the parent record)
			if (is_numeric($page_id))
			{
				// Insert the current record at the beginning when inserting into the parent record
				if ($insertInto)
				{
					$newpage_id = $page_id;
					$objSorting = $this->Database->prepare("SELECT MIN(sorting) AS sorting FROM " . $this->strTable . " WHERE page_id=?")
												 ->execute($page_id);

					// Select sorting value of the first record
					if ($objSorting->numRows)
					{
						$curSorting = $objSorting->sorting;

						// Resort if the new sorting value is not an integer or smaller than 1
						if (($curSorting % 2) != 0 || $curSorting < 1)
						{
							$objNewSorting = $this->Database->prepare("SELECT id, sorting FROM " . $this->strTable . " WHERE page_id=? ORDER BY sorting" )
															->execute($page_id);

							$count = 2;
							$newSorting = 128;

							while ($objNewSorting->next())
							{
								$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
											   ->limit(1)
											   ->execute(($count++*128), $objNewSorting->id);
							}
						}

						// Else new sorting = (current sorting / 2)
						else $newSorting = ($curSorting / 2);
					}

					// Else new sorting = 128
					else $newSorting = 128;
				}

				// Else insert the current record after the parent record
				elseif ($page_id > 0)
				{
					$objSorting = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
												 ->limit(1)
												 ->execute($page_id);

					// Set parent ID of the current record as new parent ID
					if ($objSorting->numRows)
					{
						$newpage_id = $objSorting->page_id;
						$curSorting = $objSorting->sorting;

						// Do not proceed without a parent ID
						if (is_numeric($newpage_id))
						{
							$objNextSorting = $this->Database->prepare("SELECT MIN(sorting) AS sorting FROM " . $this->strTable . " WHERE page_id=? AND sorting>?")
											  				 ->execute($newpage_id, $curSorting);

							// Select sorting value of the next record
							if (!is_null($objNextSorting->sorting))
							{
								$nxtSorting = $objNextSorting->sorting;

								// Resort if the new sorting value is no integer or bigger than a MySQL integer
								if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
								{
									$count = 1;

									$objNewSorting = $this->Database->prepare("SELECT id, sorting FROM " . $this->strTable . " WHERE page_id=? ORDER BY sorting")
																	->execute($newpage_id);

									while ($objNewSorting->next())
									{
										$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
													   ->execute(($count++*128), $objNewSorting->id);

										if ($objNewSorting->sorting == $curSorting)
										{
											$newSorting = ($count++*128);
										}
									}
								}

								// Else new sorting = (current sorting + next sorting) / 2
								else $newSorting = (($curSorting + $nxtSorting) / 2);
							}

							// Else new sorting = (current sorting + 128)
							else $newSorting = ($curSorting + 128);
						}
					}

					// Use the given parent ID as parent ID
					else
					{
						$newpage_id = $page_id;
						$newSorting = 128;
					}
				}

				// Set new sorting and new parent ID
				$this->set['page_id'] = intval($newpage_id);
				$this->set['sorting'] = intval($newSorting);
			}
		}

		// If there is only page_id
		elseif ($this->Database->fieldExists('page_id', $this->strTable))
		{
			// page_id is not set - only valid for duplicated records, as they get the same parent ID as the original record!
			if (is_null($page_id) && $this->intId && $mode == 'copy')
			{
				$page_id = $this->intId;
			}

			// page_id is set (insert after or into the parent record)
			if (is_numeric($page_id))
			{
				// Insert the current record into the parent record
				if ($insertInto)
				{
					$this->set['page_id'] = $page_id;
				}

				// Else insert the current record after the parent record
				elseif ($page_id > 0)
				{
					$objParentRecord = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
													  ->limit(1)
													  ->execute($page_id);

					if ($objParentRecord->numRows)
					{
						$this->set['page_id'] = $objParentRecord->page_id;
					}
				}
			}
		}

		// If there is only sorting
		elseif ($this->Database->fieldExists('sorting', $this->strTable))
		{
			// ID is set (insert after the current record)
			if ($this->intId)
			{
				$objCurrentRecord = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
												   ->limit(1)
											 	   ->execute($this->intId);

				// Select current record
				if ($objCurrentRecord->numRows)
				{
					$curSorting = $objCurrentRecord->sorting;

					$objNextSorting = $this->Database->prepare("SELECT MIN(sorting) AS sorting FROM " . $this->strTable . " WHERE sorting>?")
													 ->execute($curSorting);

					// Select sorting value of the next record
					if ($objNextSorting->numRows)
					{
						$nxtSorting = $objNextSorting->sorting;

						// Resort if the new sorting value is no integer or bigger than a MySQL integer field
						if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
						{
							$count = 1;

							$objNewSorting = $this->Database->execute("SELECT id, sorting FROM " . $this->strTable . " ORDER BY sorting");

							while ($objNewSorting->next())
							{
								$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
											   ->execute(($count++*128), $objNewSorting->id);

								if ($objNewSorting->sorting == $curSorting)
								{
									$newSorting = ($count++*128);
								}
							}
						}

						// Else new sorting = (current sorting + next sorting) / 2
						else $newSorting = (($curSorting + $nxtSorting) / 2);
					}

					// Else new sorting = (current sorting + 128)
					else $newSorting = ($curSorting + 128);

					// Set new sorting
					$this->set['sorting'] = intval($newSorting);
				}

				// ID is not set (insert at the end)
				else
				{
					$objNextSorting = $this->Database->execute("SELECT MAX(sorting) AS sorting FROM " . $this->strTable);

					if ($objNextSorting->numRows)
					{
						$this->set['sorting'] = intval($objNextSorting->sorting + 128);
					}
				}
			}
		}
	}


	/**
	 * Change the order of two neighbour database records
	 */
	public function move()
	{
		// Proceed only if all mandatory variables are set
		if ($this->intId && $this->Input->get('sid') && (!$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root'] || !in_array($this->intId, $this->root)))
		{
			$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=? OR id=?")
									 ->limit(2)
									 ->execute($this->intId, $this->Input->get('sid'));

			$row = $objRow->fetchAllAssoc();

			if ($row[0]['page_id'] == $row[1]['page_id'])
			{
				$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
							   ->execute($row[0]['sorting'], $row[1]['id']);

				$this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
							   ->execute($row[1]['sorting'], $row[0]['id']);
			}
		}

		$this->redirect($this->getReferer());
	}


	/**
	 * Delete all incomplete and unrelated records
	 */
	protected function reviseTable()
	{
		$reload = false;
		$ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'];
		$ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];

		$new_records = $this->Session->get('new_records');

		// HOOK: addCustomLogic
		if (isset($GLOBALS['TL_HOOKS']['reviseTable']) && is_array($GLOBALS['TL_HOOKS']['reviseTable']))
		{
			foreach ($GLOBALS['TL_HOOKS']['reviseTable'] as $callback)
			{
				$this->import($callback[0]);
				$status = $this->$callback[0]->$callback[1]($this->strTable, $new_records[$this->strTable], $ptable, $ctable);

				if ($status === true)
				{
					$reload = true;
				}
			}
		}

		// Delete all new but incomplete records (tstamp=0)
		if (is_array($new_records[$this->strTable]) && count($new_records[$this->strTable]) > 0)
		{
			$objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE id IN(" . implode(',', $new_records[$this->strTable]) . ") AND tstamp=0");

			if ($objStmt->affectedRows > 0)
			{
				$reload = true;
			}
		}

		// Delete all records of the current table that are not related to the parent table
		if (strlen($ptable))
		{
			$objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE NOT EXISTS (SELECT * FROM " . $ptable . " WHERE " . $this->strTable . ".page_id = " . $ptable . ".id)");

			if ($objStmt->affectedRows > 0)
			{
				$reload = true;
			}
		}

		// Delete all records of the child table that are not related to the current table
		if (is_array($ctable) && count($ctable))
		{
			foreach ($ctable as $v)
			{
				if (strlen($v))
				{
					$objStmt = $this->Database->execute("DELETE FROM " . $v . " WHERE NOT EXISTS (SELECT * FROM " . $this->strTable . " WHERE " . $v . ".page_id = " . $this->strTable . ".id)");

					if ($objStmt->affectedRows > 0)
					{
						$reload = true;
					}
				}
			}
		}

		// Reload the page
		if ($reload)
		{
			$this->reload();
		}
	}


	/**
 	 * Show header of the parent table and list all records of the current table
	 * @return string
	 */
	protected function parentView()
	{
		$blnClipboard = false;
		$arrClipboard = $this->Session->get('CLIPBOARD');
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;
		$blnHasSorting = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'][0] == 'sorting';
		$blnMultiboard = false;

		// Check clipboard
		if (isset($arrClipboard[$table]) && count($arrClipboard[$table]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$table];

			if (is_array($arrClipboard['id']))
			{
				$blnMultiboard = true;
			}
		}

		// Load language file and data container array of the parent table
		$this->loadLanguageFile($this->ptable);
		$this->loadDataContainer($this->ptable);

		$return = '
<div id="tl_buttons">
<a href="'.(($this->Input->get('act') == 'select') ? $this->getReferer(true) : $this->Environment->script.'?do='.$this->Input->get('do')).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' . (($this->Input->get('act') != 'select') ? ' &#160; :: &#160; ' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<a href="'.$this->addToUrl(($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;page_id='.$this->intId)).'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a>' : '') . $this->generateGlobalButtons(). ($blnClipboard ? ' &nbsp; :: &nbsp; <a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a>' : '') : '') . '
</div>';

		// Get all details of the parent record
		$objParent = $this->Database->prepare("SELECT * FROM " . $this->ptable . " WHERE id=?")
									->limit(1)
									->execute(CURRENT_ID);

		if ($objParent->numRows < 1)
		{
			return $return;
		}

		$return .= (($this->Input->get('act') == 'select') ? '

<form action="'.ampersand($this->Environment->request, true).'" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select" />' : '').'

<div class="tl_listing_container">

<div class="tl_header" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);">';

		// List all records of the child table
		if (!$this->Input->get('act') || $this->Input->get('act') == 'paste' || $this->Input->get('act') == 'select')
		{

			// Header
			$imagePasteNew = $this->generateImage('new.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]);
			$imagePasteAfter = $this->generateImage('pasteafter.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0], 'class="blink"');
			$imageEditHeader = $this->generateImage('edit.gif', $GLOBALS['TL_LANG'][$this->strTable]['editheader'][0]);

			$return .= '
<div style="text-align:right;">'.(($this->Input->get('act') == 'select') ? '
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox" />' : '&nbsp;' . (($blnHasSorting && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed']) ? ' <a href="'.$this->addToUrl('act=create&amp;mode=2&amp;page_id='.$objParent->id.'&amp;id='.$this->intId).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][0]).'">'.$imagePasteNew.'</a>' : '') . ($blnClipboard ? ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;page_id='.$objParent->id . (!$blnMultiboard ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a>' : '')) . '
</div>';

			// Format header fields
			$add = array();
			$headerFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerFields'];

			foreach ($headerFields as $v)
			{
				$_v = deserialize($objParent->$v);

				if (is_array($_v))
				{
					$_v = implode(', ', $_v);
				}
				elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['multiple'])
				{
					$_v = strlen($_v) ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
				}
				elseif ($_v && $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] == 'date')
				{
					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $_v);
				}
				elseif ($_v && $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] == 'datim')
				{
					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $_v);
				}
				elseif ($v == 'tstamp')
				{
					$objMaxTstamp = $this->Database->prepare("SELECT MAX(tstamp) AS tstamp FROM " . $this->strTable . " WHERE page_id=?")
												   ->execute($objParent->id);

					if (!$objMaxTstamp->tstamp)
					{
						$objMaxTstamp->tstamp = $objParent->tstamp;
					}

					$_v = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objMaxTstamp->tstamp);
				}
				elseif (strlen($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey']))
				{
					$arrForeignKey = trimsplit('.', $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey']);

					$objLabel = $this->Database->prepare("SELECT " . $arrForeignKey[1] . " FROM " . $arrForeignKey[0] . " WHERE id=?")
											   ->limit(1)
											   ->execute($_v);

					if ($objLabel->numRows)
					{
						$_v = $objLabel->$arrForeignKey[1];
					}
				}
				elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v] != '')
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v];
				}

				// Add sorting field
				if ($_v != '')
				{
					$key = strlen($GLOBALS['TL_LANG'][$this->ptable][$v][0]) ? $GLOBALS['TL_LANG'][$this->ptable][$v][0]  : $v;
					$add[$key] = is_array($_v) ? $_v[0] : $_v;
				}
			}

			// Output header data
			$return .= '

<table cellpadding="0" cellspacing="0" class="tl_header_table" summary="Table lists all details of the header record">';

			foreach ($add as $k=>$v)
			{
				if (is_array($v))
				{
					$v = $v[0];
				}

				$return .= '
  <tr>
    <td><span class="tl_label">'.$k.':</span> </td>
    <td>'.$v.'</td>
  </tr>';
			}

			$return .= '
</table>
</div>';

			// Add all records of the current table
			$query = "SELECT * FROM " . $this->strTable;

			if (count($this->procedure))
			{
				$query .= " WHERE " . implode(' AND ', $this->procedure);
			}

			if (is_array($this->root))
			{
				$query .= (count($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', $this->root) . ")";
			}

			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields']))
			{
				$query .= " ORDER BY " . implode(', ', $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields']);
			}

			$objOrderByStmt = $this->Database->prepare($query);

			if (strlen($this->limit))
			{
				$arrLimit = explode(',', $this->limit);
				$objOrderByStmt->limit($arrLimit[1], $arrLimit[0]);
			}

			$objOrderBy = $objOrderByStmt->execute($this->values);

			if ($objOrderBy->numRows < 1)
			{
				return $return . '
<p class="tl_empty_parent_view">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>

</div>';
			}

			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][1];

				$this->import($strClass);
				$row = $objOrderBy->fetchAllAssoc();

				// Make items sortable
				if ($blnHasSorting)
				{
					$return .= '

<ul id="ul_' . CURRENT_ID . '" class="sortable">';
				}

				for ($i=0; $i<count($row); $i++)
				{
					$this->current[] = $row[$i]['id'];
					$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id']), 'class="blink"');
					$imagePasteNew = $this->generateImage('new.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][1], $row[$i]['id']));

					// Make items sortable
					if ($blnHasSorting)
					{
						$return .= '
<li id="li_' . $row[$i]['id'] . '">';
					}

					$return .= '

<div class="tl_content" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);">
<div style="text-align:right;">';

					// Edit multiple
					if ($this->Input->get('act') == 'select')
					{
						$return .= '<input type="checkbox" name="IDS[]" id="ids_'.$row[$i]['id'].'" class="tl_tree_checkbox" value="'.$row[$i]['id'].'" />';
					}

					// Regular buttons
					else
					{
						$return .= $this->generateButtons($row[$i], $this->strTable, $this->root, false, null, $row[($i-1)]['id'], $row[($i+1)]['id']);

						// Sortable table
						if ($blnHasSorting)
						{
							// Create new button
							if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'])
							{
								$return .= ' <a href="'.$this->addToUrl('act=create&amp;mode=1&amp;page_id='.$row[$i]['id'].'&amp;id='.$objParent->id).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][1], $row[$i]['id'])).'">'.$imagePasteNew.'</a>';
							}

							// Prevent circular references
							if ($blnClipboard && $arrClipboard['mode'] == 'cut' && $row[$i]['id'] == $arrClipboard['id'] || $blnMultiboard && $arrClipboard['mode'] == 'cutAll' && in_array($row[$i]['id'], $arrClipboard['id']))
							{
								$return .= ' ' . $this->generateImage('pasteafter_.gif', '', 'class="blink"');
							}

							// Copy/move multiple
							elseif ($blnMultiboard)
							{
								$return .= ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;page_id='.$row[$i]['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a>';
							}

							// Paste buttons
							elseif ($blnClipboard)
							{
								$return .= ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;page_id='.$row[$i]['id'].'&amp;id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a>';
							}
						}
					}

					$return .= '
</div>'.$this->$strClass->$strMethod($row[$i]).'</div>';

					// Make items sortable
					if ($blnHasSorting)
					{
						$return .= '

</li>';
					}
				}
			}
		}

/*
		// Make items sortable
		if ($blnHasSorting)
		{
			$return .= '
</ul>

<script type="text/javascript">
<!--//--><![CDATA[//><!--
Backend.makeParentViewSortable("ul_' . CURRENT_ID . '");
//--><!]]>
</script>';
		}

*/
		$return .= '

</div>';

		// Close form
		if ($this->Input->get('act') == 'select')
		{
			$return .= '

<div class="tl_formbody_submit" style="text-align:right;">

<div class="tl_submit_container">
  <input type="submit" name="cut" id="cut" class="tl_submit" alt="move selected records" accesskey="x" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['moveSelected']).'" />
</div>

</div>
</div>
</form>';
		}

		return $return;
	}


	/**
	 * List all records of the current table and return them as HTML string
	 * @return string
	 */
	protected function listView()
	{
		$return = '';
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
		$firstOrderBy = preg_replace('/\s+.*$/i', '', $orderBy[0]);

		if (is_array($this->orderBy) && strlen($this->orderBy[0]))
		{
			$orderBy = $this->orderBy;
			$firstOrderBy = $this->firstOrderBy;
		}

		// Show only own undo steps
		if ($this->strTable == 'tl_undo')
		{
			$this->import('BackendUser', 'User');

			if (!$this->User->isAdmin)
			{
				$this->procedure[] = 'page_id=?';
				$this->values[] = $this->User->id;
			}
		}

		$query = "SELECT * FROM " . $this->strTable;

		if (count($this->procedure))
		{
			$query .= " WHERE " . implode(' AND ', $this->procedure);
		}

		if (is_array($this->root))
		{
			$query .= (count($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', $this->root) . ")";
		}

		if (is_array($orderBy) && strlen($orderBy[0]))
		{
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
		}

		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 1 && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0)
		{
			$query .= " DESC";
		}

		$objRowStmt = $this->Database->prepare($query);

		if (strlen($this->limit))
		{
			$arrLimit = explode(',', $this->limit);
			$objRowStmt->limit($arrLimit[1], $arrLimit[0]);
		}

		$objRow = $objRowStmt->execute($this->values);
		$this->bid = strlen($return) ? $this->bid : 'tl_buttons';

		// Display buttos
		if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] || count($GLOBALS['TL_DCA'][$this->strTable]['list']['global_operations']))
		{
			$return .= '

<div id="'.$this->bid.'">'.(($this->Input->get('act') == 'select' || $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) ? '
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' : '') . (($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] && $this->Input->get('act') != 'select') ? ' &nbsp; :: &nbsp;' : '') . (($this->Input->get('act') != 'select') ? '
'.(!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '<a href="'.(strlen($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) ? $this->addToUrl('act=create' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;page_id=' . $this->intId) : $this->addToUrl('act=create')).'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a>' : '') . $this->generateGlobalButtons() : '') . '
</div>';
		}

		// Return "no records found" message
		if ($objRow->numRows < 1)
		{
			$return .= '
<p class="tl_empty">'.$GLOBALS['TL_LANG']['MSC']['noResult'].'</p>';
		}

		// List records
		else
		{
			$result = $objRow->fetchAllAssoc();
			$return .= (($this->Input->get('act') == 'select') ? '

<form action="'.ampersand($this->Environment->request, true).'" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select" />' : '').'

<div class="tl_listing_container">'.(($this->Input->get('act') == 'select') ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox" />
</div>' : '').'

<table cellpadding="0" cellspacing="0" class="tl_listing" summary="Table lists records">';

			// Rename each page_id to its label and resort the result (sort by parent table)
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3 && $this->Database->fieldExists('page_id', $this->strTable))
			{
				$firstOrderBy = 'page_id';
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				foreach ($result as $k=>$v)
				{
					$objField = $this->Database->prepare("SELECT " . $showFields[0] . " FROM " . $this->ptable . " WHERE id=?")
											   ->limit(1)
											   ->execute($v['page_id']);

					$result[$k]['page_id'] = $objField->$showFields[0];
				}

				$aux = array();

				foreach ($result as $row)
				{
					$aux[] = $row['page_id'];
				}

				array_multisort($aux, SORT_ASC, $result);
			}

			// Process result and add label and buttons
			$remoteCur = false;
			$groupclass = 'tl_folder_tlist';
			$arrLookup = array();

			foreach ($result as $row)
			{
				$args = array();
				$this->current[] = $row['id'];
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				// Label
				foreach ($showFields as $k=>$v)
				{
					if (strpos($v, ':') !== false)
					{
						list($strKey, $strTable) = explode(':', $v);
						list($strTable, $strField) = explode('.', $strTable);

						$objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
												 ->limit(1)
												 ->execute($row[$strKey]);

						$args[$k] = $objRef->numRows ? $objRef->$strField : '';
					}
					elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
					{
						if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'date')
						{
							$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]);
						}
						elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'time')
						{
							$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['timeFormat'], $row[$v]);
						}
						else
						{
							$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]);
						}
					}
					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple'])
					{
						$args[$k] = strlen($row[$v]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : '';
					}
					else
					{
						$row_v = deserialize($row[$v]);

						if (is_array($row_v))
						{
							$args_k = array();

							foreach ($row_v as $option)
							{
								$args_k[] = strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option] : $option;
							}

							$args[$k] = implode(', ', $args_k);
						}
						elseif (isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]))
						{
							$args[$k] = is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
						}
						else
						{
							$args[$k] = $row[$v];
						}
					}
				}

				// Shorten label it if it is too long
				$label = vsprintf((strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format']) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] : '%s'), $args);

				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
				{
					$this->import('String');
					$label = trim($this->String->substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
				}

				// Remove empty brackets (), [], {}, <> and empty tags from label
				$label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/i', '', $label);
				$label = preg_replace('/<[^>]+>\s*<\/[^>]+>/i', '', $label);

				// Build sorting groups
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] > 0)
				{
					$current = $row[$firstOrderBy];
					$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
					$sortingMode = (count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag']) && !strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'])) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];

					if($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['eval']['multiple'])
					{
						$remoteNew = strlen($current) ? ucfirst($GLOBALS['TL_LANG']['MSC']['yes']) : ucfirst($GLOBALS['TL_LANG']['MSC']['no']);
					}
					elseif(strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey']))
					{
						$key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey']);

						$objParent = $this->Database->prepare("SELECT " . $key[1] . " FROM " . $key[0] . " WHERE id=?")
													->limit(1)
													->execute($current);

						if ($objParent->numRows)
						{
							$remoteNew = $objParent->$key[1];
						}
					}
					elseif (in_array($sortingMode, array(1, 2)))
					{
						$remoteNew = strlen($current) ? ucfirst(utf8_substr($current , 0, 1)) : '-';
					}
					elseif (in_array($sortingMode, array(3, 4)))
					{
						if (!strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['length']))
						{
							$GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['length'] = 2;
						}

						$remoteNew = strlen($current) ? ucfirst(utf8_substr($current , 0, $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['length'])) : '-';
					}
					elseif (in_array($sortingMode, array(5, 6)))
					{
						$remoteNew = strlen($current) ? $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $current) : '-';
					}
					elseif (in_array($sortingMode, array(7, 8)))
					{
						$remoteNew = strlen($current) ? date('Y-m', $current) : '-';
						$intMonth = strlen($current) ? (date('m', $current) - 1) : '-';

						if (strlen($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
						{
							$remoteNew = strlen($current) ? $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $current) : '-';
						}
					}
					elseif (in_array($sortingMode, array(9, 10)))
					{
						$remoteNew = strlen($current) ? date('Y', $current) : '-';
					}
					else
					{
						if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['eval']['multiple'])
						{
							$remoteNew = strlen($current) ? $firstOrderBy : '';
						}
						elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference']))
						{
							$remoteNew = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$current];
						}
						elseif (array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options']))
						{
							$remoteNew = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options'][$current];
						}
						else
						{
							$remoteNew = $current;
						}

						if (!strlen($remoteNew))
						{
							$remoteNew = '-';
						}
					}

					// Add group header
					if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false))
					{
						if (array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options']))
						{
							$group = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options'][$remoteNew];
						}
						elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options_callback']))
						{
							if (!isset($arrLookup[$firstOrderBy]))
							{
								$strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options_callback'][0];
								$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['options_callback'][1];

								$this->import($strClass);
								$arrLookup[$firstOrderBy] = $this->$strClass->$strMethod($this);
							}

							$group = $arrLookup[$firstOrderBy][$remoteNew];
						}
						else
						{
							$group = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$remoteNew] ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$remoteNew][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['reference'][$remoteNew]);
						}

						if (!strlen($group))
						{
							$group = is_array($GLOBALS['TL_LANG'][$this->strTable][$remoteNew] ? $GLOBALS['TL_LANG'][$this->strTable][$remoteNew][0] : $GLOBALS['TL_LANG'][$this->strTable][$remoteNew]);
						}

						if (!strlen($group))
						{
							$group = $remoteNew;
						}

						// Call group callback ($group, $sortingMode, $firstOrderBy, $row, $this)
						if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback']))
						{
							$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback'][0];
							$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['group_callback'][1];

							$this->import($strClass);
							$group = $this->$strClass->$strMethod($group, $sortingMode, $firstOrderBy, $row, $this);
						}

						$remoteCur = $remoteNew;

						$return .= '
  <tr onmouseover="Theme.hoverRow(this, 1);" onmouseout="Theme.hoverRow(this, 0);">
    <td colspan="2" class="'.$groupclass.'">'.$group.'</td>
  </tr>';
						$groupclass = 'tl_folder_list';
					}
				}

				$return .= '
  <tr onmouseover="Theme.hoverRow(this, 1);" onmouseout="Theme.hoverRow(this, 0);">
    <td class="tl_file_list">';

				// Call label callback ($row, $label, $this)
				if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']))
				{
					$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
					$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

					$this->import($strClass);
					$return .= $this->$strClass->$strMethod($row, $label, $this);
				}
				else
				{
					$return .= $label;
				}

				// Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
				$return .= '</td>'.(($this->Input->get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap"><input type="checkbox" name="IDS[]" id="ids_'.$row['id'].'" class="tl_tree_checkbox" value="'.$row['id'].'" /></td>' : '
    <td class="tl_file_list tl_right_nowrap">'.$this->generateButtons($row, $this->strTable, $this->root).'</td>') . '
  </tr>';
			}

			// Close table
			$return .= '
</table>

</div>';

			// Close form
			if ($this->Input->get('act') == 'select')
			{
				$return .= '

<div class="tl_formbody_submit" style="text-align:right;">

<div class="tl_submit_container">
  <input type="submit" name="delete" id="delete" class="tl_submit" alt="delete selected records" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\');" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'" />' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="override" id="override" class="tl_submit" alt="override selected records" accesskey="v" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']).'" />
  <input type="submit" name="edit" id="edit" class="tl_submit" alt="edit selected records" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'" />' : '') . '
</div>

</div>
</div>
</form>';
			}
		}

		return $return;
	}
}

