<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


require_once(TL_ROOT . '/system/drivers/DC_Table.php');

class DC_MultilingualTable extends DC_Table
{
	
	/**
	 * True if we are editing a language
	 */
	protected $blnEditLanguage;
	
	
	/**
	 * Array of languages for this product's type
	 */
	protected $arrLanguages;
	
	
	/**
	 * Autogenerate a form to edit the current database record
	 * @param integer
	 * @param integer
	 * @return string
	 */
	public function edit($intID=false, $ajaxId=false)
	{
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['notEditable'])
		{
			$this->log('Table ' . $this->strTable . ' is not editable', 'DC_MultilingualTable edit()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		if ($intID)
		{
			$this->intId = $intID;
		}

		$return = '';
		$this->values[] = $this->intId;
		$this->procedure[] = 'id=?';
		$this->blnCreateNewVersion = false;
		$this->blnEditLanguage = false;
		
		// Get current record
		$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE id=?")
								 ->limit(1)
								 ->executeUncached($this->intId);

		// Redirect if there is no record with the given ID
		if ($objRow->numRows < 1)
		{
			$this->log('Could not load record ID "'.$this->intId.'" of table "'.$this->strTable.'"!', 'DC_MultilingualTable edit()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}
		
		// ID of a language record is not allowed
		elseif (strlen($objRow->language))
		{
			$this->log('Cannot edit language record ID "'.$this->intId.'" of table "'.$this->strTable.'"!', 'DC_MultilingualTable edit()', TL_ERROR);
			$this->redirect('typolight/main.php?act=error');
		}

		$this->objActiveRecord = $objRow;

		// Load and/or change language
		$arrLangTable = explode('.', $GLOBALS['TL_DCA'][$this->strTable]['config']['ltable']);
		$objLangTable = $this->Database->prepare("SELECT * FROM " . $arrLangTable[0] . " WHERE id=?")->execute($this->activeRecord->{$GLOBALS['TL_DCA'][$this->strTable]['config']['lref']});
		$this->arrLanguages = deserialize($objLangTable->{$arrLangTable[1]});
		if (is_array($this->arrLanguages) && count($this->arrLanguages))
		{
			if ($this->Input->post('FORM_SUBMIT') == 'tl_language')
			{
				$session = $this->Session->getData();
				
				if (in_array($this->Input->post('language'), $this->arrLanguages))
				{
					$session['language'][$this->strTable][$this->intId] = $this->Input->post('language');
					
					if (strlen($this->Input->post('deleteLanguage')))
					{
						$this->Database->prepare("DELETE FROM " . $this->strTable . " WHERE pid=? AND language=?")->execute($this->intId, $this->Input->post('language'));
						unset($session['language'][$this->strTable][$this->intId]);
					}
				}
				else
				{
					unset($session['language'][$this->strTable][$this->intId]);
				}
				
				$this->Session->setData($session);
				$_SESSION['TL_INFO'] = '';
				$this->reload();
			}
			
			if (strlen($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]) && in_array($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId], $this->arrLanguages))
			{
				$objRow = $this->Database->prepare("SELECT * FROM " . $this->strTable . " WHERE pid=? AND language=?")->execute($this->intId, $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]);
				
				if (!$objRow->numRows)
				{
					$intId = $this->Database->prepare("INSERT INTO tl_product_data (pid,tstamp,language) VALUES (?,?,?)")->execute($this->intId, time(), $_SESSION['BE_DATA']['language'][$this->strTable][$this->intId])->insertId;
					
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
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'] && $this->Input->post('FORM_SUBMIT') == 'tl_version' && strlen($this->Input->post('version')))
		{
			$objData = $this->Database->prepare("SELECT * FROM tl_version WHERE fromTable=? AND pid=? AND version=?")
									  ->limit(1)
									  ->execute($this->strTable, $this->objActiveRecord->id, $this->Input->post('version'));

			if ($objData->numRows)
			{
				$data = deserialize($objData->data);

				if (is_array($data))
				{
					$this->Database->prepare("UPDATE " . $objData->fromTable . " %s WHERE id=?")
								   ->set($data)
								   ->execute($this->objActiveRecord->id);

					$this->Database->prepare("UPDATE tl_version SET active='' WHERE pid=?")
								   ->execute($this->objActiveRecord->id);

					$this->Database->prepare("UPDATE tl_version SET active=1 WHERE pid=? AND version=?")
								   ->execute($this->objActiveRecord->id, $this->Input->post('version'));

					$this->log(sprintf('Version %s of record ID %s (table %s) has been restored', $this->Input->post('version'), $this->objActiveRecord->id, $this->strTable), 'DC_Table edit()', TL_GENERAL);
				}
			}

			$this->reload();
		}
		

		// Build an array from boxes and rows
		$this->strPalette = $this->getPalette();
		$boxes = trimsplit(';', $this->strPalette);
		$legends = array();

		if (count($boxes))
		{
			foreach ($boxes as $k=>$v)
			{
				$eCount = 1;
				$boxes[$k] = trimsplit(',', $v);

				foreach ($boxes[$k] as $kk=>$vv)
				{
					if (preg_match('/^\[.*\]$/i', $vv))
					{
						++$eCount;
						continue;
					}

					if (preg_match('/^\{.*\}$/i', $vv))
					{
						$legends[$k] = substr($vv, 1, -1);
						unset($boxes[$k][$kk]);
					}

					elseif ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['exclude'] || !is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]))
					{
						unset($boxes[$k][$kk]);
					}
					
					elseif ($this->blnEditLanguage && !$GLOBALS['TL_DCA'][$this->strTable]['fields'][$vv]['attributes']['multilingual'])
					{
						unset($boxes[$k][$kk]);
					}
				}

				// Unset a box if it does not contain any fields
				if (count($boxes[$k]) < $eCount)
				{
					unset($boxes[$k]);
				}
			}

			$class = 'tl_tbox block';
			$fs = $this->Session->get('fieldset_states');

			// Render boxes
			foreach ($boxes as $k=>$v)
			{
				$strAjax = '';
				$blnAjax = false;
				$legend = '';

				if (isset($legends[$k]))
				{
					list($key, $cls) = explode(':', $legends[$k]);
					$legend = "\n" . '<legend onclick="AjaxRequest.toggleFieldset(this, \'' . $key . '\', \'' . $this->strTable . '\')">' . (isset($GLOBALS['TL_LANG'][$this->strTable][$key]) ? $GLOBALS['TL_LANG'][$this->strTable][$key] : $key) . '</legend>';
				}

				if (!$GLOBALS['TL_CONFIG']['oldBeTheme'])
				{
					if (isset($fs[$this->strTable][$key]))
					{
						$class .= ($fs[$this->strTable][$key] ? '' : ' collapsed');
					}
					else
					{
						$class .= (($cls && $legend) ? ' ' . $cls : '');
					}

					$return .= "\n\n" . '<fieldset' . ($key ? ' id="pal_'.$key.'"' : '') . ' class="' . $class . ($legend ? '' : ' nolegend') . '">' . $legend;
				}
				else
				{
					$return .= "\n\n" . '<div class="'.$class.'">';
				}

				// Build rows of the current box
				foreach ($v as $kk=>$vv)
				{
					if ($vv == '[EOF]')
					{
						if ($this->Input->post('isAjax') && $blnAjax)
						{
							return $strAjax . '<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'" />';
						}

						$blnAjax = false;
						$return .= "\n" . '</div>';

						continue;
					}

					if (preg_match('/^\[.*\]$/i', $vv))
					{
						$thisId = 'sub_' . substr($vv, 1, -1);
						$blnAjax = ($this->Input->post('isAjax') && $ajaxId == $thisId) ? true : false;
						$return .= "\n" . '<div id="'.$thisId.'">';

						continue;
					}

					$this->strField = $vv;
					$this->strInputName = $vv;
					$this->varValue = $this->objActiveRecord->$vv;

					// Call load_callback
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback']))
					{
						foreach ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['load_callback'] as $callback)
						{
							if (is_array($callback))
							{
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

				if (!$GLOBALS['TL_CONFIG']['oldBeTheme'])
				{
					$return .= "\n" . '</fieldset>';
				}
				else
				{
					$return .= "\n" . '</div>';
				}
			}
		}
		
		$version = '';

		// Check versions
		if ($GLOBALS['TL_DCA'][$this->strTable]['config']['enableVersioning'])
		{
			$objVersion = $this->Database->prepare("SELECT tstamp, version, username, active FROM tl_version WHERE fromTable=? AND pid=? ORDER BY version DESC")
									     ->execute($this->strTable, $this->objActiveRecord->id);

			if ($objVersion->numRows > 1)
			{
				$versions = '';

				while ($objVersion->next())
				{
					$versions .= '
  <option value="'.$objVersion->version.'"'.($objVersion->active ? ' selected="selected"' : '').'>'.$GLOBALS['TL_LANG']['MSC']['version'].' '.$objVersion->version.' ('.$this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objVersion->tstamp).') '.$objVersion->username.'</option>';
				}

				$version = '<form action="'.ampersand($this->Environment->request, true).'" id="tl_version" class="tl_form" method="post" style="float:right;margin-left:20px">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_version" />
<select name="version" class="tl_select">'.$versions.'
</select>
<input type="submit" name="showVersion" id="showVersion" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['restore']).'" />
</div>
</form>';
			}
		}
		
		// Check languages
		if (is_array($this->arrLanguages) && count($this->arrLanguages))
		{
			$arrAvailableLanguages = $this->Database->prepare("SELECT language FROM " . $this->strTable . " WHERE pid=?")->execute($this->intId)->fetchEach('language');
			$languages = '';
			$arrLanguageLabels = $this->getLanguages();
			
			foreach( $this->arrLanguages as $language )
			{
				if ($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId] == $language)
				{
					$languages .= '<option value="' . $language . '" selected="selected">' . $arrLanguageLabels[$language] .'</option>';
					$_SESSION['TL_INFO'] = array($GLOBALS['TL_LANG']['MSC']['editingLanguage']);
					continue;
				}
				
				$languages .= '<option value="' . $language . '">' . $arrLanguageLabels[$language] . (in_array($language, $arrAvailableLanguages) ? '' : ' ('.$GLOBALS['TL_LANG']['MSC']['undefinedLanguage'].')') . '</option>';
			}
			
			$version .= '<form action="'.ampersand($this->Environment->request, true).'" id="tl_language" class="tl_form" method="post">
<div class="tl_formbody">
<input type="hidden" name="FORM_SUBMIT" value="tl_language" />
<strong>' . $GLOBALS['TL_LANG']['MSC']['labelLanguage'] . ':</strong>
<select name="language" class="tl_select' . (strlen($_SESSION['BE_DATA']['language'][$this->strTable][$this->intId]) ? ' active' : '') . '">
	<option value="">' . $GLOBALS['TL_LANG']['MSC']['defaultLanguage'] . '</option>'.$languages.'
</select>
<input type="submit" name="editLanguage" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['editLanguage']).'" />
<input type="submit" name="deleteLanguage" class="tl_submit" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['deleteLanguage']).'" onclick="return confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteLanguageConfirm'] . '\')" />
</div>
</form>';
		}
		
		if (strlen($version))
		{
			$version = '
<div class="tl_version_panel tl_panel">

'.$version.'

</div>';
		}

		// Add some buttons and end the form
		$return .= '
</div>

<div class="tl_formbody_submit">

<div class="tl_submit_container">
<input type="submit" name="save" id="save" class="tl_submit" accesskey="s" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['save']).'" />
<input type="submit" name="saveNclose" id="saveNclose" class="tl_submit" accesskey="c" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNclose']).'" />' . (!$GLOBALS['TL_DCA'][$this->strTable]['config']['closed'] ? '
<input type="submit" name="saveNcreate" id="saveNcreate" class="tl_submit" accesskey="n" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNcreate']).'" />' : '') . ($this->Input->get('s2e') ? '
<input type="submit" name="saveNedit" id="saveNedit" class="tl_submit" accesskey="e" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNedit']).'" />' : (($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4 || strlen($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) || $GLOBALS['TL_DCA'][$this->strTable]['config']['switchToEdit']) ? '
<input type="submit" name="saveNback" id="saveNback" class="tl_submit" accesskey="g" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['saveNback']).'" />' : '')) .'
</div>

</div>
</form>';

		// Begin the form (-> DO NOT CHANGE THIS ORDER -> this way the onsubmit attribute of the form can be changed by a field)
		$return = $version . '
<div id="tl_buttons">
<a href="'.$this->getReferer(true).'" class="header_back" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['backBT']).'" accesskey="b" onclick="Backend.getScrollOffset();">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>

<h2 class="sub_headline">'.sprintf($GLOBALS['TL_LANG']['MSC']['editRecord'], ($this->intId ? 'ID '.$this->intId : '')).'</h2>'.$this->getMessages().'

<form action="'.ampersand($this->Environment->request, true).'" id="'.$this->strTable.'" class="tl_form" method="post" enctype="' . ($this->blnUploadable ? 'multipart/form-data' : 'application/x-www-form-urlencoded') . '"'.(count($this->onsubmit) ? ' onsubmit="'.implode(' ', $this->onsubmit).'"' : '').'>
<div class="tl_formbody_edit">
<input type="hidden" name="FORM_SUBMIT" value="'.specialchars($this->strTable).'" />
<input type="hidden" name="FORM_FIELDS[]" value="'.specialchars($this->strPalette).'" />'.($this->noReload ? '

<p class="tl_error">'.$GLOBALS['TL_LANG']['ERR']['general'].'</p>' : '').$return;

		// Reload the page to prevent _POST variables from being sent twice
		if ($this->Input->post('FORM_SUBMIT') == $this->strTable && !$this->noReload)
		{
			$arrValues = $this->values;
			array_unshift($arrValues, time());

			// Call onsubmit_callback
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback']))
			{
				foreach ($GLOBALS['TL_DCA'][$this->strTable]['config']['onsubmit_callback'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($this);
				}
			}
			
			// Save current version
			if ($this->blnCreateNewVersion && $this->Input->post('SUBMIT_TYPE') != 'auto')
			{
				$this->createNewVersion($this->strTable, $this->objActiveRecord->id);
				$this->log(sprintf('A new version of %s ID %s has been created', $this->strTable, $this->objActiveRecord->id), 'DC_Table edit()', TL_GENERAL);
			}

			// Set current timestamp (-> DO NOT CHANGE ORDER version - timestamp)
			$this->Database->prepare("UPDATE " . $this->strTable . " SET tstamp=? WHERE id=?")
						   ->execute(time(), $this->activeRecord->id);

			// Redirect
			if (isset($_POST['saveNclose']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$this->redirect($this->getReferer());
			}

			elseif (isset($_POST['saveNedit']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$strUrl = $this->addToUrl($GLOBALS['TL_DCA'][$this->strTable]['list']['operations']['edit']['href']);

				$strUrl = preg_replace('/(&amp;)?s2e=[^&]*/i', '', $strUrl);
				$strUrl = preg_replace('/(&amp;)?act=[^&]*/i', '', $strUrl);

				$this->redirect($strUrl);
			}

			elseif (isset($_POST['saveNback']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$this->redirect($this->Environment->script . '?do=' . $this->Input->get('do'));
			}

			elseif (isset($_POST['saveNcreate']))
			{
				$_SESSION['TL_INFO'] = '';
				$_SESSION['TL_ERROR'] = '';
				$_SESSION['TL_CONFIRM'] = '';

				setcookie('BE_PAGE_OFFSET', 0, 0, '/');
				$strUrl = $this->Environment->script . '?do=' . $this->Input->get('do');

				if (isset($_GET['table']))
				{
					$strUrl .= '&amp;table=' . $this->Input->get('table');
				}

				// Tree view
				if ($this->treeView)
				{
					$strUrl .= '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId;
				}

				// Parent view
				elseif ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 4)
				{
					$strUrl .= $this->Database->fieldExists('sorting', $this->strTable) ? '&amp;act=create&amp;mode=1&amp;pid=' . $this->intId . '&amp;id=' . CURRENT_ID : '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID;
				}

				// List view
				else
				{
					$strUrl .= strlen($GLOBALS['TL_DCA'][$this->strTable]['config']['ptable']) ? '&amp;act=create&amp;mode=2&amp;pid=' . CURRENT_ID : '&amp;act=create';
				}

				$this->redirect($strUrl);
			}

			$this->reload();
		}

		// Set the focus if there is an error
		if ($this->noReload)
		{
			$return .= '

<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent(\'domready\', function()
{
    Backend.vScrollTo(($(\'' . $this->strTable . '\').getElement(\'label.error\').getPosition().y - 20));
});
//--><!]]>
</script>';
		}

		return $return;
	}
	
	
	
	/**
	 * Recursively generate the tree and return it as HTML string
	 * @param string
	 * @param integer
	 * @param array
	 * @param boolean
	 * @param integer
	 * @param array
	 * @param boolean
	 * @param boolean
	 * @return string
	 */
	protected function generateTree($table, $id, $arrPrevNext, $blnHasSorting, $intMargin=0, $arrClipboard=false, $blnCircularReference=false, $protectedPage=false)
	{
		static $session;

		$session = $this->Session->getData();
		$node = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $this->strTable.'_'.$table.'_tree' : $this->strTable.'_tree';

		// Toggle nodes
		if ($this->Input->get('ptg'))
		{
			$session[$node][$this->Input->get('ptg')] = (isset($session[$node][$this->Input->get('ptg')]) && $session[$node][$this->Input->get('ptg')] == 1) ? 0 : 1;
			$this->Session->setData($session);

			$this->redirect(preg_replace('/(&(amp;)?|\?)ptg=[^& ]*/i', '', $this->Environment->request));
		}

		$objRow = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
								 ->limit(1)
								 ->execute($id);

		// Return if there is no result
		if ($objRow->numRows < 1)
		{
			$this->Session->setData($session);
			return '';
		}

		$return = '';
		$intSpacing = 20;

		// Add the ID to the list of current IDs
		if ($this->strTable == $table)
		{
			$this->current[] = $objRow->id;
		}

		// Check whether there are child records
		if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 || $this->strTable != $table)
		{
			$objChilds = $this->Database->prepare("SELECT id FROM " . $table . " WHERE pid=? AND language=''" . ($blnHasSorting ? " ORDER BY sorting" : ''))
										->execute($id);

			if ($objChilds->numRows)
			{
				$childs = $objChilds->fetchEach('id');
			}
		}

		// Check whether the page is protected
		$objRow->protected = ($table == 'tl_page') ? ($objRow->protected || $protectedPage) : false;
		$session[$node][$id] = (is_int($session[$node][$id])) ? $session[$node][$id] : 0;

		$return .= "\n  " . '<li class="'.((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $objRow->type == 'root') || $table != $this->strTable) ? 'tl_folder' : 'tl_file').'" onmouseover="Theme.hoverDiv(this, 1);" onmouseout="Theme.hoverDiv(this, 0);"><div class="tl_left" style="padding-left:'.($intMargin + $intSpacing).'px;">';

		// Calculate label and add a toggle button
		$args = array();
		$folderAttribute = 'style="margin-left:20px;"';
		$showFields = $GLOBALS['TL_DCA'][$table]['list']['label']['fields'];
		$level = ($intMargin / $intSpacing + 1);

		if (count($childs))
		{
			$folderAttribute = '';
			$img = ($session[$node][$id] == 1) ? 'folMinus.gif' : 'folPlus.gif';
			$alt = ($session[$node][$id] == 1) ? $GLOBALS['TL_LANG']['MSC']['collapseNode'] : $GLOBALS['TL_LANG']['MSC']['expandNode'];
			$return .= '<a href="'.$this->addToUrl('ptg='.$id).'" title="'.specialchars($alt).'" onclick="Backend.getScrollOffset(); return AjaxRequest.toggleStructure(this, \''.$node.'_'.$id.'\', '.$level.', '.$GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'].');">'.$this->generateImage($img, specialchars($alt), 'style="margin-right:2px;"').'</a>';
		}

		foreach ($showFields as $k=>$v)
		{
			if (strpos($v, ':') !== false)
			{
				list($strKey, $strTable) = explode(':', $v);
				list($strTable, $strField) = explode('.', $strTable);

				$objRef = $this->Database->prepare("SELECT " . $strField . " FROM " . $strTable . " WHERE id=?")
										 ->limit(1)
										 ->execute($objRow->$strKey);

				$args[$k] = $objRef->numRows ? $objRef->$strField : '';
			}
			elseif (in_array($GLOBALS['TL_DCA'][$table]['fields'][$v]['flag'], array(5, 6, 7, 8, 9, 10)))
			{
				$args[$k] = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $objRow->$v);
			}
			elseif ($GLOBALS['TL_DCA'][$table]['fields'][$v]['inputType'] == 'checkbox' && !$GLOBALS['TL_DCA'][$table]['fields'][$v]['eval']['multiple'])
			{
				$args[$k] = strlen($objRow->$v) ? (strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['label'][0] : $v) : '';
			}
			else
			{
				$args[$k] = strlen($GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$objRow->$v]) ? $GLOBALS['TL_DCA'][$table]['fields'][$v]['reference'][$objRow->$v] : $objRow->$v;
			}
		}

		$label = vsprintf(((strlen($GLOBALS['TL_DCA'][$table]['list']['label']['format'])) ? $GLOBALS['TL_DCA'][$table]['list']['label']['format'] : '%s'), $args);

		// Shorten label it if it is too long
		if ($GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] > 0 && $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'] < strlen(strip_tags($label)))
		{
			$this->import('String');
			$label = trim($this->String->substrHtml($label, $GLOBALS['TL_DCA'][$table]['list']['label']['maxCharacters'])) . ' …';
		}

		$label = preg_replace('/\(\) ?|\[\] ?|\{\} ?|<> ?/i', '', $label);

		// Call label_callback ($row, $label, $this)
		if (is_array($GLOBALS['TL_DCA'][$table]['list']['label']['label_callback']))
		{
			$strClass = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][0];
			$strMethod = $GLOBALS['TL_DCA'][$table]['list']['label']['label_callback'][1];

			$this->import($strClass);
			$return .= $this->$strClass->$strMethod($objRow->row(), $label, $this, $folderAttribute);
		}
		else
		{
			$return .= $this->generateImage('iconPLAIN.gif', '', $folderAttribute) . ' ' . $label;
		}

		$return .= '</div> <div class="tl_right">';
		$previous = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $arrPrevNext['pp'] : $arrPrevNext['p'];
		$next = ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 6) ? $arrPrevNext['nn'] : $arrPrevNext['n'];
		$_buttons = '';

		// Regular buttons ($row, $table, $root, $blnCircularReference, $childs, $previous, $next)
		if ($this->strTable == $table)
		{
			$_buttons .= ($this->Input->get('act') == 'select') ? '<input type="checkbox" name="IDS[]" id="ids_'.$id.'" class="tl_tree_checkbox" value="'.$id.'" />' : $this->generateButtons($objRow->row(), $table, $this->root, $blnCircularReference, $childs, $previous, $next);
		}

		// Paste buttons
		if ($arrClipboard !== false && $this->Input->get('act') != 'select')
		{
			// Call paste_button_callback(&$dc, $row, $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next)
			if (is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback']))
			{
				$strClass = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][0];
				$strMethod = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['paste_button_callback'][1];

				$this->import($strClass);
				$_buttons .= $this->$strClass->$strMethod($this, $objRow->row(), $table, $blnCircularReference, $arrClipboard, $childs, $previous, $next);
			}

			else
			{
				$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id), 'class="blink"');
				$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id), 'class="blink"');

				// Regular tree (on cut: disable buttons of the page all its childs to avoid circular references)
				if ($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5)
				{
					$_buttons .= ($arrClipboard['mode'] == 'cut' && ($blnCircularReference || $arrClipboard['id'] == $id) || $arrClipboard['mode'] == 'cutAll' && ($blnCircularReference || in_array($id, $arrClipboard['id'])) || (count($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['root']) && in_array($id, $this->root))) ? $this->generateImage('pasteafter_.gif', '', 'class="blink"').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
					$_buttons .= ($arrClipboard['mode'] == 'paste' && ($blnCircularReference || $arrClipboard['id'] == $id) || $arrClipboard['mode'] == 'cutAll' && ($blnCircularReference || in_array($id, $arrClipboard['id']))) ? $this->generateImage('pasteinto_.gif', '', 'class="blink"').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
				}

				// Extended tree
				else
				{
					$_buttons .= ($this->strTable == $table) ? (($arrClipboard['mode'] == 'cut' && ($blnCircularReference || $arrClipboard['id'] == $id) || $arrClipboard['mode'] == 'cutAll' && ($blnCircularReference || in_array($id, $arrClipboard['id']))) ? $this->generateImage('pasteafter_.gif', '', 'class="blink"') : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteafter'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ') : '';
					$_buttons .= ($this->strTable != $table) ? '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$id.(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$this->strTable]['pasteinto'][1], $id)).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ' : '';
				}
			}
		}

		$return .= (strlen($_buttons) ? $_buttons : '&nbsp;') . '</div><div style="clear:both;"></div></li>';

		// Add records of the table itself
		if ($table != $this->strTable)
		{
			$objChilds = $this->Database->prepare("SELECT id FROM " . $this->strTable . " WHERE pid=?" . ($blnHasSorting ? " ORDER BY sorting" : ''))
							 			->execute($id);

			if ($objChilds->numRows)
			{
				$ids = $objChilds->fetchEach('id');

				for ($j=0; $j<count($ids); $j++)
				{
					$return .= $this->generateTree($this->strTable, $ids[$j], array('pp'=>$ids[($j-1)], 'nn'=>$ids[($j+1)]), $blnHasSorting, ($intMargin + $intSpacing + 20), $arrClipboard, false, ($j<(count($ids)-1) || count($childs)));
				}
			}
		}

		// Begin new submenu
		if (count($childs) && $session[$node][$id] == 1)
		{
			$return .= '<li class="parent" id="'.$node.'_'.$id.'"><ul class="level_'.$level.'">';
		}

		// Add records of the parent table
		if ($session[$node][$id] == 1)
		{
			if (is_array($childs))
			{
				for ($k=0; $k<count($childs); $k++)
				{
					$return .= $this->generateTree($table, $childs[$k], array('p'=>$childs[($k-1)], 'n'=>$childs[($k+1)]), $blnHasSorting, ($intMargin + $intSpacing), $arrClipboard, ((($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']['mode'] == 5 && $childs[$k] == $arrClipboard['id']) || $blnCircularReference) ? true : false), ($objRow->protected || $protectedPage));
				}
			}
		}

		// Close submenu
		if (count($childs) && $session[$node][$id] == 1)
		{
			$return .= '</ul></li>';
		}

		$this->Session->setData($session);
		return $return;
	}
}

