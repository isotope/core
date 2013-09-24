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

namespace Isotope\Widget;


/**
 * Class ProductGroupSelector
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */
class ProductGroupSelector extends \Widget
{

    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Path nodes
     * @var array
     */
    protected $arrNodes = array();

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Ajax id
     * @var string
     */
    protected $strAjaxId;

    /**
     * Ajax key
     * @var string
     */
    protected $strAjaxKey;

    /**
     * Ajax name
     * @var string
     */
    protected $strAjaxName;


    /**
     * Load database object
     * @param array
     */
    public function __construct($arrAttributes=false)
    {
        parent::__construct($arrAttributes);

        $this->loadDataContainer('tl_iso_groups');
        \System::loadLanguageFile('tl_iso_groups');

        $this->import('Database');
        $this->import('BackendUser', 'User');
        $this->import('Isotope\tl_iso_groups', 'tl_iso_groups');
    }


    /**
     * Add specific attributes
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey)
        {
            case 'mandatory':
                $this->arrConfiguration['mandatory'] = $varValue ? true : false;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }


    /**
     * Skip the field if "change selection" is not checked
     * @param mixed
     * @return mixed
     */
    protected function validator($varInput)
    {
        if (!\Input::post($this->strName.'_save'))
        {
            $this->mandatory = false;
            $this->blnSubmitInput = false;
        }

        // Check if there is at least one value
        if ($this->fieldType == 'text')
        {
            if (is_array($varInput))
            {
                foreach ($varInput as $k => $option)
                {
                    if ($this->mandatory && $option != '')
                    {
                        $this->mandatory = false;
                    }
                    elseif ($option == '')
                    {
                        unset($varInput[$k]);
                    }
                }
            }
        }

        return parent::validator($varInput);
    }


    /**
     * Generate the widget and return it as string
     * @return string
     */
    public function generate()
    {
        $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/assets/backend'.(ISO_DEBUG ? '' : '.min').'.js';

        // Open the tree if there is an error
        if ($this->hasErrors())
        {
            $this->required = true;
        }

        // Store the keyword
        if (\Input::post('FORM_SUBMIT') == 'item_selector')
        {
            $this->Session->set('product_group_selector_search', \Input::post('keyword'));
            $this->reload();
        }

        $tree = '';
        $this->getPathNodes();
        $for = $this->Session->get('product_group_selector_search');
        $arrIds = array();

        // Search for a specific group
        if ($for != '')
        {
            // The keyword must not start with a wildcard (see #4910)
            if (strncmp($for, '*', 1) === 0)
            {
                $for = substr($for, 1);
            }

            $objRoot = \Database::getInstance()->prepare("SELECT id FROM tl_iso_groups WHERE CAST(name AS CHAR) REGEXP ?")->execute($for);

            if ($objRoot->numRows > 0)
            {
                // Respect existing limitations
                if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['rootNodes']))
                {
                    $arrRoot = array();

                    while ($objRoot->next())
                    {
                        // Predefined node set (see #3563)
                        if (count(array_intersect($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['rootNodes'], \Database::getInstance()->getParentRecords($objRoot->id, 'tl_iso_groups'))) > 0)
                        {
                            $arrRoot[] = $objRoot->id;
                        }
                    }

                    $arrIds = $arrRoot;
                }
                elseif ($this->User->isAdmin)
                {
                    // Show all pages to admins
                    $arrIds = $objRoot->fetchEach('id');
                } else {
                    $arrRoot = array();

                    while ($objRoot->next()) {
                        // Show only mounted groups to regular users
                        if (count(array_intersect($this->User->iso_groups, $this->Database->getParentRecords($objRoot->id, 'tl_iso_groups'))) > 0) {
                            $arrRoot[] = $objRoot->id;
                        }
                    }

                    $arrIds = $arrRoot;
                }
            }

            // Build the tree
            foreach ($arrIds as $id)
            {
                $tree .= $this->renderGrouptree($id, -20);
            }
        }
        else
        {
            // Breadcrumb menu
            if ($this->Session->get('iso_products_gid'))
            {
                $tree .= $this->renderGrouptree($this->Session->get('iso_products_gid'), -20);
            }

            // Predefined node set (see #3563)
            elseif (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['rootNodes']))
            {
                foreach ($this->eliminateNestedPages($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['rootNodes'], $this->strTable) as $node)
                {
                    $tree .= $this->renderGrouptree($node, -20);
                }
            }

            // Show all groups to admins
            elseif ($this->User->isAdmin)
            {
                $objGroup = \Database::getInstance()->execute("SELECT id FROM tl_iso_groups WHERE pid=0 ORDER BY sorting");

                while ($objGroup->next())
                {
                    $tree .= $this->renderGrouptree($objGroup->id, -20);
                }
            } else {
            	// Show only mounted groups to regular users
                foreach ($this->eliminateNestedPages($this->User->iso_groups, 'tl_iso_groups') as $node) {
                    $tree .= $this->renderGrouptree($node, -20);
                }
            }
        }

        // Select all checkboxes
        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'] == 'checkbox')
        {
            $strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="check_all_' . $this->strId . '" class="tl_change_selected">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="check_all_' . $this->strId . '" class="tl_tree_checkbox" value="" onclick="Backend.toggleCheckboxGroup(this,\'' . $this->strName . '\')"></div><div style="clear:both"></div></li>';
        }
        // Reset radio button selection
        else
        {
            $strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="reset_' . $this->strId . '" class="tl_change_selected">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="' . $this->strName . '" id="reset_' . $this->strName . '" class="tl_tree_radio" value="" onfocus="Backend.getScrollOffset()"></div><div style="clear:both"></div></li>';
        }

        // Return the tree
        return '<ul class="tl_listing tree_view picker_selector'.(($this->strClass != '') ? ' ' . $this->strClass : '').'" id="'.$this->strId.'">
    <li class="tl_folder_top"><div class="tl_left">'.\Image::getHtml($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['icon']).' '.$GLOBALS['TL_LANG']['tl_iso_groups']['label'].'</div> <div class="tl_right">&nbsp;</div><div style="clear:both"></div></li><li class="parent" id="'.$this->strId.'_parent"><ul>'.$tree.$strReset.'
  </ul></li></ul>';
    }


    /**
     * Generate a particular subpart of the group tree and return it as HTML string
     * @param integer
     * @param string
     * @param integer
     * @return string
     */
    public function generateAjax($id, $strField, $level)
    {
        if (!\Environment::get('isAjaxRequest'))
        {
            return '';
        }

        $this->strField = $strField;
        $this->loadDataContainer($this->strTable);

        // Load current values
        switch ($GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'])
        {
            case 'File':
                if ($GLOBALS['TL_CONFIG'][$this->strField] != '')
                {
                    $this->varValue = $GLOBALS['TL_CONFIG'][$this->strField];
                }
                break;

            case 'Table':
                if (!\Database::getInstance()->fieldExists($strField, $this->strTable))
                {
                    break;
                }

                $objField = \Database::getInstance()->prepare("SELECT " . $this->strField . " FROM " . $this->strTable . " WHERE id=?")->execute($this->strId);

                if ($objField->numRows)
                {
                    $this->varValue = deserialize($objField->$strField);
                }
                break;
        }

        $this->getPathNodes();

        // Load requested nodes
        $tree = '';
        $level = $level * 20;

        $objGroup = \Database::getInstance()->prepare("SELECT id FROM tl_iso_groups WHERE pid=? ORDER BY sorting")->execute($id);

        while ($objGroup->next())
        {
            $tree .= $this->renderGrouptree($objGroup->id, $level);
        }

        return $tree;
    }


    /**
     * Recursively render the grouptree
     * @param integer
     * @param integer
     * @return string
     */
    protected function renderGrouptree($id, $intMargin)
    {
        static $session;
        $session = $this->Session->getData();

        $flag = substr($this->strField, 0, 2).'g';
        $node = 'tree_' . $this->strTable . '_' . $this->strField;
        $xtnode = 'tree_' . $this->strTable . '_' . $this->strName;

        // Get session data and toggle nodes
        if (\Input::get($flag.'tg'))
        {
            $session[$node][\Input::get($flag.'tg')] = (isset($session[$node][\Input::get($flag.'tg')]) && $session[$node][\Input::get($flag.'tg')] == 1) ? 0 : 1;
            $this->Session->setData($session);
            \Controller::redirect(preg_replace('/(&(amp;)?|\?)'.$flag.'tg=[^& ]*/i', '', \Environment::get('request')));
        }

        $objGroup = \Database::getInstance()->prepare("SELECT * FROM tl_iso_groups WHERE id=?")->execute($id);

        // Return if there is no result
        if ($objGroup->numRows < 1)
        {
            return '';
        }

        $return = '';
        $intSpacing = 20;
        $childs = array();

        // Check whether there are child records
        if (!$blnNoRecursion)
        {
            $objNodes = \Database::getInstance()->prepare("SELECT id FROM tl_iso_groups WHERE pid=? ORDER BY sorting")->execute($id);

            if ($objNodes->numRows)
            {
                $childs = $objNodes->fetchEach('id');
            }
        }

        $return .= "\n    " . '<li class="tl_file" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);" onclick="Theme.toggleSelect(this)"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';

        $folderAttribute = 'style="margin-left:20px;"';
        $session[$node][$id] = is_numeric($session[$node][$id]) ? $session[$node][$id] : 0;
        $level = ($intMargin / $intSpacing + 1);
        $blnIsOpen = ($session[$node][$id] == 1 || in_array($id, $this->arrNodes));

        if (!empty($childs))
        {
            $folderAttribute = '';
            $img = $blnIsOpen ? 'folMinus.gif' : 'folPlus.gif';
            $alt = $blnIsOpen ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
            $return .= '<a href="'.$this->addToUrl($flag.'tg='.$id).'" title="'.specialchars($alt).'" onclick="Backend.getScrollOffset(); return Isotope.toggleProductGroupTree(this, \''.$xtnode.'_'.$id.'\', \''.$this->strField.'\', \''.$this->strName.'\', '.$level.');">'.\Image::getHtml($img, '', 'style="margin-right:2px;"').'</a>';
        }

        $href = '<a href="' . $this->addToUrl('gid='.$objGroup->id) . '" title="'.specialchars($objGroup->name . ' (ID ' . $objGroup->id . ')').'"'.(empty($childs) ? ' style="padding-left:20px;"' : '').'>'.$objGroup->name.'</a>';
        $return .= $this->tl_iso_groups->addIcon($objGroup->row(), $href, null, $folderAttribute).'</div> <div class="tl_right">';

        // Add checkbox or radio button
        switch ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'])
        {
            case 'checkbox':
                $return .= '<input type="checkbox" name="'.$this->strName.'[]" id="'.$this->strName.'_'.$id.'" class="tl_tree_checkbox" value="'.specialchars($id).'" onfocus="Backend.getScrollOffset()"'.static::optionChecked($id, $this->varValue).'>';
                break;

            default:
            case 'radio':
                $return .= '<input type="radio" name="'.$this->strName.'" id="'.$this->strName.'_'.$id.'" class="tl_tree_radio" value="'.specialchars($id).'" onfocus="Backend.getScrollOffset()"'.static::optionChecked($id, $this->varValue).'>';
                break;
        }

        $return .= '</div><div style="clear:both"></div></li>';

        // Begin a new submenu
        if (!empty($childs) && ($blnIsOpen || $this->Session->get('product_group_selector_search') != ''))
        {
            $return .= '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">';

            for ($k=0, $c=count($childs); $k<$c; $k++)
            {
                $return .= $this->renderGrouptree($childs[$k], ($intMargin + $intSpacing));
            }

            $return .= '</ul></li>';
        }

        return $return;
    }


    /**
     * Get the IDs of all parent groups of the selected groups, so they are expanded automatically
     */
    protected function getPathNodes()
    {
        if (!$this->varValue)
        {
            return;
        }

        if (!is_array($this->varValue))
        {
            $this->varValue = array($this->varValue);
        }

        foreach ($this->varValue as $id)
        {
            $arrPids = \Database::getInstance()->getParentRecords($id, 'tl_iso_groups');
            array_shift($arrPids); // the first element is the ID of the group itself
            $this->arrNodes = array_merge($this->arrNodes, $arrPids);
        }
    }
}
