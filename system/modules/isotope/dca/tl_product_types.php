<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
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
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_product_types
 */
$GLOBALS['TL_DCA']['tl_product_types'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'				=> 'Table',
		'notEditable'				=> false,
		'switchToEdit'				=> true,
		'enableVersioning'			=> true,
		'onload_callback'			=> array
		(
			array('tl_product_types', 'checkPermission'),
			array('tl_product_types', 'editLanguage'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'					=> 5,
			'fields'				=> array('name'),
			'flag'					=> 1,
			'panelLayout'			=> 'filter;search,limit',
			'paste_button_callback'	=> array('tl_product_types', 'pasteButton'),
		),
		'label' => array
		(
			'fields'				=> array('name'),
			'format'				=> '%s',
			'label_callback'		=> array('tl_product_types', 'listRow'),
		),
		'global_operations' => array
		(

			'all' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'				=> 'act=select',
				'class'				=> 'header_edit_all',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_product_types']['edit'],
				'href'				=> 'act=edit',
				'icon'				=> 'edit.gif',
			),
			'copy' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_product_types']['copy'],
				'href'				=> 'act=copy',
				'icon'				=> 'copy.gif',
				'button_callback'	=> array('tl_product_types', 'hideLanguageButtons'),
			),
			'delete' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_product_types']['delete'],
				'href'				=> 'act=delete',
				'icon'				=> 'delete.gif',
				'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'	=> array('tl_product_types', 'hideLanguageButtons'),
			),
			'show' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_product_types']['show'],
				'href'				=> 'act=show',
				'icon'				=> 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('type'),
		'default'					=> '{name_legend},name,type,description;{language_legend},language,languages;{attributes_legend},attributes;{download_legend:hide},downloads',
		'variant'					=> '{name_legend},name,type,description;{language_legend},language,languages;{attributes_legend},attributes,variant_attributes;{download_legend:hide},downloads',
		'language'					=> '{language_legend},language;{attributes_legend},attributes',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['name'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50')
		),
		'type' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['type'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'default'				=> 'default',
			'options'				=> array_keys($GLOBALS['ISO_PRD']),
			'reference'				=> &$GLOBALS['TL_LANG']['PRD'],
			'eval'					=> array('mandatory'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['description'],
			'exclude'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px'),

		),
        'attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['attributes'],
			'exclude'				=> true,
			'inputType'				=> 'attributeWizard',
			'eval'					=> array('mandatory'=>true),
		),
        'variant_attributes' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['variant_attributes'],
			'exclude'				=> true,
			'inputType'				=> 'attributeWizard',
			'eval'					=> array('noDisable'=>true),
		),
		'downloads' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['downloads'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array(),
		),
		'language' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['language'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options'				=> $this->getLanguages(),
			'eval'					=> array('mandatory'=>true, 'includeBlankOption'=>true),
		),
		'languages' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_product_types']['languages'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options'				=> $this->getLanguages(),
			'eval'					=> array('multiple'=>true),
			'save_callback'			=> array
			(
				array('tl_product_types', 'saveLanguages'),
			),
		),
	)
);


/**
 * tl_product_types class.
 *
 * @extends Backend
 */
class tl_product_types extends Backend
{
	
	public function listRow($row, $label, DataContainer $dc=null, $imageAttribute='', $blnReturnImage=false)
	{
		$arrLanguages = $this->getLanguages();
		$image = 'system/modules/isotope/html/languages/' . $row['language'] . '.gif';
		
		if ($row['pid'] > 0)
		{
			$imageAttribute = 'style="margin-left:20px; margin-right:5px; padding-bottom:3px; padding-top:4px"';
			return $this->generateImage($image, '', $imageAttribute) . $arrLanguages[$row['language']];
		}
		
		$imageAttribute = 'style="padding-bottom:3px; padding-top:4px; margin-right:2px"';
		return sprintf('%s %s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $this->generateImage($image, '', $imageAttribute), $row['name'], $arrLanguages[$row['language']]);
	}
	

	/**
	 * Check permissions to edit table tl_product_types.
	 *
	 * @access public
	 * @return void
	 */
	public function checkPermission()
	{
		$this->import('BackendUser', 'User');

		if ($this->User->isAdmin)
		{
			return;
		}

		// Set root IDs
		if (!is_array($this->User->iso_product_types) || count($this->User->iso_product_types) < 1)
		{
			$this->User->iso_product_types = array(0);
		}

		$GLOBALS['TL_DCA']['tl_product_types']['config']['closed'] = true;
		$GLOBALS['TL_DCA']['tl_product_types']['list']['sorting']['root'] = $this->User->iso_product_types;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'select':
				// Allow
				break;

			case 'edit':
			case 'show':
				if (!in_array($this->Input->get('id'), $this->User->iso_product_types))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' product type ID "'.$this->Input->get('id').'"', 'tl_product_types checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;

			case 'editAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $this->User->iso_product_types);
				$this->Session->setData($session);
				break;

			default:
				if (strlen($this->Input->get('act')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' product types', 'tl_product_types checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;
		}
	}
	
	
	public function editLanguage($dc)
	{
		if ($this->Input->get('act') == 'edit')
		{
			$objType = $this->Database->prepare("SELECT pid FROM tl_product_types WHERE id=?")->limit(1)->execute($dc->id);
			
			if ($objType->numRows && $objType->pid > 0)
			{
				$GLOBALS['TL_DCA']['tl_product_types']['palettes']['default'] = &$GLOBALS['TL_DCA']['tl_product_types']['palettes']['language'];
				$GLOBALS['TL_DCA']['tl_product_types']['fields']['language']['eval']['disabled'] = true;
				
				$this->loadDataContainer('tl_product_data');
				foreach( $GLOBALS['TL_DCA']['tl_product_data']['fields'] as $name => $data )
				{
					$GLOBALS['TL_DCA']['tl_product_data']['fields'][$name]['attributes']['fixed'] = false;
				}
			}
		}
	}
	
	
	public function saveLanguages($varValue, $dc)
	{
		$arrLanguages = deserialize($varValue, true);
		
		// Remove the "main language" if selected
		if (in_array($dc->activeRecord->language, $arrLanguages))
		{
			unset($arrLanguages[array_search($dc->activeRecord->language, $arrLanguages)]);
		}
		
		if (count($arrLanguages))
		{
			$this->Database->prepare("DELETE FROM tl_product_types WHERE pid=? AND language NOT IN ('" . implode("', '", $arrLanguages) . "')")->execute($dc->activeRecord->id);
		}
		else
		{
			$this->Database->prepare("DELETE FROM tl_product_types WHERE pid=?")->execute($dc->activeRecord->id);
		}
		
		$objTypes = $this->Database->prepare("SELECT language FROM tl_product_types WHERE pid=?")->execute($dc->activeRecord->id);
		
		$arrMissing = array_diff($arrLanguages, deserialize($objTypes->fetchEach('language'), true));
		
		if (count($arrMissing))
		{
			$time = time();
			$arrQuery = array();
			$arrValues = array();
			
			foreach( $arrMissing as $language )
			{
				$arrQuery[] = "(?, ?, ?)";
				$arrValues[] = $dc->activeRecord->id;
				$arrValues[] = $time;
				$arrValues[] = $language;
			}
			
			$this->Database->prepare("INSERT INTO tl_product_types (pid,tstamp,language) VALUES " . implode(', ', $arrQuery))->execute($arrValues);
		}
		
		return serialize($arrLanguages);
	}
	
	
	/**
	 * Hide delete and copy button from languages
	 */
	public function hideLanguageButtons($row, $href, $label, $title, $icon, $attributes, $table)
	{
		if ($row['pid'] > 0)
			return '<span>'.$this->generateImage(str_replace('.gif', '_.gif', $icon)).'</span> ';
			
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	
	public function pasteButton(DataContainer $dc, $row, $table, $cr, $arrClipboard=false)
	{
		$this->import('BackendUser', 'User');

		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));
		$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));

		if ($row['id'] == 0)
		{
			return ($row['type'] == 'root' || $cr) ? $this->generateImage('pasteinto_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&mode=2&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
		}
		elseif ($row['pid'] > 0)
		{
			return '';
		}

		return (($arrClipboard['mode'] == 'cut' && $arrClipboard['id'] == $row['id']) || $cr) ? $this->generateImage('pasteafter_.gif').' ' : '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&mode=1&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
	}
}

