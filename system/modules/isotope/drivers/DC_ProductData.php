<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\InternalServerErrorException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\StringUtil;
use Contao\Versions;
use Doctrine\DBAL\Exception\DriverException;
use Patchwork\Utf8;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Isotope\Model\Group;

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
        $this->intGroupId = (int) \Contao\Session::getInstance()->get('iso_products_gid');

        // Check if the group exists
        if ($this->intGroupId > 0) {
            $objGroup = Group::findByPk($this->intGroupId);

            if (null === $objGroup) {
                if (BackendUser::getInstance()->isAdmin || !\is_array(BackendUser::getInstance()->iso_groups)) {
                    $this->intGroupId = 0;
                }
                elseif (!BackendUser::getInstance()->isAdmin) {
                    $this->intGroupId = (int) Database::getInstance()->prepare(
                        "SELECT id FROM tl_iso_group WHERE id IN ('" . implode("','", BackendUser::getInstance()->iso_groups) . "') ORDER BY " . Database::getInstance()->findInSet('id', BackendUser::getInstance()->iso_groups)
                    )->limit(1)->execute()->id;
                }
            }
        }

        // Move multiple products to group
        /** @var Session $objSession */
        $objSession = System::getContainer()->get('session');
        $arrClipboard = $objSession->get('CLIPBOARD');
        if (($arrClipboard[$strTable]['mode'] ?? null) === 'cutAll' && Input::get('act') !== 'cutAll') {
            $firstPid = (int) Database::getInstance()
                ->prepare("SELECT pid FROM tl_iso_product WHERE id=?")
                ->execute($arrClipboard[$strTable]['id'][0])
                ->fetchRow()[0]
            ;
            if (0 === $firstPid) {
                \Contao\Controller::redirect(\Contao\Backend::addToUrl('&act=cutAll&pid=0'));
            }
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

        if (\count($arrPageLanguages) > 1) {
            $this->arrTranslationLabels = \Contao\System::getLanguages();
            $this->arrTranslations      = array_intersect(array_keys($this->arrTranslationLabels), $arrPageLanguages);
        }
    }

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

            // Perform a redirect (this is the CURRENT_ID fix)
            \Contao\Controller::redirect('contao/main.php?do=' . Input::get('do') . (Input::get('pid') ? '&id=' . Input::get('pid') : '') . '&rt=' . Input::get('rt') . '&ref=' . Input::get('ref'));
        }

        // Do not show the language records
        $this->procedure[] = "language=''";

        // Display products filtered by group
        if (!$this->intId) {
            if ($this->intGroupId > 0) {
                $this->procedure[] = "gid IN(".implode(',', array_map('intval', \Contao\Database::getInstance()->getChildRecords([$this->intGroupId], Group::getTable(), false, [$this->intGroupId]))).")";
            } elseif (!BackendUser::getInstance()->isAdmin && !empty(BackendUser::getInstance()->iso_groups)) {
                $this->procedure[] = 'gid IN('.implode(',', array_map('intval', \Contao\Database::getInstance()->getChildRecords(BackendUser::getInstance()->iso_groups, Group::getTable(), false, BackendUser::getInstance()->iso_groups))).')';
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

        $return .= $this->panel();
        $return .= (CURRENT_ID && (Input::get('pid') === null || (Input::get('pid') != '' && (int) Input::get('pid') != 0))) ? $this->parentView() : $this->listView();

        // Store the current IDs
        $session = $objSession->all();
        $session['CURRENT']['IDS'] = $this->current;
        $objSession->replace($session);

        return $return;
    }

    public function cut($blnDoNotRedirect = false)
    {
        // Save the group ID when moving products to new group
        $this->set['gid'] = $this->intGroupId;

        parent::cut($blnDoNotRedirect);
    }

    /**
     * Duplicate all child records of a duplicated record
     *
     * @param string  $table
     * @param integer $insertID
     * @param integer $id
     * @param integer $parentId
     */
    protected function copyChilds($table, $insertID, $id, $parentId)
    {
        $time = time();
        $copy = array();
        $cctable = array();
        $ctable = $GLOBALS['TL_DCA'][$table]['config']['ctable'] ?? array();

        /** PATCH: removed check for sorting field */
        if (!($GLOBALS['TL_DCA'][$table]['config']['ptable'] ?? null) && Input::get('childs') && $this->Database->fieldExists('pid', $table))
        {
            $ctable[] = $table;
        }

        if (empty($ctable) || !\is_array($ctable))
        {
            return;
        }

        // Walk through each child table
        foreach ($ctable as $v)
        {
            $this->loadDataContainer($v);
            $cctable[$v] = $GLOBALS['TL_DCA'][$v]['config']['ctable'] ?? null;

            if (!($GLOBALS['TL_DCA'][$v]['config']['doNotCopyRecords'] ?? null) && \strlen($v))
            {
                // Consider the dynamic parent table (see #4867)
                if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'] ?? null)
                {
                    $cond = ($table === 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";

                    $objCTable = $this->Database->prepare("SELECT * FROM $v WHERE pid=? AND $cond" . ($this->Database->fieldExists('sorting', $v) ? " ORDER BY sorting" : ""))
                                                ->execute($id, $table);
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

                        // Never copy passwords
                        if (($GLOBALS['TL_DCA'][$v]['fields'][$kk]['inputType'] ?? null) == 'password')
                        {
                            $vv = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$v]['fields'][$kk]['sql'] ?? array());
                        }

                        // Empty unique fields or add a unique identifier in copyAll mode
                        elseif ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['unique'] ?? null)
                        {
                            $vv = (Input::get('act') == 'copyAll') ? $vv . '-' . substr(md5(uniqid(mt_rand(), true)), 0, 8) : Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$v]['fields'][$kk]['sql'] ?? array());
                        }

                        // Reset doNotCopy and fallback fields to their default value
                        elseif (($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['doNotCopy'] ?? null) || ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['fallback'] ?? null))
                        {
                            $vv = Widget::getEmptyValueByFieldType($GLOBALS['TL_DCA'][$v]['fields'][$kk]['sql'] ?? array());

                            // Use array_key_exists to allow NULL (see #5252)
                            if (\array_key_exists('default', $GLOBALS['TL_DCA'][$v]['fields'][$kk] ?? array()))
                            {
                                $vv = \is_array($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) ? serialize($GLOBALS['TL_DCA'][$v]['fields'][$kk]['default']) : $GLOBALS['TL_DCA'][$v]['fields'][$kk]['default'];
                            }

                            // Encrypt the default value (see #3740)
                            if ($GLOBALS['TL_DCA'][$v]['fields'][$kk]['eval']['encrypt'] ?? null)
                            {
                                $vv = Encryption::encrypt($vv);
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
                    $objInsertStmt = $this->Database->prepare("INSERT INTO " . $k . " %s")
                                                    ->set($vv)
                                                    ->execute();

                    if ($objInsertStmt->affectedRows)
                    {
                        $insertID = $objInsertStmt->insertId;

                        if ($kk != $parentId && (!empty($cctable[$k]) || ($GLOBALS['TL_DCA'][$k]['list']['sorting']['mode'] ?? null) == 5))
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
     *
     * @throws InternalServerErrorException
     */
    public function copyAll()
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'] ?? null)
        {
            throw new InternalServerErrorException('Table "' . $this->strTable . '" is not copyable.');
        }

        /** @var Session $objSession */
        $objSession = System::getContainer()->get('session');

        $arrClipboard = $objSession->get('CLIPBOARD');

        if (isset($arrClipboard[$this->strTable]) && \is_array($arrClipboard[$this->strTable]['id']))
        {
            $arrIds = array();

            foreach ($arrClipboard[$this->strTable]['id'] as $id)
            {
                $this->intId = $id;
                $id = $this->copy(true);
                Input::setGet('pid', $id);
                Input::setGet('mode', 1);
                $arrIds[] = $id;
            }

            $this->Database->query("UPDATE {$this->strTable} SET gid=" . $this->intGroupId . " WHERE id IN (" . implode(',', $arrIds) . ")");
        }

        $this->redirect($this->getReferer());
    }

    /**
     * Auto-generate a form to edit the current database record
     *
     * @param integer $intId
     * @param integer $ajaxId
     *
     * @return string
     *
     * @throws AccessDeniedException
     * @throws InternalServerErrorException
     */
    public function edit($intId=null, $ajaxId=null)
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null)
        {
            throw new InternalServerErrorException('Table "' . $this->strTable . '" is not editable.');
        }

        if ($intId)
        {
            $this->intId = $intId;
        }

        // Get the current record
        $objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
            ->limit(1)
            ->execute($this->intId);

        // Redirect if there is no record with the given ID
        if ($objRow->numRows < 1)
        {
            throw new AccessDeniedException('Cannot load record "' . $this->strTable . '.id=' . $this->intId . '".');
        }

        // ID of a language record is not allowed
        if ($objRow->language != '')
        {
            throw new AccessDeniedException('Cannot edit language record "'.$this->strTable.'.id='.$this->intId.'"');
        }

        $this->objActiveRecord = $objRow;

        $return = '';
        $this->values[] = $this->intId;
        $this->procedure[] = 'id=?';

        $this->blnCreateNewVersion = false;
        $objVersions = new Versions($this->strTable, $this->intId);

        if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'] ?? null))
        {
            // Compare versions
            if (Input::get('versions'))
            {
                $objVersions->compare();
            }

            // Restore a version
            if (Input::post('FORM_SUBMIT') == 'tl_version' && Input::post('version'))
            {
                $objVersions->restore(Input::post('version'));

                $this->invalidateCacheTags();

                $this->reload();
            }
        }

        $objVersions->initialize();

        // Load and/or change language
        $this->blnEditLanguage = false;

        if (!empty($this->arrTranslations))
        {
            $blnLanguageUpdated = false;
            $session = \Contao\Session::getInstance()->getData();

            if (Input::post('FORM_SUBMIT') === 'tl_language')
            {
                if (\in_array(Input::post('language'), $this->arrTranslations))
                {
                    $session['language'][$this->strTable][$this->intId] = Input::post('language');
                }
                else
                {
                    unset($session['language'][$this->strTable][$this->intId]);
                }

                $blnLanguageUpdated = true;
            }
            elseif (Input::post('FORM_SUBMIT') == $this->strTable && isset($_POST['deleteLanguage']))
            {
                $this->Database->prepare("DELETE FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $session['language'][$this->strTable][$this->intId]);
                unset($session['language'][$this->strTable][$this->intId]);
                $blnLanguageUpdated = true;
            }

            if ($blnLanguageUpdated)
            {
                \Contao\Session::getInstance()->setData($session);
                unset($_SESSION['TL_INFO']);
                \Contao\Controller::reload();
            }

            if (($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] ?? null) != '' && \in_array($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId], $this->arrTranslations))
            {
                $objRow = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);

                if (!$objRow->numRows)
                {
                    $intLanguage = $this->Database->prepare("INSERT INTO {$this->strTable} (pid,tstamp,language) VALUES (?,?,?)")->execute($this->intId, time(), $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId])->insertId;

                    $objRow = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE id=?")->execute($intLanguage);
                }

                $this->objActiveRecord = $objRow;
                $this->values = array($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);
                $this->procedure = array('pid=?', 'language=?');
                $this->blnEditLanguage = true;
            }
        }

        // Build an array from boxes and rows
        $this->strPalette = $this->getPalette();
        $boxes = StringUtil::trimsplit(';', $this->strPalette);
        $legends = array();

        if (!empty($boxes))
        {
            foreach ($boxes as $k=>$v)
            {
                $eCount = 1;
                $boxes[$k] = StringUtil::trimsplit(',', $v);

                foreach ($boxes[$k] as $kk=>$vv)
                {
                    if (preg_match('/^\[.*]$/', $vv))
                    {
                        ++$eCount;
                        continue;
                    }

                    if (preg_match('/^{.*}$/', $vv))
                    {
                        $legends[$k] = substr($vv, 1, -1);
                        unset($boxes[$k][$kk]);
                    }
                    elseif (!\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv] ?? null) || ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] ?? null))
                    {
                        unset($boxes[$k][$kk]);
                    }
                    elseif ($this->blnEditLanguage && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['attributes']['multilingual'] ?? null))
                    {
                        unset($boxes[$k][$kk]);
                    }
                }

                // Unset a box if it does not contain any fields
                if (\count($boxes[$k]) < $eCount)
                {
                    unset($boxes[$k]);
                }
            }

            /** @var Session $objSessionBag */
            $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

            $class = 'tl_tbox';
            $fs = $objSessionBag->get('fieldset_states');

            // Render boxes
            foreach ($boxes as $k=>$v)
            {
                $arrAjax = array();
                $blnAjax = false;
                $key = '';
                $cls = '';
                $legend = '';

                if (isset($legends[$k]))
                {
                    list($key, $cls) = explode(':', $legends[$k]) + array(null, null);

                    $legend = "\n" . '<legend onclick="AjaxRequest.toggleFieldset(this,\'' . $key . '\',\'' . $this->strTable . '\')">' . ($GLOBALS['TL_LANG'][$this->strTable][$key] ?? $key) . '</legend>';
                }

                if (isset($fs[$this->strTable][$key]))
                {
                    $class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
                }
                else
                {
                    $class .= (($cls && $legend) ? ' ' . $cls : '');
                }

                $return .= "\n\n" . '<fieldset' . ($key ? ' id="pal_' . $key . '"' : '') . ' class="' . $class . ($legend ? '' : ' nolegend') . '">' . $legend;
                $thisId = '';

                // Build rows of the current box
                foreach ($v as $vv)
                {
                    if ($vv == '[EOF]')
                    {
                        if ($blnAjax && Environment::get('isAjaxRequest'))
                        {
                            if ($ajaxId == $thisId)
                            {
                                if (($intLatestVersion = $objVersions->getLatestVersion()) !== null)
                                {
                                    $arrAjax[$thisId] .= '<input type="hidden" name="VERSION_NUMBER" value="' . $intLatestVersion . '">';
                                }

                                return $arrAjax[$thisId] . '<input type="hidden" name="FORM_FIELDS[]" value="' . StringUtil::specialchars($this->strPalette) . '">';
                            }

                            if (\count($arrAjax) > 1)
                            {
                                $current = "\n" . '<div id="' . $thisId . '" class="subpal cf">' . $arrAjax[$thisId] . '</div>';
                                unset($arrAjax[$thisId]);
                                end($arrAjax);
                                $thisId = key($arrAjax);
                                $arrAjax[$thisId] .= $current;
                            }
                        }

                        $return .= "\n" . '</div>';

                        continue;
                    }

                    if (preg_match('/^\[.*]$/', $vv))
                    {
                        $thisId = 'sub_' . substr($vv, 1, -1);
                        $arrAjax[$thisId] = '';
                        $blnAjax = ($ajaxId == $thisId && Environment::get('isAjaxRequest')) ? true : $blnAjax;
                        $return .= "\n" . '<div id="' . $thisId . '" class="subpal cf">';

                        continue;
                    }

                    $this->strField = $vv;
                    $this->strInputName = $vv;
                    $this->varValue = $objRow->$vv;

                    // Convert CSV fields (see #2890)
                    if (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['multiple'] ?? null) && isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv']))
                    {
                        $this->varValue = StringUtil::trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['csv'], $this->varValue);
                    }

                    // Call load_callback
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] ?? null))
                    {
                        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
                        {
                            if (\is_array($callback))
                            {
                                $this->import($callback[0]);
                                $this->varValue = $this->{$callback[0]}->{$callback[1]}($this->varValue, $this);
                            }
                            elseif (\is_callable($callback))
                            {
                                $this->varValue = $callback($this->varValue, $this);
                            }
                        }
                    }

                    // Re-set the current value
                    $this->objActiveRecord->{$this->strField} = $this->varValue;

                    // Build the row and pass the current palette string (thanks to Tristan Lins)
                    $blnAjax ? $arrAjax[$thisId] .= $this->row($this->strPalette) : $return .= $this->row($this->strPalette);
                }

                $class = 'tl_box';
                $return .= "\n" . '</fieldset>';
            }
        }

        // Versions overview
        if (($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['hideVersionMenu'] ?? null))
        {
            $version = $objVersions->renderDropdown();
        }
        else
        {
            $version = '';
        }

        if ('' === $version)
        {
            $version = '<div class="tl_version_panel"></div>';
        }

        // Check languages
        if (!empty($this->arrTranslations))
        {
            $arrAvailableLanguages = $this->Database->prepare("SELECT language FROM {$this->strTable} WHERE pid=?")->execute($this->intId)->fetchEach('language');
            $available = '';
            $undefined = '';

            foreach ($this->arrTranslations as $language)
            {
                if (\in_array($language, $arrAvailableLanguages))
                {
                    if (($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] ?? null) == $language)
                    {
                        $available .= '<option value="' . $language . '" selected="selected">' . $this->arrTranslationLabels[$language] . '</option>';
                        $_SESSION['TL_INFO'] = array($GLOBALS['TL_LANG']['MSC']['editingLanguage']);
                    }
                    else
                    {
                        $available .= '<option value="' . $language . '">' . $this->arrTranslationLabels[$language] . '</option>';
                    }
                }
                else
                {
                    $undefined .= '<option value="' . $language . '">' . $this->arrTranslationLabels[$language] . ' (' . $GLOBALS['TL_LANG']['MSC']['undefinedLanguage'] . ')' . '</option>';
                }
            }

            $version = str_replace(
                '<div class="tl_version_panel">',
                '<div class="tl_version_panel tl_iso_products_panel">
<form action="' . ampersand(\Contao\Environment::get('request'), true) . '" id="tl_language" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_language">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
<select name="language" class="tl_select' . (!empty($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]) ? ' active' : '') . '" onchange="document.id(this).getParent(\'form\').submit()">
    <option value="">' . $GLOBALS['TL_LANG']['MSC']['defaultLanguage'] . '</option>' . $available . $undefined . '
</select>
<noscript>
<input type="submit" name="editLanguage" class="tl_submit" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['editLanguage']) . '">
</noscript>
</div>
</form>',
                $version
            );
        }

        // Submit buttons
        $arrButtons = array();
        $arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['save'] . '</button>';
        $deleteLanguageButton = '';

        if (!Input::get('nb'))
        {
            $arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['saveNclose'] . '</button>';

            if (!Input::get('nc'))
            {
                if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null))
                {
                    $arrButtons['saveNcreate'] = '<button type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n">' . $GLOBALS['TL_LANG']['MSC']['saveNcreate'] . '</button>';

                    if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notCopyable'] ?? null))
                    {
                        $arrButtons['saveNduplicate'] = '<button type="submit" name="saveNduplicate" id="saveNduplicate" class="tl_submit" accesskey="d">' . $GLOBALS['TL_LANG']['MSC']['saveNduplicate'] . '</button>';
                    }
                }

                if ($GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'] ?? null)
                {
                    $arrButtons['saveNedit'] = '<button type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e">' . $GLOBALS['TL_LANG']['MSC']['saveNedit'] . '</button>';
                }

                if ($this->ptable || ($GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit'] ?? null) || ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 4)
                {
                    $arrButtons['saveNback'] = '<button type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g">' . $GLOBALS['TL_LANG']['MSC']['saveNback'] . '</button>';
                }

                if ($this->blnEditLanguage)
                {
                    $deleteLanguageButton = '<button type="submit" name="deleteLanguage" class="tl_submit" style="float:right" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] . '\')">' . $GLOBALS['TL_LANG']['MSC']['deleteLanguage'] . '</button>';
                }
            }
        }

        // Call the buttons_callback (see #4691)
        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] ?? null))
        {
            foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
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

            $strButtons .= '</ul></div>'.$deleteLanguageButton;
        }

        // Add the buttons and end the form
        $return .= '
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
  ' . $strButtons . '
</div>
</div>
</form>';

        // Always create a new version if something has changed, even if the form has errors (see #237)
        if ($this->noReload && $this->blnCreateNewVersion && Input::post('FORM_SUBMIT') == $this->strTable)
        {
            $objVersions->create();
        }

        $strVersionField = '';

        // Store the current version number (see #8412)
        if (($intLatestVersion = $objVersions->getLatestVersion()) !== null)
        {
            $strVersionField = '
<input type="hidden" name="VERSION_NUMBER" value="' . $intLatestVersion . '">';
        }

        $copyFallback = $this->blnEditLanguage ? '&nbsp;&nbsp;::&nbsp;&nbsp;<a href="' . Backend::addToUrl('act=copyFallback') . '" class="header_iso_copy" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['copyFallback'] ?? '') . '" accesskey="d" onclick="Backend.getScrollOffset();">' . ($GLOBALS['TL_LANG']['MSC']['copyFallback'] ?? 'copyFallback') . '</a>' : '';

        // Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
        $return = $version . Message::generate() . ($this->noReload ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . '
<div id="tl_buttons">' . (Input::get('nb') ? '&nbsp;' : '
<a href="' . $this->getReferer(true) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>') . $copyFallback . '
</div>
<form id="' . $this->strTable . '" class="tl_form tl_edit_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"' . (!empty($this->onsubmit) ? ' onsubmit="' . implode(' ', $this->onsubmit) . '"' : '') . '>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . $strVersionField . '
<input type="hidden" name="FORM_FIELDS[]" value="' . StringUtil::specialchars($this->strPalette) . '">' . $return;

        // Reload the page to prevent _POST variables from being sent twice
        if (!$this->noReload && Input::post('FORM_SUBMIT') == $this->strTable)
        {
            $arrValues = $this->values;
            array_unshift($arrValues, time());

            // Trigger the onsubmit_callback
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] ?? null))
            {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
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

            // Set the current timestamp before adding a new version
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
            {
                $this->Database->prepare("UPDATE " . $this->strTable . " SET ptable=?, tstamp=? WHERE id=?")
                               ->execute($this->ptable, time(), $this->intId);
            }
            else
            {
                $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
                               ->execute(time(), $this->intId);
            }

            // Save the current version
            if ($this->blnCreateNewVersion)
            {
                $objVersions->create();

                // Call the onversion_callback
                if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] ?? null))
                {
                    @trigger_error('Using the "onversion_callback" has been deprecated and will no longer work in Contao 5.0. Use the "oncreate_version_callback" instead.', E_USER_DEPRECATED);

                    foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback)
                    {
                        if (\is_array($callback))
                        {
                            $this->import($callback[0]);
                            $this->{$callback[0]}->{$callback[1]}($this->strTable, $this->intId, $this);
                        }
                        elseif (\is_callable($callback))
                        {
                            $callback($this->strTable, $this->intId, $this);
                        }
                    }
                }
            }

            // Show a warning if the record has been saved by another user (see #8412)
            if ($intLatestVersion !== null && isset($_POST['VERSION_NUMBER']) && $intLatestVersion > Input::post('VERSION_NUMBER'))
            {
                $objTemplate = new BackendTemplate('be_conflict');
                $objTemplate->language = $GLOBALS['TL_LANGUAGE'];
                $objTemplate->title = StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['versionConflict']);
                $objTemplate->theme = Backend::getTheme();
                $objTemplate->charset = Config::get('characterSet');
                $objTemplate->base = Environment::get('base');
                $objTemplate->h1 = $GLOBALS['TL_LANG']['MSC']['versionConflict'];
                $objTemplate->explain1 = sprintf($GLOBALS['TL_LANG']['MSC']['versionConflict1'], $intLatestVersion, Input::post('VERSION_NUMBER'));
                $objTemplate->explain2 = sprintf($GLOBALS['TL_LANG']['MSC']['versionConflict2'], $intLatestVersion + 1, $intLatestVersion);
                $objTemplate->diff = $objVersions->compare(true);
                $objTemplate->href = Environment::get('request');
                $objTemplate->button = $GLOBALS['TL_LANG']['MSC']['continue'];

                throw new ResponseException($objTemplate->getResponse());
            }

            $this->invalidateCacheTags();

            // Redirect
            if (isset($_POST['saveNclose']))
            {
                Message::reset();

                $this->redirect($this->getReferer());
            }
            elseif (isset($_POST['saveNedit']))
            {
                Message::reset();

                $this->redirect($this->addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href'] ?? '', false, array('s2e', 'act', 'mode', 'pid')));
            }
            elseif (isset($_POST['saveNback']))
            {
                Message::reset();

                if (!$this->ptable)
                {
                    $this->redirect(TL_SCRIPT . '?do=' . Input::get('do'));
                }
                // TODO: try to abstract this
                elseif (($this->ptable == 'tl_theme' && $this->strTable == 'tl_style_sheet') || ($this->ptable == 'tl_page' && $this->strTable == 'tl_article'))
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
                Message::reset();

                $strUrl = TL_SCRIPT . '?do=' . Input::get('do');

                if (isset($_GET['table']))
                {
                    $strUrl .= '&amp;table=' . Input::get('table');
                }

                // Tree view
                if ($this->treeView)
                {
                    $strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
                }

                // Parent view
                elseif ($this->activeRecord->pid > 0)
                {
                    $strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->activeRecord->pid : '&amp;act=create&amp;mode=2&amp;pid=' . $this->activeRecord->pid;
                }

                // List view
                else
                {
                    $strUrl .= $this->ptable ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';
                }

                $this->redirect($strUrl . '&amp;rt=' . REQUEST_TOKEN);
            }
            elseif (isset($_POST['saveNduplicate']))
            {
                Message::reset();

                $strUrl = TL_SCRIPT . '?do=' . Input::get('do');

                if (isset($_GET['table']))
                {
                    $strUrl .= '&amp;table=' . Input::get('table');
                }

                // Tree view
                if ($this->treeView)
                {
                    $strUrl .= '&amp;act=copy&amp;mode=1&amp;id=' . $this->intId . '&amp;pid=' . $this->intId;
                }

                // Parent view
                elseif (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 4)
                {
                    $strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=copy&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . $this->intId : '&amp;act=copy&amp;mode=2&amp;pid=' . CURRENT_ID . '&amp;id=' . $this->intId;
                }

                // List view
                else
                {
                    $strUrl .= $this->ptable ? '&amp;act=copy&amp;mode=2&amp;pid=' . CURRENT_ID . '&amp;id=' . CURRENT_ID : '&amp;act=copy&amp;id=' . CURRENT_ID;
                }

                $this->redirect($strUrl . '&amp;rt=' . REQUEST_TOKEN);
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
     * Auto-generate a form to override all records that are currently shown
     *
     * @return string
     *
     * @throws InternalServerErrorException
     */
    public function overrideAll()
    {
        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'] ?? null)
        {
            throw new InternalServerErrorException('Table "' . $this->strTable . '" is not editable.');
        }

        $return = '';
        $this->import(BackendUser::class, 'User');

        /** @var Session $objSession */
        $objSession = System::getContainer()->get('session');

        // Get current IDs from session
        $session = $objSession->all();
        $ids = $session['CURRENT']['IDS'] ?? array();

        // Save field selection in session
        if (Input::post('FORM_SUBMIT') == $this->strTable . '_all' && Input::get('fields'))
        {
            $session['CURRENT'][$this->strTable] = Input::post('all_fields');
            $objSession->replace($session);
        }

        // Add fields
        $fields = $session['CURRENT'][$this->strTable] ?? array();

        if (!empty($fields) && \is_array($fields) && Input::get('fields'))
        {
            $class = 'tl_tbox';
            $formFields = array();

            // Save record
            if (Input::post('FORM_SUBMIT') == $this->strTable)
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

                    $objVersions = new Versions($this->strTable, $this->intId);
                    $objVersions->initialize();

                    $this->strPalette = StringUtil::trimsplit('[;,]', $this->getPalette());

                    // Store all fields
                    foreach ($fields as $v)
                    {
                        // Check whether field is excluded or not in palette
                        if (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude'] ?? null) || !\in_array($v, $this->strPalette))
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

                    // Always create a new version if something has changed, even if the form has errors (see #237)
                    if ($this->noReload && $this->blnCreateNewVersion)
                    {
                        $objVersions->create();
                    }

                    // Post processing
                    if (!$this->noReload)
                    {
                        // Call the onsubmit_callback
                        if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] ?? null))
                        {
                            foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
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

                        $this->invalidateCacheTags();

                        // Set the current timestamp before adding a new version
                        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
                        {
                            $this->Database->prepare("UPDATE " . $this->strTable . " SET ptable=?, tstamp=? WHERE id=?")
                                           ->execute($this->ptable, time(), $this->intId);
                        }
                        else
                        {
                            $this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
                                           ->execute(time(), $this->intId);
                        }

                        // Create a new version
                        if ($this->blnCreateNewVersion)
                        {
                            $objVersions->create();

                            // Call the onversion_callback
                            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] ?? null))
                            {
                                @trigger_error('Using the "onversion_callback" has been deprecated and will no longer work in Contao 5.0. Use the "oncreate_version_callback" instead.', E_USER_DEPRECATED);

                                foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onversion_callback'] as $callback)
                                {
                                    if (\is_array($callback))
                                    {
                                        $this->import($callback[0]);
                                        $this->{$callback[0]}->{$callback[1]}($this->strTable, $this->intId, $this);
                                    }
                                    elseif (\is_callable($callback))
                                    {
                                        $callback($this->strTable, $this->intId, $this);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Begin current row
            $return .= '
<div class="' . $class . '">';

            foreach ($fields as $v)
            {
                // Check whether field is excluded
                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['exclude'] ?? null)
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
<input type="hidden" name="FORM_FIELDS[]" value="' . StringUtil::specialchars(implode(',', $formFields)) . '">
</div>';

            // Submit buttons
            $arrButtons = array();
            $arrButtons['save'] = '<button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['save'] . '</button>';
            $arrButtons['saveNclose'] = '<button type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c">' . $GLOBALS['TL_LANG']['MSC']['saveNclose'] . '</button>';

            // Call the buttons_callback (see #4691)
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] ?? null))
            {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['edit']['buttons_callback'] as $callback)
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

            // Add the form
            $return = '
<form id="' . $this->strTable . '" class="tl_form tl_edit_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '">
<div class="tl_formbody_edit nogrid">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . ($this->noReload ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . $return . '
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
  ' . $strButtons . '
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
            if (!$this->noReload && Input::post('FORM_SUBMIT') == $this->strTable)
            {
                if (isset($_POST['saveNclose']))
                {
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
            $fields = array_merge($fields, array_keys($GLOBALS['TL_DCA'][$this->strTable]['fields'] ?? array()));

            // Add meta fields if the current user is an administrator
            if ($this->User->isAdmin)
            {
                if ($this->Database->fieldExists('sorting', $this->strTable) && !\in_array('sorting', $fields))
                {
                    array_unshift($fields, 'sorting');
                }

                if ($this->Database->fieldExists('pid', $this->strTable) && !\in_array('pid', $fields))
                {
                    array_unshift($fields, 'pid');
                }
            }

            // Show all non-excluded fields
            foreach ($fields as $field)
            {
                if ($field == 'pid' || $field == 'sorting' || (!($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['exclude'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['doNotShow'] ?? null) && (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType']) || \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'] ?? null) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['input_field_callback'] ?? null))))
                {
                    $options .= '
  <input type="checkbox" name="all_fields[]" id="all_' . $field . '" class="tl_checkbox" value="' . StringUtil::specialchars($field) . '"> <label for="all_' . $field . '" class="tl_checkbox_label">' . (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] ?? (\is_array($GLOBALS['TL_LANG']['MSC'][$field] ?? null) ? $GLOBALS['TL_LANG']['MSC'][$field][0] : ($GLOBALS['TL_LANG']['MSC'][$field] ?? null)) ?? $field) . ' <span style="color:#999;padding-left:3px">[' . $field . ']</span>') . '</label><br>';
                }
            }

            $blnIsError = ($_POST && empty($_POST['all_fields']));

            // Return the select menu
            $return .= '
<form action="' . ampersand(Environment::get('request')) . '&amp;fields=1" id="' . $this->strTable . '_all" class="tl_form tl_edit_form" method="post">
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="' . $this->strTable . '_all">
<input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">' . ($blnIsError ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['general'] . '</p>' : '') . '
<div class="tl_tbox">
<div class="widget">
<fieldset class="tl_checkbox_container">
  <legend' . ($blnIsError ? ' class="error"' : '') . '>' . $GLOBALS['TL_LANG']['MSC']['all_fields'][0] . '<span class="mandatory">*</span></legend>
  <input type="checkbox" id="check_all" class="tl_checkbox" onclick="Backend.toggleCheckboxes(this)"> <label for="check_all" style="color:#a6a6a6"><em>' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</em></label><br>' . $options . '
</fieldset>' . ($blnIsError ? '
<p class="tl_error">' . $GLOBALS['TL_LANG']['ERR']['all_fields'] . '</p>' : ((Config::get('showHelp') && isset($GLOBALS['TL_LANG']['MSC']['all_fields'][1])) ? '
<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['all_fields'][1] . '</p>' : '')) . '
</div>
</div>
</div>
<div class="tl_formbody_submit">
<div class="tl_submit_container">
  <button type="submit" name="save" id="save" class="tl_submit" accesskey="s">' . $GLOBALS['TL_LANG']['MSC']['continue'] . '</button>
</div>
</div>
</form>';
        }

        // Return
        return '
<div id="tl_buttons">
<a href="' . $this->getReferer(true) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>' . $return;
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

        // Show only main products
        $this->procedure[] = "pid=0";

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
                $firstOrderBy = 'pid';
                $showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];

                $query .= " ORDER BY (SELECT " . Database::quoteIdentifier($showFields[0]) . " FROM " . $this->ptable . " WHERE " . $this->ptable . ".id=" . $this->strTable . ".pid), " . implode(', ', $orderBy);

                // Set the foreignKey so that the label is translated
                if (!($GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] ?? null))
                {
                    $GLOBALS['TL_DCA'][$table]['fields']['pid']['foreignKey'] = $this->ptable . '.' . $showFields[0];
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
<div id="tl_buttons">' . ((Input::get('act') == 'select' || $this->ptable) ? '
<a href="' . $this->getReferer(true, $this->ptable) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : (isset($GLOBALS['TL_DCA'][$this->strTable]['config']['backlink']) ? '
<a href="' . System::getContainer()->get('router')->generate('contao_backend') . '?' . $GLOBALS['TL_DCA'][$this->strTable]['config']['backlink'] . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a> ' : '')) . ((Input::get('act') != 'select' && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null)) ? '
<a href="' . ($this->ptable ? $this->addToUrl('act=create' . ((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) < 4) ? '&amp;mode=2' : '') . '&amp;pid=' . $this->intId) : $this->addToUrl('act=create')) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1] ?? '') . '" accesskey="n" onclick="Backend.getScrollOffset()">' . $labelNew[0] . '</a> ' : '') . $this->generateGlobalButtons() . '
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

<div class="tl_listing_container iso_listing_container list_view" id="tl_listing"' . $this->getPickerValueAttribute() . '>' . (isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb']) ? $GLOBALS['TL_DCA'][$table]['list']['sorting']['breadcrumb'] : '') . ((Input::get('act') == 'select' || $this->strPickerFieldType == 'checkbox') ? '

<div class="tl_select_trigger">
<label for="tl_select_trigger" class="tl_select_label">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="tl_select_trigger" onclick="Backend.toggleCheckboxes(this)" class="tl_tree_checkbox">
</div>' : '') . '
<table class="tl_listing' . (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) ? ' showColumns' : '') . ($this->strPickerFieldType ? ' picker unselectable' : '') . '">';

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
    <th class="tl_folder_tlist tl_right_nowrap iso_operations"></th>
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
                        [$strKey, $strTable] = explode(':', $v, 2);
                        [$strTable, $strField] = explode('.', $strTable, 2);

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
                        $row_v = StringUtil::deserialize($row[$v] ?? []);

                        if (\is_array($row_v))
                        {
                            $args_k = array();

                            foreach ($row_v as $option)
                            {
                                $args_k[] = $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$option] ?? $option;
                            }

                            $implode = static function ($v) use (&$implode) {
                                return implode(', ', array_map(static function($vv) use (&$implode) {
                                    return \is_array($vv) ? $implode($vv) : $vv;
                                }, $v));
                            };
                            $args[$k] = $implode($args_k);
                        }
                        elseif (isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]))
                        {
                            $args[$k] = \is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
                        }
                        elseif ((($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['isAssociative'] ?? false) || array_is_assoc($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'] ?? array())) && isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]]))
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
                    $sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null)) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null);
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
                        $colspan = \count($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields']);
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
    <td class="tl_file_list tl_right_nowrap iso_operations"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
    <td class="tl_file_list tl_right_nowrap iso_operations">' . $this->generateButtons($row, $this->strTable, $this->root) . ($this->strPickerFieldType ? $this->getPickerInputField($row['id']) : '') . '</td>') . '
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

                if (!($GLOBALS['TL_DCA'][$this->strTable]['config']['notSortable'] ?? null))
                {
                    $arrButtons['cut'] = '<button type="submit" name="cut" id="cut" class="tl_submit" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['moveSelected'] . '</button>';
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
        $blnHasSorting = false;
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

        // Check the default labels (see #509)
        $labelNew = $GLOBALS['TL_LANG'][$this->strTable]['new'] ?? $GLOBALS['TL_LANG']['DCA']['new'];
        $labelCut = $GLOBALS['TL_LANG'][$this->strTable]['cut'] ?? $GLOBALS['TL_LANG']['DCA']['cut'];
        $labelPasteNew = $GLOBALS['TL_LANG'][$this->strTable]['pastenew'] ?? $GLOBALS['TL_LANG']['DCA']['pastenew'];
        $labelPasteAfter = $GLOBALS['TL_LANG'][$this->strTable]['pasteafter'] ?? $GLOBALS['TL_LANG']['DCA']['pasteafter'];
        $labelEditHeader = $GLOBALS['TL_LANG'][$this->strTable]['editmeta'] ?? $GLOBALS['TL_LANG'][$this->strTable]['editheader'] ?? $GLOBALS['TL_LANG']['DCA']['editheader'];
        $strBackUrl = Input::get('id') ? 'contao/main.php?do=iso_products' : \Contao\System::getReferer(true, $this->ptable);

        // TODO: fix back button in variants
        $return = Message::generate() . '
<div id="tl_buttons">' . (Input::get('nb') ? '&nbsp;' : '
<a href="' . $strBackUrl . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) . '" accesskey="b" onclick="Backend.getScrollOffset()">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>') . ' ' . ((Input::get('act') != 'select' && !$blnClipboard && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null)) ? '
<a href="' . $this->addToUrl(($blnHasSorting ? 'act=paste&amp;mode=create' : 'act=create&amp;mode=2&amp;pid=' . $this->intId)) . '" class="header_new" title="' . StringUtil::specialchars($labelNew[1]) . '" accesskey="n" onclick="Backend.getScrollOffset()">' . $labelNew[0] . '</a> ' : '') . ($blnClipboard ? '
<a href="' . $this->addToUrl('clipboard=1') . '" class="header_clipboard" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['clearClipboard']) . '" accesskey="x">' . $GLOBALS['TL_LANG']['MSC']['clearClipboard'] . '</a> ' : $this->generateGlobalButtons()) . '
</div>';

        // Get all details of the parent record
        $objParent = $this->Database->prepare("SELECT * FROM {$this->strTable} WHERE id=?")
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
<div class="tl_listing_container iso_listing_container parent_view' . ($this->strPickerFieldType ? ' picker unselectable' : '') . '" id="tl_listing"' . $this->getPickerValueAttribute() . '>
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
<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $objParent->id . (!$blnMultiboard ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . StringUtil::specialchars($labelPasteAfter[0]) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a>' : ((!($GLOBALS['TL_DCA'][$this->ptable]['config']['notEditable'] ?? null) && $this->User->canEditFieldsOf($this->ptable)) ? '
<a href="' . preg_replace('/&(amp;)?table=[^& ]*/i', ($this->ptable ? '&amp;table=' . $this->ptable : ''), $this->addToUrl('act=edit' . (Input::get('nb') ? '&amp;nc=1' : ''))) . '" class="edit" title="' . StringUtil::specialchars(sprintf(\is_array($labelEditHeader) ? $labelEditHeader[1] : $labelEditHeader, $objParent->id)) . '">' . $imageEditHeader . '</a> ' . $this->generateHeaderButtons($objParent->row(), $this->ptable) : '') . (($blnHasSorting && !($GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['config']['notCreatable'] ?? null)) ? '
<a href="' . $this->addToUrl('act=create&amp;mode=2&amp;pid=' . $objParent->id . '&amp;id=' . $this->intId) . '" title="' . StringUtil::specialchars($labelPasteNew[0]) . '">' . $imagePasteNew . '</a>' : ''))) . '
</div>';

            // Format header fields
            $add = array();
            $headerFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['headerFields'];

            foreach ($headerFields as $v)
            {
                $_v = StringUtil::deserialize($objParent->$v);

                // Translate UUIDs to paths
                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] == 'fileTree')
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
                elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['isBoolean'] ?? null) || (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['inputType'] ?? null) == 'checkbox' && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['multiple'] ?? null)))
                {
                    $_v = $_v ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                }
                elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] ?? null) == 'date')
                {
                    $_v = $_v ? Date::parse(Config::get('dateFormat'), $_v) : '-';
                }
                elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] ?? null) == 'time')
                {
                    $_v = $_v ? Date::parse(Config::get('timeFormat'), $_v) : '-';
                }
                elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['rgxp'] ?? null) == 'datim')
                {
                    $_v = $_v ? Date::parse(Config::get('datimFormat'), $_v) : '-';
                }
                elseif ($v == 'tstamp')
                {
                    $objMaxTstamp = $this->Database->prepare("SELECT MAX(tstamp) AS tstamp FROM {$this->strTable} WHERE pid=?")
                        ->execute($objParent->id);

                    if (!$objMaxTstamp->tstamp)
                    {
                        $objMaxTstamp->tstamp = $objParent->tstamp;
                    }

                    $_v = Date::parse(Config::get('datimFormat'), max($objParent->tstamp, $objMaxTstamp->tstamp));
                }
                elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['foreignKey']))
                {
                    $arrForeignKey = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['foreignKey'], 2);

                    $objLabel = $this->Database->prepare("SELECT " . Database::quoteIdentifier($arrForeignKey[1]) . " AS value FROM " . $arrForeignKey[0] . " WHERE id=?")
                        ->limit(1)
                        ->execute($_v);

                    $_v = $objLabel->numRows ? $objLabel->value : '-';
                }
                elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v] ?? null))
                {
                    $_v = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v][0];
                }
                elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v]))
                {
                    $_v = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['reference'][$_v];
                }
                elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['eval']['isAssociative'] ?? null) || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'] ?? null))
                {
                    $_v = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options'][$_v] ?? null;
                }
                elseif (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options_callback'] ?? null))
                {
                    $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options_callback'][0];
                    $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options_callback'][1];

                    $this->import($strClass);
                    $options_callback = $this->$strClass->$strMethod($this);

                    $_v = $options_callback[$_v] ?? '-';
                }
                elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options_callback'] ?? null))
                {
                    $options_callback = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['options_callback']($this);

                    $_v = $options_callback[$_v] ?? '-';
                }

                // Add the sorting field
                if ($_v)
                {
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label']))
                    {
                        $key = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$v]['label'];
                    }
                    else
                    {
                        $key = $GLOBALS['TL_LANG'][$this->strTable][$v][0] ?? $v;
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

            $arrProcedure[] = "pid=?";
            $arrValues[] = CURRENT_ID;

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

            $result = $objOrderBy->fetchAllAssoc();
            $return .= '

<table class="tl_listing' . ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ? ' showColumns' : '') . '">';

            // Automatically add the "order by" field as last column if we do not have group headers
            if ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['showColumns'] ?? null) {
                $blnFound = false;

                // Extract the real key and compare it to $firstOrderBy
                foreach (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] ?? array()) as $f)
                {
                    if (strpos($f, ':') !== false)
                    {
                        [$f] = explode(':', $f, 2);
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

                foreach (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'] ?? array()) as $f)
                {
                    if (strpos($f, ':') !== false)
                    {
                        [$f] = explode(':', $f, 2);
                    }

                    $return .= '
    <th class="tl_folder_tlist col_' . $f . (($f == $firstOrderBy) ? ' ordered_by' : '') . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$f]['label']) . '</th>';
                }

                $return .= '
    <th class="tl_folder_tlist tl_right_nowrap iso_operations"></th>
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
                $showFields = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'];

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
                        [$strKey, $strTable] = explode(':', $v);
                        [$strTable, $strField] = explode('.', $strTable);

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
                            $args[$k] = \is_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]][0] : $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$row[$v]];
                        }
                        elseif ((($GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['isAssociative'] ?? false) || array_is_assoc($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'] ?? null)) && isset($GLOBALS['TL_DCA'][$table]['fields'][$v]['options'][$row[$v]]))
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
                $label = vsprintf(($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['format'] ?? null) ?: '%s', $args);

                if (($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] ?? null) > 0 && ($GLOBALS['TL_DCA'][$this->strTable]['list']['label']['maxCharacters'] ?? null) < \strlen(strip_tags($label)))
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
                    $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
                    $sortingMode = (\count($orderBy) == 1 && $firstOrderBy == $orderBy[0] && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] ?? null) && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null)) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['flag'] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$firstOrderBy]['flag'] ?? null);
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

                    // Handle strings and arrays
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
                        $field = $GLOBALS['TL_DCA'][$this->strTable]['list']['label']['fields'][$j];

                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
                        {
                            $value = $arg ?: '-';
                        }
                        else
                        {
                            $value = (string) $arg !== '' ? $arg : '-';
                        }

                        $return .= '<td colspan="' . $colspan . '" class="tl_file_list col_' . $field . ($field == $firstOrderBy ? ' ordered_by' : '') . '">' . $value . '</td>';
                    }
                }
                else
                {
                    $return .= '<td class="tl_file_list">' . $label . '</td>';
                }

                // Buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
                $return .= ((Input::get('act') == 'select') ? '
    <td class="tl_file_list tl_right_nowrap iso_operations"><input type="checkbox" name="IDS[]" id="ids_' . $row['id'] . '" class="tl_tree_checkbox" value="' . $row['id'] . '"></td>' : '
    <td class="tl_file_list tl_right_nowrap iso_operations">' . $this->generateButtons($row, $this->strTable, $this->root) . ($this->strPickerFieldType ? $this->getPickerInputField($row['id']) : '') . '</td>') . '
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
     * Return a select menu that allows to sort results by a particular field
     *
     * @return string
     */
    protected function sortMenu()
    {
        if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) != 2 && ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) != 4)
        {
            return '';
        }

        $sortingFields = array();

        // Get sorting fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
        {
            if ($v['sorting'] ?? null)
            {
                $sortingFields[] = $k;
            }
        }

        // Return if there are no sorting fields
        if (empty($sortingFields))
        {
            return '';
        }

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $session = $objSessionBag->all();
        $sessionKey = Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;
        $orderBy = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['fields'];
        $firstOrderBy = preg_replace('/\s+.*$/', '', $orderBy[0]);

        // Add PID to order fields
        if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 3 && $this->Database->fieldExists('pid', $this->strTable))
        {
            array_unshift($orderBy, 'pid');
        }

        // Set sorting from user input
        if (Input::post('FORM_SUBMIT') == 'tl_filters')
        {
            $strSort = Input::post('tl_sort');

            // Validate the user input (thanks to aulmn) (see #4971)
            if (\in_array($strSort, $sortingFields, true))
            {
                $session['sorting'][$sessionKey] = \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strSort]['flag'] ?? null, array(2, 4, 6, 8, 10, 12)) ? "$strSort DESC" : $strSort;
                $objSessionBag->replace($session);
            }
        }

        // Overwrite the "orderBy" value with the session value
        elseif (isset($session['sorting'][$sessionKey]))
        {
            $overwrite = preg_quote(preg_replace('/\s+.*$/', '', $session['sorting'][$sessionKey]), '/');
            $orderBy = array_diff($orderBy, preg_grep('/^' . $overwrite . '/i', $orderBy));

            array_unshift($orderBy, $session['sorting'][$sessionKey]);

            $this->firstOrderBy = $overwrite;
            $this->orderBy = $orderBy;
        }

        $options_sorter = array();

        // Sorting fields
        foreach ($sortingFields as $field)
        {
            $options_label = ($lbl = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null)) ? $lbl : $GLOBALS['TL_LANG']['MSC'][$field];

            if (\is_array($options_label))
            {
                $options_label = $options_label[0];
            }

            $options_sorter[$options_label] = '  <option value="' . StringUtil::specialchars($field) . '"' . (((!isset($session['sorting'][$sessionKey]) && $field == $firstOrderBy) || $field == str_replace(' DESC', '', $session['sorting'][$sessionKey] ?? '')) ? ' selected="selected"' : '') . '>' . $options_label . '</option>';
        }

        // Sort by option values
        uksort($options_sorter, array(Utf8::class, 'strnatcasecmp'));

        return '
<div class="tl_sorting tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['sortBy'] . ':</strong>
<select name="tl_sort" id="tl_sort" class="tl_select">
' . implode("\n", $options_sorter) . '
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

        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $session = $objSessionBag->all();
        $sessionKey = Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;

        // Get search fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
        {
            if ($v['search'] ?? null)
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
        if (Input::post('FORM_SUBMIT') == 'tl_filters')
        {
            $strField = Input::post('tl_field', true);
            $strKeyword = ltrim(Input::postRaw('tl_value'), '*');

            if ($strField && !\in_array($strField, $searchFields, true))
            {
                $strField = '';
                $strKeyword = '';
            }

            $session['search'][$sessionKey]['field'] = $strField;
            $session['search'][$sessionKey]['value'] = $strKeyword;

            $objSessionBag->replace($session);
        }

        // Set the search value from the session
        elseif ((string) ($session['search'][$sessionKey]['value'] ?? '') !== '')
        {
            $searchValue = $session['search'][$sessionKey]['value'];
            $fld = $session['search'][$sessionKey]['field'];

            try
            {
                $this->Database->prepare("SELECT '' REGEXP ?")->execute($searchValue);
            }
            catch (DriverException $exception)
            {
                // Quote search string if it is not a valid regular expression
                $searchValue = preg_quote($searchValue);
            }

            $strReplacePrefix = '';
            $strReplaceSuffix = '';

            // Decode HTML entities to make them searchable
            if (empty($GLOBALS['TL_DCA'][$this->strTable]['fields'][$fld]['eval']['decodeEntities']))
            {
                $arrReplace = array(
                    '&#35;' => '#',
                    '&#60;' => '<',
                    '&#62;' => '>',
                    '&lt;' => '<',
                    '&gt;' => '>',
                    '&#40;' => '(',
                    '&#41;' => ')',
                    '&#92;' => '\\\\',
                    '&#61;' => '=',
                    '&amp;' => '&',
                );

                $strReplacePrefix = str_repeat('REPLACE(', \count($arrReplace));

                foreach ($arrReplace as $strSource => $strTarget)
                {
                    $strReplaceSuffix .= ", '$strSource', '$strTarget')";
                }
            }

            $strPattern = "$strReplacePrefix CAST(%s AS CHAR) $strReplaceSuffix REGEXP ?";

            if (substr(Config::get('dbCollation'), -3) == '_ci')
            {
                $strPattern = "$strReplacePrefix LOWER(CAST(%s AS CHAR)) $strReplaceSuffix REGEXP LOWER(?)";
            }

            if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$fld]['foreignKey']))
            {
                list($t, $f) = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$fld]['foreignKey'], 2);
                $this->procedure[] = "(" . sprintf($strPattern, Database::quoteIdentifier($fld)) . " OR " . sprintf($strPattern, "(SELECT " . Database::quoteIdentifier($f) . " FROM $t WHERE $t.id=" . $this->strTable . "." . Database::quoteIdentifier($fld) . ")") . ")";
                $this->values[] = $searchValue;
            }
            else
            {
                $this->procedure[] = sprintf($strPattern, Database::quoteIdentifier($fld));
            }

            $this->values[] = $searchValue;
        }

        $options_sorter = array();

        foreach ($searchFields as $field)
        {
            $option_label = $field;

            if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']))
            {
                $option_label = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'];
            }
            elseif (isset($GLOBALS['TL_LANG']['MSC'][$field]))
            {
                $option_label = \is_array($GLOBALS['TL_LANG']['MSC'][$field]) ? $GLOBALS['TL_LANG']['MSC'][$field][0] : $GLOBALS['TL_LANG']['MSC'][$field];
            }

            $options_sorter[$option_label . '_' . $field] = '  <option value="' . StringUtil::specialchars($field) . '"' . ((isset($session['search'][$this->strTable]['field']) && $session['search'][$sessionKey]['field'] == $field) ? ' selected="selected"' : '') . '>' . $option_label . '</option>';
        }

        // Sort by option values
        uksort($options_sorter, array(Utf8::class, 'strnatcasecmp'));

        $active = isset($session['search'][$sessionKey]['value']) && (string) $session['search'][$sessionKey]['value'] !== '';

        return '
<div class="tl_search tl_subpanel">
<strong>' . $GLOBALS['TL_LANG']['MSC']['search'] . ':</strong>
<select name="tl_field" class="tl_select tl_chosen' . ($active ? ' active' : '') . '">
' . implode("\n", $options_sorter) . '
</select>
<span>=</span>
<input type="search" name="tl_value" class="tl_text' . ($active ? ' active' : '') . '" value="' . StringUtil::specialchars($session['search'][$sessionKey]['value'] ?? '') . '">
</div>';
    }

    /**
     * Return a select menu to limit results
     *
     * @param boolean $blnOptional
     *
     * @return string
     */
    protected function limitMenu($blnOptional=false)
    {
        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $session = $objSessionBag->all();
        $filter = Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;
        $fields = '';

        // Set limit from user input
        if (\in_array(Input::post('FORM_SUBMIT'), array('tl_filters', 'tl_filters_limit')))
        {
            $strLimit = Input::post('tl_limit');

            if ($strLimit == 'tl_limit')
            {
                unset($session['filter'][$filter]['limit']);
            }
            // Validate the user input (thanks to aulmn) (see #4971)
            elseif ($strLimit == 'all' || preg_match('/^[0-9]+,[0-9]+$/', $strLimit))
            {
                    $session['filter'][$filter]['limit'] = $strLimit;
                }

            $objSessionBag->replace($session);

            if (Input::post('FORM_SUBMIT') == 'tl_filters_limit')
            {
                $this->reload();
            }
        }

        // Set limit from table configuration
        else
        {
            $this->limit = isset($session['filter'][$filter]['limit']) ? (($session['filter'][$filter]['limit'] == 'all') ? null : $session['filter'][$filter]['limit']) : '0,' . Config::get('resultsPerPage');

            $arrProcedure = $this->procedure;
            $arrValues = $this->values;
            $query = "SELECT COUNT(*) AS count FROM " . $this->strTable;

            if (Input::get('id')) {
                $arrProcedure[] = "pid=?";
                $arrValues[] = Input::get('id');
            } else {
                $arrProcedure[] = "pid=0";
            }

            if (!empty($this->root) && \is_array($this->root))
            {
                $arrProcedure[] = 'id IN(' . implode(',', $this->root) . ')';
            }

            // Support empty ptable fields
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
            {
                $arrProcedure[] = ($this->ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";
                $arrValues[] = $this->ptable;
            }

            if (!empty($arrProcedure))
            {
                $query .= " WHERE " . implode(' AND ', $arrProcedure);
            }

            $objTotal = $this->Database->prepare($query)->execute($arrValues);
            $this->total = $objTotal->count;
            $options_total = 0;
            $maxResultsPerPage = Config::get('maxResultsPerPage');
            $blnIsMaxResultsPerPage = false;

            // Overall limit
            if ($maxResultsPerPage > 0 && $this->total > $maxResultsPerPage && ($this->limit === null || preg_replace('/^.*,/', '', $this->limit) == $maxResultsPerPage))
            {
                if ($this->limit === null)
                {
                    $this->limit = '0,' . Config::get('maxResultsPerPage');
                }

                $blnIsMaxResultsPerPage = true;
                Config::set('resultsPerPage', Config::get('maxResultsPerPage'));
                $session['filter'][$filter]['limit'] = Config::get('maxResultsPerPage');
            }

            $options = '';

            // Build options
            if ($this->total > 0)
            {
                $options = '';
                $options_total = ceil($this->total / Config::get('resultsPerPage'));

                // Reset limit if other parameters have decreased the number of results
                if ($this->limit !== null && (!$this->limit || preg_replace('/,.*$/', '', $this->limit) > $this->total))
                {
                    $this->limit = '0,' . Config::get('resultsPerPage');
                }

                // Build options
                for ($i=0; $i<$options_total; $i++)
                {
                    $this_limit = ($i*Config::get('resultsPerPage')) . ',' . Config::get('resultsPerPage');
                    $upper_limit = ($i*Config::get('resultsPerPage')+Config::get('resultsPerPage'));

                    if ($upper_limit > $this->total)
                    {
                        $upper_limit = $this->total;
                    }

                    $options .= '
  <option value="' . $this_limit . '"' . Widget::optionSelected($this->limit, $this_limit) . '>' . ($i*Config::get('resultsPerPage')+1) . ' - ' . $upper_limit . '</option>';
                }

                if (!$blnIsMaxResultsPerPage)
                {
                    $options .= '
  <option value="all"' . Widget::optionSelected($this->limit, null) . '>' . $GLOBALS['TL_LANG']['MSC']['filterAll'] . '</option>';
                }
            }

            // Return if there is only one page
            if ($blnOptional && ($this->total < 1 || $options_total < 2))
            {
                return '';
            }

            $fields = '
<select name="tl_limit" class="tl_select tl_chosen' . (($session['filter'][$filter]['limit'] ?? null) != 'all' && $this->total > Config::get('resultsPerPage') ? ' active' : '') . '" onchange="this.form.submit()">
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
     * @param integer $intFilterPanel
     *
     * @return string
     */
    protected function filterMenu($intFilterPanel)
    {
        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $fields = '';
        $sortingFields = array();
        $session = $objSessionBag->all();
        $filter = Input::get('id') ? $this->strTable . '_' . CURRENT_ID : $this->strTable;

        // Get the sorting fields
        foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'] as $k=>$v)
        {
            if (($v['filter'] ?? null) == $intFilterPanel)
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
        if (Input::post('FORM_SUBMIT') == 'tl_filters')
        {
            foreach ($sortingFields as $field)
            {
                if (Input::post($field, true) != 'tl_' . $field)
                {
                    $session['filter'][$filter][$field] = Input::post($field, true);
                }
                else
                {
                    unset($session['filter'][$filter][$field]);
                }
            }

            $objSessionBag->replace($session);
        }

        // Set filter from table configuration
        else
        {
            foreach ($sortingFields as $field)
            {
                $what = Database::quoteIdentifier($field);

                if (isset($session['filter'][$filter][$field]))
                {
                    // Sort by day
                    if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(5, 6)))
                    {
                        if (!$session['filter'][$filter][$field])
                        {
                            $this->procedure[] = $what . "=''";
                        }
                        else
                        {
                            $objDate = new Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $what . ' BETWEEN ? AND ?';
                            $this->values[] = $objDate->dayBegin;
                            $this->values[] = $objDate->dayEnd;
                        }
                    }

                    // Sort by month
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(7, 8)))
                    {
                        if (!$session['filter'][$filter][$field])
                        {
                            $this->procedure[] = $what . "=''";
                        }
                        else
                        {
                            $objDate = new Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $what . ' BETWEEN ? AND ?';
                            $this->values[] = $objDate->monthBegin;
                            $this->values[] = $objDate->monthEnd;
                        }
                    }

                    // Sort by year
                    elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(9, 10)))
                    {
                        if (!$session['filter'][$filter][$field])
                        {
                            $this->procedure[] = $what . "=''";
                        }
                        else
                        {
                            $objDate = new Date($session['filter'][$filter][$field]);
                            $this->procedure[] = $what . ' BETWEEN ? AND ?';
                            $this->values[] = $objDate->yearBegin;
                            $this->values[] = $objDate->yearEnd;
                        }
                    }

                    // Manual filter
                    elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'] ?? null)
                    {
                        // CSV lists (see #2890)
                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv']))
                        {
                            $this->procedure[] = $this->Database->findInSet('?', $field, true);
                            $this->values[] = $session['filter'][$filter][$field] ?? null;
                        }
                        else
                        {
                            $this->procedure[] = $what . ' LIKE ?';
                            $this->values[] = '%"' . $session['filter'][$filter][$field] . '"%';
                        }
                    }

                    // Other sort algorithm
                    else
                    {
                        $this->procedure[] = $what . '=?';
                        $this->values[] = $session['filter'][$filter][$field] ?? null;
                    }
                }
            }
        }

        // Add sorting options
        foreach ($sortingFields as $cnt=>$field)
        {
            $arrValues = array();
            $arrProcedure = array();

            if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 4)
            {
                $arrProcedure[] = 'pid=?';
                $arrValues[] = CURRENT_ID;
            }

            if (!empty($this->root) && \is_array($this->root))
            {
                $arrProcedure[] = "id IN(" . implode(',', array_map('\intval', $this->root)) . ")";
            }

            // Check for a static filter (see #4719)
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] ?? null))
            {
                foreach ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['filter'] as $fltr)
                {
                    if (\is_string($fltr))
                    {
                        $arrProcedure[] = $fltr;
                    }
                    else
                    {
                        $arrProcedure[] = $fltr[0];
                        $arrValues[] = $fltr[1];
                    }
                }
            }

            // Support empty ptable fields
            if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dynamicPtable'] ?? null)
            {
                $arrProcedure[] = ($this->ptable == 'tl_article') ? "(ptable=? OR ptable='')" : "ptable=?";
                $arrValues[] = $this->ptable;
            }

            $what = Database::quoteIdentifier($field);

            // Optimize the SQL query (see #8485)
            if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag']))
            {
                // Sort by day
                if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(5, 6)))
                {
                    $what = "IF($what!='', FLOOR(UNIX_TIMESTAMP(FROM_UNIXTIME($what , '%%Y-%%m-%%d'))), '') AS $what";
                }

                // Sort by month
                elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(7, 8)))
                {
                    $what = "IF($what!='', FLOOR(UNIX_TIMESTAMP(FROM_UNIXTIME($what , '%%Y-%%m-01'))), '') AS $what";
                }

                // Sort by year
                elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'], array(9, 10)))
                {
                    $what = "IF($what!='', FLOOR(UNIX_TIMESTAMP(FROM_UNIXTIME($what , '%%Y-01-01'))), '') AS $what";
                }
            }

            $table = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->ptable : $this->strTable;

            // Limit the options if there are root records
            if (isset($GLOBALS['TL_DCA'][$table]['list']['sorting']['root']) && $GLOBALS['TL_DCA'][$table]['list']['sorting']['root'] !== false)
            {
                $rootIds = array_map('\intval', $GLOBALS['TL_DCA'][$table]['list']['sorting']['root']);

                // Also add the child records of the table (see #1811)
                if (($GLOBALS['TL_DCA'][$table]['list']['sorting']['mode'] ?? null) == 5)
                {
                    $rootIds = array_merge($rootIds, $this->Database->getChildRecords($rootIds, $table));
                }

                if (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] ?? null) == 6)
                {
                    $arrProcedure[] = "pid IN(" . implode(',', $rootIds) . ")";
                }
                else
                {
                    $arrProcedure[] = "id IN(" . implode(',', $rootIds) . ")";
                }
            }

            $objFields = $this->Database->prepare("SELECT DISTINCT " . $what . " FROM " . $this->strTable . ((\is_array($arrProcedure) && isset($arrProcedure[0])) ? ' WHERE ' . implode(' AND ', $arrProcedure) : ''))
                                        ->execute($arrValues);

            // Begin select menu
            $fields .= '
<select name="' . $field . '" id="' . $field . '" class="tl_select tl_chosen' . (isset($session['filter'][$filter][$field]) ? ' active' : '') . '">
  <option value="tl_' . $field . '">' . (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['label'] ?? null)) . '</option>
  <option value="tl_' . $field . '">---</option>';

            if ($objFields->numRows)
            {
                $options = $objFields->fetchEach($field);

                // Sort by day
                if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(5, 6)))
                {
                    ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null) == 6 ? rsort($options) : sort($options);

                    foreach ($options as $k=>$v)
                    {
                        if ($v === '')
                        {
                            $options[$v] = '-';
                        }
                        else
                        {
                            $options[$v] = Date::parse(Config::get('dateFormat'), $v);
                        }

                        unset($options[$k]);
                    }
                }

                // Sort by month
                elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(7, 8)))
                {
                    ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null) == 8 ? rsort($options) : sort($options);

                    foreach ($options as $k=>$v)
                    {
                        if ($v === '')
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
                elseif (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(9, 10)))
                {
                    ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null) == 10 ? rsort($options) : sort($options);

                    foreach ($options as $k=>$v)
                    {
                        if ($v === '')
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
                if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'] ?? null)
                {
                    $moptions = array();

                    // TODO: find a more effective solution
                    foreach ($options as $option)
                    {
                        // CSV lists (see #2890)
                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv']))
                        {
                            $doptions = StringUtil::trimsplit($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['csv'], $option);
                        }
                        else
                        {
                            $doptions = StringUtil::deserialize($option);
                        }

                        if (\is_array($doptions))
                        {
                            $moptions = array_merge($moptions, $doptions);
                        }
                    }

                    $options = $moptions;
                }

                $options = array_unique($options);
                $options_callback = array();

                // Call the options_callback
                if (!($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'] ?? null) && (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null) || \is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null)))
                {
                    if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null))
                    {
                        $strClass = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][0];
                        $strMethod = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'][1];

                        $this->import($strClass);
                        $options_callback = $this->$strClass->$strMethod($this);
                    }
                    elseif (\is_callable($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback'] ?? null))
                    {
                        $options_callback = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options_callback']($this);
                    }

                    // Sort options according to the keys of the callback array
                    $options = array_intersect(array_keys($options_callback), $options);
                }

                $options_sorter = array();
                $blnDate = \in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(5, 6, 7, 8, 9, 10));

                // Options
                foreach ($options as $kk=>$vv)
                {
                    $value = $blnDate ? $kk : $vv;

                    // Options callback
                    if (!empty($options_callback) && \is_array($options_callback))
                    {
                        $vv = $options_callback[$vv];
                    }

                    // Replace the ID with the foreign key
                    elseif (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
                    {
                        $key = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey'], 2);

                        $objParent = $this->Database->prepare("SELECT " . Database::quoteIdentifier($key[1]) . " AS value FROM " . $key[0] . " WHERE id=?")
                                                    ->limit(1)
                                                    ->execute($vv);

                        if ($objParent->numRows)
                        {
                            $vv = $objParent->value;
                        }
                    }

                    // Replace boolean checkbox value with "yes" and "no"
                    elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isBoolean'] ?? null) || (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['inputType'] ?? null) == 'checkbox' && !($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['multiple'] ?? null)))
                    {
                        $vv = $vv ? $GLOBALS['TL_LANG']['MSC']['yes'] : $GLOBALS['TL_LANG']['MSC']['no'];
                    }

                    // Get the name of the parent record (see #2703)
                    elseif ($field == 'pid')
                    {
                        $this->loadDataContainer($this->ptable);
                        $showFields = $GLOBALS['TL_DCA'][$this->ptable]['list']['label']['fields'] ?? array();

                        if (!($showFields[0] ?? null))
                        {
                            $showFields[0] = 'id';
                        }

                        $objShowFields = $this->Database->prepare("SELECT " . Database::quoteIdentifier($showFields[0]) . " FROM " . $this->ptable . " WHERE id=?")
                                                        ->limit(1)
                                                        ->execute($vv);

                        if ($objShowFields->numRows)
                        {
                            $vv = $objShowFields->{$showFields[0]};
                        }
                    }

                    $option_label = '';

                    // Use reference array
                    if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference']))
                    {
                        $option_label = \is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv] ?? null) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv][0] : ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['reference'][$vv] ?? null);
                    }

                    // Associative array
                    elseif (($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['eval']['isAssociative'] ?? null) || array_is_assoc($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'] ?? null))
                    {
                        $option_label = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['options'][$vv] ?? null;
                    }

                    // No empty options allowed
                    if (!$option_label)
                    {
                        if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['foreignKey']))
                    {
                        $option_label = $vv ?: '-';
                    }
                        else
                        {
                            $option_label = (string) $vv !== '' ? $vv : '-';
                        }
                    }

                    $options_sorter[$option_label . '_' . $field] = '  <option value="' . StringUtil::specialchars($value) . '"' . ((isset($session['filter'][$filter][$field]) && $value == $session['filter'][$filter][$field]) ? ' selected="selected"' : '') . '>' . StringUtil::specialchars($option_label) . '</option>';
                }

                // Sort by option values
                if (!$blnDate)
                {
                    uksort($options_sorter, array(Utf8::class, 'strnatcasecmp'));

                    if (\in_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['flag'] ?? null, array(2, 4, 12)))
                    {
                        $options_sorter = array_reverse($options_sorter, true);
                    }
                }

                $fields .= "\n" . implode("\n", array_values($options_sorter));
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
        /** @var AttributeBagInterface $objSessionBag */
        $objSessionBag = System::getContainer()->get('session')->getBag('contao_backend');

        $session = $objSessionBag->all();
        $strLanguage = $session['language'][$this->strTable][$this->intId] ?? null;
        $arrDuplicate = array();

        foreach (StringUtil::trimsplit('[;,]', $this->getPalette()) as $field)
        {
            if (\is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field] ?? null) && ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$field]['attributes']['multilingual'] ?? null)) {
                $arrDuplicate[] = $field;
            }
        }

        if (!empty($arrDuplicate))
        {
            $language = $this->Database->prepare("SELECT id FROM {$this->strTable} WHERE pid=? AND language=?")->execute($this->intId, $strLanguage);

            if ($language->numRows)
            {
                $objVersions = new Versions($this->strTable, $language->id);
                $objVersions->initialize();

                $arrRow = $this->Database->prepare('SELECT '. implode(',', $arrDuplicate) . " FROM {$this->strTable} WHERE id=?")->execute($this->intId)->fetchAssoc();
                $this->Database->prepare("UPDATE {$this->strTable} %s WHERE id=?")->set($arrRow)->execute($language->id);

                $objVersions->create();

                $this->log(sprintf('A new version of record ID %s (table %s) has been created', $language->id, $this->strTable), __METHOD__, TL_GENERAL);
            }
        }

        \Contao\Controller::redirect(\Contao\Backend::addToUrl('act=edit'));
    }
}
