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
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_product_attribute_sets 
 */
$GLOBALS['TL_DCA']['tl_product_attribute_sets'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ctable'                      => array('tl_product_attributes'),
		'switchToEdit'                => true,
		'enableVersioning'            => true,
		'onload_callback'             => array
			(
				array('tl_product_attribute_sets', 'checkPermission')
			)
		),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('name'),
			'flag'                    => 1,
			'panelLayout'             => 'filter;search,limit'
		),

		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s',
			'label_callback'					=> array('tl_product_attribute_sets','getRowLabel')
		),

		'global_operations' => array
		(
            
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
		),

		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['edit'],
				'href'				  => 'table=tl_product_data',
				'icon'                => 'edit.gif',
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'			=> array('tl_product_attribute_sets', 'copyBtn')
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'button_callback'			=> array('tl_product_attribute_sets', 'deleteBtn'),
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'

			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
      		'attributes' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['attributes'],
				'href'                => 'table=tl_product_attributes',
				'icon'                => 'tablewizard.gif',
        		'button_callback'     => array('tl_product_attribute_sets', 'fieldsButton')
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'            => array('addImage'),
		'default'                 => 'name,store_id;addImage;format',
	),

	// Subpalettes
	'subpalettes' => array
	(
		'addImage'                 => 'singleSRC,size',
	),


	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>61),
			'save_callback'			  => array
			(
				array('tl_product_attribute_sets','standardize_mysql_storeTable')
			)
		),
        'store_id' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['store_id'],
			'exclude'				  => true,
			'inputType'				  => 'select',
			'foreignKey'			  => 'tl_store.store_configuration_name',
			'eval'					  => array('includeBlankOption'=>true,'mandatory'=>true)		
		),
		/*'storeTable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['storeTable'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>64, 'doNotCopy'=>true),
			'save_callback'           => array
			(
				array('ProductCatalog', 'renameTable')
			)
		),*/
        
		'noTable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['noTable'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
		'addImage' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['addImage'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'                    => array('submitOnChange'=>true)
		),
		'singleSRC' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['singleSRC'],
			'exclude'                 => true,
			'inputType'               => 'fileTree',
			'eval'                    => array('fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'mandatory'=>true, 'extensions' => 'jpg,jpeg,gif,png,tif,tiff')
		),
		'size' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['size'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'rgxp'=>'digit', 'nospace'=>true)
		),
		'format' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attribute_sets']['format'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'textarea',
			'eval'                    => array('allowHtml'=>true)
		),
	)
);


/**
 * tl_product_attribute_sets class.
 * 
 * @extends Backend
 */
class tl_product_attribute_sets extends Backend
{

	/**
	 * Check permissions to edit table tl_product_attribute_sets.
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
/*
		// Set root IDs
		if (!is_array($this->User->catalogs) || count($this->User->catalogs) < 1)
		{
			$root = array(0);
		}
		else
		{
			$root = $this->User->catalogs;
		}
*/
		$GLOBALS['TL_DCA']['tl_product_attribute_sets']['config']['closed'] = true;
		$GLOBALS['TL_DCA']['tl_product_attribute_sets']['list']['sorting']['root'] = $root;

		// Check current action
		switch ($this->Input->get('act'))
		{
			case 'select':
				// Allow
				break;

			case 'edit':
			case 'show':
				if (!in_array($this->Input->get('id'), $root))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' catalog type ID "'.$this->Input->get('id').'"', 'tl_product_attribute_sets checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;

			case 'editAll':
				$session = $this->Session->getData();
				$session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
				$this->Session->setData($session);
				break;

			default:
				if (strlen($this->Input->get('act')))
				{
					$this->log('Not enough permissions to '.$this->Input->get('act').' catalog types', 'tl_product_attribute_sets checkPermission', 5);
					$this->redirect('typolight/main.php?act=error');
				}
				break;
		}
	}


	/**
	 * getRowLabel function.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $label
	 * @param object $dc
	 * @return string
	 */
	public function getRowLabel($row, $label, $dc)
	{
		// add image
		$image = '';
		if ($row['addImage'])
		{
			$size = deserialize($row['size']);
			$image = '<div class="image" style="padding-top:3px"><img src="'.$this->getImage($row['singleSRC'], $size[0], $size[1]).'" alt="'.htmlspecialchars($label).'" /></div> ';
		}
		
		// count items
		$objCount = $this->Database->prepare("SELECT count(*) AS itemCount FROM ".$row['storeTable'])->execute();
		
		$itemCount =  sprintf($GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemFormat'], $objCount->itemCount, ($objCount->itemCount == 1) ? sprintf($GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemSingle']) : sprintf($GLOBALS['TL_LANG']['tl_product_attribute_sets']['itemPlural']));
		
		return '<span class="name">'.$label. $itemCount . '</span>'.$image;
	}


	/**
	 * Return the copy archive button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function copyBtn($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->isAdmin)
		{
			return '';
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}


	/**
	 * Return the delete archive button.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function deleteBtn($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$this->User->isAdmin)
		{
			return '';
		}

		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}
	
	
	/**
	 * editItem function.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function editItem($row, $href, $label, $title, $icon, $attributes)
	{
		return '<a href="'.$this->addToUrl('table=tl_catalog_items&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a>  ';
	}


	/**
	 * fieldsButton function.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 * @return string
	 */
	public function fieldsButton($row, $href, $label, $title, $icon, $attributes)
	{
		$this->import('BackendUser', 'User');
	
		if (!$this->User->isAdmin)
		{
			return '';
		}
	
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	} 
   

	/**
	 * getAliasFields function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getAliasFields(DataContainer $dc)
	{
		$objField = $this->Database->prepare("SELECT pid FROM tl_product_attributes WHERE id=?")
				->limit(1)
				->execute($dc->id);
			
		if (!$objField->numRows)
		{
				return array();
		}
		
		$pid = $objField->pid;

		$objFields = $this->Database->prepare("SELECT name, colName FROM tl_product_attributes WHERE pid=? AND id!=? AND type=?")
				->execute($pid, $dc->id, 'alias');
		 
		$result = array();
		while ($objFields->next())
		{
			$result[$objFields->colName] = $objFields->name;
		}
		
		return $result;
	}


	/**
	 * getTitleFields function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getTitleFields(DataContainer $dc)
	{
		$objField = $this->Database->prepare("SELECT pid FROM tl_product_attributes WHERE id=?")
				->limit(1)
				->execute($dc->id);
			
		if (!$objField->numRows)
		{
			return array();
		}

		$objFields = $this->Database->prepare("SELECT name, colName FROM tl_product_attributes WHERE pid=? AND id!=? AND type=? AND titleField=?")
									->execute($objField->pid, $dc->id, 'text', 1);
		 
		$result = array();
		
		while ($objFields->next())
		{
			$result[$objFields->colName] = $objFields->name;
		}
		
		return $result;
	}
	
	
	/**
	 * Standardize a parameter (strip special characters and convert spaces to underscores).
	 * 
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return mixed
	 */
	public function standardize_mysql_storeTable($varValue, DataContainer $dc)
	{
		$storeTable = utf8_romanize($varValue);
	
		$storeTable = preg_replace('/[^a-zA-Z0-9 _-]+/i', '', $storeTable);
		$storeTable = 'iso_' . preg_replace('/ +/i', '_', $storeTable);
	
		if (preg_match('/^[^a-zA-Z]/i', $storeTable))
		{
			$storeTable = $storeTable . '_' . $dc->id;
		}
		
		$this->import('ProductCatalog');
			
		if(!$this->Database->tableExists(strtolower($storeTable)))
		{
			$isNewTable = true;
		}
			
		$this->ProductCatalog->renameTable(strtolower($storeTable), $dc, 'tl_product_attribute_sets');

		$this->Database->prepare("UPDATE tl_product_attribute_sets SET storeTable=? WHERE id=?")
					   ->execute(strtolower($storeTable), $dc->id);
					   
		if($isNewTable)
		{
			$this->ProductCatalog->insertDefaultAttributes($dc, strtolower($storeTable));		
		}			   
		
		return $varValue;
	}
}
