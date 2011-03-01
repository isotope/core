<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


class ProductTree extends Widget
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
		$this->loadLanguageFile('tl_iso_products');
		
		$this->import('Database');
		$this->import('tl_iso_products', 'dca');
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
		if (!$this->Input->post($this->strName.'_save'))
		{
			$this->mandatory = false;
			$this->blnSubmitInput = false;
		}

		return parent::validator($varInput);
	}
	
	
	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/backend_src.js';
		$GLOBALS['TL_CSS'][] = 'system/modules/isotope/html/backend_src.css';
		
		$this->import('BackendUser', 'User');

		$tree = '';
		$this->getPathNodes();


		$arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
		$objProduct = $this->Database->execute("SELECT id FROM tl_iso_products WHERE pid=0 AND language='' AND archive<2" . ($this->User->isAdmin ? '' : " AND type IN ('','" . implode("','", $arrTypes) . "')"));

		while ($objProduct->next())
		{
			$tree .= $this->renderProductTree($objProduct->id, -20);
		}

		$strReset = '';

		// Reset radio button selection
		if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'] == 'radio')
		{
			$strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="reset_' . $this->strId . '" class="tl_change_selected">' . $GLOBALS['TL_LANG']['MSC']['resetSelected'] . '</label> <input type="radio" name="' . $this->strName . '" id="reset_' . $this->strName . '" class="tl_tree_radio" value="" onfocus="Backend.getScrollOffset();" /></div><div style="clear:both;"></div></li>';
		}

		// Select all checkboxes
		elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'] == 'checkbox')
		{
			$strReset = "\n" . '    <li class="tl_folder"><div class="tl_left">&nbsp;</div> <div class="tl_right"><label for="check_all_' . $this->strId . '" class="tl_change_selected">' . $GLOBALS['TL_LANG']['MSC']['selectAll'] . '</label> <input type="checkbox" id="check_all_' . $this->strId . '" class="tl_tree_checkbox" value="" onclick="Backend.toggleCheckboxGroup(this, \'' . $this->strName . '\')" /></div><div style="clear:both;"></div></li>';
		}

		// Return the tree
		return '<ul class="tl_listing tree_view product_tree'.(strlen($this->strClass) ? ' ' . $this->strClass : '').'" id="'.$this->strId.'">
    <li class="tl_folder_top"><div class="tl_left">'.$this->generateImage((strlen($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['icon']) ? $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['icon'] : 'pagemounts.gif')).' '.(strlen($GLOBALS['TL_CONFIG']['websiteTitle']) ? $GLOBALS['TL_CONFIG']['websiteTitle'] : 'Contao Open Source CMS').'</div> <div class="tl_right"><label for="ctrl_'.$this->strId.'" class="tl_change_selected">'.$GLOBALS['TL_LANG']['MSC']['changeSelected'].'</label> <input type="checkbox" name="'.$this->strName.'_save" id="ctrl_'.$this->strId.'" class="tl_tree_checkbox" value="1" onclick="Backend.showTreeBody(this, \''.$this->strId.'_parent\');" /></div><div style="clear:both;"></div></li><li class="parent" id="'.$this->strId.'_parent"><ul>'.$tree.$strReset.'
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
		if (!$this->Input->post('isAjax'))
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
				if (!$this->Database->fieldExists($strField, $this->strTable))
				{
					break;
				}

				$objField = $this->Database->prepare("SELECT " . $strField . " FROM " . $this->strTable . " WHERE id=?")
										   ->limit(1)
										   ->execute($this->strId);

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

		$objProducts = $this->Database->execute("SELECT id FROM tl_iso_products WHERE language='' AND archive<2 AND pid=".$id);

		while ($objProducts->next())
		{
			$tree .= $this->renderProductTree($objProducts->id, $level);
		}

		return $tree;
	}


	/**
	 * Check the Ajax pre actions
	 * @param string
	 * @param object
	 * @return string
	 */
	public function executePreActions($action)
	{
		switch ($action)
		{
			// Toggle nodes of the file or page tree
			case 'toggleProductTree':
				$this->strAjaxId = preg_replace('/.*_([0-9a-zA-Z]+)$/i', '$1', $this->Input->post('id'));
				$this->strAjaxKey = str_replace('_' . $this->strAjaxId, '', $this->Input->post('id'));

				if ($this->Input->get('act') == 'editAll')
				{
					$this->strAjaxKey = preg_replace('/(.*)_[0-9a-zA-Z]+$/i', '$1', $this->strAjaxKey);
					$this->strAjaxName = preg_replace('/.*_([0-9a-zA-Z]+)$/i', '$1', $this->Input->post('name'));
				}

				$nodes = $this->Session->get($this->strAjaxKey);
				$nodes[$this->strAjaxId] = intval($this->Input->post('state'));

				$this->Session->set($this->strAjaxKey, $nodes);
				exit; break;

			// Load nodes of the file or page tree
			case 'loadProductTree':
				$this->strAjaxId = preg_replace('/.*_([0-9a-zA-Z]+)$/i', '$1', $this->Input->post('id'));
				$this->strAjaxKey = str_replace('_' . $this->strAjaxId, '', $this->Input->post('id'));

				if ($this->Input->get('act') == 'editAll')
				{
					$this->strAjaxKey = preg_replace('/(.*)_[0-9a-zA-Z]+$/i', '$1', $this->strAjaxKey);
					$this->strAjaxName = preg_replace('/.*_([0-9a-zA-Z]+)$/i', '$1', $this->Input->post('name'));
				}

				$nodes = $this->Session->get($this->strAjaxKey);
				$nodes[$this->strAjaxId] = intval($this->Input->post('state'));

				$this->Session->set($this->strAjaxKey, $nodes);
				break;
		}
	}


	/**
	 * Check the Ajax post actions
	 * @param string
	 * @param object
	 * @return string
	 */
	public function executePostActions($action, $dc)
	{
		if ($action == 'loadProductTree')
		{
			$arrData['strTable'] = $dc->table;
			$arrData['id'] = strlen($this->strAjaxName) ? $this->strAjaxName : $dc->id;
			$arrData['name'] = $this->Input->post('name');
		
			$objWidget = new $GLOBALS['BE_FFL']['productTree']($arrData, $dc);

			echo $objWidget->generateAjax($this->strAjaxId, $this->Input->post('field'), intval($this->Input->post('level')));
			exit;
		}
	}
	
	
	/**
	 * Recursively render the pagetree
	 * @param int
	 * @param integer
	 * @param boolean
	 * @return string
	 */
	protected function renderProductTree($id, $intMargin)
	{
		static $session;
		$session = $this->Session->getData();

		$flag = substr($this->strField, 0, 2);
		$node = 'tree_' . $this->strTable . '_' . $this->strField;
		$xtnode = 'tree_' . $this->strTable . '_' . $this->strName;

		// Get session data and toggle nodes
		if ($this->Input->get($flag.'tg'))
		{
			$session[$node][$this->Input->get($flag.'tg')] = (isset($session[$node][$this->Input->get($flag.'tg')]) && $session[$node][$this->Input->get($flag.'tg')] == 1) ? 0 : 1;
			$this->Session->setData($session);

			$this->redirect(preg_replace('/(&(amp;)?|\?)'.$flag.'tg=[^& ]*/i', '', $this->Environment->request));
		}

		$objProduct = $this->Database->execute("SELECT * FROM tl_iso_products WHERE id=".$id);

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
			$objNodes = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE pid=? AND language='' AND archive<2")
									   ->execute($id);
	
			if ($objNodes->numRows)
			{
				$childs = $objNodes->fetchEach('id');
			}
		}

		$return .= "\n    " . '<li class="tl_file" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';

		$folderAttribute = 'style="margin-left:20px;"';
		$session[$node][$id] = is_numeric($session[$node][$id]) ? $session[$node][$id] : 0;
		$level = ($intMargin / $intSpacing + 1);
		$blnIsOpen = ($session[$node][$id] == 1 || in_array($id, $this->arrNodes));

		if (count($childs))
		{
			$folderAttribute = '';
			$img = $blnIsOpen ? 'folMinus.gif' : 'folPlus.gif';
			$return .= '<a href="'.$this->addToUrl($flag.'tg='.$id).'" onclick="Backend.getScrollOffset(); return Isotope.toggleProductTree(this, \''.$xtnode.'_'.$id.'\', \''.$this->strField.'\', \''.$this->strName.'\', '.$level.');">'.$this->generateImage($img, '', 'style="margin-right:2px;"').'</a>';
		}

		$sub = 0;
		$image = 'system/modules/isotope/html/icon-products.gif';

		// Add page name
		$return .= $this->dca->getRowLabel($objProduct->row()).'</div> <div class="tl_right">';

		if (!count($childs) || $objProduct->pid > 0 || !$this->variantsOnly)
		{
			// Add checkbox or radio button
			switch ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['fieldType'])
			{
				case 'checkbox':
					$return .= '<input type="checkbox" name="'.$this->strName.'[]" id="'.$this->strName.'_'.$id.'" class="tl_tree_checkbox" value="'.specialchars($id).'" onfocus="Backend.getScrollOffset();"'.$this->optionChecked($id, $this->varValue).' />';
					break;
	
				case 'radio':
					$return .= '<input type="radio" name="'.$this->strName.'" id="'.$this->strName.'_'.$id.'" class="tl_tree_radio" value="'.specialchars($id).'" onfocus="Backend.getScrollOffset();"'.$this->optionChecked($id, $this->varValue).' />';
					break;
			}
		}

		$return .= '</div><div style="clear:both;"></div></li>';

		// Begin new submenu
		if (count($childs) && $blnIsOpen)
		{
			$return .= '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">';

			for ($k=0; $k<count($childs); $k++)
			{
				$return .= $this->renderProductTree($childs[$k], ($intMargin + $intSpacing));
			}

			$return .= '</ul></li>';
		}

		return $return;
	}
	
	
	/**
	 * Get the IDs of all parent products of the selected product
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
				$objProduct = $this->Database->prepare("SELECT pid FROM tl_iso_products WHERE id=?")
											 ->limit(1)
											 ->execute($id);

				if ($objProduct->numRows < 1)
				{
					break;
				}

				// Path has been calculated already
				if (in_array($objProduct->pid, $this->arrNodes))
				{
					break;
				}

				// Add pid to the nodes array
				if ($objProduct->pid > 0)
				{
					$this->arrNodes[] = $objProduct->pid;
				}

				$id = $objProduct->pid;
			}
			while ($objProduct->pid > 0);
		}
	}
}

