<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Class DC_ProductData
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */
class DC_ProductData extends \DC_Table
{

    /**
     * True if we are editing a language
     */
    protected $blnEditLanguage;

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
     * ID of an active group
     * @integer
     */
    protected $intGroupId;


    /**
     * Initialize the object
     * @param string
     */
    public function __construct($strTable)
    {
        $this->import('Session');

        // Reset the last product ID from session
        if (isset($_GET['gid'])) {
            $this->Session->set('iso_products_id', null);
        }

        $this->intGroupId = (int)\Session::getInstance()->get('iso_products_gid') ? : (\BackendUser::getInstance()->isAdmin ? 0 : intval(\BackendUser::getInstance()->iso_groups[0]));

        // Check if the group exists
        if ($this->intGroupId > 0) {
            $objGroup = \Database::getInstance()->prepare("SELECT id FROM tl_iso_groups WHERE id=?")->execute($this->intGroupId);

            if (!$objGroup->numRows) {
                if (\BackendUser::getInstance()->isAdmin || !is_array(\BackendUser::getInstance()->iso_groups)) {
                    $this->intGroupId = 0;
                }
            } else {
                $this->intGroupId = (int)\Database::getInstance()->prepare(
                    "SELECT id FROM tl_iso_groups WHERE id IN ('" . implode("','", \BackendUser::getInstance()->iso_groups) . "') ORDER BY " . \Database::getInstance()->findInSet('id', \BackendUser::getInstance()->iso_groups)
                )->limit(1)->execute()->id;
            }
        }

        // Redirect if the product was not found
        if (isset($_GET['id'])) {
            $objProduct = \Database::getInstance()->prepare("SELECT id FROM tl_iso_products WHERE id=?")
                ->limit(1)
                ->execute(\Input::get('id', true));

            if (!$objProduct->numRows) {
                \Controller::redirect(preg_replace('/(&amp;)?id=[^&]*/i', '', \Environment::get('request')));
            }

            // Store the last product ID in session (e.g. for breadcrumb)
            if (!isset($_GET['act']) && \Input::get('id', true) != $this->Session->get('iso_products_id')) {
                $this->Session->set('iso_products_id', \Input::get('id', true));
            }
        }

        // Display last product details
        if (!isset($_GET['id']) && $this->Session->get('iso_products_id')) {
            \Controller::redirect(\Backend::addToUrl('&id=' . $this->Session->get('iso_products_id')));
        }

        $arrClipboard = $this->Session->get('CLIPBOARD');

        // Cut all records
        if ($arrClipboard[$strTable]['mode'] == 'cutAll' && \Input::get('act') != 'cutAll') {
            \Controller::redirect(\Backend::addToUrl('&act=cutAll'));
        }

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
        if ($this->strTable == 'tl_undo' && strlen($GLOBALS['TL_CONFIG']['undoPeriod'])) {
            $this->Database->prepare("DELETE FROM tl_undo WHERE tstamp<?")
                ->execute(intval(time() - $GLOBALS['TL_CONFIG']['undoPeriod']));
        } elseif ($this->strTable == 'tl_log' && strlen($GLOBALS['TL_CONFIG']['logPeriod'])) {
            $this->Database->prepare("DELETE FROM tl_log WHERE tstamp<?")
                ->execute(intval(time() - $GLOBALS['TL_CONFIG']['logPeriod']));
        }

        $this->reviseTable();

        // Add to clipboard
        if (\Input::get('act') == 'paste') {
            $arrClipboard = $this->Session->get('CLIPBOARD');

            $arrClipboard[$this->strTable] = array
            (
                'id' => \Input::get('id'),
                'childs' => \Input::get('childs'),
                'mode' => \Input::get('mode')
            );

            $this->Session->set('CLIPBOARD', $arrClipboard);

            // Perform a redirect (this is the CURRENT_ID fix)
            \Controller::redirect('contao/main.php?do=' . \Input::get('do') . (\Input::get('pid') ? '&id=' . \Input::get('pid') : '') . '&rt=' . \Input::get('rt') . '&ref=' . \Input::get('ref'));
        }

        // Do not show the language records
        $this->procedure[] = "language=''";

        // Display products filtered by group
        $this->procedure[] = "gid IN(" . implode(',', array_map('intval', \Database::getInstance()->getChildRecords(array($this->intGroupId), 'tl_iso_groups', false, array($this->intGroupId)))) . ")";

        // Custom filter
        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']) && !empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $filter) {
                $this->procedure[] = $filter[0];
                $this->values[] = $filter[1];
            }
        }

        $return .= $this->panel();
        $return .= (CURRENT_ID && (\Input::get('pid') === null || (\Input::get('pid') != '' && intval(\Input::get('pid')) != 0))) ? $this->parentView() : $this->listView();

        // Add another panel at the end of the page
        if (strpos($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['panelLayout'], 'limit') !== false && ($strLimit = $this->limitMenu(true)) != false) {
            $return .= '

<form action="' . ampersand(\Environment::get('request'), true) . '" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_filters_limit">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">

<div class="tl_panel_bottom">

<div class="tl_submit_panel tl_subpanel">
<input type="image" name="btfilter" id="btfilter" src="' . TL_FILES_URL . 'system/themes/' . \Backend::getTheme() . '/images/reload.gif" class="tl_img_submit" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['applyTitle']) . '" alt="' . specialchars($GLOBALS['TL_LANG']['MSC']['apply']) . '">
</div>' . $strLimit . '

<div class="clear"></div>

</div>

</div>
</form>
';
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
    public function cut($blnDoNotRedirect = false)
    {
        if ($this->intId > 0) {
            $time = time();

            // Empty clipboard
            $arrClipboard = $this->Session->get('CLIPBOARD');
            $arrClipboard[$this->strTable] = array();
            $this->Session->set('CLIPBOARD', $arrClipboard);

            $objRecord = $this->Database->prepare("SELECT pid FROM {$this->strTable} WHERE id=?")
                ->limit(1)
                ->execute($this->intId);

            // Update only the variant
            if ($objRecord->pid > 0) {
                $this->Database->prepare("UPDATE {$this->strTable} SET tstamp=?, gid=?, pid=? WHERE id=?")
                    ->execute($time, $this->intGroupId, \Input::get('pid'), $this->intId);
            } // Update the main product and its variants
            else {
                $this->Database->prepare("UPDATE {$this->strTable} SET tstamp=?, gid=? WHERE id=? OR pid=?")
                    ->execute($time, $this->intGroupId, $this->intId, $this->intId);
            }

            // Call the oncut_callback
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['oncut_callback'] as $callback) {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($this);
                }
            }

            if (!$blnDoNotRedirect) {
                \Controller::redirect(\System::getReferer());
            }

            return;
        }

        parent::cut($blnDoNotRedirect);
    }


    /**
     * Move all selected records
     */
    public function cutAll()
    {
        $arrClipboard = $this->Session->get('CLIPBOARD');

        if (isset($arrClipboard[$this->strTable]) && is_array($arrClipboard[$this->strTable]['id'])) {
            foreach ($arrClipboard[$this->strTable]['id'] as $id) {
                $this->intId = $id;
                $this->cut(true);
            }
        }

        \Controller::redirect(\System::getReferer());
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

        if (!$GLOBALS['TL_DCA'][$table]['config']['ptable'] && \Input::get('childs') != '' && $this->Database->fieldExists('pid', $table)) {
            $ctable[] = $table;
        }

        if (!is_array($ctable)) {
            return;
        }

        // Walk through each child table
        foreach ($ctable as $v) {
            $this->loadDataContainer($v);
            $cctable[$v] = $GLOBALS['TL_DCA'][$v]['config']['ctable'];

            if (!$GLOBALS['TL_DCA'][$v]['config']['doNotCopyRecords'] && strlen($v)) {
                $objCTable = $this->Database->prepare("SELECT * FROM " . $v . " WHERE pid=?" . ($this->Database->fieldExists('sorting', $v) ? " ORDER BY sorting" : ""))
                    ->execute($id);

                foreach ($objCTable->fetchAllAssoc() as $row) {
                    // Exclude the duplicated record itself
                    if ($v == $table && $row['id'] == $parentId) {
                        continue;
                    }

                    foreach ($row as $kk => $vv) {
                        if ($kk == 'id') {
                            continue;
                        }

                        // Reset all unique, doNotCopy and fallback fields to their default value
                        if ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['unique'] || $GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['doNotCopy'] || $GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['fallback']) {
                            $vv = '';

                            // Use array_key_exists to allow NULL (see #5252)
                            if (array_key_exists('default', $GLOBALS['TL_DCA'][$v]['fields'][$kk])) {
                                $vv = is_array($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) ? serialize($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) : $GLOBALS['TL_DCA'][$v]['fields'][$kk]['default'];
                            }

                            // Encrypt the default value (see #3740)
                            if ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['encrypt']) {
                                $vv = \Encryption::encrypt($vv);
                            }
                        }

                        $copy[$v][$row['id']][$kk] = $vv;
                    }

                    $copy[$v][$row['id']]['pid'] = $insertID;
                    $copy[$v][$row['id']]['tstamp'] = $time;
                }
            }
        }

        // Duplicate the child records
        foreach ($copy as $k => $v) {
            if (!empty($v)) {
                foreach ($v as $kk => $vv) {
                    $objInsertStmt = $this->Database->prepare("INSERT INTO " . $k . " %s")
                        ->set($vv)
                        ->execute();

                    if ($objInsertStmt->affectedRows && (!empty($cctable[$k]) || $GLOBALS['TL_DCA'][$k]['list']['sorting']['mode'] == 5) && $kk != $parentId) {
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
        $arrClipboard = $this->Session->get('CLIPBOARD');

        if (isset($arrClipboard[$this->strTable]) && is_array($arrClipboard[$this->strTable]['id'])) {
            $arrIds = array();

            foreach ($arrClipboard[$this->strTable]['id'] as $id) {
                $this->intId = $id;
                $arrIds[] = $this->copy(true);
            }

            $this->Database->query("UPDATE {$this->strTable} SET gid=" . $this->intGroupId . " WHERE id IN (" . implode(',', $arrIds) . ")");
        }

        \Controller::redirect(\System::getReferer());
    }


    /**
     * Calculate the new position of a moved or inserted record
     * @param string
     * @param integer
     * @param boolean
     */
    protected function getNewPosition($mode, $pid = null, $insertInto = false)
    {
        // PID is not set - only valid for duplicated records, as they get the same parent ID as the original record!
        if (is_null($pid) && $this->intId && $mode == 'copy') {
            $pid = $this->intId;
        }

        // PID is set (insert after or into the parent record)
        if (is_numeric($pid)) {
            // Insert the current record into the parent record
            if ($insertInto) {
                $this->set['pid'] = $pid;
            } // Else insert the current record after the parent record
            elseif ($pid > 0) {
                $objParentRecord = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
                    ->limit(1)
                    ->executeUncached($pid);

                if ($objParentRecord->numRows) {
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
    public function edit($intID = false, $ajaxId = false)
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
            \System::log('Table ' . $this->strTable . ' is not editable', 'DC_ProductData edit()', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        if ($intID) {
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
        if ($objRow->numRows < 1) {
            \System::log('Could not load record ID "' . $this->intId . '" of table "' . $this->strTable . '"!', 'DC_ProductData edit()', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        } // ID of a language record is not allowed
        elseif ($objRow->language != '') {
            \System::log('Cannot edit language record ID "' . $this->intId . '" of table "' . $this->strTable . '"!', 'DC_ProductData edit()', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $this->objActiveRecord = $objRow;

        // Load and/or change language

        // Add support for i18nl10n extension
        if (in_array('i18nl10n', \Config::getInstance()->getActiveModules())) {
            $arrPageLanguages = array_filter(array_unique(deserialize($GLOBALS['TL_CONFIG']['i18nl10n_languages'], true)));
        } else {
            $arrPageLanguages = $this->Database->execute("SELECT DISTINCT language FROM tl_page WHERE type='root'")->fetchEach('language');
            $arrPageLanguages = array_map(function ($strLang) {
                return str_replace('-', '_', $strLang);
            }, $arrPageLanguages);
        }

        if (count($arrPageLanguages) > 1) {
            $this->arrLanguageLabels = \System::getLanguages();
            $this->arrLanguages = array_intersect(array_keys($this->arrLanguageLabels), $arrPageLanguages);

            if (\Input::post('FORM_SUBMIT') == 'tl_language') {
                $session = $this->Session->getData();

                if (in_array(\Input::post('language'), $this->arrLanguages)) {
                    $session['language'][$this->strTable][$this->intId] = \Input::post('language');

                    if (\Input::post('deleteLanguage') != '') {
                        $this->Database->prepare("DELETE FROM " . $this->strTable . " WHERE pid=? AND language=?")->execute($this->intId, \Input::post('language'));
                        unset($session['language'][$this->strTable][$this->intId]);
                    }
                } else {
                    unset($session['language'][$this->strTable][$this->intId]);
                }

                $this->Session->setData($session);
                $_SESSION['TL_INFO'] = '';
                \Controller::reload();
            }

            if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] != '' && in_array($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId], $this->arrLanguages)) {
                $objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE pid=? AND language=?")->execute($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);

                if (!$objRow->numRows) {
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
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] && \Input::post('FORM_SUBMIT') == 'tl_version' && \Input::post('version') != '') {
            $objData = $this->Database->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? AND version=?")
                ->limit(1)
                ->execute($this->strTable, $this->objActiveRecord->id, \Input::post('version'));

            if ($objData->numRows) {
                $data = deserialize($objData->data);

                if (is_array($data)) {
                    $this->Database->prepare("UPDATE " . $objData->fromTable . " %s WHERE id=?")
                        ->set($data)
                        ->execute($this->objActiveRecord->id);

                    $this->Database->prepare("UPDATE tl_version SET active='' WHERE pid=?")
                        ->execute($this->objActiveRecord->id);

                    $this->Database->prepare("UPDATE tl_version SET active=1 WHERE pid=? AND version=?")
                        ->execute($this->objActiveRecord->id, \Input::post('version'));

                    \System::log(sprintf('Version %s of record ID %s (table %s) has been restored', \Input::post('version'), $this->objActiveRecord->id, $this->strTable), 'DC_ProductData edit()', TL_GENERAL);

                    // Trigger the onrestore_callback
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onrestore_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onrestore_callback'] as $callback) {
                            if (is_array($callback)) {
                                $this->import($callback[0]);
                                $this->$callback[0]->$callback[1]($this->objActiveRecord->id, $this->strTable, $data, \Input::post('version'));
                            }
                        }
                    }
                }
            }

            \Controller::reload();
        }


        // Build an array from boxes and rows
        $this->strPalette = $this->getPalette();
        $boxes = trimsplit(';', $this->strPalette);
        $legends = array();

        if (!empty($boxes)) {
            foreach ($boxes as $k => $v) {
                $eCount = 1;
                $boxes[$k] = trimsplit(',', $v);

                foreach ($boxes[$k] as $kk => $vv) {
                    if (preg_match('/^\[.*\]$/i', $vv)) {
                        ++$eCount;
                        continue;
                    }

                    if (preg_match('/^\{.*\}$/i', $vv)) {
                        $legends[$k] = substr($vv, 1, -1);
                        unset($boxes[$k][$kk]);
                    } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] || !is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv])) {
                        unset($boxes[$k][$kk]);
                    } elseif ($this->blnEditLanguage && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['attributes']['multilingual']) {
                        unset($boxes[$k][$kk]);
                    }
                }

                // Unset a box if it does not contain any fields
                if (count($boxes[$k]) < $eCount) {
                    unset($boxes[$k]);
                }
            }

            $class = 'tl_tbox block';
            $fs = $this->Session->get('fieldset_states');
            $blnIsFirst = true;

            // Render boxes
            foreach ($boxes as $k => $v) {
                $strAjax = '';
                $blnAjax = false;
                $legend = '';

                if (isset($legends[$k])) {
                    list($key, $cls) = explode(':', $legends[$k]);
                    $legend = "\n" . '<legend onclick="AjaxRequest.toggleFieldset(this, \'' . $key . '\', \'' . $this->strTable . '\')">' . (isset($GLOBALS['TL_LANG'][$this->strTable][$key]) ? $GLOBALS['TL_LANG'][$this->strTable][$key] : $key) . '</legend>';
                }

                if (!$GLOBALS['TL_CONFIG']['oldBeTheme']) {
                    if (isset($fs[$this->strTable][$key])) {
                        $class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
                    } else {
                        $class .= (($cls && $legend) ? ' ' . $cls : '');
                    }

                    $return .= "\n\n" . '<fieldset' . ($key ? ' id="pal_' . $key . '"' : '') . ' class="' . $class . ($legend ? '' : ' nolegend') . '">' . $legend;
                } else {
                    $return .= "\n\n" . '<div class="' . $class . '">';
                }

                // Build rows of the current box
                foreach ($v as $vv) {
                    if ($vv == '[EOF]') {
                        if ($blnAjax && \Environment::get('isAjaxRequest')) {
                            return $strAjax . '<input type="hidden" name="FORM_FIELDS[]" value="' . specialchars($this->strPalette) . '">';
                        }

                        $blnAjax = false;
                        $return .= "\n" . '</div>';

                        continue;
                    }

                    if (preg_match('/^\[.*\]$/i', $vv)) {
                        $thisId = 'sub_' . substr($vv, 1, -1);
                        $blnAjax = ($ajaxId == $thisId && \Environment::get('isAjaxRequest')) ? true : false;
                        $return .= "\n" . '<div id="' . $thisId . '">';

                        continue;
                    }

                    $this->strField = $vv;
                    $this->strInputName = $vv;
                    $this->varValue = $this->objActiveRecord->$vv;

                    // Autofocus the first field
                    if ($blnIsFirst && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] == 'text') {
                        $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['autofocus'] = 'autofocus';
                        $blnIsFirst = false;
                    }

                    // Convert CSV fields (see #2890)
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'])) {
                        $this->varValue = trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'], $this->varValue);
                    }

                    // Call load_callback
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback) {
                            if (is_array($callback)) {
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

                if (!$GLOBALS['TL_CONFIG']['oldBeTheme']) {
                    $return .= "\n" . '</fieldset>';
                } else {
                    $return .= "\n" . '</div>';
                }
            }
        }

        $version = '';

        // Check versions
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning']) {
            $objVersion = $this->Database->prepare("SELECT tstamp, version, username, active FROM tl_version WHERE fromTable=? AND pid=? ORDER BY version DESC")
                ->execute($this->strTable, $this->objActiveRecord->id);

            if ($objVersion->numRows > 1) {
                $versions = '';

                while ($objVersion->next()) {
                    $versions .= '
  <option value="' . $objVersion->version . '"' . ($objVersion->active ? ' selected="selected"' : '') . '>' . $GLOBALS['TL_LANG']['MSC']['version'] . ' ' . $objVersion->version . ' (' . \System::parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objVersion->tstamp) . ') ' . $objVersion->username . '</option>';
                }

                $version = '<form action="' . ampersand(\Environment::get('request'), true) . '" id="tl_version" class="tl_form" method="post" style="float:right;">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_version">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
<select name="version" class="tl_select">' . $versions . '
</select>
<input type="submit" name="showVersion" id="showVersion" class="tl_submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['restore']) . '">
</div>
</form>';
            }
        }

        // Check languages
        if (is_array($this->arrLanguages) && !empty($this->arrLanguages)) {
            $arrAvailableLanguages = $this->Database->prepare("SELECT language FROM " . $this->strTable . " WHERE pid=?")->execute($this->intId)->fetchEach('language');
            $available = '';
            $undefined = '';

            foreach ($this->arrLanguages as $language) {
                if (in_array($language, $arrAvailableLanguages)) {
                    if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] == $language) {
                        $available .= '<option value="' . $language . '" selected="selected">' . $this->arrLanguageLabels[$language] . '</option>';
                        $_SESSION['TL_INFO'] = array($GLOBALS['TL_LANG']['MSC']['editingLanguage']);
                    } else {
                        $available .= '<option value="' . $language . '">' . $this->arrLanguageLabels[$language] . '</option>';
                    }
                } else {
                    $undefined .= '<option value="' . $language . '">' . $this->arrLanguageLabels[$language] . ' (' . $GLOBALS['TL_LANG']['MSC']['undefinedLanguage'] . ')' . '</option>';
                }
            }

            $version .= '<form action="' . ampersand(\Environment::get('request'), true) . '" id="tl_language" class="tl_form" method="post" style="float:left;margin-left:20px;">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_language">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
<select name="language" class="tl_select' . ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] == '' ? '' : ' active') . '">
    <option value="">' . $GLOBALS['TL_LANG']['MSC']['defaultLanguage'] . '</option>' . $available . $undefined . '
</select>
<input type="submit" name="editLanguage" class="tl_submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['editLanguage']) . '">
<input type="submit" name="deleteLanguage" class="tl_submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['deleteLanguage']) . '" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] . '\')">
</div>
</form>';
        }

        if ($version != '') {
            $version = '
<div class="tl_version_panel">

' . $version . '
<div class="clear"></div>
</div>';
        }

        // Add some buttons and end the form
        $return .= '
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['save']) . '">
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']) . '">' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['saveNcreate']) . '">' : '') . (\Input::get('s2e') ? '
<input type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['saveNedit']) . '">' : (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4 || $this->ptable != '' || $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit']) ? '
<input type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['saveNback']) . '">' : '')) . '
</div>

</div>
</form>

<script>
window.addEvent(\'domready\', function() {
  var first = $(\'' . $this->strTable . '\').getElement(\'input[type="text"]\');
  if (first) first.focus();
});
</script>';

        $copyFallback = $this->blnEditLanguage ? '&nbsp;&nbsp;::&nbsp;&nbsp;<a href="' . \Backend::addToUrl('act=copyFallback') . '" class="header_iso_copy" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['copyFallback']) . '" accesskey="d" onclick="Backend.getScrollOffset();">' . ($GLOBALS['TL_LANG']['MSC']['copyFallback'] ? $GLOBALS['TL_LANG']['MSC']['copyFallback'] : 'copyFallback') . '</a>' : '';

        // Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
        $return = $version . '
<div id="tl_buttons">
<a href="' . \System::getReferer(true) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>' . $copyFallback . '
</div>

<h2 class="sub_headline">' . sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], ($this->intId ? 'ID ' . $this->intId : '')) . '</h2>
' . $this->getMessages() . '
<form action="' . ampersand(\Environment::get('request'), true) . '" id="' . $this->strTable . '" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"' . (count($this->onsubmit) ? ' onsubmit="' . implode(' ', $this->onsubmit) . '"' : '') . '>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . specialchars($this->strTable) . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
<input type="hidden" name="FORM_FIELDS[]" value="' . specialchars($this->strPalette) . '">' . ($this->noReload ? '

<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . $return;

        // Reload the page to prevent _POST variables from being sent twice
        if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload) {
            $arrValues = $this->values;
            array_unshift($arrValues, time());

            // Trigger the onsubmit_callback
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
                    $this->import($callback[0]);
                    $this->$callback[0]->$callback[1]($this);
                }
            }

            // Save the current version
            if ($this->blnCreateNewVersion && \Input::post('SUBMIT_TYPE') != 'auto') {
                $this->createNewVersion($this->strTable, $this->objActiveRecord->id);

                // Call the onversion_callback
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback) {
                        $this->import($callback[0]);
                        $this->$callback[0]->$callback[1]($this->strTable, $this->objActiveRecord->id, $this);
                    }
                }

                \System::log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->objActiveRecord->id), 'DC_ProductData edit()', TL_GENERAL);
            }

            // Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
            $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
                ->execute(time(), $this->activeRecord->id);

            // Redirect
            if (isset($_POST['saveNclose'])) {
                $_SESSION['TL_INFO'] = '';
                $_SESSION['TL_ERROR'] = '';
                $_SESSION['TL_CONFIRM'] = '';

                setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                \Controller::redirect(\System::getReferer());
            } elseif (isset($_POST['saveNedit'])) {
                $_SESSION['TL_INFO'] = '';
                $_SESSION['TL_ERROR'] = '';
                $_SESSION['TL_CONFIRM'] = '';

                setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                $strUrl = \Backend::addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href']);

                $strUrl = preg_replace('/(&amp;)?s2e=[^&]*/i', '', $strUrl);
                $strUrl = preg_replace('/(&amp;)?act=[^&]*/i', '', $strUrl);

                \Controller::redirect($strUrl);
            } elseif (isset($_POST['saveNback'])) {
                $_SESSION['TL_INFO'] = '';
                $_SESSION['TL_ERROR'] = '';
                $_SESSION['TL_CONFIRM'] = '';

                setcookie('BE_PAGE_OFFSET', 0, 0, '/');

                if ($this->ptable == '') {
                    \Controller::redirect(\Environment::get('script') . '?do=' . \Input::get('do'));
                } elseif ($this->ptable == 'tl_theme' && $this->strTable == 'tl_style_sheet') {
                    \Controller::redirect(\System::getReferer(false, $this->strTable));
                } else {
                    \Controller::redirect(\System::getReferer(false, $this->ptable));
                }
            } elseif (isset($_POST['saveNcreate'])) {
                $_SESSION['TL_INFO'] = '';
                $_SESSION['TL_ERROR'] = '';
                $_SESSION['TL_CONFIRM'] = '';

                setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                $strUrl = \Environment::get('script') . '?do=' . \Input::get('do');

                if (isset($_GET['table'])) {
                    $strUrl .= '&amp;table=' . \Input::get('table');
                }

                // Tree view
                if ($this->treeView) {
                    $strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
                } // Parent view
                elseif ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4) {
                    $strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->activeRecord->pid : '&amp;act=create&amp;mode=2&amp;pid=' . $this->activeRecord->pid;
                } // List view
                else {
                    $strUrl .= $this->ptable != '' ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';
                }

                \Controller::redirect($strUrl);
            }

            \Controller::reload();
        }

        // Set the focus if there is an error
        if ($this->noReload) {
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
    public function editAll($intId = false, $ajaxId = false)
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
            \System::log('Table "' . $this->strTable . '" is not editable', 'DC_Table editAll()', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $return = '';
        $this->import('BackendUser', 'User');

        // Get current IDs from session
        $session = $this->Session->getData();
        $ids = $session['CURRENT']['IDS'];

        if (\Environment::get('isAjaxRequest')) {
            $ids = array($intId);
        }

        // Save field selection in session
        if (\Input::post('FORM_SUBMIT') == $this->strTable . '_all' && \Input::get('fields')) {
            $session['CURRENT'][$this->strTable] = deserialize(\Input::post('all_fields'));
            $this->Session->setData($session);
        }

        // Add fields
        $fields = $session['CURRENT'][$this->strTable];

        if (is_array($fields) && !empty($fields) && \Input::get('fields')) {
            $class = 'tl_tbox block';
            $this->checkForTinyMce();

            // Walk through each record
            foreach ($ids as $id) {
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
<div class="' . $class . '">';

                $class = 'tl_box block';
                $formFields = array();

                // Get the field values
                $objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
                    ->limit(1)
                    ->executeUncached($this->intId);

                // Store the active record
                $this->objActiveRecord = $objRow;

                foreach ($this->strPalette as $v) {
                    // Check whether field is excluded
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude']) {
                        continue;
                    }

                    if ($v == '[EOF]') {
                        if ($blnAjax && \Environment::get('isAjaxRequest')) {
                            return $strAjax . '<input type="hidden" name="FORM_FIELDS_' . $id . '[]" value="' . specialchars(implode(',', $formFields)) . '">';
                        }

                        $blnAjax = false;
                        $return .= "\n  " . '</div>';

                        continue;
                    }

                    if (preg_match('/^\[.*\]$/i', $v)) {
                        $thisId = 'sub_' . substr($v, 1, -1) . '_' . $id;
                        $blnAjax = ($ajaxId == $thisId && \Environment::get('isAjaxRequest')) ? true : false;
                        $return .= "\n  " . '<div id="' . $thisId . '">';

                        continue;
                    }

                    if (!in_array($v, $fields)) {
                        continue;
                    }

                    $this->strField = $v;
                    $this->strInputName = $v . '_' . $this->intId;
                    $formFields[] = $v . '_' . $this->intId;

                    // Set the default value and try to load the current value from DB
                    $this->varValue = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['default'] : '';

                    if ($objRow->$v !== false) {
                        $this->varValue = $objRow->$v;
                    }

                    // Call load_callback
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback) {
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
  <input type="hidden" name="FORM_FIELDS_' . $this->intId . '[]" value="' . specialchars(implode(',', $formFields)) . '">
</div>';

                // Save record
                if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload) {
                    // Call onsubmit_callback
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
                            $this->import($callback[0]);
                            $this->$callback[0]->$callback[1]($this);
                        }
                    }

                    // Create a new version
                    if ($this->blnCreateNewVersion && \Input::post('SUBMIT_TYPE') != 'auto') {
                        $this->createNewVersion($this->strTable, $this->intId);

                        // Call the onversion_callback
                        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'])) {
                            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback) {
                                $this->import($callback[0]);
                                $this->$callback[0]->$callback[1]($this->strTable, $this->intId, $this);
                            }
                        }

                        \System::log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->intId), 'DC_Table editAll()', TL_GENERAL);
                    }

                    // Set the current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
                    $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
                        ->execute(time(), $this->intId);
                }
            }

            // Add the form
            $return = '

<h2 class="sub_headline_all">' . sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable) . '</h2>

<form action="' . ampersand(\Environment::get('request'), true) . '" id="' . $this->strTable . '" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . ($this->noReload ? '

<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . $return . '

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['save']) . '">
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']) . '">
</div>

</div>
</form>';

            // Set the focus if there is an error
            if ($this->noReload) {
                $return .= '

<script>
window.addEvent(\'domready\', function() {
  Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
});
</script>';
            }

            // Reload the page to prevent _POST variables from being sent twice
            if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload) {
                if (\Input::post('saveNclose')) {
                    setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                    \Controller::redirect(\System::getReferer());
                }

                \Controller::reload();
            }
        } // Else show a form to select the fields
        else {
            $options = '';
            $fields = array();

            // Add fields of the current table
            $fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));

            // Show all non-excluded fields
            foreach ($fields as $field) {
                if ($field == 'pid' || $field == 'sorting' || (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] != '' || is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'])))) {
                    $options .= '
  <input type="checkbox" name="all_fields[]" id="all_' . $field . '" class="tl_checkbox" value="' . specialchars($field) . '"> <label for="all_' . $field . '" class="tl_checkbox_label">' . ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] != '' ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field][0]) . '</label><br>';
                }
            }

            $blnIsError = ($_POST && empty($_POST['all_fields']));

            // Return the select menu
            $return .= '

<h2 class="sub_headline_all">' . sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable) . '</h2>

<form action="' . ampersand(\Environment::get('request'), true) . '&amp;fields=1" id="' . $this->strTable . '_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '_all">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . ($blnIsError ? '

<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . '

<div class="tl_tbox block">
<fieldset class="tl_checkbox_container">
  <legend' . ($blnIsError ? ' class="error"' : '') . '>' . $GLOBALS['TL_LANG']['MSC']['all_fields'][0] . '</legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>' . $options . '
</fieldset>' . ($blnIsError ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['all_fields'] . '</p>' : (($GLOBALS['TL_CONFIG']['showHelp'] && $GLOBALS['TL_LANG']['MSC']['all_fields'][1] != '') ? '
<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['all_fields'][1] . '</p>' : '')) . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['continue']) . '">
</div>

</div>
</form>';
        }

        // Return
        return '
<div id="tl_buttons">
<a href="' . \System::getReferer(true) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>' . $return;
    }


    /**
     * Auto-generate a form to override all records that are currently shown
     * @author Based on a patch by Andreas Schempp
     * @return string
     */
    public function overrideAll()
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
            \System::log('Table ' . $this->strTable . ' is not editable', 'DC_Table overrideAll()', TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $return = '';
        $this->import('BackendUser', 'User');

        // Get current IDs from session
        $session = $this->Session->getData();
        $ids = $session['CURRENT']['IDS'];

        // Save field selection in session
        if (\Input::post('FORM_SUBMIT') == $this->strTable . '_all' && \Input::get('fields')) {
            $session['CURRENT'][$this->strTable] = deserialize(\Input::post('all_fields'));
            $this->Session->setData($session);
        }

        // Add fields
        $fields = $session['CURRENT'][$this->strTable];

        if (is_array($fields) && !empty($fields) && \Input::get('fields')) {
            $class = 'tl_tbox block';
            $formFields = array();
            $this->checkForTinyMce();

            // Save record
            if (\Input::post('FORM_SUBMIT') == $this->strTable) {
                foreach ($ids as $id) {
                    $this->intId = $id;
                    $this->procedure = array('id=?');
                    $this->values = array($this->intId);
                    $this->blnCreateNewVersion = false;

                    $this->createInitialVersion($this->strTable, $this->intId);

                    $this->strPalette = trimsplit('[;,]', $this->getPalette());

                    // Store all fields
                    foreach ($fields as $v) {
                        // Check whether field is excluded or not in palette
                        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude'] || !in_array($v, $this->strPalette)) {
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
                    if (!$this->noReload) {
                        // Call onsubmit_callback
                        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
                            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
                                $this->import($callback[0]);
                                $this->$callback[0]->$callback[1]($this);
                            }
                        }

                        // Create a new version
                        if ($this->blnCreateNewVersion) {
                            $this->createNewVersion($this->strTable, $this->intId);
                            \System::log(sprintf('A new version of record ID %s (table %s) has been created', $this->intId, $this->strTable), 'DC_Table editAll()', TL_GENERAL);
                        }

                        // Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
                        $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
                            ->execute(time(), $this->intId);
                    }
                }
            }

            // Begin current row
            $return .= '
<div class="' . $class . '">';

            foreach ($fields as $v) {
                // Check whether field is excluded
                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude']) {
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
<input type="hidden" name="FORM_FIELDS[]" value="' . specialchars(implode(',', $formFields)) . '">
</div>';

            // Add the form
            $return = '

<h2 class="sub_headline_all">' . sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable) . '</h2>

<form action="' . ampersand(\Environment::get('request'), true) . '" id="' . $this->strTable . '" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . ($this->noReload ? '

<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . $return . '

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['save']) . '">
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']) . '">
</div>

</div>
</form>';

            // Set the focus if there is an error
            if ($this->noReload) {
                $return .= '

<script>
window.addEvent(\'domready\', function() {
  Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
});
</script>';
            }

            // Reload the page to prevent _POST variables from being sent twice
            if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload) {
                if (\Input::post('saveNclose')) {
                    setcookie('BE_PAGE_OFFSET', 0, 0, '/');
                    \Controller::redirect(\System::getReferer());
                }

                \Controller::reload();
            }
        } // Else show a form to select the fields
        else {
            $options = '';
            $fields = array();

            // Add fields of the current table
            $fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields']));

            // Add meta fields if the current user is an administrator
            if ($this->User->isAdmin) {
                if ($this->Database->fieldExists('sorting', $this->strTable) && !in_array('sorting', $fields)) {
                    array_unshift($fields, 'sorting');
                }

                if ($this->Database->fieldExists('pid', $this->strTable) && !in_array('pid', $fields)) {
                    array_unshift($fields, 'pid');
                }
            }

            // Show all non-excluded fields
            foreach ($fields as $field) {
                if ($field == 'pid' || $field == 'sorting' || (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] != '' || is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'])))) {
                    $options .= '
  <input type="checkbox" name="all_fields[]" id="all_' . $field . '" class="tl_checkbox" value="' . specialchars($field) . '"> <label for="all_' . $field . '" class="tl_checkbox_label">' . ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] != '' ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_LANG']['MSC'][$field][0]) . '</label><br>';
                }
            }

            $blnIsError = ($_POST && empty($_POST['all_fields']));

            // Return the select menu
            $return .= '

<h2 class="sub_headline_all">' . sprintf($GLOBALS['TL_LANG']['MSC']['all_info'], $this->strTable) . '</h2>

<form action="' . ampersand(\Environment::get('request'), true) . '&amp;fields=1" id="' . $this->strTable . '_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '_all">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . ($blnIsError ? '

<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . '

<div class="tl_tbox block">
<fieldset class="tl_checkbox_container">
  <legend' . ($blnIsError ? ' class="error"' : '') . '>' . $GLOBALS['TL_LANG']['MSC']['all_fields'][0] . '</legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6;"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>' . $options . '
</fieldset>' . ($blnIsError ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['all_fields'] . '</p>' : (($GLOBALS['TL_CONFIG']['showHelp'] && $GLOBALS['TL_LANG']['MSC']['all_fields'][1] != '') ? '
<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['all_fields'][1] . '</p>' : '')) . '
</div>

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['continue']) . '">
</div>

</div>
</form>';
        }

        // Return
        return '
<div id="tl_buttons">
<a href="' . \System::getReferer(true) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '" accesskey="b" onclick="Backend.getScrollOffset();">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>' . $return;
    }


    /**
     * List all records of the current table and return them as HTML string
     * @return string
     */
    protected function listView()
    {
        $return = '';
        $table = $this->strTable;
        $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

        if (is_array($this->orderBy) && $this->orderBy[0] != '') {
            $orderBy = $this->orderBy;
            $firstOrderBy = $this->firstOrderBy;
        }

        $query = "SELECT * FROM " . $this->strTable;

        // Show only main products
        $this->procedure[] = "pid=0";

        if (!empty($this->procedure)) {
            $query .= " WHERE " . implode(' AND ', $this->procedure);
        }

        if (!empty($this->root) && is_array($this->root)) {
            $query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('intval', $this->root)) . ")";
        }

        if (is_array($orderBy) && $orderBy[0] != '') {
            foreach ($orderBy as $k => $v) {
                list($key, $direction) = explode(' ', $v, 2);

                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['findInSet']) {
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'])) {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'][1];

                        $this->import($strClass);
                        $keys = $this->$strClass->$strMethod($this);
                    } else {
                        $keys = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options'];
                    }

                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['eval']['isAssociative'] || array_is_assoc($keys)) {
                        $keys = array_keys($keys);
                    }

                    $orderBy[$k] = $this->Database->findInSet($v, $keys);
                } elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['flag'], array(5, 6, 7, 8, 9, 10))) {
                    $orderBy[$k] = "CAST($key AS SIGNED)" . ($direction ? " $direction" : ""); // see #5503
                }
            }

            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3) {
                $firstOrderBy = 'pid';
                $showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

                $query .= " ORDER BY (SELECT " . $showFields[0] . " FROM " . $this->ptable . " WHERE " . $this->ptable . ".id=" . $this->strTable . ".pid), " . implode(', ', $orderBy);

                // Set the foreignKey so that the label is translated (also for backwards compatibility)
                if ($GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] == '') {
                    $GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] = $this->ptable . '.' . $showFields[0];
                }

                // Remove the parent field from label fields
                array_shift($showFields);
                $GLOBALS['TL_DCA'][$table]['list']['label']['fields'] = $showFields;
            } else {
                $query .= " ORDER BY " . implode(', ', $orderBy);
            }
        }

        if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 1 && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] % 2) == 0) {
            $query .= " DESC";
        }

        $objRowStmt = $this->Database->prepare($query);

        if ($this->limit != '') {
            $arrLimit = explode(',', $this->limit);
            $objRowStmt->limit($arrLimit[1], $arrLimit[0]);
        }

        $objRow = $objRowStmt->execute($this->values);
        $this->bid = ($return != '') ? $this->bid : 'tl_buttons';
        $blnClipboard = false;
        $arrClipboard = $this->Session->get('CLIPBOARD');

        // Check the clipboard
        if (!empty($arrClipboard[$this->strTable])) {
            $blnClipboard = true;
        }

        // Display buttons
        if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] || !empty($GLOBALS['TL_DCA'][$this->strTable]['list']['global_operations'])) {
            $return .= '
<div id="' . $this->bid . '">' . ((\Input::get('act') == 'select' || $this->ptable) ? '
<a href="' . \System::getReferer(true, $this->ptable) . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="contao/main.php?' . $GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'] . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : '')) . ((\Input::get('act') != 'select') ? '
' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '<a href="' . (($this->ptable != '') ? \Backend::addToUrl('act=create' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->intId) : \Backend::addToUrl('act=create')) . '" class="header_new" title="' . specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]) . '" accesskey="n" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG'][$this->strTable]['new'][0] . '</a> ' : '') . $this->generateGlobalButtons() : '') . ($blnClipboard ? '<a href="' . \Backend::addToUrl('clipboard=1') . '" class="header_clipboard" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']) . '" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['clearClipboard'] . '</a> ' : '') . '
</div>' . \Message::generate(true);
        }

        // Show breadcrumb
        $return .= '<div class="breadcrumb_container">' . \Isotope\Backend::generateGroupsBreadcrumb($this->intGroupId, $this->Session->get('iso_products_id')) . '</div>';

        // Return "no records found" message
        if ($objRow->numRows < 1) {
            $return .= '
<p class="tl_empty">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>';
        } // List records
        else {
            $result = $objRow->fetchAllAssoc();
            $return .= ((\Input::get('act') == 'select') ? '

<form action="' . ampersand(\Environment::get('request'), true) . '" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' : '') . '

<div class="tl_listing_container iso_listing_container list_view">' . ((\Input::get('act') == 'select') ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '') . '

<table class="tl_listing' . ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ? ' showColumns' : '') . '">';

            // Generate the table header if the "show columns" option is active
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                $return .= '
  <tr>';

                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f) {
                    $return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) . '</th>';
                }

                $return .= '
    <th class="tl_folder_tlist tl_right_nowrap iso_operations">&nbsp;</th>
  </tr>';
            }

            // Process result and add label and buttons
            $eoCount = -1;

            foreach ($result as $row) {
                $args = array();
                $this->current[] = $row['id'];
                $showFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'];

                // Label
                foreach ($showFields as $k => $v) {
                    // Decrypt the value
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['encrypt']) {
                        $row[$v] = \Encryption::decrypt(deserialize($row[$v]));
                    }

                    if (strpos($v, ':') !== false) {
                        list($strKey, $strTable) = explode(':', $v);
                        list($strTable, $strField) = explode('.', $strTable);

                        $objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
                            ->limit(1)
                            ->execute($row[$strKey]);

                        $args[$k] = $objRef->numRows ? $objRef->$strField : '';
                    } elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10))) {
                        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'date') {
                            $args[$k] = $row[$v] ? \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '-';
                        } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'time') {
                            $args[$k] = $row[$v] ? \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $row[$v]) : '-';
                        } else {
                            $args[$k] = $row[$v] ? \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '-';
                        }
                    } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                        $args[$k] = ($row[$v] != '') ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0] : '';
                    } else {
                        $row_v = deserialize($row[$v]);

                        if (is_array($row_v)) {
                            $args_k = array();

                            foreach ($row_v as $option) {
                                $args_k[] = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$option] ? : $option;
                            }

                            $args[$k] = implode(', ', $args_k);
                        } elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]])) {
                            $args[$k] = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]];
                        } elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'])) && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'][$row[$v]])) {
                            $args[$k] = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'][$row[$v]];
                        } else {
                            $args[$k] = $row[$v];
                        }
                    }
                }

                // Shorten the label it if it is too long
                $label = vsprintf($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] ? : '%s', $args);

                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < strlen(strip_tags($label))) {
                    $label = trim(\String::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
                }

                // Remove empty brackets (), [], {}, <> and empty tags from the label
                $label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/', '', $label);
                $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/', '', $label);

                // Build the sorting groups
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] > 0) {
                    $current = $row[$firstOrderBy];
                    $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                    $sortingMode = (count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] != '' && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] == '') ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];
                }

                $return .= '
  <tr class="' . ((++$eoCount % 2 == 0) ? 'even' : 'odd') . ' click2edit" onmouseover="Theme.hoverRow(this,1)" onmouseout="Theme.hoverRow(this,0)" onclick="Theme.toggleSelect(this)">
    ';

                $colspan = 1;

                // Call the label callback ($row, $label, $this)
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                    $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
                    $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

                    $this->import($strClass);
                    $args = $this->$strClass->$strMethod($row, $label, $this, $args);

                    // Handle strings and arrays (backwards compatibility)
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                        $label = is_array($args) ? implode(' ', $args) : $args;
                    } elseif (!is_array($args)) {
                        $args = array($args);
                        $colspan = count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields']);
                    }
                }

                // Show columns
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                    foreach ($args as $j => $arg) {
                        $return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] == $firstOrderBy) ? ' ordered_by' : '') . '">' . ($arg ? : '-') . '</td>';
                    }
                } else {
                    $return .= '<td class="tl_file_list">' . $label . '</td>';
                }

                // Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
                $return .= ((\Input::get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap iso_operations"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
    <td class="tl_file_list tl_right_nowrap iso_operations">' . $this->generateButtons($row, $this->strTable, $this->root) . '</td>') . '
  </tr>';
            }

            // Close the table
            $return .= '
</table>

</div>';

            // Close the form
            if (\Input::get('act') == 'select') {
                $callbacks = '';

                // Call the buttons_callback
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback) {
                        $this->import($callback[0]);
                        $callbacks .= $this->$callback[0]->$callback[1]($this);
                    }
                }

                $return .= '

<div class="tl_formbody_submit" style="text-align:right">

<div class="tl_submit_container">' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ? '
  <input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['delAllConfirm'] . '\')" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']) . '"> ' : '') . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['moveSelected']) . '">
  <input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['copySelected']) . '">
  <input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']) . '">
  <input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']) . '"> ' : '') . $callbacks . '
</div>

</div>
</div>
</form>';
            }
        }

        return $return;
    }


    /**
     * Show header of the parent table and list all records of the current table
     * @return string
     */
    protected function parentView()
    {
        $blnClipboard = false;
        $arrClipboard = $this->Session->get('CLIPBOARD');
        $blnMultiboard = false;

        // Check clipboard
        if (!empty($arrClipboard[$this->strTable])) {
            $blnClipboard = true;
            $arrClipboard = $arrClipboard[$this->strTable];

            if (is_array($arrClipboard['id'])) {
                $blnMultiboard = true;
            }
        }

        // Load the fonts to display the paste hint
        $GLOBALS['TL_CONFIG']['loadGoogleFonts'] = $blnClipboard;

        $strReferer = \System::getReferer(true, $this->strTable);

        // Referer by default should point to the products view
        if (!\Input::get('act')) {
            $strReferer = 'contao/main.php?do=' . \Input::get('do') . '&amp;gid=' . $this->Session->get('iso_products_gid') . '&amp;ref=' . \Input::get('ref');
        }

        $return = '
<div id="tl_buttons">
<a href="' . $strReferer . '" class="header_back" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' . (!$blnClipboard ? ((\Input::get('act') != 'select') ? (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<a href="' . \Backend::addToUrl('act=create&amp;mode=2&amp;pid=' . $this->intId) . '" class="header_new" title="' . specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]) . '" accesskey="n" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG'][$this->strTable]['new'][0] . '</a> ' : '') . $this->generateGlobalButtons() : '') : '<a href="' . \Backend::addToUrl('clipboard=1') . '" class="header_clipboard" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']) . '" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['clearClipboard'] . '</a> ') . '
</div>' . \Message::generate(true);

        // Get all details of the parent record
        $objParent = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
            ->limit(1)
            ->execute(CURRENT_ID);

        if ($objParent->numRows < 1) {
            return $return;
        }

        $return .= ((\Input::get('act') == 'select') ? '

<form action="' . ampersand(\Environment::get('request'), true) . '" id="tl_select" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_select">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' : '') . ($blnClipboard ? '

<div id="paste_hint">
  <p>' . $GLOBALS['TL_LANG']['MSC']['selectNewPosition'] . '</p>
</div>' : '') . '

<div class="tl_listing_container iso_listing_container parent_view">

<div class="tl_header click2edit" onmouseover="Theme.hoverDiv(this,1)" onmouseout="Theme.hoverDiv(this,0)">';

        // List all records of the child table
        if (!\Input::get('act') || \Input::get('act') == 'paste' || \Input::get('act') == 'select') {
            $imagePasteAfter = \Image::getHtml('pasteafter.gif', $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]);

            // Temporarily limit the header operations
            $headerButtons = $GLOBALS['TL_DCA'][$this->strTable]['list']['operations'];
            $GLOBALS['TL_DCA'][$this->strTable]['list']['operations'] = array_intersect_key($GLOBALS['TL_DCA'][$this->strTable]['list']['operations'], array_flip($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerOperations']));

            $return .= '
<div class="tl_content_right iso_content_right">' . ((\Input::get('act') == 'select') ? '
<div class="tl_select_all">
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox"></div>' : ($blnClipboard ? ' <a href="' . \Backend::addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $objParent->id . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '') . '&amp;table=' . $this->strTable) . '" title="' . specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>' : '')) . '
<div class="iso_operations">' .
                $this->generateButtons($objParent->row(), $this->strTable) . '
</div>
</div>';

            // Restore the available operations
            $GLOBALS['TL_DCA'][$this->strTable]['list']['operations'] = $headerButtons;

            // Format header fields
            $add = array();
            $headerFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerFields'];

            foreach ($headerFields as $v) {
                $_v = deserialize($objParent->$v);

                if (is_array($_v)) {
                    $_v = implode(', ', $_v);
                } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                    $_v = ($_v != '') ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'date') {
                    $_v = $_v ? \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $_v) : '-';
                } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'time') {
                    $_v = $_v ? \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $_v) : '-';
                } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'datim') {
                    $_v = $_v ? \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $_v) : '-';
                } elseif ($v == 'tstamp') {
                    $objMaxTstamp = $this->Database->prepare("SELECT MAX(tstamp) AS tstamp FROM " . $this->strTable . " WHERE pid=?")
                        ->execute($objParent->id);

                    if (!$objMaxTstamp->tstamp) {
                        $objMaxTstamp->tstamp = $objParent->tstamp;
                    }

                    $_v = \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], max($objParent->tstamp, $objMaxTstamp->tstamp));
                } elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['foreignKey'])) {
                    $arrForeignKey = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['foreignKey'], 2);

                    $objLabel = $this->Database->prepare("SELECT " . $arrForeignKey[1] . " AS value FROM " . $arrForeignKey[0] . " WHERE id=?")
                        ->limit(1)
                        ->execute($_v);

                    if ($objLabel->numRows) {
                        $_v = $objLabel->value;
                    }
                } elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v])) {
                    $_v = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v][0];
                } elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v])) {
                    $_v = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v];
                } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'])) {
                    $_v = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'][$_v];
                }

                // Add the sorting field
                if ($_v != '') {
                    $key = isset($GLOBALS['TL_LANG'][$this->strTable][$v][0]) ? $GLOBALS['TL_LANG'][$this->strTable][$v][0] : $v;
                    $add[$key] = $_v;
                }
            }

            // Trigger the header_callback (see #3417)
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['header_callback'])) {
                $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['header_callback'][0];
                $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['header_callback'][1];

                $this->import($strClass);
                $add = $this->$strClass->$strMethod($add, $this);
            }

            // Output the header data
            $return .= '

<table class="tl_header_table">';

            foreach ($add as $k => $v) {
                if (is_array($v)) {
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

            if (is_array($this->orderBy) && strlen($this->orderBy[0])) {
                $orderBy = $this->orderBy;
                $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

                // Order by the foreign key
                if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey'])) {
                    $key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['foreignKey'], 2);
                    $query = "SELECT *, (SELECT " . $key[1] . " FROM " . $key[0] . " WHERE " . $this->strTable . "." . $firstOrderBy . "=" . $key[0] . ".id) AS foreignKey FROM " . $this->strTable;
                    $orderBy[0] = 'foreignKey';
                }
            } elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'])) {
                $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);
            }

            $this->procedure[] = "pid=?";
            $this->values[] = CURRENT_ID;

            // Support empty ptable fields (backwards compatibility)
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable']) {
                $this->procedure[] = "ptable=?";
                $this->values[] = $this->strTable;
            }

            // WHERE
            if (!empty($this->procedure)) {
                $query .= " WHERE " . implode(' AND ', $this->procedure);
            }
            if (!empty($this->root) && is_array($this->root)) {
                $query .= (!empty($this->procedure) ? " AND " : " WHERE ") . "id IN(" . implode(',', array_map('intval', $this->root)) . ")";
            }

            // ORDER BY
            if (!empty($orderBy) && is_array($orderBy)) {
                $query .= " ORDER BY " . implode(', ', $orderBy);
            }

            $objOrderByStmt = $this->Database->prepare($query);

            // LIMIT
            if (strlen($this->limit)) {
                $arrLimit = explode(',', $this->limit);
                $objOrderByStmt->limit($arrLimit[1], $arrLimit[0]);
            }

            $objOrderBy = $objOrderByStmt->execute($this->values);

            if ($objOrderBy->numRows < 1) {
                return $return . '
<p class="tl_empty_parent_view">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>

</div>';
            }

            $result = $objOrderBy->fetchAllAssoc();
            $return .= '

<table class="tl_listing' . ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ? ' showColumns' : '') . '">';

            // Automatically add the "order by" field as last column if we do not have group headers
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] && !in_array($firstOrderBy, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'])) {
                $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][] = $firstOrderBy;
            }

            // Generate the table header if the "show columns" option is active
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                $return .= '
  <tr>';

                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f) {
                    $return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) . '</th>';
                }

                $return .= '
    <th class="tl_folder_tlist tl_right_nowrap iso_operations">&nbsp;</th>
  </tr>';
            }

            // Process result and add label and buttons
            $remoteCur = false;
            $groupclass = 'tl_folder_tlist';
            $eoCount = -1;

            foreach ($result as $row) {
                $args = array();
                $this->current[] = $row['id'];
                $showFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'];

                // Label
                foreach ($showFields as $k => $v) {
                    // Decrypt the value
                    if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['encrypt']) {
                        $row[$v] = \Encryption::decrypt(deserialize($row[$v]));
                    }

                    if (strpos($v, ':') !== false) {
                        list($strKey, $strTable) = explode(':', $v);
                        list($strTable, $strField) = explode('.', $strTable);

                        $objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
                            ->limit(1)
                            ->execute($row[$strKey]);

                        $args[$k] = $objRef->numRows ? $objRef->$strField : '';
                    } elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10))) {
                        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'date') {
                            $args[$k] = $row[$v] ? \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $row[$v]) : '-';
                        } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] == 'time') {
                            $args[$k] = $row[$v] ? \Date::parse($GLOBALS['TL_CONFIG']['timeFormat'], $row[$v]) : '-';
                        } else {
                            $args[$k] = $row[$v] ? \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row[$v]) : '-';
                        }
                    } elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple']) {
                        $args[$k] = ($row[$v] != '') ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0] : '';
                    } else {
                        $row_v = deserialize($row[$v]);

                        if (is_array($row_v)) {
                            $args_k = array();

                            foreach ($row_v as $option) {
                                $args_k[] = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$option] ? : $option;
                            }

                            $args[$k] = implode(', ', $args_k);
                        } elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]])) {
                            $args[$k] = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$row[$v]];
                        } elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'])) && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'][$row[$v]])) {
                            $args[$k] = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'][$row[$v]];
                        } else {
                            $args[$k] = $row[$v];
                        }
                    }
                }

                // Shorten the label it if it is too long
                $label = vsprintf($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] ? : '%s', $args);

                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] < strlen(strip_tags($label))) {
                    $label = trim(\String::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
                }

                // Remove empty brackets (), [], {}, <> and empty tags from the label
                $label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/', '', $label);
                $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/', '', $label);

                // Build the sorting groups
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] > 0) {
                    $current = $row[$firstOrderBy];
                    $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                    $sortingMode = (count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] != '' && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] == '') ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'];
                    $remoteNew = $this->formatCurrentValue($firstOrderBy, $current, $sortingMode);

                    // Add the group header
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] && !$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['disableGrouping'] && ($remoteNew != $remoteCur || $remoteCur === false)) {
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
  <tr class="' . ((++$eoCount % 2 == 0) ? 'even' : 'odd') . ' click2edit" onmouseover="Theme.hoverRow(this,1)" onmouseout="Theme.hoverRow(this,0)" onclick="Theme.toggleSelect(this)">
    ';

                $colspan = 1;

                // Call the label callback ($row, $label, $this)
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                    $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
                    $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

                    $this->import($strClass);
                    $args = $this->$strClass->$strMethod($row, $label, $this, $args);

                    // Handle strings and arrays (backwards compatibility)
                    if (!$GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                        $label = is_array($args) ? implode(' ', $args) : $args;
                    } elseif (!is_array($args)) {
                        $args = array($args);
                        $colspan = count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields']);
                    }
                }

                // Show columns
                if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                    foreach ($args as $j => $arg) {
                        $return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j] == $firstOrderBy) ? ' ordered_by' : '') . '">' . ($arg ? : '-') . '</td>';
                    }
                } else {
                    $return .= '<td class="tl_file_list">' . $label . '</td>';
                }

                // Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
                $return .= ((\Input::get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap iso_operations"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
    <td class="tl_file_list tl_right_nowrap iso_operations">' . $this->generateButtons($row, $this->strTable, $this->root) . '</td>') . '
  </tr>';
            }

            // Close the table
            $return .= '
</table>

</div>';
        }

        // Close form
        if (\Input::get('act') == 'select') {
            $callbacks = '';

            // Call the buttons_callback
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback) {
                    $this->import($callback[0]);
                    $callbacks .= $this->$callback[0]->$callback[1]($this);
                }
            }

            $return .= '

<div class="tl_formbody_submit" style="text-align:right">

<div class="tl_submit_container">' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'] ? '
  <input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['delAllConfirm'] . '\')" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']) . '"> ' : '') . '
  <input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['moveSelected']) . '">
  <input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['copySelected']) . '"> ' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ? '
  <input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']) . '">
  <input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']) . '"> ' : '') . $callbacks . '
</div>

</div>
</div>
</form>';
        }

        return $return;
    }


    /**
     * Return a select menu to limit results
     * @param boolean
     * @return string
     */
    protected function limitMenu($blnOptional = false)
    {
        $session = $this->Session->getData();
        $filter = \Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;
        $fields = '';

        // Set limit from user input
        if (\Input::post('FORM_SUBMIT') == 'tl_filters' || \Input::post('FORM_SUBMIT') == 'tl_filters_limit') {
            $strLimit = \Input::post('tl_limit');

            if ($strLimit == 'tl_limit') {
                unset($session['filter'][$filter]['limit']);
            } else {
                // Validate the user input (thanks to aulmn) (see #4971)
                if ($strLimit == 'all' || preg_match('/^[0-9]+,[0-9]+$/', $strLimit)) {
                    $session['filter'][$filter]['limit'] = $strLimit;
                }
            }

            $this->Session->setData($session);

            if (\Input::post('FORM_SUBMIT') == 'tl_filters_limit') {
                \Controller::reload();
            }
        } // Set limit from table configuration
        else {
            $this->limit = ($session['filter'][$filter]['limit'] != '') ? (($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']) : '0,' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
            $query = "SELECT COUNT(*) AS count FROM " . $this->strTable;

            if (\Input::get('id')) {
                $this->procedure[] = "pid=?";
                $this->values[] = \Input::get('id');
            } else {
                $this->procedure[] = "pid=0";
            }

            if (!empty($this->root) && is_array($this->root)) {
                $this->procedure[] = 'id IN(' . implode(',', $this->root) . ')';
            }

            if (!empty($this->procedure)) {
                $query .= " WHERE " . implode(' AND ', $this->procedure);
            }

            $objTotal = $this->Database->prepare($query)->execute($this->values);
            $total = $objTotal->count;
            $options_total = 0;
            $blnIsMaxResultsPerPage = false;

            // Overall limit
            if ($total > $GLOBALS['TL_CONFIG']['maxResultsPerPage'] && ($this->limit === null || preg_replace('/^.*,/', '', $this->limit) == $GLOBALS['TL_CONFIG']['maxResultsPerPage'])) {
                if ($this->limit === null) {
                    $this->limit = '0,' . $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
                }

                $blnIsMaxResultsPerPage = true;
                $GLOBALS['TL_CONFIG']['resultsPerPage'] = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
                $session['filter'][$filter]['limit'] = $GLOBALS['TL_CONFIG']['maxResultsPerPage'];
            }

            $options = '';

            // Build options
            if ($total > 0) {
                $options = '';
                $options_total = ceil($total / $GLOBALS['TL_CONFIG']['resultsPerPage']);

                // Reset limit if other parameters have decreased the number of results
                if ($this->limit !== null && ($this->limit == '' || preg_replace('/,.*$/', '', $this->limit) > $total)) {
                    $this->limit = '0,' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
                }

                // Build options
                for ($i = 0; $i < $options_total; $i++) {
                    $this_limit = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage']) . ',' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
                    $upper_limit = ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + $GLOBALS['TL_CONFIG']['resultsPerPage']);

                    if ($upper_limit > $total) {
                        $upper_limit = $total;
                    }

                    $options .= '
  <option value="' . $this_limit . '"' . \Widget::optionSelected($this->limit, $this_limit) . '>' . ($i * $GLOBALS['TL_CONFIG']['resultsPerPage'] + 1) . ' - ' . $upper_limit . '</option>';
                }

                if (!$blnIsMaxResultsPerPage) {
                    $options .= '
  <option value="all"' . \Widget::optionSelected($this->limit, null) . '>' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</option>';
                }
            }

            // Return if there is only one page
            if ($blnOptional && ($total < 1 || $options_total < 2)) {
                return '';
            }

            $fields = '
<select name="tl_limit" class="tl_select' . (($session['filter'][$filter]['limit'] != 'all' && $total > $GLOBALS['TL_CONFIG']['resultsPerPage']) ? ' active' : '') . '" onchange="this.form.submit()">
  <option value="tl_limit">' . $GLOBALS['TL_LANG']['MSC']['filterRecords'] . '</option>' . $options . '
</select> ';
        }

        return '

<div class="tl_limit tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['showOnly'] . ':</strong> ' . $fields . '
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

        foreach ($this->strPalette as $field) {
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]) && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['attributes']['multilingual']) {
                $arrDuplicate[] = $field;
            }
        }

        if (!empty($arrDuplicate)) {
            $intLanguageId = $this->Database->execute("SELECT id FROM {$this->strTable} WHERE pid={$this->intId} AND language='$strLanguage'")->id;

            $this->createInitialVersion($this->strTable, $intLanguageId);

            $arrRow = $this->Database->execute("SELECT " . implode(',', $arrDuplicate) . " FROM {$this->strTable} WHERE id={$this->intId}")->fetchAssoc();
            $this->Database->prepare("UPDATE {$this->strTable} %s WHERE id=$intLanguageId")->set($arrRow)->executeUncached();

            $this->createNewVersion($this->strTable, $intLanguageId);
            \System::log(sprintf('A new version of record ID %s (table %s) has been created', $intLanguageId, $this->strTable), 'DC_ProductData copyFallback()', TL_GENERAL);
        }

        \Controller::redirect(\Backend::addToUrl('act=edit'));
    }
}
