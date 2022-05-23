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
     * @return string
     */
    public function showAll()
    {
        $return = '';
        $this->limit = '';
        $this->bid = 'tl_buttons';

        // Clean up old tl_undo and tl_log entries
        if ($this->strTable == 'tl_undo' && \strlen(\Contao\Config::get('undoPeriod')))
        {
            $this->Database->prepare("DELETE FROM tl_undo WHERE tstamp<?")
                           ->execute((int) (time() - \Contao\Config::get('undoPeriod')));
        }
        elseif ($this->strTable == 'tl_log' && \strlen(\Contao\Config::get('logPeriod')))
        {
            $this->Database->prepare("DELETE FROM tl_log WHERE tstamp<?")
                           ->execute((int) (time() - \Contao\Config::get('logPeriod')));
        }

        $this->reviseTable();

        // Add to clipboard
        if ('paste' === Input::get('act'))
        {
            $arrClipboard = System::getContainer()->get('session')->get('CLIPBOARD');

            $arrClipboard[$this->strTable] = array
            (
                'id' => Input::get('id'),
                'childs' => Input::get('childs'),
                'mode' => Input::get('mode')
            );

            System::getContainer()->get('session')->set('CLIPBOARD', $arrClipboard);
        }

        // Custom filter
        if (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']) && \is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']))
        {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $filter)
            {
                $this->procedure[] = $filter[0];
                $this->values[] = $filter[1];
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
            if (Input::get('table') && $this->ptable && $this->Database->fieldExists('page_id', $this->strTable))
            {
                $this->procedure[] = 'page_id=?';
                $this->values[] = CURRENT_ID;
            }

            $return .= $this->panel();
            $return .= ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) ? $this->parentView() : $this->listView();

            // Add another panel at the end of the page
            if (strpos($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'], 'limit') !== false)
            {
                $return .= $this->paginationMenu();
            }
        }

        // Store the current IDs
        $session = System::getContainer()->get('session')->all();
        $session['CURRENT']['IDS'] = $this->current;
        System::getContainer()->get('session')->replace($session);

        return $return;
    }



    /**
     * Assign a new position to an existing record
     * @param boolean
     */
    public function cut($blnDoNotRedirect=false)
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
        {
            throw new InternalServerErrorException('Table "'.$this->strTable.'" is not sortable');
        }

        $cr = array();

        // ID and page_id are mandatory
        if (!$this->intId || !\strlen(Input::get('page_id')))
        {
            $this->redirect($this->getReferer());
        }

        // Get the new position
        $this->getNewPosition('cut', Input::get('page_id'), (Input::get('mode') == '2' ? true : false));

        // Avoid circular references when there is no parent table
        if ($this->Database->fieldExists('page_id', $this->strTable) && !\strlen($this->ptable))
        {
            $cr = $this->Database->getChildRecords($this->intId, $this->strTable);
            $cr[] = $this->intId;
        }

        // Empty clipboard
        $arrClipboard = System::getContainer()->get('session')->get('CLIPBOARD');
        $arrClipboard[$this->strTable] = array();
        System::getContainer()->get('session')->set('CLIPBOARD', $arrClipboard);

        // Update the record
        if (\in_array($this->set['page_id'], $cr))
        {
            throw new InternalServerErrorException('Attempt to relate record '.$this->intId.' of table "'.$this->strTable.'" to its child record '.Input::get('page_id').' (circular reference)');
        }

        $this->set['tstamp'] = time();

        // HOOK: style sheet category
        if ($this->strTable == 'tl_style')
        {
            $filter = Session::getInstance()->get('filter');
            $category = $filter['tl_style_' . CURRENT_ID]['category'];

            if ($category != '')
            {
                $this->set['category'] = $category;
            }
        }

        // Dynamically set the parent table of tl_content
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
        {
            $this->set['ptable'] = $this->ptable;
        }

        $this->Database->prepare("UPDATE " . $this->strTable . " %s WHERE id=?")
                       ->set($this->set)
                       ->execute($this->intId);

        // Call the oncut_callback
        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback']))
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
     */
    public function cutAll()
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
        {
            throw new InternalServerErrorException('Table "'.$this->strTable.'" is not sortable');
        }

        // page_id is mandatory
        if (!\strlen(Input::get('page_id')))
        {
            $this->redirect($this->getReferer());
        }

        $arrClipboard = System::getContainer()->get('session')->get('CLIPBOARD');

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
            if ($page_id === null && $this->intId && $mode == 'copy')
            {
                $page_id = $this->intId;
            }

            // page_id is set (insert after or into the parent record)
            if (is_numeric($page_id))
            {
                $newpage_id = null;
                $newSorting = null;

                // Insert the current record at the beginning when inserting into the parent record
                if ($insertInto)
                {
                    $newpage_id = $page_id;
                    $objSorting = $this->Database->prepare("SELECT MIN(sorting) AS sorting FROM {$this->strTable} WHERE page_id=?")
                                                 ->execute($page_id);

                    // Select sorting value of the first record
                    if ($objSorting->numRows)
                    {
                        $curSorting = $objSorting->sorting;

                        // Resort if the new sorting value is not an integer or smaller than 1
                        if (($curSorting % 2) != 0 || $curSorting < 1)
                        {
                            $objNewSorting = $this->Database->prepare("SELECT id FROM {$this->strTable} WHERE page_id=? ORDER BY sorting" )
                                                            ->execute($page_id);

                            $count = 2;
                            $newSorting = 128;

                            while ($objNewSorting->next())
                            {
                                $this->Database->prepare("UPDATE {$this->strTable} SET sorting=? WHERE id=?")
                                               ->limit(1)
                                               ->execute(($count++ * 128), $objNewSorting->id);
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
                    $objSorting = $this->Database->prepare("SELECT page_id, sorting FROM {$this->strTable} WHERE id=?")
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
                            $objNextSorting = $this->Database->prepare("SELECT MIN(sorting) AS sorting FROM {$this->strTable} WHERE page_id=? AND sorting>?")
                                                             ->execute($newpage_id, $curSorting);

                            // Select sorting value of the next record
                            if ($objNextSorting->sorting !== null)
                            {
                                $nxtSorting = $objNextSorting->sorting;

                                // Resort if the new sorting value is no integer or bigger than a MySQL integer
                                if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
                                {
                                    $count = 1;

                                    $objNewSorting = $this->Database->prepare("SELECT id, sorting FROM {$this->strTable} WHERE page_id=? ORDER BY sorting")
                                                                    ->execute($newpage_id);

                                    while ($objNewSorting->next())
                                    {
                                        $this->Database->prepare("UPDATE {$this->strTable} SET sorting=? WHERE id=?")
                                                       ->execute(($count++ * 128), $objNewSorting->id);

                                        if ($objNewSorting->sorting == $curSorting)
                                        {
                                            $newSorting = ($count++ * 128);
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
                    $objParentRecord = $this->Database->prepare("SELECT page_id FROM {$this->strTable} WHERE id=?")
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
                $objCurrentRecord = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE id=?")
                                                   ->limit(1)
                                                   ->execute($this->intId);

                // Select current record
                if ($objCurrentRecord->numRows)
                {
                    $newSorting = null;
                    $curSorting = $objCurrentRecord->sorting;

                    $objNextSorting = $this->Database->prepare("SELECT MIN(sorting) AS sorting FROM {$this->strTable} WHERE sorting>?")
                                                     ->execute($curSorting);

                    // Select sorting value of the next record
                    if ($objNextSorting->numRows)
                    {
                        $nxtSorting = $objNextSorting->sorting;

                        // Resort if the new sorting value is no integer or bigger than a MySQL integer field
                        if ((($curSorting + $nxtSorting) % 2) != 0 || $nxtSorting >= 4294967295)
                        {
                            $count = 1;

                            $objNewSorting = $this->Database->execute("SELECT id, sorting FROM {$this->strTable} ORDER BY sorting");

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
                        else $newSorting = (($curSorting + $nxtSorting) / 2);
                    }

                    // Else new sorting = (current sorting + 128)
                    else $newSorting = ($curSorting + 128);

                    // Set new sorting
                    $this->set['sorting'] = (int) $newSorting;
                    return;
                }
            }

            // ID is not set or not found (insert at the end)
            $objNextSorting = $this->Database->execute("SELECT MAX(sorting) AS sorting FROM {$this->strTable}");
            $this->set['sorting'] = ((int) $objNextSorting->sorting + 128);
        }
    }


    /**
     * Delete all incomplete and unrelated records
     */
    protected function reviseTable()
    {
        $reload = false;
        $ptable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ptable'];
        $ctable = $GLOBALS['TL_DCA'][$this->strTable]['config']['ctable'];

        $new_records = Session::getInstance()->get('new_records');

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['reviseTable']) && \is_array($GLOBALS['TL_HOOKS']['reviseTable']))
        {
            foreach ($GLOBALS['TL_HOOKS']['reviseTable'] as $callback)
            {
                $status = null;

                if (\is_array($callback))
                {
                    $this->import($callback[0]);
                    $status = $this->{$callback[0]}->{$callback[1]}($this->strTable, $new_records[$this->strTable], $ptable, $ctable);
                }
                elseif (\is_callable($callback))
                {
                    $status = $callback($this->strTable, $new_records[$this->strTable], $ptable, $ctable);
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
            $objStmt = $this->Database->execute("DELETE FROM {$this->strTable} WHERE id IN(" . implode(',', array_map('intval', $new_records[$this->strTable])) . ") AND tstamp=0");

            if ($objStmt->affectedRows > 0)
            {
                $reload = true;
            }
        }

        // Delete all records of the current table that are not related to the parent table
        if ($ptable != '')
        {
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
            {
                $objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE ptable='" . $ptable . "' AND NOT EXISTS (SELECT * FROM " . $ptable . " WHERE " . $this->strTable . ".page_id = " . $ptable . ".id)");
            }
            else
            {
                $objStmt = $this->Database->execute("DELETE FROM " . $this->strTable . " WHERE NOT EXISTS (SELECT * FROM " . $ptable . " WHERE " . $this->strTable . ".page_id = " . $ptable . ".id)");
            }

            if ($objStmt->affectedRows > 0)
            {
                $reload = true;
            }
        }

        // Delete all records of the child table that are not related to the current table
        if (!empty($ctable) && \is_array($ctable))
        {
            foreach ($ctable as $v)
            {
                if ($v != '')
                {
                    // Load the DCA configuration so we can check for "dynamicPtable"
                    if (!isset($GLOBALS['loadDataContainer'][$v]))
                    {
                        $this->loadDataContainer($v);
                    }

                    if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'])
                    {
                        $objStmt = $this->Database->execute("DELETE FROM $v WHERE ptable='" . $this->strTable . "' AND NOT EXISTS (SELECT * FROM " . $this->strTable . " WHERE $v.page_id = " . $this->strTable . ".id)");
                    }
                    else
                    {
                        $objStmt = $this->Database->execute("DELETE FROM $v WHERE NOT EXISTS (SELECT * FROM " . $this->strTable . " WHERE $v.page_id = " . $this->strTable . ".id)");
                    }

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
        $arrClipboard = System::getContainer()->get('session')->get('CLIPBOARD');
        $table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;
        $blnHasSorting = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'][0] == 'sorting';
        $blnMultiboard = false;

        // Check clipboard
        if (!empty($arrClipboard[$table]))
        {
            $blnClipboard = true;
            $arrClipboard = $arrClipboard[$table];

            if (\is_array($arrClipboard['id']))
            {
                $blnMultiboard = true;
            }
        }

        // Load the language file and data container array of the parent table
        System::loadLanguageFile($this->ptable);
        $this->loadDataContainer($this->ptable);

        $return = '
<div id="tl_buttons">
<a href="'.$this->getReferer(true, $this->ptable).'" class="header_back" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>' . ((Input::get('act') !== 'select') ? (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<a href="'.$this->addToUrl(($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;page_id='.$this->intId)).'" class="header_new" title="'.StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a>' : '') . $this->generateGlobalButtons() . ($blnClipboard ? '<a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="x">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a>' : '') : '') . '
</div>' . $this->getMessages(true);

        // Get all details of the parent record
        $objParent = $this->Database->prepare("SELECT * FROM {$this->ptable} WHERE id=?")
                                    ->limit(1)
                                    ->execute(CURRENT_ID);

        if ($objParent->numRows < 1)
        {
            return $return;
        }

        $return .= (('select' === Input::get('act')) ? '

<form action="'.ampersand(Environment::get('request'), true).'" id="tl_select" class="tl_form" method="post" novalidate>
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">' : '').($blnClipboard ? '

<div id="paste_hint">
  <p>'.$GLOBALS['TL_LANG']['MSC']['selectNewPosition'].'</p>
</div>' : '').'

<div class="tl_listing_container parent_view">

<div class="tl_header click2edit hover-div">';

        // List all records of the child table
        if (!Input::get('act') || 'paste' === Input::get('act') || 'select' === Input::get('act'))
        {
            // Header
            $imagePasteNew = Image::getHtml('new.gif', $GLOBALS['TL_LANG'][$this->strTable]['pastenew'][0]);
            $imagePasteAfter = Image::getHtml('pasteafter.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]);
            $imageEditHeader = Image::getHtml('edit.gif', $GLOBALS['TL_LANG'][$this->strTable]['editheader'][0]);
            $strEditHeader = ($this->ptable != '') ? $GLOBALS['TL_LANG'][$this->ptable]['edit'][0] : $GLOBALS['TL_LANG'][$this->strTable]['editheader'][1];

            $return .= '
<div class="tl_content_right">'.((Input::get('act') == 'select') ? '
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">' : (!$GLOBALS['TL_DCA'][$this->ptable]['config']['notEditable'] ? '
<a href="'.preg_replace('/&(amp;)?table=[^& ]*/i', (($this->ptable != '') ? '&amp;table='.$this->ptable : ''), $this->addToUrl('act=edit')).'" class="edit" title="'.StringUtil::specialchars($strEditHeader).'">'.$imageEditHeader.'</a>' : '') . (($blnHasSorting && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) ? ' <a href="'.$this->addToUrl('act=create&amp;mode=2&amp;page_id='.$objParent->id.'&amp;id='.$this->intId).'" title="'.StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][0]).'">'.$imagePasteNew.'</a>' : '') . ($blnClipboard ? ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;page_id='.$objParent->id . (!$blnMultiboard ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a>' : '')) . '
</div>';

            // Format header fields
            $add = array();
            $headerFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerFields'];

            foreach ($headerFields as $v)
            {
                $_v = \Contao\StringUtil::deserialize($objParent->$v);

                if (\is_array($_v))
                {
                    $_v = implode(', ', $_v);
                }
                elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['multiple'])
                {
                    $_v = ($_v != '') ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                }
                elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] == 'date')
                {
                    $_v = $_v ? Date::parse(Config::get('dateFormat'), $_v) : '-';
                }
                elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] == 'time')
                {
                    $_v = $_v ? Date::parse(Config::get('timeFormat'), $_v) : '-';
                }
                elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['rgxp'] == 'datim')
                {
                    $_v = $_v ? Date::parse(Config::get('datimFormat'), $_v) : '-';
                }
                elseif ($v == 'tstamp')
                {
                    $objMaxTstamp = $this->Database->prepare("SELECT MAX(tstamp) AS tstamp FROM {$this->strTable} WHERE page_id=?")
                                                   ->execute($objParent->id);

                    if (!$objMaxTstamp->tstamp)
                    {
                        $objMaxTstamp->tstamp = $objParent->tstamp;
                    }

                    $_v = Date::parse(Config::get('datimFormat'), max($objParent->tstamp, $objMaxTstamp->tstamp));
                }
                elseif (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey']))
                {
                    $arrForeignKey = explode('.', $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['foreignKey'], 2);

                    $objLabel = $this->Database->prepare("SELECT " . $arrForeignKey[1] . " AS value FROM " . $arrForeignKey[0] . " WHERE id=?")
                                               ->limit(1)
                                               ->execute($_v);

                    if ($objLabel->numRows)
                    {
                        $_v = $objLabel->value;
                    }
                }
                elseif (\is_array($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v]))
                {
                    $_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v][0];
                }
                elseif (isset($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v]))
                {
                    $_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['reference'][$_v];
                }
                elseif ($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options']))
                {
                    $_v = $GLOBALS['TL_DCA'][$this->ptable]['fields'][$v]['options'][$_v];
                }

                // Add the sorting field
                if ($_v != '')
                {
                    $key = isset($GLOBALS['TL_LANG'][$this->ptable][$v][0]) ? $GLOBALS['TL_LANG'][$this->ptable][$v][0] : $v;
                    $add[$key] = $_v;
                }
            }

            // Trigger the header_callback (see #3417)
            if (\is_array($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback']))
            {
                $strClass = $GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][0];
                $strMethod = $GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback'][1];

                $this->import($strClass);
                $add = $this->$strClass->$strMethod($add, $this);
            }
            elseif (\is_callable($GLOBALS['TL_DCA'][$table]['list']['sorting']['header_callback']))
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
    <td><span class="tl_label">'.$k.':</span> </td>
    <td>'.$v.'</td>
  </tr>';
            }

            $return .= '
</table>
</div>';

            $orderBy = array();
            $firstOrderBy = array();

            // Add all records of the current table
            $query = "SELECT * FROM {$this->strTable}";

            if (\is_array($this->orderBy) && \strlen($this->orderBy[0]))
            {
                $orderBy = $this->orderBy;
                $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

                // Order by the foreign key
                if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey']))
                {
                    $key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey'], 2);
                    $query = "SELECT *, (SELECT ". $key[1] ." FROM ". $key[0] ." WHERE ". $this->strTable .".". $firstOrderBy ."=". $key[0] .".id) AS foreignKey FROM " . $this->strTable;
                    $orderBy[0] = 'foreignKey';
                }
            }
            elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields']))
            {
                $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);
            }

            // Support empty ptable fields (backwards compatibility)
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
            {
                $this->procedure[] = ($this->ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";
                $this->values[] = $this->ptable;
            }

            // WHERE
            if (!empty($this->procedure))
            {
                $query .= " WHERE " . implode(' AND ', $this->procedure);
            }
            if (!empty($this->root) && \is_array($this->root))
            {
                $query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('intval', $this->root)) . ")";
            }

            // ORDER BY
            if (!empty($orderBy) && \is_array($orderBy))
            {
                $query .= " ORDER BY " . implode(', ', $orderBy);
            }

            $objOrderByStmt = $this->Database->prepare($query);

            // LIMIT
            if (\strlen($this->limit))
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
                    $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id']));
                    $imagePasteNew = Image::getHtml('new.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][1], $row[$i]['id']));

                    // Decrypt encrypted value
                    foreach ($row[$i] as $k=>$v)
                    {
                        if ($GLOBALS['TL_DCA'][$table]['fields'][$k]['eval']['encrypt'])
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
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] && $firstOrderBy != 'sorting')
                    {
                        $sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] != '' && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] == '') ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];
                        $remoteNew = $this->formatCurrentValue($firstOrderBy, $row[$i][$firstOrderBy], $sortingMode);
                        $group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);

                        if ($group != $strGroup)
                        {
                            $return .= "\n\n" . '<div class="tl_content_header">'.$group.'</div>';
                            $strGroup = $group;
                        }
                    }

                    $blnWrapperStart = \in_array($row[$i]['type'], $GLOBALS['TL_WRAPPERS']['start']);
                    $blnWrapperSeparator = \in_array($row[$i]['type'], $GLOBALS['TL_WRAPPERS']['separator']);
                    $blnWrapperStop = \in_array($row[$i]['type'], $GLOBALS['TL_WRAPPERS']['stop']);

                    // Closing wrappers
                    if ($blnWrapperStop)
                    {
                        if (--$intWrapLevel < 1)
                        {
                            $blnIndent = false;
                        }
                    }

                    $return .= '

<div class="tl_content'.($blnWrapperStart ? ' wrapper_start' : '').($blnWrapperSeparator ? ' wrapper_separator' : '').($blnWrapperStop ? ' wrapper_stop' : '').($blnIndent ? ' indent' : '').(($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_class'] != '') ? ' ' . $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_class'] : '').(($i%2 == 0) ? ' even' : ' odd').' click2edit hover-div">
<div class="tl_content_right">';

                    // Opening wrappers
                    if ($blnWrapperStart)
                    {
                        if (++$intWrapLevel > 0)
                        {
                            $blnIndent = true;
                        }
                    }

                    // Edit multiple
                    if ('select' === Input::get('act'))
                    {
                        $return .= '<input type="checkbox" name="IDS[]" id="ids_'.$row[$i]['id'].'" class="tl_tree_checkbox" value="'.$row[$i]['id'].'">';
                    }

                    // Regular buttons
                    else
                    {
                        $return .= $this->generateButtons($row[$i], $this->strTable, $this->root, false, null, $row[($i-1)]['id'], $row[($i+1)]['id']);

                        // Sortable table
                        if ($blnHasSorting)
                        {
                            // Create new button
                            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'])
                            {
                                $return .= ' <a href="'.$this->addToUrl('act=create&amp;mode=1&amp;page_id='.$row[$i]['id'].'&amp;id='.$objParent->id).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][1], $row[$i]['id'])).'">'.$imagePasteNew.'</a>';
                            }

                            // Prevent circular references
                            if ($blnClipboard && $arrClipboard['mode'] == 'cut' && $row[$i]['id'] == $arrClipboard['id'] || $blnMultiboard && 'cutAll' === $arrClipboard['mode'] && \in_array($row[$i]['id'], $arrClipboard['id']))
                            {
                                $return .= ' ' . Image::getHtml('pasteafter_.gif');
                            }

                            // Copy/move multiple
                            elseif ($blnMultiboard)
                            {
                                $return .= ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;page_id='.$row[$i]['id']).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a>';
                            }

                            // Paste buttons
                            elseif ($blnClipboard)
                            {
                                $return .= ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;page_id='.$row[$i]['id'].'&amp;id='.$arrClipboard['id']).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $row[$i]['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a>';
                            }

                            // Drag handle
                            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
                            {
                                $return .= ' ' . Image::getHtml('drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG'][$this->strTable]['cut'][1], $row[$i]['id']) . '"');
                            }
                        }
                    }

                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']))
                    {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback'][1];

                        $this->import($strClass);
                        $return .= '</div>'.$this->$strClass->$strMethod($row[$i]).'</div>';
                    }
                    elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']))
                    {
                        $return .= '</div>'.$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['child_record_callback']($row[$i]).'</div>';
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
        if ($blnHasSorting)
        {
            $return .= '
</ul>

<script>
Isotope.makeParentViewSortable("ul_' . CURRENT_ID . '");
</script>';
        }

        $return .= '

</div>';

        // Close form
        if ('select' === Input::get('act'))
        {
            // Submit buttons
            $arrButtons = array();

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
            {
                $arrButtons['delete'] = '<input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\')" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'">';
            }

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
            {
                $arrButtons['cut'] = '<input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['moveSelected']).'">';
            }

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'])
            {
                $arrButtons['copy'] = '<input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['copySelected']).'">';
            }

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
            {
                $arrButtons['override'] = '<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']).'">';
                $arrButtons['edit'] = '<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'">';
            }

            // Call the buttons_callback (see #4691)
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback']))
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

            $return .= '

<div class="tl_formbody_submit" style="text-align:right">

<div class="tl_submit_container">
  ' . implode(' ', $arrButtons) . '
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
        $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

        if (\is_array($this->orderBy) && $this->orderBy[0] != '')
        {
            $orderBy = $this->orderBy;
            $firstOrderBy = $this->firstOrderBy;
        }

        $query = "SELECT * FROM {$this->strTable}";

        if (!empty($this->procedure))
        {
            $query .= " WHERE " . implode(' AND ', $this->procedure);
        }

        if (!empty($this->root) && \is_array($this->root))
        {
            $query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('intval', $this->root)) . ")";
        }

        if (\is_array($orderBy) && $orderBy[0] != '')
        {
            foreach ($orderBy as $k=>$v)
            {
                [$key, $direction] = explode(' ', $v, 2);

                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['findInSet'])
                {
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback']))
                    {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][1];

                        $this->import($strClass);
                        $keys = $this->$strClass->$strMethod($this);
                    }
                    elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback']))
                    {
                        $keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback']($this);
                    }
                    else
                    {
                        $keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options'];
                    }

                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['isAssociative'] || array_is_assoc($keys))
                    {
                        $keys = array_keys($keys);
                    }

                    $orderBy[$k] = $this->Database->findInSet($v, $keys);
                }
                elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'], array(5, 6, 7, 8, 9, 10)))
                {
                    $orderBy[$k] = "CAST($key AS SIGNED)" . ($direction ? " $direction" : ""); // see #5503
                }
            }

            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3)
            {
                $firstOrderBy = 'page_id';
                $showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

                $query .= " ORDER BY (SELECT " . $showFields[0] . " FROM " . $this->ptable . " WHERE " . $this->ptable . ".id=" . $this->strTable . ".page_id), " . implode(', ', $orderBy);

                // Set the foreignKey so that the label is translated (also for backwards compatibility)
                if ($GLOBALS['TL_DCA'][$table]['fields']['page_id']['foreignKey'] == '')
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

        if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 1 && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0)
        {
            $query .= " DESC";
        }

        $objRowStmt = $this->Database->prepare($query);

        if ($this->limit != '')
        {
            $arrLimit = explode(',', $this->limit);
            $objRowStmt->limit($arrLimit[1], $arrLimit[0]);
        }

        $objRow = $objRowStmt->execute($this->values);
        $this->bid = ($return != '') ? $this->bid : 'tl_buttons';

        // Display buttos
        if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] || !empty($GLOBALS['TL_DCA'][$this->strTable]['list']['global_operations']))
        {
            $return .= '

<div id="'.$this->bid.'">'.((Input::get('act') == 'select' || $this->ptable) ? '
<a href="'.$this->getReferer(true, $this->ptable).'" class="header_back" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="contao/main.php?'.$GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'].'" class="header_back" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a> ' : '')) . ((Input::get('act') != 'select') ? '
'.((!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) ? '<a href="'.(($this->ptable != '') ? $this->addToUrl('act=create' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;page_id=' . $this->intId) : $this->addToUrl('act=create')).'" class="header_new" title="'.StringUtil::specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a> ' : '') . $this->generateGlobalButtons() : '') . '
</div>' . Message::generate();
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
            $return .= ((Input::get('act') == 'select') ? '

<form action="'.ampersand(Environment::get('request'), true).'" id="tl_select" class="tl_form" method="post" novalidate>
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">' : '').'

<div class="tl_listing_container list_view">'.((Input::get('act') == 'select') ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '').'

<table class="tl_listing' . ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ? ' showColumns' : '') . '">';

            // Automatically add the "order by" field as last column if we do not have group headers
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'])
            {
                $blnFound = false;

                // Extract the real key and compare it to $firstOrderBy
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
                {
                    if (strpos($f, ':') !== false)
                    {
                        [$f,] = explode(':', $f, 2);
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
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'])
            {
                $return .= '
  <tr>';

                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f)
                {
                    if (strpos($f, ':') !== false)
                    {
                        [$f,] = explode(':', $f, 2);
                    }

                    $return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">'.(\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']).'</th>';
                }

                $return .= '
    <th class="tl_folder_tlist tl_right_nowrap">&nbsp;</th>
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
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['encrypt'])
                    {
                        $row[$v] = Encryption::decrypt(StringUtil::deserialize($row[$v]));
                    }

                    if (strpos($v, ':') !== false)
                    {
                        [$strKey, $strTable] = explode(':', $v);
                        [$strTable, $strField] = explode('.', $strTable);

                        $objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
                                                 ->limit(1)
                                                 ->execute($row[$strKey]);

                        $args[$k] = $objRef->numRows ? $objRef->$strField : '';
                    }
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
                    {
                        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'date')
                        {
                            $args[$k] = $row[$v] ? Date::parse(Config::get('dateFormat'), $row[$v]) : '-';
                        }
                        elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'time')
                        {
                            $args[$k] = $row[$v] ? Date::parse(Config::get('timeFormat'), $row[$v]) : '-';
                        }
                        else
                        {
                            $args[$k] = $row[$v] ? Date::parse(Config::get('datimFormat'), $row[$v]) : '-';
                        }
                    }
                    elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple'])
                    {
                        $args[$k] = ($row[$v] != '') ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : '';
                    }
                    else
                    {
                        $row_v = \Contao\StringUtil::deserialize($row[$v]);

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
                            $args[$k] = \is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
                        }
                        elseif (($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'])) && isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]]))
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
                $label = vsprintf($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] ?: '%s', $args);

                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < \strlen(strip_tags($label)))
                {
                    $label = trim(StringUtil::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
                }

                // Remove empty brackets (), [], {}, <> and empty tags from the label
                $label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/', '', $label);
                $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/', '', $label);

                // Build the sorting groups
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] > 0)
                {
                    $current = $row[$firstOrderBy];
                    $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                    $sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] != '' && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] == '') ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];
                    $remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

                    // Add the group header
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] && !$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false))
                    {
                        $eoCount = -1;
                        $group = $this->formatGroupHeader($firstOrderBy, $remoteNew, $sortingMode, $row);
                        $remoteCur = $remoteNew;

                        $return .= '
  <tr>
    <td colspan="2" class="'.$groupclass.'">'.$group.'</td>
  </tr>';
                        $groupclass = 'tl_folder_list';
                    }
                }

                $return .= '
  <tr class="'.((++$eoCount % 2 == 0) ? 'even' : 'odd').' click2edit hover-row">
    ';

                $colspan = 1;

                // Call the label_callback ($row, $label, $this)
                if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']))
                {
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']))
                    {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

                        $this->import($strClass);
                        $args = $this->$strClass->$strMethod($row, $label, $this, $args);
                    }
                    elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']))
                    {
                        $args = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']($row, $label, $this, $args);
                    }

                    // Handle strings and arrays (backwards compatibility)
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'])
                    {
                        $label = \is_array($args) ? implode(' ', $args) : $args;
                    }
                    elseif (!\is_array($args))
                    {
                        $args = array($args);
                        $colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields']);
                    }
                }

                // Show columns
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'])
                {
                    foreach ($args as $j=>$arg)
                    {
                        $return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] == $firstOrderBy) ? ' ordered_by' : '') . '">' . ($arg ?: '-') . '</td>';
                    }
                }
                else
                {
                    $return .= '<td class="tl_file_list">' . $label . '</td>';
                }

                // Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
                $return .= ((Input::get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap"><input type="checkbox" name="IDS[]" id="ids_'.$row['id'].'" class="tl_tree_checkbox" value="'.$row['id'].'"></td>' : '
    <td class="tl_file_list tl_right_nowrap">'.$this->generateButtons($row, $this->strTable, $this->root).'</td>') . '
  </tr>';
            }

            // Close the table
            $return .= '
</table>

</div>';

            // Close the form
            if (Input::get('act') == 'select')
            {
                // Submit buttons
                $arrButtons = array();

                if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
                {
                    $arrButtons['delete'] = '<input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\')" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'">';
                }

                if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
                {
                    $arrButtons['override'] = '<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']).'">';
                    $arrButtons['edit'] = '<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'">';
                }

                // Call the buttons_callback (see #4691)
                if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback']))
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

                $return .= '

<div class="tl_formbody_submit" style="text-align:right">

<div class="tl_submit_container">
  ' . implode(' ', $arrButtons) . '
</div>

</div>
</div>
</form>';
            }
        }

        return $return;
    }


    /**
     * Compile buttons from the table configuration array and return them as HTML
     * @param array
     * @param string
     * @param array
     * @param boolean
     * @param array
     * @param integer
     * @param integer
     * @return string
     */
    protected function generateButtons($arrRow, $strTable, $arrRootIds=array(), $blnCircularReference=false, $arrChildRecordIds=null, $strPrevious=null, $strNext=null)
    {
        if (empty($GLOBALS['TL_DCA'][$strTable]['list']['operations']))
        {
            return '';
        }

        $return = '';

        foreach ($GLOBALS['TL_DCA'][$strTable]['list']['operations'] as $k=>$v)
        {
            $v = \is_array($v) ? $v : array($v);
            $id = StringUtil::specialchars(rawurldecode($arrRow['id']));

            $label = $v['label'][0] ?: $k;
            $title = sprintf($v['label'][1] ?: $k, $arrRow['pid']);
            $attributes = ($v['attributes'] != '') ? ' ' . ltrim(sprintf($v['attributes'], $id, $id)) : '';

            // Add the key as CSS class
            if (strpos($attributes, 'class="') !== false)
            {
                $attributes = str_replace('class="', 'class="' . $k . ' ', $attributes);
            }
            else
            {
                $attributes = ' class="' . $k . '"' . $attributes;
            }

            // Call a custom function instead of using the default button
            if (\is_array($v['button_callback']))
            {
                $this->import($v['button_callback'][0]);
                $return .= $this->$v['button_callback'][0]->$v['button_callback'][1]($arrRow, $v['href'], $label, $title, $v['icon'], $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);
                continue;
            }
            elseif (\is_callable($v['button_callback']))
            {
                $return .= $v['button_callback']($arrRow, $v['href'], $label, $title, $v['icon'], $attributes, $strTable, $arrRootIds, $arrChildRecordIds, $blnCircularReference, $strPrevious, $strNext);
                continue;
            }

            // Generate all buttons except "move up" and "move down" buttons
            if ($k != 'move' && $v != 'move')
            {
                if ($k == 'show')
                {
                    $return .= '<a href="'.$this->addToUrl($v['href'].'&amp;id='.$arrRow['id'].'&amp;popup=1').'" title="'.StringUtil::specialchars($title).'" onclick="Backend.openModalIframe({\'width\':768,\'title\':\''.\Contao\StringUtil::specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG'][$strTable]['show'][1], $arrRow['id']))).'\',\'url\':this.href});return false"'.$attributes.'>'.Image::getHtml($v['icon'], $label).'</a> ';
                }
                else
                {
                    $return .= '<a href="'.$this->addToUrl($v['href'].'&amp;id='.$arrRow['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($v['icon'], $label).'</a> ';
                }

                continue;
            }

            $arrDirections = array('up', 'down');
            $arrRootIds = \is_array($arrRootIds) ? $arrRootIds : array($arrRootIds);

            foreach ($arrDirections as $dir)
            {
                $label = $GLOBALS['TL_LANG'][$strTable][$dir][0] ?: $dir;
                $title = $GLOBALS['TL_LANG'][$strTable][$dir][1] ?: $dir;

                $label = Image::getHtml($dir.'.gif', $label);
                $href = $v['href'] ?: '&amp;act=move';

                if ($dir == 'up')
                {
                    $return .= ((is_numeric($strPrevious) && (!\in_array($arrRow['id'], $arrRootIds) || empty($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$arrRow['id']).'&amp;sid='.(int) $strPrevious.'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$label.'</a> ' : Image::getHtml('up_.gif')).' ';
                    continue;
                }

                $return .= ((is_numeric($strNext) && (!\in_array($arrRow['id'], $arrRootIds) || empty($GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root']))) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$arrRow['id']).'&amp;sid='.(int) $strNext.'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$label.'</a> ' : Image::getHtml('down_.gif')).' ';
            }
        }

        return trim($return);
    }
}
