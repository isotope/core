<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\StringUtil;

class DC_TablePageId extends \DC_Table
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

        /** @var Session $objSession */
        $objSession = System::getContainer()->get('session');

        $this->reviseTable();

        // Add to clipboard
        if (Input::get('act') == 'paste')
        {
            $arrClipboard = $objSession->get('CLIPBOARD');

            $arrClipboard[$this->strTable] = array
            (
                'id' => Input::get('id'),
                'childs' => Input::get('childs'),
                'mode' => Input::get('mode')
            );

            $objSession->set('CLIPBOARD', $arrClipboard);
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
            if ($this->ptable && Input::get('table') && $this->Database->fieldExists('page_id', $this->strTable))
            {
                $this->procedure[] = 'page_id=?';
                $this->values[] = CURRENT_ID;
            }

            $return .= $this->panel();
            $return .= ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->parentView() : $this->listView();
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
            throw new InternalServerErrorException('Table "' . $this->strTable . '" is not sortable.');
        }

        $cr = array();

        // ID and page_id are mandatory
        if (!$this->intId || !isset($_GET['page_id']))
        {
            $this->redirect($this->getReferer());
        }

        // Get the new position
        $this->getNewPosition('cut', Input::get('page_id'), Input::get('mode') == '2');

        // Avoid circular references when there is no parent table
        if (!$this->ptable && $this->Database->fieldExists('page_id', $this->strTable))
        {
            $cr = $this->Database->getChildRecords($this->intId, $this->strTable);
            $cr[] = $this->intId;
        }

        /** @var Session $objSession */
        $objSession = System::getContainer()->get('session');

        // Empty clipboard
        $arrClipboard = $objSession->get('CLIPBOARD');
        $arrClipboard[$this->strTable] = array();
        $objSession->set('CLIPBOARD', $arrClipboard);

        // Check for circular references
        if (\in_array($this->set['page_id'], $cr))
        {
            throw new InternalServerErrorException('Attempt to relate record ' . $this->intId . ' of table "' . $this->strTable . '" to its child record ' . Input::get('page_id') . ' (circular reference).');
        }

        $this->set['tstamp'] = time();

        // HOOK: style sheet category
        if ($this->strTable == 'tl_style')
        {
            /** @var AttributeBagInterface $objSessionBag */
            $objSessionBag = $objSession->getBag('contao_backend');

            $filter = $objSessionBag->get('filter');
            $category = $filter['tl_style_' . CURRENT_ID]['category'];

            if ($category)
            {
                $this->set['category'] = $category;
            }
        }

        // Dynamically set the parent table of tl_content
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
        {
            $this->set['ptable'] = $this->ptable;
        }

        $this->Database->prepare("UPDATE " . $this->strTable . " %s WHERE id=?")
                       ->set($this->set)
                       ->execute($this->intId);

        // Call the oncut_callback
        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'] ?? null))
        {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'] as $callback)
            {
                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($this);
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
            throw new InternalServerErrorException('Table "' . $this->strTable . '" is not sortable.');
        }

        // page_id is mandatory
        if (!isset($_GET['page_id']))
        {
            $this->redirect($this->getReferer());
        }

        /** @var Session $objSession */
        $objSession = System::getContainer()->get('session');

        $arrClipboard = $objSession->get('CLIPBOARD');

        if (isset($arrClipboard[$this->strTable]) && \is_array($arrClipboard[$this->strTable]['id']))
        {
            foreach ($arrClipboard[$this->strTable]['id'] as $id)
            {
                $this->intId = $id;
                $this->cut(true);
                Input::setGet('page_id', $id);
                Input::setGet('mode', 1);
            }
        }

        $this->redirect($this->getReferer());
    }

    /**
     * Calculate the new position of a moved or inserted record
     *
     * @param string  $mode
     * @param integer $page_id
     * @param boolean $insertInto
     */
    protected function getNewPosition($mode, $page_id=null, $insertInto=false)
    {
        // If there is page_id and sorting
        if ($this->Database->fieldExists('page_id', $this->strTable) && $this->Database->fieldExists('sorting', $this->strTable))
        {
            // page_id is not set - only valid for duplicated records, as they get the same parent ID as the original record!
            if ($page_id === null && $this->intId && $mode == 'copy')
            {
                $page_id = $this->intId;
            }

            // page_id is set (insert after or into the parent record)
            if (is_numeric($page_id))
            {
                $newpage_id = null;
                $newSorting = null;
                $filter = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->strTable . '_' . CURRENT_ID : $this->strTable;

                /** @var Session $objSession */
                $objSession = System::getContainer()->get('session');
                $session = $objSession->all();

                // Consider the pagination menu when inserting at the top (see #7895)
                if ($insertInto && isset($session['filter'][$filter]['limit']))
                {
                    $limit = substr($session['filter'][$filter]['limit'], 0, strpos($session['filter'][$filter]['limit'], ','));

                    if ($limit > 0)
                    {
                        $objInsertAfter = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE page_=? ORDER BY sorting")
                                                         ->limit(1, $limit - 1)
                                                         ->execute($page_id);

                        if ($objInsertAfter->numRows)
                        {
                            $insertInto = false;
                            $page_id = $objInsertAfter->id;
                        }
                    }
                }

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
                            $objNewSorting = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE page_id=? ORDER BY sorting")
                                                            ->execute($page_id);

                            $count = 2;
                            $newSorting = 128;

                            while ($objNewSorting->next())
                            {
                                $this->Database->prepare("UPDATE " . $this->strTable . " SET sorting=? WHERE id=?")
                                               ->limit(1)
                                               ->execute(($count++ * 128), $objNewSorting->id);
                            }
                        }

                        // Else new sorting = (current sorting / 2)
                        else
                        {
                            $newSorting = ($curSorting / 2);
                        }
                    }

                    // Else new sorting = 128
                    else
                    {
                        $newSorting = 128;
                    }
                }

                // Else insert the current record after the parent record
                elseif ($page_id > 0)
                {
                    $objSorting = $this->Database->prepare("SELECT page_id, sorting FROM " . $this->strTable . " WHERE id=?")
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
                            if ($objNextSorting->sorting !== null)
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
                                                       ->execute(($count++ * 128), $objNewSorting->id);

                                        if ($objNewSorting->sorting == $curSorting)
                                        {
                                            $newSorting = ($count++ * 128);
                                        }
                                    }
                                }

                                // Else new sorting = (current sorting + next sorting) / 2
                                else
                                {
                                    $newSorting = (($curSorting + $nxtSorting) / 2);
                                }
                            }

                            // Else new sorting = (current sorting + 128)
                            else
                            {
                                $newSorting = ($curSorting + 128);
                            }
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
                $this->set['page_id'] = (int) $newpage_id;
                $this->set['sorting'] = (int) $newSorting;
            }
        }

        // If there is only page_id
        elseif ($this->Database->fieldExists('page_id', $this->strTable))
        {
            // page_id is not set - only valid for duplicated records, as they get the same parent ID as the original record!
            if ($page_id === null && $this->intId && $mode == 'copy')
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
                    $objParentRecord = $this->Database->prepare("SELECT page_id FROM " . $this->strTable . " WHERE id=?")
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
                    $newSorting = null;
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
                                               ->execute(($count++ * 128), $objNewSorting->id);

                                if ($objNewSorting->sorting == $curSorting)
                                {
                                    $newSorting = ($count++ * 128);
                                }
                            }
                        }

                        // Else new sorting = (current sorting + next sorting) / 2
                        else
                        {
                            $newSorting = (($curSorting + $nxtSorting) / 2);
                        }
                    }

                    // Else new sorting = (current sorting + 128)
                    else
                    {
                        $newSorting = ($curSorting + 128);
                    }

                    // Set new sorting
                    $this->set['sorting'] = (int) $newSorting;

                    return;
                }
            }

            // ID is not set or not found (insert at the end)
            $objNextSorting = $this->Database->execute("SELECT MAX(sorting) AS sorting FROM " . $this->strTable);
            $this->set['sorting'] = ((int) $objNextSorting->sorting + 128);
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

        if ($ptable === null && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 5)
        {
            $ptable = $this->strTable;
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $new_records = $objSessionBag->get('new_records');

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['reviseTable']) && \is_array($GLOBALS['TL_HOOKS']['reviseTable']))
        {
            foreach ($GLOBALS['TL_HOOKS']['reviseTable'] as $callback)
            {
                $status = null;

                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $status = $this->{$callback[0]}->{$callback[1]}($this->strTable, $new_records[$this->strTable] ?? null, $ptable, $ctable);
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

        // Delete all new but incomplete records (tstamp=0)
        if (!empty($new_records[$this->strTable]) && \is_array($new_records[$this->strTable]))
        {
            $intPreserved = null;

            // Unset the preserved record (see #1129)
            if ($this->intPreserveRecord && ($index = array_search($this->intPreserveRecord, $new_records[$this->strTable])) !== false)
            {
                $intPreserved = $new_records[$this->strTable][$index];
                unset($new_records[$this->strTable][$index]);
            }

            // Remove the entries from the database
            if (!empty($new_records[$this->strTable]))
            {
                $origId = $this->id;
                $origActiveRecord = $this->activeRecord;
                $ids = array_map('\intval', $new_records[$this->strTable]);

                foreach ($ids as $id)
                {
                    // Get the current record
                    $objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
                                             ->limit(1)
                                             ->execute($id);

                    $this->id = $id;
                    $this->activeRecord = $objRow;

                    // Invalidate cache tags (no need to invalidate the parent)
                    $this->invalidateCacheTags();
                }

                $this->id = $origId;
                $this->activeRecord = $origActiveRecord;

                $objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE id IN(" . implode(',', $ids) . ") AND tstamp=0");

                if ($objStmt->affectedRows > 0)
                {
                    $reload = true;
                }
            }

            // Remove the entries from the session
            if ($intPreserved !== null)
            {
                $new_records[$this->strTable] = array($intPreserved);
            }
            else
            {
                unset($new_records[$this->strTable]);
            }

            $objSessionBag->set('new_records', $new_records);
        }

        // Delete all records of the current table that are not related to the parent table
        if ($ptable)
        {
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
            {
                $objIds = $this->Database->execute("SELECT c.id FROM " . $this->strTable . " c LEFT JOIN " . $ptable . " p ON c.page_id=p.id WHERE c.ptable='" . $ptable . "' AND p.id IS NULL");
            }
            elseif ($ptable == $this->strTable)
            {
                $objIds = $this->Database->execute('SELECT c.id FROM ' . $this->strTable . ' c LEFT JOIN ' . $ptable . ' p ON c.page_id=p.id WHERE p.id IS NULL AND c.page_id > 0');
            }
            else
            {
                $objIds = $this->Database->execute("SELECT c.id FROM " . $this->strTable . " c LEFT JOIN " . $ptable . " p ON c.page_id=p.id WHERE p.id IS NULL");
            }

            if ($objIds->numRows)
            {
                $objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE id IN(" . implode(',', array_map('\intval', $objIds->fetchEach('id'))) . ")");

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
                    // Load the DCA configuration, so we can check for "dynamicPtable"
                        $this->loadDataContainer($v);

                    if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'] ?? null)
                    {
                        $objIds = $this->Database->execute("SELECT c.id FROM " . $v . " c LEFT JOIN " . $this->strTable . " p ON c.page_id=p.id WHERE c.ptable='" . $this->strTable . "' AND p.id IS NULL");
                    }
                    else
                    {
                        $objIds = $this->Database->execute("SELECT c.id FROM " . $v . " c LEFT JOIN " . $this->strTable . " p ON c.page_id=p.id WHERE p.id IS NULL");
                    }

                    if ($objIds->numRows)
                    {
                        $objStmt = $this->Database->execute("DELETE FROM " . $v . " WHERE id IN(" . implode(',', array_map('\intval', $objIds->fetchEach('id'))) . ")");

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
        /** @var Session $objSession */
        $objSession = System::getContainer()->get('session');

        $blnClipboard = false;
        $arrClipboard = $objSession->get('CLIPBOARD');
        $table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 6 ? $this->ptable : $this->strTable;
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
        $labelEditHeader = $GLOBALS['TL_LANG'][$this->ptable]['editmeta'] ?? $GLOBALS['TL_LANG'][$this->strTable]['editheader'] ?? $GLOBALS['TL_LANG']['DCA']['editheader'];

        $return = Message::generate() . '
<div id="tl_buttons">' . (Input::get('nb') ? '&nbsp;' : ('
<a href="' . $this->getReferer(true, 'tl_iso_product') . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>')) . ' ' . ((Input::get('act') != 'select' && !$blnClipboard && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null)) ? '
<a href="' . $this->addToUrl(($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;page_id=' . $this->intId)) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1]) . '" accesskey="n" onclick="Backend.getScrollOffset()">' . $labelNew[0] . '</a> ' : '') . ($blnClipboard ? '
<a href="' . $this->addToUrl('clipboard=1') . '" class="header_clipboard" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']) . '" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['clearClipboard'] . '</a> ' : $this->generateGlobalButtons()) . '
</div>';

        // Get all details of the parent record
        $objParent = $this->Database->prepare("SELECT * FROM " . $this->ptable . " WHERE id=?")
                                    ->limit(1)
                                    ->execute(CURRENT_ID);

        if ($objParent->numRows < 1)
        {
            return $return;
        }

        $return .= ((Input::get('act') == 'select') ? '

<form id="tl_select" class="tl_form' . ((Input::get('act') == 'select') ? ' unselectable' : '') . '" method="post" novalidate>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' : '') . ($blnClipboard ? '
<div id="paste_hint" data-add-to-scroll-offset="20">
  <p>' . $GLOBALS['TL_LANG']['MSC']['selectNewPosition'] . '</p>
</div>' : '') . '
<div class="tl_listing_container parent_view' . ($this->strPickerFieldType ? ' picker unselectable' : '') . '" id="tl_listing"' . $this->getPickerValueAttribute() . '>
<div class="tl_header click2edit toggle_select hover-div">';

        // List all records of the child table
        if (!Input::get('act') || \in_array(Input::get('act'), array('paste', 'select')))
        {
            $this->import(BackendUser::class, 'User');

            // Header
            $imagePasteNew = Image::getHtml('new.svg', $labelPasteNew[0]);
            $imagePasteAfter = Image::getHtml('pasteafter.svg', $labelPasteAfter[0]);
            $imageEditHeader = Image::getHtml('header.svg', sprintf(\is_array($labelEditHeader) ? $labelEditHeader[0] : $labelEditHeader, $objParent->id));

            $return .= '
<div class="tl_content_right">' . ((Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">' : ($blnClipboard ? '
<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;page_id=' . $objParent->id . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars($labelPasteAfter[0]) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>' : ((!($GLOBALS['TL_DCA'][$this->ptable]['config']['notEditable'] ?? null) && $this->User->canEditFieldsOf($this->ptable)) ? '
<a href="' . preg_replace('/&(amp;)?table=[^& ]*/i', ($this->ptable ? '&amp;table=' . $this->ptable : ''), $this->addToUrl('act=edit' . (Input::get('nb') ? '&amp;nc=1' : ''))) . '" class="edit" title="' . StringUtil::specialchars(sprintf(\is_array($labelEditHeader) ? $labelEditHeader[1] : $labelEditHeader, $objParent->id)) . '">' . $imageEditHeader . '</a> ' . $this->generateHeaderButtons($objParent->row(), $this->ptable) : '') . (($blnHasSorting && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null)) ? '
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

                    $objLabel = $this->Database->prepare("SELECT " . Database::quoteIdentifier($arrForeignKey[1]) . " AS value FROM " . $arrForeignKey[0] . " WHERE id=?")
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
                elseif (($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['isAssociative'] ?? null) || array_is_assoc($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options'] ?? null))
                {
                    $_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options'][$_v] ?? null;
                }
                elseif (\is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'] ?? null))
                {
                    $strClass = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'][0];
                    $strMethod = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options_callback'][1];

                    $this->import($strClass);
                    $options_callback = $this->$strClass->$strMethod($this);

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
                $strClass = $GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][0];
                $strMethod = $GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][1];

                $this->import($strClass);
                $add = $this->$strClass->$strMethod($add, $this);
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
                    $query = "SELECT *, (SELECT " . Database::quoteIdentifier($key[1]) . " FROM " . $key[0] . " WHERE " . $this->strTable . "." . Database::quoteIdentifier($firstOrderBy) . "=" . $key[0] . ".id) AS foreignKey FROM " . $this->strTable;
                    $orderBy[0] = 'foreignKey';
                }
            }
            elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? null))
            {
                $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);
            }

            $arrProcedure = $this->procedure;
            $arrValues = $this->values;

            // Support empty ptable fields
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
            {
                $arrProcedure[] = ($this->ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";
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
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag']) && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'] % 2) == 0)
                    {
                        $orderBy[$k] .= ' DESC';
                    }
                }

                $query .= " ORDER BY " . implode(', ', $orderBy);
            }

            $objOrderByStmt = $this->Database->prepare($query);

            // LIMIT
            if ($this->limit)
            {
                $arrLimit = explode(',', $this->limit) + array(null, null);
                $objOrderByStmt->limit($arrLimit[1], $arrLimit[0]);
            }

            $objOrderBy = $objOrderByStmt->execute($arrValues);

            if ($objOrderBy->numRows < 1)
            {
                return $return . '
<p class="tl_empty_parent_view">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>
</div>';
            }

            // Call the child_record_callback
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']))
            {
                $strGroup = '';
                $blnIndent = false;
                $intWrapLevel = 0;
                $row = $objOrderBy->fetchAllAssoc();

                // Make items sortable
                if ($blnHasSorting)
                {
                    $return .= '

<ul id="ul_' . CURRENT_ID . '">';
                }

                for ($i=0, $c=\count($row); $i<$c; $i++)
                {
                    $this->current[] = $row[$i]['id'];
                    $imagePasteAfter = Image::getHtml('pasteafter.svg', sprintf($labelPasteAfter[1] ?? $labelPasteAfter[0], $row[$i]['id']));
                    $imagePasteNew = Image::getHtml('new.svg', sprintf($labelPasteNew[1] ?? $labelPasteNew[0], $row[$i]['id']));

                    // Decrypt encrypted value
                    foreach ($row[$i] as $k=>$v)
                    {
                        if ($GLOBALS['TL_DCA'][$table]['fields'][$k]['eval']['encrypt'] ?? null)
                        {
                            $row[$i][$k] = Encryption::decrypt(StringUtil::deserialize($v));
                        }
                    }

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
<div class="tl_content' . ($blnWrapperStart ? ' wrapper_start' : '') . ($blnWrapperSeparator ? ' wrapper_separator' : '') . ($blnWrapperStop ? ' wrapper_stop' : '') . ($blnIndent ? ' indent indent_' . $intWrapLevel : '') . ($blnIndentFirst ? ' indent_first' : '') . ($blnIndentLast ? ' indent_last' : '') . (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_class']) ? ' ' . $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_class'] : '') . (($i%2 == 0) ? ' even' : ' odd') . ' click2edit toggle_select hover-div">
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
                        $return .= $this->generateButtons($row[$i], $this->strTable, $this->root, false, null, ($row[($i-1)]['id'] ?? null), ($row[($i+1)]['id'] ?? null));

                        // Sortable table
                        if ($blnHasSorting)
                        {
                            // Create new button
                            if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null))
                            {
                                $return .= ' <a href="' . $this->addToUrl('act=create&amp;mode=1&amp;page_id=' . $row[$i]['id'] . '&amp;id=' . $objParent->id . (Input::get('nb') ? '&amp;nc=1' : '')) . '" title="' . StringUtil::specialchars(sprintf($labelPasteNew[1], $row[$i]['id'])) . '">' . $imagePasteNew . '</a>';
                            }

                            // Prevent circular references
                            if (($blnClipboard && $arrClipboard['mode'] == 'cut' && $row[$i]['id'] == $arrClipboard['id']) || ($blnMultiboard && $arrClipboard['mode'] == 'cutAll' && \in_array($row[$i]['id'], $arrClipboard['id'])))
                            {
                                $return .= ' ' . Image::getHtml('pasteafter_.svg');
                            }

                            // Copy/move multiple
                            elseif ($blnMultiboard)
                            {
                                $return .= ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;page_id=' . $row[$i]['id']) . '" title="' . StringUtil::specialchars(sprintf($labelPasteAfter[1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
                            }

                            // Paste buttons
                            elseif ($blnClipboard)
                            {
                                $return .= ' <a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;page_id=' . $row[$i]['id'] . '&amp;id=' . $arrClipboard['id']) . '" title="' . StringUtil::specialchars(sprintf($labelPasteAfter[1], $row[$i]['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>';
                            }

                            // Drag handle
                            if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null))
                            {
                                $return .= ' <button type="button" class="drag-handle" title="' . StringUtil::specialchars(sprintf(\is_array($labelCut) ? $labelCut[1] : $labelCut, $row[$i]['pid'])) . '" aria-hidden="true">' . Image::getHtml('drag.svg') . '</button>';
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

                        $this->import($strClass);
                        $return .= '</div>' . $this->$strClass->$strMethod($row[$i]) . '</div>';
                    }
                    elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'] ?? null))
                    {
                        $return .= '</div>' . $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']($row[$i]) . '</div>';
                    }

                    // Make items sortable
                    if ($blnHasSorting)
                    {
                        $return .= '
</li>';
                    }
                }
            }
        }

        // Make items sortable
        if ($blnHasSorting && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null) && Input::get('act') != 'select')
        {
            $return .= '
</ul>
<script>
Isotope.makeParentViewSortable("ul_' . CURRENT_ID . '");
</script>';
        }

        $return .= ($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '') . '
</div>';

        // Add another panel at the end of the page
        if (strpos($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'] ?? '', 'limit') !== false)
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
                        $this->import($callback[0]);
                        $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
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

        return $return;
    }

    /**
     * List all records of the current table and return them as HTML string
     *
     * @return string
     */
    protected function listView()
    {
        $table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 6 ? $this->ptable : $this->strTable;
        $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? array();
        $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

        if (\is_array($this->orderBy) && !empty($this->orderBy[0]))
        {
            $orderBy = $this->orderBy;
            $firstOrderBy = $this->firstOrderBy;
        }

        // Check the default labels (see #509)
        $labelNew = $GLOBALS['TL_LANG'][$this->strTable]['new'] ?? $GLOBALS['TL_LANG']['DCA']['new'];

        $query = "SELECT * FROM " . $this->strTable;

        if (!empty($this->procedure))
        {
            $query .= " WHERE " . implode(' AND ', $this->procedure);
        }

        if (!empty($this->root) && \is_array($this->root))
        {
            $query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
        }

        if (\is_array($orderBy) && $orderBy[0])
        {
            foreach ($orderBy as $k=>$v)
            {
                list($key, $direction) = explode(' ', $v, 2) + array(null, null);

                // If there is no direction, check the global flag in sorting mode 1 or the field flag in all other sorting modes
                if (!$direction)
                {
                    if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 1 && isset($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag']) && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0)
                    {
                        $direction = 'DESC';
                    }
                    elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag']) && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'] % 2) == 0)
                    {
                        $direction = 'DESC';
                    }
                }

                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['findInSet'] ?? null)
                {
                    $direction = null;

                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'] ?? null))
                    {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][1];

                        $this->import($strClass);
                        $keys = $this->$strClass->$strMethod($this);
                    }
                    elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'] ?? null))
                    {
                        $keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback']($this);
                    }
                    else
                    {
                        $keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options'] ?? array();
                    }

                    if (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['isAssociative'] ?? null) || array_is_assoc($keys))
                    {
                        $keys = array_keys($keys);
                    }

                    $orderBy[$k] = $this->Database->findInSet($v, $keys);
                }
                elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'] ?? null, array(5, 6, 7, 8, 9, 10)))
                {
                    $orderBy[$k] = "CAST($key AS SIGNED)"; // see #5503
                }

                if ($direction)
                {
                    $orderBy[$k] = $key . ' ' . $direction;
                }
            }

            if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 3)
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
                $query .= " ORDER BY " . implode(', ', $orderBy);
            }
        }

        $objRowStmt = $this->Database->prepare($query);

        if ($this->limit)
        {
            $arrLimit = explode(',', $this->limit) + array(null, null);
            $objRowStmt->limit($arrLimit[1], $arrLimit[0]);
        }

        $objRow = $objRowStmt->execute($this->values);

        // Display buttos
        $return = Message::generate() . '
<div id="tl_buttons">' . (Input::get('act') == 'select' ? '
<a href="' . $this->getReferer(true, 'tl_iso_product') . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="contao/main.php?' . $GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'] . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : '')) . ((Input::get('act') != 'select' && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null)) ? '
<a href="' . ($this->ptable ? $this->addToUrl('act=create' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;page_id=' . $this->intId) : $this->addToUrl('act=create')) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1] ?? '') . '" accesskey="n" onclick="Backend.getScrollOffset()">' . $labelNew[0] . '</a> ' : '') . $this->generateGlobalButtons() . '
</div>';

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
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' : '') . '
<div class="tl_listing_container list_view" id="tl_listing"' . $this->getPickerValueAttribute() . '>' . ((Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '
<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '') . '
<table class="tl_listing' . ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ? ' showColumns' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '">';

            // Automatically add the "order by" field as last column if we do not have group headers
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
            {
                $blnFound = false;

                // Extract the real key and compare it to $firstOrderBy
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
                {
                    if (strpos($f, ':') !== false)
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
  <tr>';

                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
                {
                    if (strpos($f, ':') !== false)
                    {
                        list($f) = explode(':', $f, 2);
                    }

                    $return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'] ?? null)) . '</th>';
                }

                $return .= '
    <th class="tl_folder_tlist tl_right_nowrap"></th>
  </tr>';
            }

            // Process result and add label and buttons
            $remoteCur = false;
            $groupclass = 'tl_folder_tlist';
            $eoCount = -1;

            foreach ($result as $row)
            {
                $args = array();
                $this->current[] = $row['id'];
                $showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

                // Label
                foreach ($showFields as $k=>$v)
                {
                    // Decrypt the value
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['encrypt'] ?? null)
                    {
                        $row[$v] = Encryption::decrypt(StringUtil::deserialize($row[$v]));
                    }

                    if (strpos($v, ':') !== false)
                    {
                        list($strKey, $strTable) = explode(':', $v, 2);
                        list($strTable, $strField) = explode('.', $strTable, 2);

                        $objRef = $this->Database->prepare("SELECT " . Database::quoteIdentifier($strField) . " FROM " . $strTable . " WHERE id=?")
                                                 ->limit(1)
                                                 ->execute($row[$strKey]);

                        $args[$k] = $objRef->numRows ? $objRef->$strField : '';
                    }
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'] ?? null, array(5, 6, 7, 8, 9, 10)))
                    {
                        if (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] ?? null) == 'date')
                        {
                            $args[$k] = $row[$v] ? Date::parse(Config::get('dateFormat'), $row[$v]) : '-';
                        }
                        elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] ?? null) == 'time')
                        {
                            $args[$k] = $row[$v] ? Date::parse(Config::get('timeFormat'), $row[$v]) : '-';
                        }
                        else
                        {
                            $args[$k] = $row[$v] ? Date::parse(Config::get('datimFormat'), $row[$v]) : '-';
                        }
                    }
                    elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['isBoolean'] ?? null) || (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] ?? null) == 'checkbox' && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple'] ?? null)))
                    {
                        $args[$k] = $row[$v] ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                    }
                    else
                    {
                        $row_v = StringUtil::deserialize($row[$v]);

                        if (\is_array($row_v))
                        {
                            $args_k = array();

                            foreach ($row_v as $option)
                            {
                                $args_k[] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option] ?: $option;
                            }

                            $args[$k] = implode(', ', $args_k);
                        }
                        elseif (isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]))
                        {
                            $args[$k] = \is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]] ?? null) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
                        }
                        elseif ((($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['isAssociative'] ?? null) || array_is_assoc($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'] ?? null)) && isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]]))
                        {
                            $args[$k] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]];
                        }
                        else
                        {
                            $args[$k] = $row[$v];
                        }
                    }
                }

                // Shorten the label it if it is too long
                $label = vsprintf($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] ?? '%s', $args);

                if (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] ?? null) > 0 && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < \strlen(strip_tags($label)))
                {
                    $label = trim(StringUtil::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
                }

                // Remove empty brackets (), [], {}, <> and empty tags from the label
                $label = preg_replace('/\( *\) ?|\[ *] ?|{ *} ?|< *> ?/', '', $label);
                $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/', '', $label);

                // Build the sorting groups
                if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) > 0)
                {
                    $current = $row[$firstOrderBy];
                    $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'] ?? array();
                    $sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null)) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];
                    $remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

                    // Add the group header
                    if (($remoteNew != $remoteCur || $remoteCur === false) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] ?? null))
                    {
                        $eoCount = -1;
                        $group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);
                        $remoteCur = $remoteNew;

                        $return .= '
  <tr>
    <td colspan="2" class="' . $groupclass . '">' . $group . '</td>
  </tr>';
                        $groupclass = 'tl_folder_list';
                    }
                }

                $return .= '
  <tr class="' . ((++$eoCount % 2 == 0) ? 'even' : 'odd') . ' click2edit toggle_select hover-row">
    ';

                $colspan = 1;

                // Call the label_callback ($row, $label, $this)
                if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'] ?? null) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'] ?? null))
                {
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'] ?? null))
                    {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

                        $this->import($strClass);
                        $args = $this->$strClass->$strMethod($row, $label, $this, $args);
                    }
                    elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'] ?? null))
                    {
                        $args = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']($row, $label, $this, $args);
                    }

                    // Handle strings and arrays
                    if (!($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null))
                    {
                        $label = \is_array($args) ? implode(' ', $args) : $args;
                    }
                    elseif (!\is_array($args))
                    {
                        $args = array($args);
                        $colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] ?? array());
                    }
                }

                // Show columns
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null)
                {
                    foreach ($args as $j=>$arg)
                    {
                        $field = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] ?? null;

                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
                        {
                            $value = $arg ?: '-';
                        }
                        else
                        {
                            $value = (string) $arg !== '' ? $arg : '-';
                        }

                        $return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . explode(':', $field, 2)[0] . ($field == $firstOrderBy ? ' ordered_by' : '') . '">' . $value . '</td>';
                    }
                }
                else
                {
                    $return .= '<td class="tl_file_list">' . $label . '</td>';
                }

                // Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
                $return .= ((Input::get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
    <td class="tl_file_list tl_right_nowrap">' . $this->generateButtons($row, $this->strTable, $this->root) . ($this->strPickerFieldType ? $this->getPickerInputField($row['id']) : '') . '</td>') . '
  </tr>';
            }

            // Close the table
            $return .= '
</table>' . ($this->strPickerFieldType == 'radio' ? '
<div class="tl_radio_reset">
<label for="tl_radio_reset" class="tl_radio_label">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="picker" id="tl_radio_reset" value="" class="tl_tree_radio">
</div>' : '') . '
</div>';

            // Add another panel at the end of the page
            if (strpos($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'] ?? '', 'limit') !== false)
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
                            $this->import($callback[0]);
                            $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
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

        return $return;
    }
}
