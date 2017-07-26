<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2016 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
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
     * Array of translations for this product's type
     * @var array
     */
    protected $arrTranslations;

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
        $this->intGroupId = (int) \Session::getInstance()->get('iso_products_gid');

        // Check if the group exists
        if ($this->intGroupId > 0) {
            $objGroup = \Isotope\Model\Group::findByPk($this->intGroupId);

            if (null === $objGroup) {
                if (\BackendUser::getInstance()->isAdmin || !is_array(\BackendUser::getInstance()->iso_groups)) {
                    $this->intGroupId = 0;
                }
                elseif (!\BackendUser::getInstance()->isAdmin) {
                    $this->intGroupId = (int) \Database::getInstance()->prepare(
                        "SELECT id FROM tl_iso_group WHERE id IN ('" . implode("','", \BackendUser::getInstance()->iso_groups) . "') ORDER BY " . \Database::getInstance()->findInSet('id', \BackendUser::getInstance()->iso_groups)
                    )->limit(1)->execute()->id;
                }
            }
        }

        // Redirect if the product was not found
        if (isset($_GET['id'])) {
            $objProduct = \Database::getInstance()->prepare("SELECT id FROM $strTable WHERE id=?")
                ->limit(1)
                ->execute(\Input::get('id', true));

            if (!$objProduct->numRows) {
                \Controller::redirect(preg_replace('/(&amp;)?id=[^&]*/i', '', \Environment::get('request')));
            }
        }

        $arrClipboard = $this->getSession()->get('CLIPBOARD');

        // Cut all records
        if ($arrClipboard[$strTable]['mode'] == 'cutAll' && \Input::get('act') != 'cutAll') {
            \Controller::redirect(\Backend::addToUrl('&act=cutAll'));
        }

        parent::__construct($strTable);

        // Allow to customize languages via the onload_callback
        if (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['languages'])) {
            $arrPageLanguages = $GLOBALS['TL_DCA'][$this->strTable]['config']['languages'];
        } else {
            $arrPageLanguages = array_map(
                function ($strLang) {
                    return str_replace('-', '_', $strLang);
                },
                $this->Database
                    ->execute("SELECT DISTINCT language FROM tl_page WHERE type='root' AND language!=''")
                    ->fetchEach('language')
            );
        }

        if (count($arrPageLanguages) > 1) {
            $this->arrTranslationLabels = \System::getLanguages();
            $this->arrTranslations      = array_intersect(array_keys($this->arrTranslationLabels), $arrPageLanguages);
        }
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
            $arrClipboard = $this->getSession()->get('CLIPBOARD');

            $arrClipboard[$this->strTable] = array
            (
                'id' => \Input::get('id'),
                'childs' => \Input::get('childs'),
                'mode' => \Input::get('mode')
            );

            $this->getSession()->set('CLIPBOARD', $arrClipboard);

            // Perform a redirect (this is the CURRENT_ID fix)
            \Controller::redirect('contao/main.php?do=' . \Input::get('do') . (\Input::get('pid') ? '&id=' . \Input::get('pid') : '') . '&rt=' . \Input::get('rt') . '&ref=' . \Input::get('ref'));
        }

        // Do not show the language records
        $this->procedure[] = "language=''";

        // Display products filtered by group
        if (!$this->intId && $this->intGroupId > 0) {
            $this->procedure[] = "gid IN(" . implode(',', array_map('intval', \Database::getInstance()->getChildRecords(array($this->intGroupId), \Isotope\Model\Group::getTable(), false, array($this->intGroupId)))) . ")";
        }

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
        $session = $this->getSessionData();
        $session['CURRENT']['IDS'] = $this->current;
        $this->setSessionData($session);

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
            $arrClipboard = $this->getSession()->get('CLIPBOARD');
            $arrClipboard[$this->strTable] = array();
            $this->getSession()->set('CLIPBOARD', $arrClipboard);

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
                    if (is_array($callback)) {
                        $this->import($callback[0]);
                        $this->{$callback[0]}->{$callback[1]}($this);
                    } elseif (is_callable($callback)) {
                        call_user_func($callback, $this);
                    }
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

        /** PATCH: removed check for sorting field */
        if (!$GLOBALS['TL_DCA'][$table]['config']['ptable'] && strlen(\Input::get('childs')) && $this->Database->fieldExists('pid', $table))
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

            if (!$GLOBALS['TL_DCA'][$v]['config']['doNotCopyRecords'] && strlen($v))
            {
                // Consider the dynamic parent table (see #4867)
                if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'])
                {
                    $ptable = $GLOBALS['TL_DCA'][$v]['config']['ptable'];
                    $cond = ($ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?"; // backwards compatibility

                    $objCTable = $this->Database->prepare("SELECT * FROM $v WHERE pid=? AND $cond" . ($this->Database->fieldExists('sorting', $v) ? " ORDER BY sorting" : ""))
                                                ->execute($id, $ptable);
                }
                else
                {
                    $objCTable = $this->Database->prepare("SELECT * FROM $v WHERE pid=?" . ($this->Database->fieldExists('sorting', $v) ? " ORDER BY sorting" : ""))
                                                ->execute($id);
                }

                while ($objCTable->next())
                {
                    // Exclude the duplicated record itself
                    if ($v == $table && $objCTable->id == $parentId)
                    {
                        continue;
                    }

                    foreach ($objCTable->row() as $kk=>$vv)
                    {
                        if ($kk == 'id')
                        {
                            continue;
                        }

                        // Reset all unique, doNotCopy and fallback fields to their default value
                        if ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['unique'] || $GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['doNotCopy'] || $GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['fallback'])
                        {
                            $vv = '';

                            // Use array_key_exists to allow NULL (see #5252)
                            if (array_key_exists('default', $GLOBALS['TL_DCA'][$v]['fields'][$kk]))
                            {
                                $vv = is_array($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) ? serialize($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) : $GLOBALS['TL_DCA'][$v]['fields'][$kk]['default'];
                            }

                            // Encrypt the default value (see #3740)
                            if ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['encrypt'])
                            {
                                $vv = \Encryption::encrypt($vv);
                            }
                        }

                        $copy[$v][$objCTable->id][$kk] = $vv;
                    }

                    $copy[$v][$objCTable->id]['pid'] = $insertID;
                    $copy[$v][$objCTable->id]['tstamp'] = $time;
                }
            }
        }

        // Duplicate the child records
        foreach ($copy as $k=>$v)
        {
            if (!empty($v))
            {
                foreach ($v as $kk=>$vv)
                {
                    $objInsertStmt = $this->Database->prepare("INSERT INTO $k %s")
                                                    ->set($vv)
                                                    ->execute();

                    if ($objInsertStmt->affectedRows)
                    {
                        $insertID = $objInsertStmt->insertId;

                        if ((!empty($cctable[$k]) || $GLOBALS['TL_DCA'][$k]['list']['sorting']['mode'] == 5) && $kk != $parentId)
                        {
                            $this->copyChilds($k, $insertID, $kk, $parentId);
                        }
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
        $arrClipboard = $this->getSession()->get('CLIPBOARD');

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
     * Auto-generate a form to edit the current database record
     * @param integer
     * @param integer
     * @return string
     */
    public function edit($intID = false, $ajaxId = false)
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable']) {
            \System::log('Table ' . $this->strTable . ' is not editable', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        if ($intID) {
            $this->intId = $intID;
        }

        // Get the current record
        $objRow = \Database::getInstance()->prepare("SELECT * FROM {$this->strTable} WHERE id=?")
            ->limit(1)
            ->execute($this->intId);

        // Redirect if there is no record with the given ID
        if ($objRow->numRows < 1) {
            \System::log('Could not load record "'.$this->strTable.'.id='.$this->intId.'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        } // ID of a language record is not allowed
        elseif ($objRow->language != '') {
            \System::log('Cannot edit language record "'.$this->strTable.'.id='.$this->intId.'"', __METHOD__, TL_ERROR);
            \Controller::redirect('contao/main.php?act=error');
        }

        $this->objActiveRecord = $objRow;

        $return = '';
        $this->values[] = $this->intId;
        $this->procedure[] = 'id=?';

        $this->blnCreateNewVersion = false;
        $objVersions = new \Versions($this->strTable, $this->intId);

        // Compare versions
        if (\Input::get('versions')) {
            $objVersions->compare();
        }

        // Restore a version
        if (\Input::post('FORM_SUBMIT') == 'tl_version' && \Input::post('version') != '') {
            $objVersions->restore(\Input::post('version'));
            $this->reload();
        }

        $objVersions->initialize();

        // Load and/or change language
        $this->blnEditLanguage = false;

        if (!empty($this->arrTranslations)) {
            $blnLanguageUpdated = false;
            $session = \Session::getInstance()->getData();

            if (\Input::post('FORM_SUBMIT') == 'tl_language') {
                if (in_array(\Input::post('language'), $this->arrTranslations)) {
                    $session['language'][$this->strTable][$this->intId] = \Input::post('language');
                } else {
                    unset($session['language'][$this->strTable][$this->intId]);
                }

                $blnLanguageUpdated = true;
            } elseif (\Input::post('FORM_SUBMIT') == $this->strTable && isset($_POST['deleteLanguage'])) {
                $this->Database->prepare("DELETE FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $session['language'][$this->strTable][$this->intId]);
                unset($session['language'][$this->strTable][$this->intId]);
                $blnLanguageUpdated = true;
            }

            if ($blnLanguageUpdated) {
                \Session::getInstance()->setData($session);
                $_SESSION['TL_INFO'] = '';
                \Controller::reload();
            }

            if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] != '' && in_array($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId], $this->arrTranslations)) {
                $objRow = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);

                if (!$objRow->numRows) {
                    $intId = $this->Database->prepare("INSERT INTO {$this->strTable} (pid,tstamp,language) VALUES (?,?,?)")->execute($this->intId, time(), $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId])->insertId;

                    $objRow = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE id=?")->execute($intId);
                }

                $this->objActiveRecord = $objRow;
                $this->values = array($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);
                $this->procedure = array('pid=?', 'language=?');
                $this->blnEditLanguage = true;
            }
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

                    if (preg_match('/^\{.*\}$/', $vv)) {
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

            $class = 'tl_tbox';
            $fs = $this->getSession()->get('fieldset_states');
            $blnIsFirst = true;

            // Render boxes
            foreach ($boxes as $k => $v) {
                $strAjax = '';
                $blnAjax = false;
                $key = '';
                $cls = '';
                $legend = '';

                if (isset($legends[$k])) {
                    list($key, $cls) = explode(':', $legends[$k]);
                    $legend = "\n" . '<legend onclick="AjaxRequest.toggleFieldset(this,\'' . $key . '\',\'' . $this->strTable . '\')">' . (isset($GLOBALS['TL_LANG'][$this->strTable][$key]) ? $GLOBALS['TL_LANG'][$this->strTable][$key] : $key) . '</legend>';
                }

                if (isset($fs[$this->strTable][$key])) {
                    $class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
                } else {
                    $class .= (($cls && $legend) ? ' ' . $cls : '');
                }

                $return .= "\n\n" . '<fieldset' . ($key ? ' id="pal_'.$key.'"' : '') . ' class="' . $class . ($legend ? '' : ' nolegend') . '">' . $legend;

                // Build rows of the current box
                foreach ($v as $vv) {
                    if ($vv == '[EOF]') {
                        if ($blnAjax && \Environment::get('isAjaxRequest')) {
                            return $strAjax . '<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'">';
                        }

                        $blnAjax = false;
                        $return .= "\n" . '</div>';

                        continue;
                    }

                    if (preg_match('/^\[.*\]$/', $vv)) {
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
                                $this->varValue = $this->{$callback[0]}->{$callback[1]}($this->varValue, $this);
                            } elseif (is_callable($callback)) {
                                $this->varValue = call_user_func($callback, $this->varValue, $this);
                            }
                        }
                    }

                    // Re-set the current value
                    $this->objActiveRecord->{$this->strField} = $this->varValue;

                    // Build the row and pass the current palette string (thanks to Tristan Lins)
                    $blnAjax ? $strAjax .= $this->row($this->strPalette) : $return .= $this->row($this->strPalette);
                }

                $class = 'tl_box';
                $return .= "\n" . '</fieldset>';
            }
        }

        $version = '';

        // Versions overview
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning']) {
            $version = $objVersions->renderDropdown();
        }

        if ('' === $version) {
            $version = '<div class="tl_version_panel"></div>';
        }

        // Check languages
        if (!empty($this->arrTranslations)) {
            $arrAvailableLanguages = $this->Database->prepare("SELECT language FROM {$this->strTable} WHERE pid=?")->execute($this->intId)->fetchEach('language');
            $available = '';
            $undefined = '';

            foreach ($this->arrTranslations as $language) {
                if (in_array($language, $arrAvailableLanguages)) {
                    if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] == $language) {
                        $available .= '<option value="' . $language . '" selected="selected">' . $this->arrTranslationLabels[$language] . '</option>';
                        $_SESSION['TL_INFO'] = array($GLOBALS['TL_LANG']['MSC']['editingLanguage']);
                    } else {
                        $available .= '<option value="' . $language . '">' . $this->arrTranslationLabels[$language] . '</option>';
                    }
                } else {
                    $undefined .= '<option value="' . $language . '">' . $this->arrTranslationLabels[$language] . ' (' . $GLOBALS['TL_LANG']['MSC']['undefinedLanguage'] . ')' . '</option>';
                }
            }

            $version = str_replace(
                '<div class="tl_version_panel">',
                '<div class="tl_version_panel tl_iso_products_panel">
<form action="' . ampersand(\Environment::get('request'), true) . '" id="tl_language" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_language">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
<select name="language" class="tl_select' . (strlen($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]) ? ' active' : '') . '" onchange="document.id(this).getParent(\'form\').submit()">
    <option value="">' . $GLOBALS['TL_LANG']['MSC']['defaultLanguage'] . '</option>' . $available . $undefined . '
</select>
<noscript>
<input type="submit" name="editLanguage" class="tl_submit" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['editLanguage']) . '">
</noscript>
</div>
</form>',
                $version
            );
        }

        // Submit buttons
        $arrButtons = array();
        $arrButtons['save'] = '<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'">';

        if (!\Input::get('nb')) {
            $arrButtons['saveNclose'] = '<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'">';
        }

        if (!\Input::get('popup') && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) {
            $arrButtons['saveNcreate'] = '<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNcreate']).'">';
        }

        if (\Input::get('s2e')) {
            $arrButtons['saveNedit'] = '<input type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNedit']).'">';
        } elseif (!\Input::get('popup') && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4 || strlen($this->ptable) || $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'])) {
            $arrButtons['saveNback'] = '<input type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNback']).'">';
        }

        if ($this->blnEditLanguage) {
            $arrButtons['deleteLanguage'] = '<input type="submit" name="deleteLanguage" class="tl_submit" style="float:right" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['deleteLanguage']) . '" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] . '\')">';
        }

        // Call the buttons_callback (see #4691)
        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
                } elseif (is_callable($callback)) {
                    $arrButtons = $callback($arrButtons, $this);
                }
            }
        }

        // Add the buttons and end the form
        $return .= '
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  ' . implode(' ', $arrButtons) . '
</div>

</div>
</form>

<script>
  window.addEvent(\'domready\', function() {
    Theme.focusInput("'.$this->strTable.'");
  });
</script>';

        $copyFallback = $this->blnEditLanguage ? '&nbsp;&nbsp;::&nbsp;&nbsp;<a href="' . \Backend::addToUrl('act=copyFallback') . '" class="header_iso_copy" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['copyFallback']) . '" accesskey="d" onclick="Backend.getScrollOffset();">' . ($GLOBALS['TL_LANG']['MSC']['copyFallback'] ? $GLOBALS['TL_LANG']['MSC']['copyFallback'] : 'copyFallback') . '</a>' : '';

        // Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
        $return = $version . '
<div id="tl_buttons">' . (\Input::get('nb') ? '&nbsp;' : '
<a href="'.\System::getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>') . $copyFallback . '
</div>
'.\Message::generate().'
<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"'.(!empty($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.specialchars($this->strTable).'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">
<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return;

        // Reload the page to prevent _POST variables from being sent twice
        if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload) {
            $arrValues = $this->values;
            array_unshift($arrValues, time());

            // Trigger the onsubmit_callback
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'])) {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback) {
                    if (is_array($callback)) {
                        $this->import($callback[0]);
                        $this->{$callback[0]}->{$callback[1]}($this);
                    } elseif (is_callable($callback)) {
                        call_user_func($callback, $this);
                    }
                }
            }

            // Save the current version
            if ($this->blnCreateNewVersion) {
                $objVersions->create();

                // Call the onversion_callback
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'])) {
                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback) {
                        if (is_array($callback)) {
                            $this->import($callback[0]);
                            $this->{$callback[0]}->{$callback[1]}($this->strTable, $this->objActiveRecord->id, $this);
                        } elseif (is_callable($callback)) {
                            call_user_func($callback, $this->strTable, $this->objActiveRecord->id, $this);
                        }
                    }
                }

                \System::log('A new version of record "'.$this->strTable.'.id='.$this->intId.'" has been created'.$this->getParentEntries($this->strTable, $this->intId), __METHOD__, TL_GENERAL);
            }

            // Set the current timestamp (-> DO NOT CHANGE THE ORDER version - timestamp)
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable']) {
                $this->Database->prepare("UPDATE " . $this->strTable . " SET ptable=?, tstamp=? WHERE id=?")
                               ->execute($this->ptable, time(), $this->intId);
            } else {
                $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
                               ->execute(time(), $this->intId);
            }

            // Redirect
            if (isset($_POST['saveNclose'])) {
                \Message::reset();
                \System::setCookie('BE_PAGE_OFFSET', 0, 0);

                \Controller::redirect(\System::getReferer());
            } elseif (isset($_POST['saveNedit'])) {
                \Message::reset();
                \System::setCookie('BE_PAGE_OFFSET', 0, 0);

                $strUrl = \Backend::addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href'], false);
                $strUrl = preg_replace('/(&amp;)?(s2e|act)=[^&]*/i', '', $strUrl);

                \Controller::redirect($strUrl);
            } elseif (isset($_POST['saveNback'])) {
                \Message::reset();
                \System::setCookie('BE_PAGE_OFFSET', 0, 0);

                if ($this->ptable == '') {
                    \Controller::redirect(TL_SCRIPT . '?do=' . \Input::get('do'));
                } elseif (($this->ptable == 'tl_theme' && $this->strTable == 'tl_style_sheet') || ($this->ptable == 'tl_page' && $this->strTable == 'tl_article')) {
                    \Controller::redirect(\System::getReferer(false, $this->strTable));
                } else {
                    \Controller::redirect(\System::getReferer(false, $this->ptable));
                }
            } elseif (isset($_POST['saveNcreate'])) {
                \Message::reset();
                \System::setCookie('BE_PAGE_OFFSET', 0, 0);

                $strUrl = TL_SCRIPT . '?do=' . \Input::get('do');

                if (isset($_GET['table'])) {
                    $strUrl .= '&amp;table=' . \Input::get('table');
                }

                // Tree view
                if ($this->treeView) {
                    $strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
                } // Parent view
                elseif ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4 || $this->activeRecord->pid > 0) {
                    $strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->activeRecord->pid : '&amp;act=create&amp;mode=2&amp;pid=' . $this->activeRecord->pid;
                } // List view
                else {
                    $strUrl .= $this->ptable != '' ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';
                }

                \Controller::redirect($strUrl . '&amp;rt=' . REQUEST_TOKEN);
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
     * Auto-generate a form to override all records that are currently shown
     *
     * @return string
     */
    public function overrideAll()
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
        {
            $this->log('Table "'.$this->strTable.'" is not editable', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $return = '';
        $this->import('BackendUser', 'User');

        // Get current IDs from session
        $session = $this->getSessionData();
        $ids = $session['CURRENT']['IDS'];

        // Save field selection in session
        if (\Input::post('FORM_SUBMIT') == $this->strTable.'_all' && \Input::get('fields'))
        {
            $session['CURRENT'][$this->strTable] = \Input::post('all_fields');
            $this->setSessionData($session);
        }

        // Add fields
        $fields = $session['CURRENT'][$this->strTable];

        if (!empty($fields) && is_array($fields) && \Input::get('fields'))
        {
            $class = 'tl_tbox';
            $formFields = array();

            // Save record
            if (\Input::post('FORM_SUBMIT') == $this->strTable)
            {
                foreach ($ids as $id)
                {
                    $this->intId = $id;
                    $this->procedure = array('id=?');
                    $this->values = array($this->intId);
                    $this->blnCreateNewVersion = false;

                    // Get the field values
                    $objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
                                             ->limit(1)
                                             ->execute($this->intId);

                    // Store the active record
                    $this->objActiveRecord = $objRow;

                    $objVersions = new \Versions($this->strTable, $this->intId);
                    $objVersions->initialize();

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
                        // Call the onsubmit_callback
                        if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
                        {
                            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
                            {
                                if (is_array($callback))
                                {
                                    $this->import($callback[0]);
                                    $this->{$callback[0]}->{$callback[1]}($this);
                                }
                                elseif (is_callable($callback))
                                {
                                    $callback($this);
                                }
                            }
                        }

                        // Create a new version
                        if ($this->blnCreateNewVersion)
                        {
                            $objVersions->create();

                            // Call the onversion_callback
                            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback']))
                            {
                                @trigger_error('Using the onversion_callback has been deprecated and will no longer work in Contao 5.0. Use the oncreate_version_callback instead.', E_USER_DEPRECATED);

                                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback)
                                {
                                    if (is_array($callback))
                                    {
                                        $this->import($callback[0]);
                                        $this->{$callback[0]}->{$callback[1]}($this->strTable, $this->intId, $this);
                                    }
                                    elseif (is_callable($callback))
                                    {
                                        $callback($this->strTable, $this->intId, $this);
                                    }
                                }
                            }
                        }

                        // Set the current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
                        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
                        {
                            $this->Database->prepare("UPDATE " . $this->strTable . " SET ptable=?, tstamp=? WHERE id=?")
                                           ->execute($this->ptable, time(), $this->intId);
                        }
                        else
                        {
                            $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
                                           ->execute(time(), $this->intId);
                        }
                    }
                }
            }

            $blnIsFirst = true;

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

                // Autofocus the first field
                if ($blnIsFirst && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['inputType'] == 'text')
                {
                    $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['autofocus'] = 'autofocus';
                    $blnIsFirst = false;
                }

                // Disable auto-submit
                $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['submitOnChange'] = false;
                $return .= $this->row();
            }

            // Close box
            $return .= '
<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars(implode(',', $formFields)).'">
</div>';

            // Submit buttons
            $arrButtons = array();
            $arrButtons['save'] = '<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'">';
            $arrButtons['saveNclose'] = '<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'">';

            // Call the buttons_callback (see #4691)
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback']))
            {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
                {
                    if (is_array($callback))
                    {
                        $this->import($callback[0]);
                        $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
                    }
                    elseif (is_callable($callback))
                    {
                        $arrButtons = $callback($arrButtons, $this);
                    }
                }
            }

            // Add the form
            $return = '

<form action="'.ampersand(\Environment::get('request'), true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return.'

</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
  ' . implode(' ', $arrButtons) . '
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
            if (\Input::post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
            {
                if (\Input::post('saveNclose'))
                {
                    \System::setCookie('BE_PAGE_OFFSET', 0, 0);
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
                if ($field == 'pid' || $field == 'sorting' || (!$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] && (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType']) || is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback']) || is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback']))))
                {
                    $options .= '
  <input type="checkbox" name="all_fields[]" id="all_'.$field.'" class="tl_checkbox" value="'.specialchars($field).'"> <label for="all_'.$field.'" class="tl_checkbox_label">'.($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] ?: $GLOBALS['TL_LANG']['MSC'][$field][0]).'</label><br>';
                }
            }

            $blnIsError = ($_POST && empty($_POST['all_fields']));

            // Return the select menu
            $return .= '

<form action="'.ampersand(\Environment::get('request'), true).'&amp;fields=1" id="'.$this->strTable.'_all" class="tl_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.$this->strTable.'_all">
<input type="hidden" name="REQUEST_TOKEN" value="'.REQUEST_TOKEN.'">'.($blnIsError ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').'

<div class="tl_tbox">
<fieldset class="tl_checkbox_container">
  <legend'.($blnIsError ? ' class="error"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['all_fields'][0].'</legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6"><em>'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</em></label><br>'.$options.'
</fieldset>'.($blnIsError ? '
<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['all_fields'].'</p>' : ((\Config::get('showHelp') && strlen($GLOBALS['TL_LANG']['MSC']['all_fields'][1])) ? '
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
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>'.$return;
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

        $query = "SELECT * FROM {$this->strTable}";

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
                    } elseif (is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'])) {
                        $keys = call_user_func($GLOBALS['TL_DCA'][$this->strTable]['fields'][$key]['options_callback'], $this);
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
        $arrClipboard = $this->getSession()->get('CLIPBOARD');

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

<div class="tl_listing_container iso_listing_container list_view">' . (isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb']) ? $GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb'] : '') . ((\Input::get('act') == 'select') ? '

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
                    $label = trim(\StringUtil::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
                }

                // Remove empty brackets (), [], {}, <> and empty tags from the label
                $label = preg_replace('/\( *\) ?|\[ *\] ?|\{ *\} ?|< *> ?/', '', $label);
                $label = preg_replace('/<[^>]+>\s*<\/[^>]+>/', '', $label);

                $return .= '
  <tr class="' . ((++$eoCount % 2 == 0) ? 'even' : 'odd') . ' click2edit" onmouseover="Theme.hoverRow(this,1)" onmouseout="Theme.hoverRow(this,0)" onclick="Theme.toggleSelect(this)">
    ';

                $colspan = 1;

                // Call the label callback ($row, $label, $this)
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']) || is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

                        $this->import($strClass);
                        $args = $this->$strClass->$strMethod($row, $label, $this, $args);
                    } else {
                        $args = call_user_func($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'], $row, $label, $this, $args);
                    }

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
            if (\Input::get('act') == 'select')
            {
                // Submit buttons
                $arrButtons = array();

                if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
                {
                    $arrButtons['delete'] = '<input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\')" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'">';
                }

                if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
                {
                    $arrButtons['cut'] = '<input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['moveSelected']).'">';
                }

                if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'])
                {
                    $arrButtons['copy'] = '<input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['copySelected']).'">';
                }

                if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
                {
                    $arrButtons['override'] = '<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']).'">';
                    $arrButtons['edit'] = '<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'">';
                }

                // Call the buttons_callback (see #4691)
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback']))
                {
                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
                    {
                        if (is_array($callback))
                        {
                            $this->import($callback[0]);
                            $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
                        }
                        elseif (is_callable($callback))
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
     * Show header of the parent table and list all records of the current table
     * @return string
     */
    protected function parentView()
    {
        $blnClipboard = false;
        $arrClipboard = $this->getSession()->get('CLIPBOARD');
        $blnHasSorting = false;
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
        $strBackUrl = \Input::get('id') ? 'contao/main.php?do=iso_products' : \System::getReferer(true, $this->ptable);

        $return = '
<div id="tl_buttons">' . (\Input::get('nb') ? '&nbsp;' : '
<a href="'.$strBackUrl.'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>') . ' ' . (!$blnClipboard ? ((\Input::get('act') != 'select') ? ((!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) ? '
<a href="'.$this->addToUrl(($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;pid='.$this->intId)).'" class="header_new" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['new'][1]).'" accesskey="n" onclick="Backend.getScrollOffset()">'.$GLOBALS['TL_LANG'][$this->strTable]['new'][0].'</a> ' : '') . $this->generateGlobalButtons() : '') : '<a href="'.$this->addToUrl('clipboard=1').'" class="header_clipboard" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']).'" accesskey="x">'.$GLOBALS['TL_LANG']['MSC']['clearClipboard'].'</a> ') . '
</div>' . \Message::generate(true);

        // Get all details of the parent record
        $objParent = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE id=?")
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
            $imageEditHeader = \Image::getHtml('edit.gif', $GLOBALS['TL_LANG'][$this->strTable]['edit'][0]);
            $strEditHeader = $GLOBALS['TL_LANG'][$this->strTable]['edit'][0];

            $return .= '
<div class="tl_content_right">'.((\Input::get('act') == 'select') ? '
<label for="tl_select_trigger" class="tl_select_label">'.$GLOBALS['TL_LANG']['MSC']['selectAll'].'</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">' : (!$GLOBALS['TL_DCA'][$this->ptable]['config']['notEditable'] ? '
<a href="'.preg_replace('/&(amp;)?table=[^& ]*/i', (($this->ptable != '') ? '&amp;table='.$this->ptable : ''), $this->addToUrl('act=edit')).'" class="edit" title="'.specialchars($strEditHeader).'">'.$imageEditHeader.'</a>' : '') . (($blnHasSorting && !$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] && !$GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable']) ? ' <a href="'.$this->addToUrl('act=create&amp;mode=2&amp;pid='.$objParent->id.'&amp;id='.$this->intId).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pastenew'][0]).'">'.$imagePasteNew.'</a>' : '') . ($blnClipboard ? ' <a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$objParent->id . (!$blnMultiboard ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][0]).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a>' : '')) . '
</div>';

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
                    $objMaxTstamp = $this->Database->prepare("SELECT MAX(tstamp) AS tstamp FROM {$this->strTable} WHERE pid=?")
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
            } elseif (is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['header_callback'])) {
                $add = call_user_func($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['header_callback'], $add, $this);
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
            $query = "SELECT * FROM {$this->strTable}";

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
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                $blnFound = false;

                // Extract the real key and compare it to $firstOrderBy
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f) {
                    if (strpos($f, ':') !== false) {
                        list($f,) = explode(':', $f, 2);
                    }

                    if ($firstOrderBy == $f) {
                        $blnFound = true;
                        break;
                    }
                }

                if (!$blnFound) {
                    $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][] = $firstOrderBy;
                }
            }

            // Generate the table header if the "show columns" option is active
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns']) {
                $return .= '
  <tr>';

                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] as $f) {
                    if (strpos($f, ':') !== false)  {
                        list($f,) = explode(':', $f, 2);
                    }

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
                    $label = trim(\StringUtil::substrHtml($label, $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'])) . ' …';
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
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback']) || is_callable($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'])) {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'][1];

                        $this->import($strClass);
                        $args = $this->$strClass->$strMethod($row, $label, $this, $args);
                    } else {
                        $args = call_user_func($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['label_callback'], $row, $label, $this, $args);
                    }

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
        if (\Input::get('act') == 'select')
        {
            // Submit buttons
            $arrButtons = array();

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notDeletable'])
            {
                $arrButtons['delete'] = '<input type="submit" name="delete" id="delete" class="tl_submit" accesskey="d" onclick="return confirm(\''.$GLOBALS['TL_LANG']['MSC']['delAllConfirm'].'\')" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteSelected']).'">';
            }

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'])
            {
                $arrButtons['cut'] = '<input type="submit" name="cut" id="cut" class="tl_submit" accesskey="x" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['moveSelected']).'">';
            }

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'])
            {
                $arrButtons['copy'] = '<input type="submit" name="copy" id="copy" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['copySelected']).'">';
            }

            if (!$GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
            {
                $arrButtons['override'] = '<input type="submit" name="override" id="override" class="tl_submit" accesskey="v" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['overrideSelected']).'">';
                $arrButtons['edit'] = '<input type="submit" name="edit" id="edit" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editSelected']).'">';
            }

            // Call the buttons_callback (see #4691)
            if (is_array($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback']))
            {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['select']['buttons_callback'] as $callback)
                {
                    if (is_array($callback))
                    {
                        $this->import($callback[0]);
                        $arrButtons = $this->{$callback[0]}->{$callback[1]}($arrButtons, $this);
                    }
                    elseif (is_callable($callback))
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
     * Return a select menu that allows to sort results by a particular field
     * @return string
     */
    protected function sortMenu()
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] != 2 && $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] != 4)
        {
            return '';
        }

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
        if (empty($sortingFields))
        {
            return '';
        }

        $this->bid = 'tl_buttons_a';
        $session = \Session::getInstance()->getData();
        $sessionKey = \Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;
        $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

        // Add PID to order fields
        if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 3 && $this->Database->fieldExists('pid', $this->strTable))
        {
            array_unshift($orderBy, 'pid');
        }

        // Set sorting from user input
        if (\Input::post('FORM_SUBMIT') == 'tl_filters')
        {
            $strSort = \Input::post('tl_sort');

            // Validate the user input (thanks to aulmn) (see #4971)
            if (in_array($strSort, $sortingFields))
            {
                $session['sorting'][$sessionKey] = in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strSort]['flag'], array(2, 4, 6, 8, 10, 12)) ? "$strSort DESC" : $strSort;
                \Session::getInstance()->setData($session);
            }
        }

        // Overwrite the "orderBy" value with the session value
        elseif (strlen($session['sorting'][$sessionKey]))
        {
            $overwrite = preg_quote(preg_replace('/\s+.*$/', '', $session['sorting'][$sessionKey]), '/');
            $orderBy = array_diff($orderBy, preg_grep('/^'.$overwrite.'/i', $orderBy));

            array_unshift($orderBy, $session['sorting'][$sessionKey]);

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

            $options_sorter[$options_label] = '  <option value="'.specialchars($field).'"'.((!strlen($session['sorting'][$sessionKey]) && $field == $firstOrderBy || $field == str_replace(' DESC', '', $session['sorting'][$sessionKey])) ? ' selected="selected"' : '').'>'.$options_label.'</option>';
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
     * Override search menu to use a different key in the session for variant options.
     *
     * @return string
     */
    protected function searchMenu()
    {
        $searchFields = array();
        $session = \Session::getInstance()->getData();
        $sessionKey = \Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;

        // Get search fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
        {
            if ($v['search'])
            {
                $searchFields[] = $k;
            }
        }

        // Return if there are no search fields
        if (empty($searchFields))
        {
            return '';
        }

        // Store search value in the current session
        if (\Input::post('FORM_SUBMIT') == 'tl_filters')
        {
            $session['search'][$sessionKey]['value'] = '';
            $session['search'][$sessionKey]['field'] = \Input::post('tl_field', true);

            // Make sure the regular expression is valid
            if (\Input::postRaw('tl_value') != '')
            {
                try
                {
                    $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE " . \Input::post('tl_field', true) . " REGEXP ?")
                                   ->limit(1)
                                   ->execute(\Input::postRaw('tl_value'));

                    $session['search'][$sessionKey]['value'] = \Input::postRaw('tl_value');
                }
                catch (\Exception $e) {}
            }

            \Session::getInstance()->setData($session);
        }

        // Set the search value from the session
        elseif ($session['search'][$sessionKey]['value'] != '')
        {
            if (substr(\Config::get('dbCollation'), -3) == '_ci')
            {
                $this->procedure[] = "LOWER(CAST(".$session['search'][$sessionKey]['field']." AS CHAR)) REGEXP LOWER(?)";
            }
            else
            {
                $this->procedure[] = "CAST(".$session['search'][$sessionKey]['field']." AS CHAR) REGEXP ?";
            }

            $this->values[] = $session['search'][$sessionKey]['value'];
        }

        $options_sorter = array();

        foreach ($searchFields as $field)
        {
            $option_label = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] ?: $GLOBALS['TL_LANG']['MSC'][$field];
            $options_sorter[utf8_romanize($option_label).'_'.$field] = '  <option value="'.specialchars($field).'"'.(($field == $session['search'][$sessionKey]['field']) ? ' selected="selected"' : '').'>'.$option_label.'</option>';
        }

        // Sort by option values
        $options_sorter = natcaseksort($options_sorter);
        $active = ($session['search'][$sessionKey]['value'] != '') ? true : false;

        return '

<div class="tl_search tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['search'] . ':</strong>
<select name="tl_field" class="tl_select' . ($active ? ' active' : '') . '">
'.implode("\n", $options_sorter).'
</select>
<span> = </span>
<input type="search" name="tl_value" class="tl_text' . ($active ? ' active' : '') . '" value="'.specialchars($session['search'][$sessionKey]['value']).'">
</div>';
    }


    /**
     * Return a select menu to limit results
     * @param boolean
     * @return string
     */
    protected function limitMenu($blnOptional = false)
    {
        $session = \Session::getInstance()->getData();
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

            \Session::getInstance()->setData($session);

            if (\Input::post('FORM_SUBMIT') == 'tl_filters_limit') {
                \Controller::reload();
            }
        } // Set limit from table configuration
        else {
            $this->limit = ($session['filter'][$filter]['limit'] != '') ? (($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']) : '0,' . $GLOBALS['TL_CONFIG']['resultsPerPage'];
            $query = "SELECT COUNT(*) AS count FROM {$this->strTable}";

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
     * Override the parent method to override the session key.
     *
     * @param integer
     * @return string
     */
    protected function filterMenu($intFilterPanel)
    {

        $fields = '';
        $this->bid = 'tl_buttons_a';
        $sortingFields = array();
        $session = \Session::getInstance()->getData();
        $filter = \Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;

        // Get the sorting fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
        {
            if (intval($v['filter']) == $intFilterPanel)
            {
                $sortingFields[] = $k;
            }
        }

        // Return if there are no sorting fields
        if (empty($sortingFields))
        {
            return '';
        }

        // Set filter from user input
        if (\Input::post('FORM_SUBMIT') == 'tl_filters')
        {
            foreach ($sortingFields as $field)
            {
                if (\Input::post($field, true) != 'tl_'.$field)
                {
                    $session['filter'][$filter][$field] = \Input::post($field, true);
                }
                else
                {
                    unset($session['filter'][$filter][$field]);
                }
            }

            \Session::getInstance()->setData($session);
        }

        // Set filter from table configuration
        else
        {
            foreach ($sortingFields as $field)
            {
                if (isset($session['filter'][$filter][$field]))
                {
                    // Sort by day
                    if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6)))
                    {
                        if ($session['filter'][$filter][$field] == '')
                        {
                            $this->procedure[] = $field . "=''";
                        }
                        else
                        {
                            $objDate = new \Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $field . ' BETWEEN ? AND ?';
                            $this->values[] = $objDate->dayBegin;
                            $this->values[] = $objDate->dayEnd;
                        }
                    }

                    // Sort by month
                    elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(7, 8)))
                    {
                        if ($session['filter'][$filter][$field] == '')
                        {
                            $this->procedure[] = $field . "=''";
                        }
                        else
                        {
                            $objDate = new \Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $field . ' BETWEEN ? AND ?';
                            $this->values[] = $objDate->monthBegin;
                            $this->values[] = $objDate->monthEnd;
                        }
                    }

                    // Sort by year
                    elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(9, 10)))
                    {
                        if ($session['filter'][$filter][$field] == '')
                        {
                            $this->procedure[] = $field . "=''";
                        }
                        else
                        {
                            $objDate = new \Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $field . ' BETWEEN ? AND ?';
                            $this->values[] = $objDate->yearBegin;
                            $this->values[] = $objDate->yearEnd;
                        }
                    }

                    // Manual filter
                    elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'])
                    {
                        // CSV lists (see #2890)
                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv']))
                        {
                            $this->procedure[] = $this->Database->findInSet('?', $field, true);
                            $this->values[] = $session['filter'][$filter][$field];
                        }
                        else
                        {
                            $this->procedure[] = $field . ' LIKE ?';
                            $this->values[] = '%"' . $session['filter'][$filter][$field] . '"%';
                        }
                    }

                    // Other sort algorithm
                    else
                    {
                        $this->procedure[] = $field . '=?';
                        $this->values[] = $session['filter'][$filter][$field];
                    }
                }
            }
        }

        // Add sorting options
        foreach ($sortingFields as $cnt=>$field)
        {
            $arrValues = array();
            $arrProcedure = array();

            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4)
            {
                $arrProcedure[] = 'pid=?';
                $arrValues[] = CURRENT_ID;
            }

            if (!empty($this->root) && is_array($this->root))
            {
                $arrProcedure[] = "id IN(" . implode(',', array_map('intval', $this->root)) . ")";
            }

            // Check for a static filter (see #4719)
            if (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']) && is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter']))
            {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $fltr)
                {
                    $arrProcedure[] = $fltr[0];
                    $arrValues[] = $fltr[1];
                }
            }

            // Support empty ptable fields (backwards compatibility)
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'])
            {
                $arrProcedure[] = ($this->ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";
                $arrValues[] = $this->ptable;
            }

            $objFields = $this->Database->prepare("SELECT DISTINCT " . $field . " FROM " . $this->strTable . ((is_array($arrProcedure) && strlen($arrProcedure[0])) ? ' WHERE ' . implode(' AND ', $arrProcedure) : ''))
                                        ->execute($arrValues);

            // Begin select menu
            $fields .= '
<select name="'.$field.'" id="'.$field.'" class="tl_select' . (isset($session['filter'][$filter][$field]) ? ' active' : '') . '">
  <option value="tl_'.$field.'">'.(is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']).'</option>
  <option value="tl_'.$field.'">---</option>';

            if ($objFields->numRows)
            {
                $options = $objFields->fetchEach($field);

                // Sort by day
                if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6)))
                {
                    ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] == 6) ? rsort($options) : sort($options);

                    foreach ($options as $k=>$v)
                    {
                        if ($v == '')
                        {
                            $options[$v] = '-';
                        }
                        else
                        {
                            $options[$v] = \Date::parse(\Config::get('dateFormat'), $v);
                        }

                        unset($options[$k]);
                    }
                }

                // Sort by month
                elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(7, 8)))
                {
                    ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] == 8) ? rsort($options) : sort($options);

                    foreach ($options as $k=>$v)
                    {
                        if ($v == '')
                        {
                            $options[$v] = '-';
                        }
                        else
                        {
                            $options[$v] = date('Y-m', $v);
                            $intMonth = (date('m', $v) - 1);

                            if (isset($GLOBALS['TL_LANG']['MONTHS'][$intMonth]))
                            {
                                $options[$v] = $GLOBALS['TL_LANG']['MONTHS'][$intMonth] . ' ' . date('Y', $v);
                            }
                        }

                        unset($options[$k]);
                    }
                }

                // Sort by year
                elseif (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(9, 10)))
                {
                    ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] == 10) ? rsort($options) : sort($options);

                    foreach ($options as $k=>$v)
                    {
                        if ($v == '')
                        {
                            $options[$v] = '-';
                        }
                        else
                        {
                            $options[$v] = date('Y', $v);
                        }

                        unset($options[$k]);
                    }
                }

                // Manual filter
                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'])
                {
                    $moptions = array();

                    // TODO: find a more effective solution
                    foreach($options as $option)
                    {
                        // CSV lists (see #2890)
                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv']))
                        {
                            $doptions = trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv'], $option);
                        }
                        else
                        {
                            $doptions = deserialize($option);
                        }

                        if (is_array($doptions))
                        {
                            $moptions = array_merge($moptions, $doptions);
                        }
                    }

                    $options = $moptions;
                }

                $options = array_unique($options);
                $options_callback = array();

                // Call the options_callback
                if ((is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']) || is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'])) && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'])
                {
                    if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']))
                    {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][1];

                        $this->import($strClass);
                        $options_callback = $this->$strClass->$strMethod($this);
                    }
                    elseif (is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']))
                    {
                        $options_callback = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']($this);
                    }

                    // Sort options according to the keys of the callback array
                    $options = array_intersect(array_keys($options_callback), $options);
                }

                $options_sorter = array();
                $blnDate = in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6, 7, 8, 9, 10));

                // Options
                foreach ($options as $kk=>$vv)
                {
                    $value = $blnDate ? $kk : $vv;

                    // Options callback
                    if (!empty($options_callback) && is_array($options_callback))
                    {
                        $vv = $options_callback[$vv];
                    }

                    // Replace the ID with the foreign key
                    elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
                    {
                        $key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'], 2);

                        $objParent = $this->Database->prepare("SELECT " . $key[1] . " AS value FROM " . $key[0] . " WHERE id=?")
                                                    ->limit(1)
                                                    ->execute($vv);

                        if ($objParent->numRows)
                        {
                            $vv = $objParent->value;
                        }
                    }

                    // Replace boolean checkbox value with "yes" and "no"
                    elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isBoolean'] || ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple']))
                    {
                        $vv = ($vv != '') ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                    }

                    // Get the name of the parent record (see #2703)
                    elseif ($field == 'pid')
                    {
                        $this->loadDataContainer($this->ptable);
                        $showFields = $GLOBALS['TL_DCA'][$this->ptable]['list']['label']['fields'];

                        if (!$showFields[0])
                        {
                            $showFields[0] = 'id';
                        }

                        $objShowFields = $this->Database->prepare("SELECT " . $showFields[0] . " FROM ". $this->ptable . " WHERE id=?")
                                                        ->limit(1)
                                                        ->execute($vv);

                        if ($objShowFields->numRows)
                        {
                            $vv = $objShowFields->$showFields[0];
                        }
                    }

                    $option_label = '';

                    // Use reference array
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference']))
                    {
                        $option_label = is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv]) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv];
                    }

                    // Associative array
                    elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isAssociative'] || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options']))
                    {
                        $option_label = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'][$vv];
                    }

                    // No empty options allowed
                    if (!strlen($option_label))
                    {
                        $option_label = $vv ?: '-';
                    }

                    $options_sorter['  <option value="' . specialchars($value) . '"' . ((isset($session['filter'][$filter][$field]) && $value == $session['filter'][$filter][$field]) ? ' selected="selected"' : '').'>'.$option_label.'</option>'] = utf8_romanize($option_label);
                }

                // Sort by option values
                if (!$blnDate)
                {
                    natcasesort($options_sorter);

                    if (in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(2, 4, 12)))
                    {
                        $options_sorter = array_reverse($options_sorter, true);
                    }
                }

                $fields .= "\n" . implode("\n", array_keys($options_sorter));
            }

            // End select menu
            $fields .= '
</select> ';

            // Force a line-break after six elements (see #3777)
            if ((($cnt + 1) % 6) == 0)
            {
                $fields .= '<br>';
            }
        }

        return '

<div class="tl_filter tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['filter'] . ':</strong> ' . $fields . '
</div>';
    }


    /**
     * Copy multilingual fields from fallback to current language
     */
    public function copyFallback()
    {
        $session = $this->getSessionData();
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
            \Database::getInstance()->prepare("UPDATE {$this->strTable} %s WHERE id=$intLanguageId")->set($arrRow)->execute();

            $this->createNewVersion($this->strTable, $intLanguageId);
            \System::log(sprintf('A new version of record ID %s (table %s) has been created', $intLanguageId, $this->strTable), 'DC_ProductData copyFallback()', TL_GENERAL);
        }

        \Controller::redirect(\Backend::addToUrl('act=edit'));
    }

    /**
     * Gets session instance depending on Contao 3 or Contao 4.
     *
     * @return Session|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private function getSession()
    {
        if (method_exists('Contao\System', 'getContainer')) {
            return \System::getContainer()->get('session');
        }

        return \Session::getInstance();
    }

    /**
     * Gets session data depending on Contao 3 or Contao 4.
     *
     * @return array
     */
    private function getSessionData()
    {
        if (method_exists('Contao\System', 'getContainer')) {
            return $this->getSession()->all();
        }

        return $this->getSession()->getData();
    }

    /**
     * Sets session data depending on Contao 3 or Contao 4.
     *
     * @param array $data
     */
    private function setSessionData(array $data)
    {
        if (method_exists('Contao\System', 'getContainer')) {
            return $this->getSession()->replace($data);
        }

        return $this->getSession()->setData($data);
    }
}
