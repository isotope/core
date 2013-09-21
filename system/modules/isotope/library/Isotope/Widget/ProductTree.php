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
 * Class ProductTree
 *
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class ProductTree extends \Widget
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
    protected $arrNodes = array('products'=>array(), 'groups'=>array());

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * Allowed product types
     * @var array
     */
    protected $arrTypes;

    /**
     * dca object to render rows
     * @var object
     */
    protected $dca;

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

        $this->loadDataContainer('tl_iso_products');
        \System::loadLanguageFile('tl_iso_products');

        $this->loadDataContainer('tl_iso_groups');
        \System::loadLanguageFile('tl_iso_groups');

        $this->import('BackendUser', 'User');
        $this->import('Isotope\ProductCallbacks', 'ProductCallbacks');
        $this->import('Isotope\tl_iso_groups', 'tl_iso_groups');

        $this->arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
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

        $tree = '';
        $this->getPathNodes();


        $objGroups = \Database::getInstance()->execute("SELECT id FROM tl_iso_groups WHERE pid=0 ORDER BY sorting");
        while( $objGroups->next() )
        {
            $tree .= $this->renderGroups($objGroups->id, -20);
        }

        $objProducts = \Database::getInstance()->execute("SELECT id FROM tl_iso_products WHERE pid=0 AND gid=0 AND language=''" . ($this->User->isAdmin ? '' : " AND type IN ('','" . implode("','", $this->arrTypes) . "')"));

        while ($objProducts->next())
        {
            $tree .= $this->renderProducts($objProducts->id, -20);
        }

        $strReset = '';

        // Reset radio button selection
        if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'] == 'radio')
        {
            $strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="reset_' . $this->strId . '" class="tl_change_selected">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="' . $this->strName . '" id="reset_' . $this->strName . '" class="tl_tree_radio" value="" onfocus="Backend.getScrollOffset();"></div><div style="clear:both;"></div></li>';
        }

        // Select all checkboxes
        elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'] == 'checkbox')
        {
            $strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="check_all_' . $this->strId . '" class="tl_change_selected">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="check_all_' . $this->strId . '" class="tl_tree_checkbox" value="" onclick="Backend.toggleCheckboxGroup(this, \'' . $this->strName . '\')"></div><div style="clear:both;"></div></li>';
        }

        // Return the tree
        return '<ul class="tl_listing tree_view product_tree'.(strlen($this->strClass) ? ' ' . $this->strClass : '').'" id="'.$this->strId.'">
    <li class="tl_folder_top"><div class="tl_left">'.\Image::getHtml('system/modules/isotope/assets/store-open.png').' '.(strlen($GLOBALS['TL_CONFIG']['websiteTitle']) ? $GLOBALS['TL_CONFIG']['websiteTitle'] : 'Contao Open Source CMS').'</div> <div class="tl_right"><label for="ctrl_'.$this->strId.'" class="tl_change_selected">'.$GLOBALS['TL_LANG']['MSC']['changeSelected'].'</label> <input type="checkbox" name="'.$this->strName.'_save" id="ctrl_'.$this->strId.'" class="tl_tree_checkbox" value="1" onclick="Backend.showTreeBody(this, \''.$this->strId.'_parent\');"></div><div style="clear:both;"></div></li><li class="parent" id="'.$this->strId.'_parent"><ul>'.$tree.$strReset.'
  </ul></li></ul>';
    }


    /**
     * Generate a particular subpart of the page tree and return it as HTML string
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
                if (strlen($GLOBALS['TL_CONFIG'][$this->strField]))
                {
                    $this->varValue = $GLOBALS['TL_CONFIG'][$this->strField];
                }
                break;

            case 'Table':
                if (!\Database::getInstance()->fieldExists($strField, $this->strTable))
                {
                    break;
                }

                $objField = \Database::getInstance()->prepare("SELECT " . $strField . " FROM " . $this->strTable . " WHERE id=?")->execute($this->strId);

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

        if (strpos(\Input::post('id'), 'groups') === false)
        {
            $objProducts = \Database::getInstance()->prepare("
                SELECT id FROM tl_iso_products WHERE language='' AND pid=?".($this->User->isAdmin ? '' : " AND type IN ('','" . implode("','", $this->arrTypes) . "')")." ORDER BY name
            ")->execute($id);

            while ($objProducts->next())
            {
                $tree .= $this->renderProducts($objProducts->id, $level);
            }
        }
        else
        {
            $objGroups = \Database::getInstance()->execute("SELECT id FROM tl_iso_groups WHERE pid=".$id." ORDER BY sorting");

            while ($objGroups->next())
            {
                $tree .= $this->renderGroups($objGroups->id, $level);
            }

            $objProducts = \Database::getInstance()->prepare("
                SELECT id FROM tl_iso_products WHERE language='' AND gid=?".($this->User->isAdmin ? '' : " AND type IN ('','" . implode("','", $this->arrTypes) . "')")." ORDER BY name
            ")->execute($id);

            while ($objProducts->next())
            {
                $tree .= $this->renderProducts($objProducts->id, $level);
            }
        }

        return $tree;
    }


    /**
     * Recursively render product groups
     *
     * @param int
     * @param integer
     * @return string
     */
    protected function renderGroups($id, $intMargin)
    {
        static $session;
        $session = $this->Session->getData();

        $flag = substr($this->strField, 0, 2).'g';
        $node = 'tree_' . $this->strTable . '_' . $this->strField . '_groups';
        $xtnode = 'tree_' . $this->strTable . '_' . $this->strName . '_groups';

        // Get session data and toggle nodes
        if (\Input::get($flag.'tg'))
        {
            $session[$node][\Input::get($flag.'tg')] = (isset($session[$node][\Input::get($flag.'tg')]) && $session[$node][\Input::get($flag.'tg')] == 1) ? 0 : 1;
            $this->Session->setData($session);

            \Controller::redirect(preg_replace('/(&(amp;)?|\?)'.$flag.'tg=[^& ]*/i', '', \Environment::get('request')));
        }

        $objGroup = \Database::getInstance()->execute("SELECT * FROM tl_iso_groups WHERE id=$id");

        // Return if there is no result
        if ($objGroup->numRows < 1)
        {
            return '';
        }

        $return = '';
        $intSpacing = 20;

        // Check whether there are child groups
        $childs = \Database::getInstance()->prepare("SELECT id FROM tl_iso_groups WHERE pid=? ORDER BY sorting")->execute($id)->fetchEach('id');

        // Check whether there are child products
        $products = \Database::getInstance()->prepare("SELECT id FROM tl_iso_products WHERE gid=?".($this->User->isAdmin ? '' : " AND type IN ('','" . implode("','", $this->arrTypes) . "')")." ORDER BY name")->execute($id)->fetchEach('id');

        if (empty($products) && empty($childs))
        {
            return '';
        }

        $return .= "\n    " . '<li class="tl_folder" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';

        $session[$node][$id] = is_numeric($session[$node][$id]) ? $session[$node][$id] : 0;
        $level = ($intMargin / $intSpacing + 1);
        $blnIsOpen = ($session[$node][$id] == 1 || in_array($id, $this->arrNodes['groups']));

        if (!empty($childs) || !empty($products))
        {
            $img = $blnIsOpen ? 'folMinus.gif' : 'folPlus.gif';
            $alt = $blnIsOpen ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
            $return .= '<a href="'.$this->addToUrl($flag.'tg='.$id).'" title="'.specialchars($alt).'" onclick="Backend.getScrollOffset(); return Isotope.toggleProductTree(this, \''.$xtnode.'_'.$id.'\', \''.$this->strField.'\', \''.$this->strName.'\', '.$level.');">'.\Image::getHtml($img, '', 'style="margin-right:2px;"').'</a>';
        }

        // Add group name
        $return .= $this->tl_iso_groups->addIcon($objGroup->row(), $objGroup->name).'</div><div style="clear:both;"></div></li>';

        // Add child products
        if ((!empty($products) || !empty($childs)) && $blnIsOpen)
        {
            $group = '';
            $return .= '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">';

            if (!empty($products))
            {
                for ($k=0, $count=count($products); $k<$count; $k++)
                {
                    $group .= $this->renderProducts($products[$k], ($intMargin + $intSpacing));
                }
            }

            if (!empty($childs))
            {
                for ($k=0, $count=count($childs); $k<$count; $k++)
                {
                    $group .= $this->renderGroups($childs[$k], ($intMargin + $intSpacing));
                }
            }

            if ($group == '')
                return '';

            $return .= $group.'</ul></li>';
        }

        return $return;
    }


    /**
     * Recursively render the product tree
     *
     * @param int
     * @param integer
     * @return string
     */
    protected function renderProducts($id, $intMargin)
    {
        static $session;
        $session = $this->Session->getData();

        $flag = substr($this->strField, 0, 2);
        $node = 'tree_' . $this->strTable . '_' . $this->strField;
        $xtnode = 'tree_' . $this->strTable . '_' . $this->strName;

        // Get session data and toggle nodes
        if (\Input::get($flag.'tg'))
        {
            $session[$node][\Input::get($flag.'tg')] = (isset($session[$node][\Input::get($flag.'tg')]) && $session[$node][\Input::get($flag.'tg')] == 1) ? 0 : 1;
            $this->Session->setData($session);

            \Controller::redirect(preg_replace('/(&(amp;)?|\?)'.$flag.'tg=[^& ]*/i', '', \Environment::get('request')));
        }

        $objProduct = \Database::getInstance()->prepare("SELECT * FROM tl_iso_products WHERE id=?".($this->User->isAdmin ? '' : " AND type IN ('','" . implode("','", $this->arrTypes) . "')"))->execute($id);

        // Return if there is no result
        if ($objProduct->numRows < 1)
        {
            return '';
        }

        $return = '';
        $intSpacing = 20;
        $childs = array();

        if ($this->variants)
        {
            // Check whether there are child records
            $objNodes = \Database::getInstance()->prepare("SELECT id FROM tl_iso_products WHERE pid=? AND language=''")->execute($id);

            if ($objNodes->numRows)
            {
                $childs = $objNodes->fetchEach('id');
            }
        }

        $return .= "\n    " . '<li class="tl_file" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';

        $session[$node][$id] = is_numeric($session[$node][$id]) ? $session[$node][$id] : 0;
        $level = ($intMargin / $intSpacing + 1);
        $blnIsOpen = ($session[$node][$id] == 1 || in_array($id, $this->arrNodes['products']));

        if (!empty($childs))
        {
            $img = $blnIsOpen ? 'folMinus.gif' : 'folPlus.gif';
            $alt = $blnIsOpen ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
            $return .= '<a href="'.$this->addToUrl($flag.'tg='.$id).'" title="'.specialchars($alt).'" onclick="Backend.getScrollOffset(); return Isotope.toggleProductTree(this, \''.$xtnode.'_'.$id.'\', \''.$this->strField.'\', \''.$this->strName.'\', '.$level.');">'.\Image::getHtml($img, '', 'style="margin-right:2px;"').'</a>';
        }

        // Add product name
        $return .= $this->ProductCallbacks->getRowLabel($objProduct->row()).'</div> <div class="tl_right">';

        if (empty($childs) || $objProduct->pid > 0 || !$this->variantsOnly)
        {
            // Add checkbox or radio button
            switch ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'])
            {
                case 'checkbox':
                    $return .= '<input type="checkbox" name="'.$this->strName.'[]" id="'.$this->strName.'_'.$id.'" class="tl_tree_checkbox" value="'.specialchars($id).'" onfocus="Backend.getScrollOffset();"'.$this->optionChecked($id, $this->varValue).'>';
                    break;

                case 'radio':
                    $return .= '<input type="radio" name="'.$this->strName.'" id="'.$this->strName.'_'.$id.'" class="tl_tree_radio" value="'.specialchars($id).'" onfocus="Backend.getScrollOffset();"'.$this->optionChecked($id, $this->varValue).'>';
                    break;

                case 'text':
                    $style = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['style'] ? ' style="'.$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['style'].'"' : '';
                    $maxlength = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['maxlength'] ? ' maxlength="'.$GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['maxlength'].'"' : '';
                    $return .= '<input type="text" class="text" name="'.$this->strName.'['.$id.']" id="'.$this->strName.'_'.$id.'" class="tl_tree_radio" value="'.specialchars($this->varValue[$id]).'" onfocus="Backend.getScrollOffset();"'.$style.$maxlength.'>';
                    break;
            }
        }

        $return .= '</div><div style="clear:both;"></div></li>';

        // Begin new submenu
        if (!empty($childs) && $blnIsOpen)
        {
            $return .= '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">';

            for ($k=0, $count=count($childs); $k<$count; $k++)
            {
                $return .= $this->renderProducts($childs[$k], ($intMargin + $intSpacing));
            }

            $return .= '</ul></li>';
        }

        return $return;
    }


    /**
     * Get the IDs of all parent products and groups of the selected product
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
            do
            {
                $objProduct = \Database::getInstance()->prepare("SELECT pid,gid FROM tl_iso_products WHERE id=?")->limit(1)->execute($id);

                if ($objProduct->numRows < 1)
                {
                    break;
                }

                // Path has been calculated already
                if (in_array($objProduct->pid, $this->arrNodes['products']) && in_array($objProduct->gid, $this->arrNodes['groups']))
                {
                    break;
                }

                // Add pid to the nodes array
                if ($objProduct->pid > 0)
                {
                    $this->arrNodes['products'][] = $objProduct->pid;
                }

                // Add pid to the nodes array
                if ($objProduct->gid > 0)
                {
                    $this->arrNodes['groups'][] = $objProduct->gid;
                }

                $id = $objProduct->pid;
            }
            while ($objProduct->pid > 0);
        }
    }
}
