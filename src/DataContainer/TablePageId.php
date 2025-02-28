<?php

namespace Isotope\CoreBundle\DataContainer;

use Contao\ArrayUtil;
use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\NotFoundException;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Contao\CoreBundle\Security\DataContainer\CreateAction;
use Contao\CoreBundle\Security\DataContainer\ReadAction;
use Contao\CoreBundle\Security\DataContainer\UpdateAction;
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\DC_Table;
use Contao\FilesModel;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TablePageId extends DC_Table
{

	/**
	 * List all records of a particular table
	 *
	 * @return string
	 */
	public function showAll()
	{
		$return = '';
		$this->limit = '';

		$objSession = System::getContainer()->get('request_stack')->getSession();

		$this->reviseTable();

		// Add to clipboard
		if (Input::get('act') == 'paste')
		{
			$this->denyAccessUnlessGranted(...$this->getClipboardPermission(Input::get('mode'), (int) Input::get('id')));

			$children = Input::get('children');

			// Backwards compatibility
			if (Input::get('childs') !== null)
			{
				trigger_deprecation('contao/core-bundle', '5.3', 'Using the "childs" query parameter has been deprecated and will no longer work in Contao 6. Use the "children" parameter instead.');
				$children = Input::get('childs');
			}

			$arrClipboard = $objSession->get('CLIPBOARD');

			$arrClipboard[$this->strTable] = array
			(
				'id' => Input::get('id'),
				'childs' => $children, // backwards compatibility
				'children' => $children,
				'mode' => Input::get('mode')
			);

			$objSession->set('CLIPBOARD', $arrClipboard);

			if ($this->currentPid)
			{
				$this->redirect(Backend::addToUrl('id=' . $this->currentPid, false, array('act', 'mode')));
			}
			else
			{
				$this->redirect(Backend::addToUrl('', false, array('act', 'mode', 'id')));
			}
		}

		// Custom filter
		if (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']) && \is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $filter)
			{
				if (\is_string($filter))
				{
					$this->procedure[] = $filter;
				}
				else
				{
				$this->procedure[] = $filter[0];
				$this->values[] = $filter[1];
			}
		}
		}

		// Render view
		if ($this->treeView)
		{
			$return .= $this->panel();
			$return .= $this->treeView();
		}
		else
		{
			if ($this->ptable && Input::get('table') && Database::getInstance()->fieldExists('page_id', $this->strTable))
			{
				$this->procedure[] = 'page_id=?';
				$this->values[] = $this->currentPid;
			}

			$return .= $this->panel();
			$return .= ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_PARENT ? $this->parentView() : $this->listView();
		}

		return $return;
	}

	/**
	 * Assign a new position to an existing record
	 *
	 * @param boolean $blnDoNotRedirect
	 *
	 * @throws InternalServerErrorException
	 */
	public function cut($blnDoNotRedirect=false)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null)
		{
			throw new AccessDeniedException('Table "' . $this->strTable . '" is not sortable.');
		}

		$cr = array();

		// ID and page_id are mandatory
		if (!$this->intId || Input::get('page_id') === null)
		{
			throw new NotFoundException('Cannot load record "' . $this->strTable . '.id=' . $this->intId . '".');
		}

		try
		{
			// Load current record before calculating new position etc. in case the user does not have read access
			$currentRecord = $this->getCurrentRecord();
		}
		catch (AccessDeniedException)
		{
			$currentRecord = null;
		}

		if ($currentRecord === null)
		{
			if (!$blnDoNotRedirect)
			{
				$this->redirect($this->getReferer());
			}

			return;
		}

		$db = Database::getInstance();

		// Get the new position
		$this->getNewPosition('cut', Input::get('page_id'), Input::get('mode') == self::PASTE_INTO);

		// Avoid circular references when there is no parent table
		if (!$this->ptable && $db->fieldExists('page_id', $this->strTable))
		{
			$cr = $db->getChildRecords($this->intId, $this->strTable);
			$cr[] = $this->intId;
		}

		$objSession = System::getContainer()->get('request_stack')->getSession();

		// Empty clipboard
		$arrClipboard = $objSession->get('CLIPBOARD');
		$arrClipboard[$this->strTable] = array();
		$objSession->set('CLIPBOARD', $arrClipboard);

		// Check for circular references
		if (\in_array($this->set['page_id'], $cr))
		{
			throw new UnprocessableEntityHttpException('Attempt to relate record ' . $this->intId . ' of table "' . $this->strTable . '" to its child record ' . Input::get('page_id') . ' (circular reference).');
		}

		// Dynamically set the parent table of tl_content
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
		{
			$this->set['ptable'] = $this->ptable;
		}

		$this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new UpdateAction($this->strTable, $currentRecord, $this->set));

		$db
			->prepare("UPDATE " . $this->strTable . " %s WHERE id=?")
			->set($this->set)
			->execute($this->intId);

		// Call the oncut_callback
		if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'] ?? null))
		{
			foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'] as $callback)
			{
				if (\is_array($callback))
				{
					System::importStatic($callback[0])->{$callback[1]}($this);
				}
				elseif (\is_callable($callback))
				{
					$callback($this);
				}
			}
		}

		if (!$blnDoNotRedirect)
		{
			$this->redirect($this->getReferer());
		}
	}

	/**
	 * Move all selected records
	 *
	 * @throws InternalServerErrorException
	 */
	public function cutAll()
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null)
		{
			throw new AccessDeniedException('Table "' . $this->strTable . '" is not sortable.');
		}

		$objSession = System::getContainer()->get('request_stack')->getSession();

		$arrClipboard = $objSession->get('CLIPBOARD');

		if (isset($arrClipboard[$this->strTable]) && \is_array($arrClipboard[$this->strTable]['id']))
		{
			foreach ($arrClipboard[$this->strTable]['id'] as $id)
			{
				$this->intId = $id;

				try
				{
					$this->cut(true);
				}
				catch (AccessDeniedException)
				{
					continue;
				}

				Input::setGet('page_id', $id);
				Input::setGet('mode', DataContainer::PASTE_AFTER);
			}
		}

		$this->redirect($this->getReferer());
	}

	/**
	 * Calculate the new position of a moved or inserted record
	 *
	 * @param string  $mode
	 * @param integer $pid
	 * @param boolean $insertInto
	 */
	protected function getNewPosition($mode, $pid=null, $insertInto=false)
	{
		$db = Database::getInstance();

		// If there is page_id and sorting
		if ($db->fieldExists('page_id', $this->strTable) && $db->fieldExists('sorting', $this->strTable))
		{
			// page_id is not set - only valid for duplicated records, as they get the same parent ID as the original record!
			if ($pid === null && $this->intId && $mode == 'copy')
			{
				$pid = $this->intId;
			}

			// page_id is set (insert after or into the parent record)
			if (is_numeric($pid))
			{
				$newPID = null;
				$newSorting = null;
				$filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_PARENT ? $this->strTable . '_' . $this->intCurrentPid : $this->strTable;

				$objSession = System::getContainer()->get('request_stack')->getSession();
				$session = $objSession->all();

				// Consider the pagination menu when inserting at the top (see #7895)
				if ($insertInto && isset($session['filter'][$filter]['limit']))
				{
					$limit = substr($session['filter'][$filter]['limit'], 0, strpos($session['filter'][$filter]['limit'], ','));

					if ($limit > 0)
					{
						$objInsertAfter = $db
							->prepare("SELECT id FROM " . $this->strTable . " WHERE page_id=? ORDER BY sorting")
							->limit(1, $limit - 1)
							->execute($pid);

						if ($objInsertAfter->numRows)
						{
							$insertInto = false;
							$pid = $objInsertAfter->id;
						}
					}
				}

				// Insert the current record at the beginning when inserting into the parent record
				if ($insertInto)
				{
					$newPID = $pid;

					$objSorting = $db
						->prepare("SELECT MIN(sorting) AS sorting FROM " . $this->strTable . " WHERE page_id=?")
						->execute($pid);

					// Select sorting value of the first record
					if ($objSorting->numRows)
					{
						$curSorting = $objSorting->sorting;

						// Resort if the new sorting value is not an integer or smaller than 1
						if (($curSorting % 2) != 0 || $curSorting < 1)
						{
							$objNewSorting = $db
								->prepare("SELECT id FROM " . $this->strTable . " WHERE page_id=? ORDER BY sorting")
								->execute($pid);

							$count = 2;
							$newSorting = 128;

							while ($objNewSorting->next())
							{
								$db
									->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
									->limit(1)
									->execute($count++ * 128, $objNewSorting->id);
							}
						}

						// Else new sorting = (current sorting / 2)
						else
						{
							$newSorting = $curSorting / 2;
						}
					}

					// Else new sorting = 128
					else
					{
						$newSorting = 128;
					}
				}

				// Else insert the current record after the parent record
				elseif ($pid > 0)
				{
					$objSorting = $db
						->prepare("SELECT page_id, sorting FROM " . $this->strTable . " WHERE id=?")
						->limit(1)
						->execute($pid);

					// Set parent ID of the current record as new parent ID
					if ($objSorting->numRows)
					{
						$newPID = $objSorting->page_id;
						$curSorting = $objSorting->sorting;

						// Do not proceed without a parent ID
						if (is_numeric($newPID))
						{
							$objNextSorting = $db
								->prepare("SELECT MIN(sorting) AS sorting FROM " . $this->strTable . " WHERE page_id=? AND sorting>?")
								->execute($newPID, $curSorting);

							// Select sorting value of the next record
							if ($objNextSorting->sorting !== null)
							{
								$nxtSorting = $objNextSorting->sorting;

								// Resort if the new sorting value is no integer or bigger than a MySQL integer
								if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
								{
									$count = 1;

									$objNewSorting = $db
										->prepare("SELECT id, sorting FROM " . $this->strTable . " WHERE page_id=? ORDER BY sorting")
										->execute($newPID);

									while ($objNewSorting->next())
									{
										$db
											->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
											->execute($count++ * 128, $objNewSorting->id);

										if ($objNewSorting->sorting == $curSorting)
										{
											$newSorting = $count++ * 128;
										}
									}
								}

								// Else new sorting = (current sorting + next sorting) / 2
								else
								{
									$newSorting = ($curSorting + $nxtSorting) / 2;
								}
							}

							// Else new sorting = (current sorting + 128)
							else
							{
								$newSorting = $curSorting + 128;
							}
						}
					}

					// Use the given parent ID as parent ID
					else
					{
						$newPID = $pid;
						$newSorting = 128;
					}
				}

				if (!$newPID)
				{
					$newPID = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$this->strTable]['fields']['page_id']['sql'] ?? array()) === null ? null : 0;
				}

				// Set new sorting and new parent ID
				$this->set['page_id'] = (int) $newPID;
				$this->set['sorting'] = (int) $newSorting;
			}
		}

		// If there is only page_id
		elseif ($db->fieldExists('page_id', $this->strTable))
		{
			// page_id is not set - only valid for duplicated records, as they get the same parent ID as the original record!
			if ($pid === null && $this->intId && $mode == 'copy')
			{
				$pid = $this->intId;
			}

			// page_id is set (insert after or into the parent record)
			if (is_numeric($pid))
			{
				// Insert the current record into the parent record
				if ($insertInto)
				{
					$this->set['page_id'] = $pid;
				}

				// Else insert the current record after the parent record
				elseif ($pid > 0)
				{
					$objParentRecord = $db
						->prepare("SELECT page_id FROM " . $this->strTable . " WHERE id=?")
						->limit(1)
						->execute($pid);

					if ($objParentRecord->numRows)
					{
						$this->set['page_id'] = $objParentRecord->page_id;
					}
				}
			}
		}

		// If there is only sorting
		elseif ($db->fieldExists('sorting', $this->strTable))
		{
			// ID is set (insert after the current record)
			if ($this->intId)
			{
				try
				{
					$currentRecord = $this->getCurrentRecord();
				}
				catch (AccessDeniedException)
				{
					$currentRecord = null;
				}

				// Select current record
				if (null !== $currentRecord)
				{
					$newSorting = null;
					$curSorting = $currentRecord['sorting'] ?? null;

					$objNextSorting = $db
						->prepare("SELECT MIN(sorting) AS sorting FROM " . $this->strTable . " WHERE sorting>?")
						->execute($curSorting);

					// Select sorting value of the next record
					if ($objNextSorting->numRows)
					{
						$nxtSorting = $objNextSorting->sorting;

						// Resort if the new sorting value is no integer or bigger than a MySQL integer field
						if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
						{
							$count = 1;

							$objNewSorting = $db->execute("SELECT id, sorting FROM " . $this->strTable . " ORDER BY sorting, id");

							while ($objNewSorting->next())
							{
								$db
									->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
									->execute($count++ * 128, $objNewSorting->id);

								if ($objNewSorting->sorting == $curSorting)
								{
									$newSorting = $count++ * 128;
								}
							}
						}

						// Else new sorting = (current sorting + next sorting) / 2
						else
						{
							$newSorting = ($curSorting + $nxtSorting) / 2;
						}
					}

					// Else new sorting = (current sorting + 128)
					else
					{
						$newSorting = $curSorting + 128;
					}

					// Set new sorting
					$this->set['sorting'] = (int) $newSorting;

					return;
				}
			}

			// ID is not set or not found (insert at the end)
			$objNextSorting = $db->execute("SELECT MAX(sorting) AS sorting FROM " . $this->strTable);
			$this->set['sorting'] = (int) $objNextSorting->sorting + 128;
		}
	}

	/**
	 * Delete all incomplete and unrelated records
	 */
	protected function reviseTable()
	{
		$reload = false;
		$ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'] ?? null;
		$ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'] ?? null;

		if ($ptable === null && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_TREE)
		{
			$ptable = $this->strTable;
		}

        /** @var AttributeBagInterface $objSessionBag */
		$objSessionBag = System::getContainer()->get('request_stack')->getSession()->getBag('contao_backend');

		$new_records = $objSessionBag->get('new_records');

		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['reviseTable']) && \is_array($GLOBALS['TL_HOOKS']['reviseTable']))
		{
			foreach ($GLOBALS['TL_HOOKS']['reviseTable'] as $callback)
			{
				$status = null;

				if (\is_array($callback))
				{
					$status = System::importStatic($callback[0])->{$callback[1]}($this->strTable, $new_records[$this->strTable] ?? null, $ptable, $ctable);
				}
				elseif (\is_callable($callback))
				{
					$status = $callback($this->strTable, $new_records[$this->strTable] ?? null, $ptable, $ctable);
				}

				if ($status === true)
				{
					$reload = true;
				}
			}
		}

		if (isset($new_records[$this->strTable]))
		{
			unset($new_records[$this->strTable]);
			$objSessionBag->set('new_records', $new_records);
		}

		$db = Database::getInstance();

		// Delete all new but incomplete records (tstamp=0)
		if ($strReviseTable = Input::get('revise'))
		{
			list($strTable, $intId) = explode('.', $strReviseTable, 2);

			if ($intId && $strTable === $this->strTable)
			{
				$origId = $this->id;
				$origActiveRecord = $this->activeRecord;

				try
				{
					// Get the current record
					$currentRecord = $this->getCurrentRecord($intId);
				}
				catch (AccessDeniedException)
				{
					$currentRecord = null;
				}

				if ($currentRecord !== null)
				{
					$this->id = $intId;
					$this->activeRecord = (object) $currentRecord;

					// Invalidate cache tags (no need to invalidate the parent)
					$this->invalidateCacheTags();

					$this->id = $origId;
					$this->activeRecord = $origActiveRecord;

					$objStmt = $db
						->prepare("DELETE FROM " . $this->strTable . " WHERE id=? AND tstamp=0")
						->execute((int) $intId);

					if ($objStmt->affectedRows > 0)
					{
						$reload = true;
					}
				}
			}
		}

		// Delete all records of the current table that are not related to the parent table
		if ($ptable && !($GLOBALS['TL_DCA'][$this->strTable]['config']['doNotDeleteRecords'] ?? null))
		{
			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
			{
				$objIds = $db->execute("SELECT c.id FROM " . $this->strTable . " c LEFT JOIN " . $ptable . " p ON c.page_id=p.id WHERE c.ptable='" . $ptable . "' AND p.id IS NULL");
			}
			elseif ($ptable == $this->strTable)
			{
				$objIds = $db->execute('SELECT c.id FROM ' . $this->strTable . ' c LEFT JOIN ' . $ptable . ' p ON c.page_id=p.id WHERE p.id IS NULL AND c.page_id > 0');
			}
			else
			{
				$objIds = $db->execute("SELECT c.id FROM " . $this->strTable . " c LEFT JOIN " . $ptable . " p ON c.page_id=p.id WHERE p.id IS NULL");
			}

			if ($objIds->numRows)
			{
				$objStmt = $db->execute("DELETE FROM " . $this->strTable . " WHERE id IN(" . implode(',', array_map('\intval', $objIds->fetchEach('id'))) . ")");

				if ($objStmt->affectedRows > 0)
				{
					$reload = true;
				}
			}
		}

		// Delete all records of the child table that are not related to the current table
		if (!empty($ctable) && \is_array($ctable))
		{
			foreach ($ctable as $v)
			{
				if ($v)
				{
					// Load the DCA configuration, so we can check for "dynamicPtable" and "doNotDeleteRecords"
						$this->loadDataContainer($v);

					if ($GLOBALS['TL_DCA'][$v]['config']['doNotDeleteRecords'] ?? null)
					{
						continue;
					}

					if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'] ?? null)
					{
						$objIds = $db->execute("SELECT c.id FROM " . $v . " c LEFT JOIN " . $this->strTable . " p ON c.page_id=p.id WHERE c.ptable='" . $this->strTable . "' AND p.id IS NULL");
					}
					else
					{
						$objIds = $db->execute("SELECT c.id FROM " . $v . " c LEFT JOIN " . $this->strTable . " p ON c.page_id=p.id WHERE p.id IS NULL");
					}

					if ($objIds->numRows)
					{
						$objStmt = $db->execute("DELETE FROM " . $v . " WHERE id IN(" . implode(',', array_map('\intval', $objIds->fetchEach('id'))) . ")");

						if ($objStmt->affectedRows > 0)
						{
							$reload = true;
						}
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
	 *
	 * @return string
	 */
	protected function parentView()
	{
		$objSession = System::getContainer()->get('request_stack')->getSession();

		$blnClipboard = false;
		$arrClipboard = $objSession->get('CLIPBOARD');
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_TREE_EXTENDED ? $this->ptable : $this->strTable;
		$blnHasSorting = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'][0] ?? null) == 'sorting';
		$blnMultiboard = false;

		// Check clipboard
		if (!empty($arrClipboard[$table]))
		{
			$blnClipboard = true;
			$arrClipboard = $arrClipboard[$table];

			if (\is_array($arrClipboard['id'] ?? null))
			{
				$blnMultiboard = true;
			}
		}
		else
		{
			$arrClipboard = null;
		}

		// Load the language file and data container array of the parent table
		System::loadLanguageFile($this->ptable);
		$this->loadDataContainer($this->ptable);

		// Check the default labels (see #509)
		$labelNew = $GLOBALS['TL_LANG'][$this->strTable]['new'] ?? $GLOBALS['TL_LANG']['DCA']['new'];
		$labelCut = $GLOBALS['TL_LANG'][$this->strTable]['cut'] ?? $GLOBALS['TL_LANG']['DCA']['cut'];
		$labelPasteNew = $GLOBALS['TL_LANG'][$this->strTable]['pastenew'] ?? $GLOBALS['TL_LANG']['DCA']['pastenew'];
		$labelPasteAfter = $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'] ?? $GLOBALS['TL_LANG']['DCA']['pasteafter'];
		$labelEditHeader = $GLOBALS['TL_LANG'][$this->ptable]['edit'] ?? $GLOBALS['TL_LANG']['DCA']['edit'];
		$limitHeight = BackendUser::getInstance()->doNotCollapse ? false : (int) ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['limitHeight'] ?? 0);

		$db = Database::getInstance();
		$security = System::getContainer()->get('security.helper');

		$buttons = (Input::get('nb') ? '' : ('
<a href="' . $this->getReferer(true, 'tl_iso_product') . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>')) . ' ' . ((Input::get('act') != 'select' && !$blnClipboard && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null)) ? '
<a href="' . $this->addToUrl($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;page_id=' . $this->intId) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1]) . '" accesskey="n" data-action="contao--scroll-offset#store">' . $labelNew[0] . '</a> ' : '') . ($blnClipboard ? '
<a href="' . $this->addToUrl('clipboard=1') . '" class="header_clipboard" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']) . '" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['clearClipboard'] . '</a> ' : $this->generateGlobalButtons());

		$return = Message::generate() . ($buttons ? '<div id="tl_buttons">' . $buttons . '</div>' : '');

		// Get all details of the parent record
		$objParent = $db
			->prepare("SELECT * FROM " . $this->ptable . " WHERE id=?")
			->limit(1)
			->execute($this->intCurrentPid);

		if ($objParent->numRows < 1)
		{
			return $return;
		}

		$return .= ((Input::get('act') == 'select') ? '

<form id="tl_select" class="tl_form' . ((Input::get('act') == 'select') ? ' unselectable' : '') . '" method="post" novalidate>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . htmlspecialchars(System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '">' : '') . ($blnClipboard ? '
<div id="paste_hint">
  <p>' . $GLOBALS['TL_LANG']['MSC']['selectNewPosition'] . '</p>
</div>' : '') . '
<div class="tl_listing_container parent_view' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['renderAsGrid'] ?? false) ? ' as-grid' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '" id="tl_listing"' . $this->getPickerValueAttribute() . '>
<div class="tl_header click2edit toggle_select hover-div">';

		// List all records of the child table
		if (\in_array(Input::get('act'), array('select', null)))
		{
			// Header
			$imagePasteNew = Image::getHtml('new.svg', $labelPasteNew[0]);
			$imagePasteAfter = Image::getHtml('pasteafter.svg', $labelPasteAfter[0]);
			$imageEditHeader = Image::getHtml('edit.svg', \sprintf(\is_array($labelEditHeader) ? $labelEditHeader[0] : $labelEditHeader, $objParent->id));

			$security = System::getContainer()->get('security.helper');

			$return .= '
<div class="tl_content_right">' . ((Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">' : ($blnClipboard ? '
<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;page_id=' . $objParent->id . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars($labelPasteAfter[0]) . '" data-action="contao--scroll-offset#store">' . $imagePasteAfter . '</a>' : ((!($GLOBALS['TL_DCA'][$this->ptable]['config']['notEditable'] ?? null) && $security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, $this->ptable) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->ptable, new UpdateAction($this->ptable, $objParent->row()))) ? '
<a href="' . preg_replace('/&(amp;)?table=[^& ]*/i', $this->ptable ? '&amp;table=' . $this->ptable : '', $this->addToUrl('act=edit' . (Input::get('nb') ? '&amp;nc=1' : ''))) . '" class="edit" title="' . StringUtil::specialchars(\sprintf(\is_array($labelEditHeader) ? $labelEditHeader[1] : $labelEditHeader, $objParent->id)) . '">' . $imageEditHeader . '</a> ' . $this->generateHeaderButtons($objParent->row(), $this->ptable) : '') . (($blnHasSorting && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable, array('pid' => $objParent->id, 'sorting' => 0)))) ? '
<a href="' . $this->addToUrl('act=create&amp;mode=2&amp;page_id=' . $objParent->id . '&amp;id=' . $this->intId) . '" title="' . StringUtil::specialchars($labelPasteNew[0]) . '">' . $imagePasteNew . '</a>' : ''))) . '
</div>';

			// Format header fields
			$add = array();
			$headerFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerFields'];

			foreach ($headerFields as $v)
			{
				$_v = StringUtil::deserialize($objParent->$v);

				// Translate UUIDs to paths
				if (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['inputType'] ?? null) == 'fileTree')
				{
					$objFiles = FilesModel::findMultipleByUuids((array) $_v);

					if ($objFiles !== null)
					{
						$_v = $objFiles->fetchEach('path');
					}
				}

				if (\is_array($_v))
				{
					$_v = implode(', ', $_v);
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['isBoolean'] ?? null) || (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['inputType'] ?? null) == 'checkbox' && !($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['multiple'] ?? null)))
				{
					$_v = $_v ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] ?? null) == 'date')
				{
					$_v = $_v ? Date::parse(Config::get('dateFormat'), $_v) : '-';
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] ?? null) == 'time')
				{
					$_v = $_v ? Date::parse(Config::get('timeFormat'), $_v) : '-';
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] ?? null) == 'datim')
				{
					$_v = $_v ? Date::parse(Config::get('datimFormat'), $_v) : '-';
				}
				elseif ($v == 'tstamp')
				{
					$_v = Date::parse(Config::get('datimFormat'), $objParent->tstamp);
				}
				elseif (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey']))
				{
					$arrForeignKey = explode('.', $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey'], 2);

					$objLabel = $db
						->prepare("SELECT " . Database::quoteIdentifier($arrForeignKey[1]) . " AS value FROM " . $arrForeignKey[0] . " WHERE id=?")
						->limit(1)
						->execute($_v);

					$_v = $objLabel->numRows ? $objLabel->value : '-';
				}
				elseif (\is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v] ?? null))
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v][0];
				}
				elseif (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v]))
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v];
				}
				elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['isAssociative'] ?? null) || ArrayUtil::isAssoc($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options'] ?? null))
				{
					$_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options'][$_v] ?? null;
				}
				elseif (\is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'] ?? null))
				{
					$strClass = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'][0];
					$strMethod = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'][1];

					$options_callback = System::importStatic($strClass)->$strMethod($this);

					$_v = $options_callback[$_v] ?? '-';
				}
				elseif (\is_callable($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'] ?? null))
				{
					$options_callback = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback']($this);

					$_v = $options_callback[$_v] ?? '-';
				}

				// Add the sorting field
				if ($_v)
				{
					if (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label']))
					{
						$key = \is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label']) ? $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label'][0] : $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['label'];
					}
					else
					{
						$key = $GLOBALS['TL_LANG'][$this->ptable][$v][0] ?? $v;
					}

					$add[$key] = $_v;
				}
			}

			// Trigger the header_callback (see #3417)
			if (\is_array($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'] ?? null))
			{
				$add = System::importStatic($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][0])->{$GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][1]}($add, $this);
			}
			elseif (\is_callable($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'] ?? null))
			{
				$add = $GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback']($add, $this);
			}

			// Output the header data
			$return .= '

<table class="tl_header_table">';

			foreach ($add as $k=>$v)
			{
				if (\is_array($v))
				{
					$v = $v[0];
				}

				$return .= '
  <tr>
    <td><span class="tl_label">' . $k . ':</span> </td>
    <td>' . $v . '</td>
  </tr>';
			}

			$return .= '
</table>
</div>';

			$orderBy = array();
			$firstOrderBy = array();

			// Add all records of the current table
			$query = "SELECT * FROM " . $this->strTable;

			if (\is_array($this->orderBy) && isset($this->orderBy[0]))
			{
				$orderBy = $this->orderBy;
				$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

				// Order by the foreign key
				if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey']))
				{
					$key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey'], 2);
					$orderBy[0] = "(SELECT " . Database::quoteIdentifier($key[1]) . " FROM " . $key[0] . " WHERE " . $this->strTable . "." . Database::quoteIdentifier($firstOrderBy) . "=" . $key[0] . ".id)";
				}
			}
			elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? null))
			{
				$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
				$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);
			}

			$arrProcedure = $this->procedure;
			$arrValues = $this->values;

			if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
			{
				$arrProcedure[] = 'ptable=?';
				$arrValues[] = $this->ptable;
			}

			// WHERE
			if (!empty($arrProcedure))
			{
				$query .= " WHERE " . implode(' AND ', $arrProcedure);
			}

			if (!empty($this->root) && \is_array($this->root))
			{
				$query .= (!empty($arrProcedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
			}

			// ORDER BY
			if (!empty($orderBy) && \is_array($orderBy))
			{
				foreach ($orderBy as $k=>$v)
				{
					if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'] ?? null, array(self::SORT_INITIAL_LETTER_DESC, self::SORT_INITIAL_LETTERS_DESC, self::SORT_DAY_DESC, self::SORT_MONTH_DESC, self::SORT_YEAR_DESC, self::SORT_DESC)))
					{
						$orderBy[$k] .= ' DESC';
					}
				}

				$query .= " ORDER BY " . implode(', ', $orderBy) . ', id';
			}

			$objOrderByStmt = $db->prepare($query);

			// LIMIT
			if ($this->limit)
			{
				$arrLimit = explode(',', $this->limit) + array(null, null);
				$objOrderByStmt->limit($arrLimit[1], $arrLimit[0]);
			}

			$objOrderBy = $objOrderByStmt->execute(...$arrValues);

			if ($objOrderBy->numRows < 1)
			{
				return $return . '
<p class="tl_empty_parent_view">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>
</div>';
			}

			// Render the child records
			$strGroup = '';
			$blnIndent = false;
			$intWrapLevel = 0;
			$row = $objOrderBy->fetchAllAssoc();

			// Make items sortable
			if ($blnHasSorting)
			{
				$return .= '

<ul id="ul_' . $this->intCurrentPid . '">';
			}

			for ($i=0, $c=\count($row); $i<$c; $i++)
			{
				// Improve performance
				static::setCurrentRecordCache($row[$i]['id'], $this->strTable, $row[$i]);

				$this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new ReadAction($this->strTable, $row[$i]));

				$this->current[] = $row[$i]['id'];
				$imagePasteAfter = Image::getHtml('pasteafter.svg', \sprintf($labelPasteAfter[1] ?? $labelPasteAfter[0], $row[$i]['id']));
				$imagePasteNew = Image::getHtml('new.svg', \sprintf($labelPasteNew[1] ?? $labelPasteNew[0], $row[$i]['id']));

				// Make items sortable
				if ($blnHasSorting)
				{
					$return .= '
<li id="li_' . $row[$i]['id'] . '">';
				}

				// Add the group header
				if ($firstOrderBy != 'sorting' && !($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] ?? null))
				{
					$sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null)) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null);
					$remoteNew = $this->formatCurrentValue($firstOrderBy, $row[$i][$firstOrderBy], $sortingMode);
					$group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row[$i]);

					if ($group != $strGroup)
					{
						$return .= "\n\n" . '<div class="tl_content_header">' . $group . '</div>';
						$strGroup = $group;
					}
				}

				$blnWrapperStart = isset($row[$i]['type']) && \in_array($row[$i]['type'], $GLOBALS['TL_WRAPPERS']['start']);
				$blnWrapperSeparator = isset($row[$i]['type']) && \in_array($row[$i]['type'], $GLOBALS['TL_WRAPPERS']['separator']);
				$blnWrapperStop = isset($row[$i]['type']) && \in_array($row[$i]['type'], $GLOBALS['TL_WRAPPERS']['stop']);
				$blnIndentFirst = isset($row[$i - 1]['type']) && \in_array($row[$i - 1]['type'], $GLOBALS['TL_WRAPPERS']['start']);
				$blnIndentLast = isset($row[$i + 1]['type']) && \in_array($row[$i + 1]['type'], $GLOBALS['TL_WRAPPERS']['stop']);

				// Closing wrappers
				if ($blnWrapperStop && --$intWrapLevel < 1)
				{
					$blnIndent = false;
				}

				$return .= '
<div class="tl_content' . ($blnWrapperStart ? ' wrapper_start' : '') . ($blnWrapperSeparator ? ' wrapper_separator' : '') . ($blnWrapperStop ? ' wrapper_stop' : '') . ($blnIndent ? ' indent indent_' . $intWrapLevel : '') . ($blnIndentFirst ? ' indent_first' : '') . ($blnIndentLast ? ' indent_last' : '') . ((string) $row[$i]['tstamp'] === '0' ? ' draft' : '') . (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_class']) ? ' ' . $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_class'] : '') . ' click2edit toggle_select">
<div class="inside hover-div"' . ($limitHeight && !$blnWrapperStart && !$blnWrapperStop && !$blnWrapperSeparator ? ' data-contao--limit-height-target="node"' : '') . '>
<div class="tl_content_right">';

				// Opening wrappers
				if ($blnWrapperStart && ++$intWrapLevel > 0)
				{
					$blnIndent = true;
				}

				// Edit multiple
				if (Input::get('act') == 'select')
				{
					$return .= '<input type="checkbox" name="IDS[]" id="ids_' . $row[$i]['id'] . '" class="tl_tree_checkbox" value="' . $row[$i]['id'] . '">';
				}

				// Regular buttons
				else
				{
					$return .= $this->generateButtons($row[$i], $this->strTable, $this->root, false, null, $row[$i - 1]['id'] ?? null, $row[$i + 1]['id'] ?? null);

					// Sortable table
					if ($blnHasSorting)
					{
						// Create new button
						if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable, array('page_id' => $row[$i]['page_id'], 'sorting' => $row[$i]['sorting'] + 1))))
						{
							$return .= ' <a href="' . $this->addToUrl('act=create&amp;mode=1&amp;page_id=' . $row[$i]['id'] . '&amp;id=' . $objParent->id . (Input::get('nb') ? '&amp;nc=1' : '')) . '" title="' . StringUtil::specialchars(sprintf($labelPasteNew[1], $row[$i]['id'])) . '">' . $imagePasteNew . '</a>';
						}

						// Prevent circular references
						if (($blnClipboard && $arrClipboard['mode'] == 'cut' && $row[$i]['id'] == $arrClipboard['id']) || ($blnMultiboard && $arrClipboard['mode'] == 'cutAll' && \in_array($row[$i]['id'], $arrClipboard['id'])))
						{
							$return .= ' ' . Image::getHtml('pasteafter--disabled.svg');
						}

						// Copy/move multiple
						elseif ($blnMultiboard)
						{
							$return .= ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;page_id=' . $row[$i]['id']) . '" title="' . StringUtil::specialchars(\sprintf($labelPasteAfter[1], $row[$i]['id'])) . '" data-action="contao--scroll-offset#store">' . $imagePasteAfter . '</a>';
						}

						// Paste buttons
						elseif ($blnClipboard)
						{
							$return .= ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;page_id=' . $row[$i]['id'] . '&amp;id=' . $arrClipboard['id']) . '" title="' . StringUtil::specialchars(\sprintf($labelPasteAfter[1], $row[$i]['id'])) . '" data-action="contao--scroll-offset#store">' . $imagePasteAfter . '</a>';
						}

						// Drag handle
						if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new UpdateAction($this->strTable, $row[$i])))
						{
							$return .= ' <button type="button" class="drag-handle" title="' . StringUtil::specialchars(\sprintf(\is_array($labelCut) ? $labelCut[1] : $labelCut, $row[$i]['pid'])) . '" aria-hidden="true">' . Image::getHtml('drag.svg') . '</button>';
						}
					}

					// Picker
					if ($this->strPickerFieldType)
					{
						$return .= $this->getPickerInputField($row[$i]['id']);
					}
				}

				if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'] ?? null))
				{
					$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][0];
					$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][1];

					$return .= '</div>' . System::importStatic($strClass)->$strMethod($row[$i]) . '</div>';
				}
				elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'] ?? null))
				{
					$return .= '</div>' . $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']($row[$i]) . '</div>';
				}
				else
				{
					$return .= '</div><div class="tl_content_left">' . $this->generateRecordLabel($row[$i]) . '</div></div>';
				}

				$return .= '</div>';

				// Make items sortable
				if ($blnHasSorting)
				{
					$return .= '</li>';
				}
			}
		}

		// Make items sortable
		if ($blnHasSorting)
		{
			$return .= '
</ul>';

			if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null) && Input::get('act') != 'select')
			{
				$return .= '
<script>
  Isotope.makeParentViewSortable("ul_' . $this->intCurrentPid . '");
</script>';
			}
		}

		$return .= ($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '') . '
</div>';

		// Add another panel at the end of the page
		if (str_contains($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'] ?? '', 'limit'))
		{
			$return .= $this->paginationMenu();
		}

		// Close the form
		if (Input::get('act') == 'select')
		{
			// Submit buttons
			$arrButtons = array();

			if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
			{
				$arrButtons['edit'] = '<button type="submit" name="edit" id="edit" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['editSelected'] . '</button>';
			}

			if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ?? null))
			{
				$arrButtons['delete'] = '<button type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['delAllConfirm'] . '\')">' . $GLOBALS['TL_LANG']['MSC']['deleteSelected'] . '</button>';
			}

			if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'] ?? null))
			{
				$arrButtons['copy'] = '<button type="submit" name="copy" id="copy" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['copySelected'] . '</button>';
				$arrButtons['copyMultiple'] = '<button type="submit" name="copyMultiple" id="copyMultiple" class="tl_submit" accesskey="m">' . $GLOBALS['TL_LANG']['MSC']['copyMultiple'] . '</button>';
			}

			if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null))
			{
				$arrButtons['cut'] = '<button type="submit" name="cut" id="cut" class="tl_submit" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['moveSelected'] . '</button>';
			}

			if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
			{
				$arrButtons['override'] = '<button type="submit" name="override" id="override" class="tl_submit" accesskey="v">' . $GLOBALS['TL_LANG']['MSC']['overrideSelected'] . '</button>';
			}

			// Call the buttons_callback (see #4691)
			if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] ?? null))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
				{
					if (\is_array($callback))
					{
						$arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons, $this);
					}
					elseif (\is_callable($callback))
					{
						$arrButtons = $callback($arrButtons, $this);
					}
				}
			}

			if (\count($arrButtons) < 3)
			{
				$strButtons = implode(' ', $arrButtons);
			}
			else
			{
				$strButtons = array_shift($arrButtons) . ' ';
				$strButtons .= '<div class="split-button">';
				$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

				foreach ($arrButtons as $strButton)
				{
					$strButtons .= '<li>' . $strButton . '</li>';
				}

				$strButtons .= '</ul></div>';
			}

			$return .= '
</div>
<div class="tl_formbody_submit" style="text-align:right">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';
		}

		if ($limitHeight)
		{
			$return = '<div
				data-controller="contao--limit-height"
				data-contao--limit-height-max-value="' . $limitHeight . '"
				data-contao--limit-height-expand-value="' . $GLOBALS['TL_LANG']['MSC']['expandNode'] . '"
				data-contao--limit-height-collapse-value="' . $GLOBALS['TL_LANG']['MSC']['collapseNode'] . '"
				data-contao--limit-height-expand-all-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][0] . '"
				data-contao--limit-height-expand-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][1] . '"
				data-contao--limit-height-collapse-all-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
				data-contao--limit-height-collapse-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
			>' . $return . '</div>';
		}

		return $return;
	}

	/**
	 * List all records of the current table and return them as HTML string
	 *
	 * @return string
	 */
	protected function listView()
	{
		$table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_TREE_EXTENDED ? $this->ptable : $this->strTable;
		$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? array('id');
		$firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

		if (\is_array($this->orderBy) && !empty($this->orderBy[0]))
		{
			$orderBy = $this->orderBy;
			$firstOrderBy = $this->firstOrderBy;
		}

		// Check the default labels (see #509)
		$labelNew = $GLOBALS['TL_LANG'][$this->strTable]['new'] ?? $GLOBALS['TL_LANG']['DCA']['new'];
		$limitHeight = BackendUser::getInstance()->doNotCollapse ? false : (int) ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['limitHeight'] ?? 0);

		$query = "SELECT * FROM " . $this->strTable;

		if (!empty($this->procedure))
		{
			$query .= " WHERE " . implode(' AND ', $this->procedure);
		}

		if (!empty($this->root) && \is_array($this->root))
		{
			$query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
		}

		$db = Database::getInstance();

		if (\is_array($orderBy) && $orderBy[0])
		{
			foreach ($orderBy as $k=>$v)
			{
				list($key, $direction) = explode(' ', $v, 2) + array(null, null);

				$orderBy[$k] = $key;

				// If there is no direction, check the global flag in sorting mode 1 or the field flag in all other sorting modes
				if (!$direction)
				{
					if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_SORTED && \in_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null, array(self::SORT_INITIAL_LETTER_DESC, self::SORT_INITIAL_LETTERS_DESC, self::SORT_DAY_DESC, self::SORT_MONTH_DESC, self::SORT_YEAR_DESC, self::SORT_DESC)))
					{
						$direction = 'DESC';
					}
					elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'] ?? null, array(self::SORT_INITIAL_LETTER_DESC, self::SORT_INITIAL_LETTERS_DESC, self::SORT_DAY_DESC, self::SORT_MONTH_DESC, self::SORT_YEAR_DESC, self::SORT_DESC)))
					{
						$direction = 'DESC';
					}
				}

				if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['foreignKey']))
				{
					$chunks = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['foreignKey'], 2);
					$orderBy[$k] = "(SELECT " . Database::quoteIdentifier($chunks[1]) . " FROM " . $chunks[0] . " WHERE " . $chunks[0] . ".id=" . $this->strTable . "." . $key . ")";
				}

				if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'] ?? null, array(self::SORT_DAY_ASC, self::SORT_DAY_DESC, self::SORT_DAY_BOTH, self::SORT_MONTH_ASC, self::SORT_MONTH_DESC, self::SORT_MONTH_BOTH, self::SORT_YEAR_ASC, self::SORT_YEAR_DESC, self::SORT_YEAR_BOTH)))
				{
					$orderBy[$k] = "CAST(" . $orderBy[$k] . " AS SIGNED)"; // see #5503
				}

				if ($direction)
				{
					$orderBy[$k] .= ' ' . $direction;
				}

				if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['findInSet'] ?? null)
				{
					if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'] ?? null))
					{
						$strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][0];
						$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][1];

						$keys = System::importStatic($strClass)->$strMethod($this);
					}
					elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'] ?? null))
					{
						$keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback']($this);
					}
					else
					{
						$keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options'] ?? array();
					}

					if (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['isAssociative'] ?? null) || ArrayUtil::isAssoc($keys))
					{
						$keys = array_keys($keys);
					}

					$orderBy[$k] = $db->findInSet($v, $keys);
				}
			}

			if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == self::MODE_SORTED_PARENT)
			{
				$firstOrderBy = 'page_id';
				$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

				$query .= " ORDER BY (SELECT " . Database::quoteIdentifier($showFields[0]) . " FROM " . $this->ptable . " WHERE " . $this->ptable . ".id=" . $this->strTable . ".page_id), " . implode(', ', $orderBy);

				// Set the foreignKey so that the label is translated
				if (!($GLOBALS['TL_DCA'][$table]['fields']['page_id']['foreignKey'] ?? null))
				{
					$GLOBALS['TL_DCA'][$table]['fields']['page_id']['foreignKey'] = $this->ptable . '.' . $showFields[0];
				}

				// Remove the parent field from label fields
				array_shift($showFields);
				$GLOBALS['TL_DCA'][$table]['list']['label']['fields'] = $showFields;
			}
			else
			{
				$query .= " ORDER BY " . implode(', ', $orderBy) . ', id';
			}
		}

		$objRowStmt = $db->prepare($query);

		if ($this->limit)
		{
			$arrLimit = explode(',', $this->limit) + array(null, null);
			$objRowStmt->limit($arrLimit[1], $arrLimit[0]);
		}

		$objRow = $objRowStmt->execute(...$this->values);
		$security = System::getContainer()->get('security.helper');

		// Display buttons
		$buttons = (Input::get('act') == 'select' ? '
<a href="' . $this->getReferer(true, 'tl_iso_product') . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="' . System::getContainer()->get('router')->generate('contao_backend') . '?' . $GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'] . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" data-action="contao--scroll-offset#discard">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : '')) . ((Input::get('act') != 'select' && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null) && $security->isGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new CreateAction($this->strTable))) ? '
<a href="' . ($this->ptable ? $this->addToUrl('act=create' . ((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) < self::MODE_PARENT) ? '&amp;mode=2' : '') . '&amp;page_id=' . $this->intId) : $this->addToUrl('act=create')) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1] ?? '') . '" accesskey="n" data-action="contao--scroll-offset#store">' . $labelNew[0] . '</a> ' : '') . $this->generateGlobalButtons();

		$return = Message::generate() . ($buttons ? '<div id="tl_buttons">' . $buttons . '</div>' : '');

		// Return "no records found" message
		if ($objRow->numRows < 1)
		{
			$return .= '
<p class="tl_empty">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>';
		}

		// List records
		else
		{
			$result = $objRow->fetchAllAssoc();

			$return .= ((Input::get('act') == 'select') ? '
<form id="tl_select" class="tl_form' . ((Input::get('act') == 'select') ? ' unselectable' : '') . '" method="post" novalidate>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . htmlspecialchars(System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue(), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5) . '">' : '') . '
<div class="tl_listing_container list_view" id="tl_listing"' . $this->getPickerValueAttribute() . '>' . ((Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '
<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '') . '
<table class="tl_listing' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) ? ' showColumns' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '">';

			// Automatically add the "order by" field as last column if we do not have group headers
			if (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) && false !== ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showFirstOrderBy'] ?? null))
			{
				$blnFound = false;

				// Extract the real key and compare it to $firstOrderBy
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
				{
					if (str_contains($f, ':'))
					{
						list($f) = explode(':', $f, 2);
					}

					if ($firstOrderBy == $f)
					{
						$blnFound = true;
						break;
					}
				}

				if (!$blnFound)
				{
					$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][] = $firstOrderBy;
				}
			}

			// Generate the table header if the "show columns" option is active
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
			{
				$return .= '
<thead>
  <tr>';

				foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
				{
					if (str_contains($f, ':'))
					{
						list($f) = explode(':', $f, 2);
					}

					$return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? $f)) . '</th>';
				}

				$return .= '
    <th class="tl_folder_tlist tl_right_nowrap"></th>
  </tr>
</thead>
<tbody>';
			}

			// Process result and add label and buttons
			$remoteCur = false;
			$groupclass = 'tl_folder_tlist';

			foreach ($result as $row)
			{
				// Improve performance for $dc->getCurrentRecord($id);
				static::setCurrentRecordCache($row['id'], $this->strTable, $row);

				$this->denyAccessUnlessGranted(ContaoCorePermissions::DC_PREFIX . $this->strTable, new ReadAction($this->strTable, $row));

				$this->current[] = $row['id'];
				$label = $this->generateRecordLabel($row, $this->strTable);

				// Build the sorting groups
				if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) > 0)
				{
					$current = $row[$firstOrderBy];
					$orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? array('id');
					$sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null)) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null);
					$remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

					// Add the group header
					if (($remoteNew != $remoteCur || $remoteCur === false) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] ?? null))
					{
						$group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);
						$remoteCur = $remoteNew;

						$return .= '
  <tr>
    <th colspan="2" class="' . $groupclass . '">' . $group . '</th>
  </tr>';
						$groupclass = 'tl_folder_list';
					}
				}

				$return .= '
  <tr class="' . ((string) ($row['tstamp'] ?? null) === '0' ? 'draft ' : '') . 'click2edit toggle_select hover-row">
    ';

				$colspan = 1;

				// Handle strings and arrays
				if (!($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null))
				{
					$label = \is_array($label) ? implode(' ', $label) : $label;
				}
				elseif (!\is_array($label))
				{
					$label = array($label);
					$colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] ?? array());
				}

				// Show columns
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
				{
					foreach ($label as $j=>$arg)
					{
						$field = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] ?? null;
						$value = (string) $arg !== '' ? $arg : '-';

						$return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . explode(':', $field, 2)[0] . ($field == $firstOrderBy ? ' ordered_by' : '') . '">' . $value . '</td>';
					}
				}
				else
				{
					$return .= '<td class="tl_file_list">' . ($limitHeight ? '<div data-contao--limit-height-target="node">' : '') . $label . ($limitHeight ? '</div>' : '') . '</td>';
				}

				// Buttons ($row, $table, $root, $blnCircularReference, $children, $previous, $next)
				$return .= ((Input::get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
    <td class="tl_file_list tl_right_nowrap">' . $this->generateButtons($row, $this->strTable, $this->root) . ($this->strPickerFieldType ? $this->getPickerInputField($row['id']) : '') . '</td>') . '
  </tr>';
			}

			// Close the table body if the "show columns" option is active
			if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
			{
				$return .= '</tbody>';
			}

			// Close the table
			$return .= '
</table>' . ($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '') . '
</div>';

			// Add another panel at the end of the page
			if (str_contains($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'] ?? '', 'limit'))
			{
				$return .= $this->paginationMenu();
			}

			// Close the form
			if (Input::get('act') == 'select')
			{
				// Submit buttons
				$arrButtons = array();

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
				{
					$arrButtons['edit'] = '<button type="submit" name="edit" id="edit" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['editSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ?? null))
				{
					$arrButtons['delete'] = '<button type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['delAllConfirm'] . '\')">' . $GLOBALS['TL_LANG']['MSC']['deleteSelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'] ?? null))
				{
					$arrButtons['copy'] = '<button type="submit" name="copy" id="copy" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['copySelected'] . '</button>';
				}

				if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null))
				{
					$arrButtons['override'] = '<button type="submit" name="override" id="override" class="tl_submit" accesskey="v">' . $GLOBALS['TL_LANG']['MSC']['overrideSelected'] . '</button>';
				}

				// Call the buttons_callback (see #4691)
				if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] ?? null))
				{
					foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
					{
						if (\is_array($callback))
						{
							$arrButtons = System::importStatic($callback[0])->{$callback[1]}($arrButtons, $this);
						}
						elseif (\is_callable($callback))
						{
							$arrButtons = $callback($arrButtons, $this);
						}
					}
				}

				if (\count($arrButtons) < 3)
				{
					$strButtons = implode(' ', $arrButtons);
				}
				else
				{
					$strButtons = array_shift($arrButtons) . ' ';
					$strButtons .= '<div class="split-button">';
					$strButtons .= array_shift($arrButtons) . '<button type="button" id="sbtog">' . Image::getHtml('navcol.svg') . '</button> <ul class="invisible">';

					foreach ($arrButtons as $strButton)
					{
						$strButtons .= '<li>' . $strButton . '</li>';
					}

					$strButtons .= '</ul></div>';
				}

				$return .= '
</div>
<div class="tl_formbody_submit" style="text-align:right">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';
			}
		}

		if ($limitHeight)
		{
			$return = '<div
				data-controller="contao--limit-height"
				data-contao--limit-height-max-value="' . $limitHeight . '"
				data-contao--limit-height-expand-value="' . $GLOBALS['TL_LANG']['MSC']['expandNode'] . '"
				data-contao--limit-height-collapse-value="' . $GLOBALS['TL_LANG']['MSC']['collapseNode'] . '"
				data-contao--limit-height-expand-all-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][0] . '"
				data-contao--limit-height-expand-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['expandNodes'][1] . '"
				data-contao--limit-height-collapse-all-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
				data-contao--limit-height-collapse-all-title-value="' . $GLOBALS['TL_LANG']['DCA']['collapseNodes'][0] . '"
			>' . $return . '</div>';
		}

		return $return;
	}
}
