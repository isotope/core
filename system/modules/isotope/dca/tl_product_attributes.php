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
 * Table tl_product_attributes 
 */
$GLOBALS['TL_DCA']['tl_product_attributes'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'                      => 'tl_product_attribute_sets',
		'enableVersioning'            => true,
		'onload_callback'             => array
		(
			array('tl_product_attributes', 'loadAttributes'),
			array('tl_product_attributes', 'checkPermission'),
		),
		'ondelete_callback'			  => array
		(
			array('tl_product_attributes', 'checkFieldLock')
		)
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter,limit', 
			'headerFields'            => array('name', 'storeTable', 'tstamp'), 
			'flag'                    => 1,
			'child_record_callback'   => array('tl_product_attributes', 'renderAttribute') 
		),
		'label' => array
		(
			'fields'                  => array('name'),
			'format'                  => '%s'
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attributes']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),/*
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attributes']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),*/
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attributes']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attributes']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_attributes']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

    // Palettes
    'palettes' => array
    (
        '__selector__'				=> array('type','use_alternate_source'),
		'default'					=> 'name,description,type,field_name',
		'text'                     	=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_order_by_enabled,is_listing_field;rgxp;load_callback;save_callback',
		'shorttext'               	=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_order_by_enabled,is_listing_field;rgxp;load_callback;save_callback',
		'integer'					=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_order_by_enabled,is_used_for_price_rules,is_listing_field;load_callback;save_callback',
		'decimal'					=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_order_by_enabled,is_used_for_price_rules,is_listing_field;load_callback;save_callback',
		'longtext'					=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_order_by_enabled,use_rich_text_editor,is_listing_field;load_callback;save_callback',
		'datetime'					=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_searchable,is_order_by_enabled,is_listing_field;load_callback;save_callback',
		'select'					=> 'name,description,type,field_name,option_list,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_multiple_select,is_order_by_enabled,is_listing_field;use_alternate_source;load_callback;save_callback',
		'boolean'					=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_multiple_select,is_order_by_enabled,is_listing_field;load_callback;save_callback',
		'options'					=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_multiple_select,is_order_by_enabled,is_listing_field;load_callback;save_callback',
		'fileattach'				=> 'name,description,type,field_name,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_listing_field;load_callback;save_callback',
		'filetree'					=> 'name,description,type,field_name,show_files;is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_searchable,is_listing_field;load_callback;save_callback',
		'media'						=> 'name,description,type,field_name,show_files;is_visible_on_front;is_hidden_on_backend;is_required,is_listing_field;load_callback;save_callback',
		'checkbox'					=> 'name,description,type,field_name,option_list,is_visible_on_front;is_hidden_on_backend;is_required,is_filterable,is_order_by_enabled,is_listing_field;use_alternate_source;load_callback;save_callback'
    ),

    // Subpalettes
    'subpalettes' => array
    (
       'use_alternate_source'		=> 'list_source_table,list_source_field'
    ),

    // Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['name'],
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'save_callback'			  => array
			(
//				array('tl_product_attributes','standardizeAndChangeColumnType')
			)
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['description'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255),
		),
		'type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['type'],
			'inputType'               => 'select',
			'eval'                    => array('mandatory'=>true,'includeBlankOption'=>true,'submitOnChange'=>true),
			'options_callback'		  => array('tl_product_attributes','getAttributeTypes')/*,
			'save_callback'			  => array
			(
				array('tl_product_attributes','standardizeAndChangeColumnType')
			)*/
		),
		'field_name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['field_name'],
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
			'save_callback'			  => array
			(
				array('ProductCatalog','renameColumn'),
			)
		),
		'option_list' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['option_list'],
			'exclude'                 => true,
			'inputType'               => 'optionWizard'
		),
		'show_files' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['show_files'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		)/*,
		'attr_use_mode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['attr_use_mode'],	//Is customer defined will determine what to do - whether to show just 
																									//a label or a form control
			'exclude'                 => true,
			'default'				  => 'label',
			'inputType'               => 'select',
			'options'				  => array('label','input'),
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'submitOnChange'=>true)
		)*/,
		'attr_default_value' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['attr_default_value'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255),
			'save_callback'			  => array
			(
				array('tl_product_attributes','validateInput')
			)
		),
		'is_customer_defined' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_customer_defined'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_visible_on_front' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_visible_on_front'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_hidden_on_backend' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_hidden_on_backend'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_required' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_required'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_filterable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_filterable'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_searchable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_searchable'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_order_by_enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_order_by_enabled'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_used_for_price_rules' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_used_for_price_rules'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'is_multiple_select' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_multiple_select'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'use_rich_text_editor' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_attributes']['use_rich_text_editor'],
			'exclude'				  => true,
			'default'				  => 0,
			'inputType'				  => 'checkbox'
		)/*,
		'storeTable' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_attributes']['storeTable'],
			'exclude'				  => true,
			'default'				  => 0,
			'inputType'				  => 'checkbox',
			'eval'					  => array('multiple'=>true)
		)*/,
		'is_listing_field' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_listing_field'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox'
		),
		'use_alternate_source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['use_alternate_source'],
			'exclude'                 => true,
			'default'				  => 0,
			'inputType'               => 'checkbox',
			'eval'					  => array('submitOnChange'=>true)
		),
		'list_source_table' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['list_source_table'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('includeBlankOption'=>true,'submitOnChange'=>true),
			'options_callback'		  => array('tl_product_attributes','getTables')
		),
		'list_source_field' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['list_source_field'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('includeBlankOption'=>true,'submitOnChange'=>true),
			'options_callback'		  => array('tl_product_attributes','getFields'),
			'save_callback'			  => array
			(
				array('ProductCatalog','importAlternateSourceToCollection')
			)
		),
		'rgxp' => array
		(
			'label'					  =>  &$GLOBALS['TL_LANG']['tl_product_attributes']['rgxp'],
			'exclude'				  => true,
			'inputType'				  => 'select',
			'options'				  => array('alnum','extnd','email','url'),
			'eval'				 	  => array('includeBlankOption'=>true),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_product_attributes']['rgxpOptions']	
		),
	   'load_callback' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['load_callback'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'save_callback' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['save_callback'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		)
	)
);


/**
 * tl_product_attributes class.
 * 
 * @extends Backend
 */
class tl_product_attributes extends Backend
{

	/**
	 * Returns all allowed page types as array.
	 * 
	 * @todo returns string in case of error, should return array
	 *
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getAttributeTypes(DataContainer $dc)
	{
		$arrOptions = array();

		$objAttributeTypes = $this->Database->execute("SELECT type FROM tl_product_attribute_types");
		
		if($objAttributeTypes->numRows < 1)
		{
			return '';
		}			
		
		$arrAttributeTypes = $objAttributeTypes->fetchAllAssoc();								

		foreach ($arrAttributeTypes as $attrType)
		{
			$arrOptions[$attrType['type']] = $GLOBALS['TL_LANG']['tl_product_attributes'][$attrType['type']];
		}

		return $arrOptions;
	}
	

	/**
	 * loadAttributes function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return void
	 */
	public function loadAttributes(DataContainer $dc)
	{
		$this->import('ProductCatalog');

		$act = $this->Input->get('act');
		switch ($act)
		{
			case 'delete':
				$this->ProductCatalog->deleteColumn(array($dc->id));
				break;
				
			case "deleteAll":
				$session = $this->Session->getData();
				$this->ProductCatalog->deleteColumn($session['CURRENT']['IDS']);
				break;

			default:;
		}

		if (!strlen($act) && $dc->table == 'tl_product_attributes' && $dc->id)
		{
			$this->ProductCatalog->regenerateDca($dc->id);
			
			// Load default attributes if empty
			$objAttributeSet = $this->Database->prepare("SELECT * FROM tl_product_attribute_sets WHERE id=?")->limit(1)->execute($dc->id);
			
			if ($objAttributeSet->numRows)
			{
				$objAttributes = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE pid=?")->execute($objAttributeSet->id);
				if (!$objAttributes->numRows)
				{
					$this->ProductCatalog->insertDefaultAttributes($dc, $objAttributeSet->storeTable);
				}
			}
		}
	}
	
	
	public function checkPermission($dc)
	{
		if ($this->Input->get('act') == 'edit')
		{
			$objAttribute = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE id=?")->limit(1)->execute($dc->id);
			
			if ($objAttribute->numRows)
			{
				$blnMandatory = false;
				
				foreach( $GLOBALS['ISO_ATTR'] as $attr )
				{
					if ($attr['field_name'] == $objAttribute->field_name)
					{
						$blnMandatory = true;
					}
				}
				
				if ($blnMandatory)
				{
					$GLOBALS['TL_DCA']['tl_product_attributes']['fields']['field_name']['eval']['style'] = 'background-color: #fee;" readonly="readonly';
				}
			}
		}
	}
	

	/**
	 * renderAttribute function.
	 * 
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function renderAttribute($arrRow)
	{
		$this->import('ProductCatalog');
		
		$strTemplateKey = $arrRow['field_name'];
				
		$strTemplate = "%s (%s, <strong>%s</strong>)" . ($arrRow['is_visible_on_front']==1 ? "<br />" . $GLOBALS['TL_LANG']['tl_product_attributes']['template_key'] . "<strong>%s</strong>" : "") . "<br />" . ($arrRow['is_visible_on_front'] ? $GLOBALS['TL_LANG']['tl_product_attributes']['template_visibility_title'] . "<strong>" . $GLOBALS['TL_LANG']['tl_product_attributes']['product_reader_enabled'] . ($arrRow['is_listing_field']==1 ? ", " . $GLOBALS['TL_LANG']['tl_product_attributes']['product_listing_enabled'] : "") . "</strong>" : "");
		
		return sprintf($strTemplate, $arrRow['name'], $GLOBALS['TL_LANG']['tl_product_attributes'][$arrRow['type']], $GLOBALS['TL_LANG']['tl_product_attributes']['required_val'][$arrRow['is_required']], $strTemplateKey);
	}

	
	/**
	 * getTableKeys function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return void
	 */
	public function getTableKeys(DataContainer $dc)
	{
		$objTable = $this->Database->prepare("SELECT itemTable FROM tl_product_attributes WHERE id=?")
								   ->limit(1)
								   ->execute($dc->id);
		 
		if ($objTable->numRows > 0 && $this->Database->tableExists($objTable->itemTable))
		{
			$fields = $this->Database->listFields($objTable->itemTable);
			
			return array_map(create_function('$x', 'return $x["name"];'), array_filter($fields, create_function('$x', 'return array_key_exists("index", $x) && $x["type"] == "int";')));
		}
	}
	
 
 	/**
	 * getTableFields function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return void
	 */
	public function getTableFields(DataContainer $dc)
	{
		$objTable = $this->Database->prepare("SELECT itemTable FROM tl_product_attributes WHERE id=?")
								   ->limit(1)
								   ->execute($dc->id);
		 
		if ($objTable->numRows > 0 && $this->Database->tableExists($objTable->itemTable))
		{
			$fields = $this->Database->listFields($objTable->itemTable);
			
			return array_map(create_function('$x', 'return $x["name"];'), $fields);
		}
	}


	/**
	 * getItems function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getItems(DataContainer $dc)
	{
		$objField = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE id=?")
				->limit(1)
				->execute($dc->id);
				
		if ($objField->numRows > 0)
		{
			$idCol = 'id';//$objField->itemTableIdCol;
			$valueCol = $objField->itemTableValueCol;
			$itemTable = $objField->itemTable;
			
			try
			{
					$objItems = $this->Database->execute("SELECT $idCol, $valueCol FROM $itemTable");
			}
			catch (Exception $e)
			{
					// return empty array - no items yet
					return array();
			}
			
			$result = array();
			while($objItems->next())
			{
					$result[$objItems->$idCol] = $objItems->$valueCol;
			}
			
			return $result;
		}
	}
	
	
	/**
	 * getSelectors function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getSelectors(DataContainer $dc)
	{
		$objField = $this->Database->prepare("SELECT pid FROM tl_product_attributes WHERE id=?")
				->limit(1)
				->execute($dc->id);
				
		if (!$objField->numRows)
		{
			return array();
		}
		
		$pid = $objField->pid;
		
		$objFields = $this->Database->prepare("SELECT name, colName FROM tl_product_attributes WHERE pid=? AND id != ? AND type=?")
				->execute($pid, $dc->id, 'checkbox');
		 
		$result = array();
		while ($objFields->next())
		{
			$result[$objFields->colName] = $objFields->name;
		}
		
		return $result;
	}


	/**
	 * checkAliasDuplicate function.
	 * 
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return mixed
	 */
	public function checkAliasDuplicate($varValue, DataContainer $dc)
	{
		$arrAlias = $this->getAliasFields($dc);
		if ($varValue == 'alias' && count($arrAlias))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasDuplicate'],join(', ', array_keys($arrAlias))));
		}
		
		return $varValue;
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
		
		$pid = $objField->pid;

		$objFields = $this->Database->prepare("SELECT name, colName FROM tl_product_attributes WHERE pid=? AND id!=? AND type=? AND titleField=?")
				->execute($pid, $dc->id, 'text', 1);
		 
		$result = array();
		while ($objFields->next())
		{
			$result[$objFields->colName] = $objFields->name;
		}
		
		return $result;
	}
	
	
	/**
	 * onLoadItems function.
	 * 
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return mixed
	 */
	public function onLoadItems($varValue, DataContainer $dc)
	{
		$objField = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE id=?")
				->limit(1)
				->execute($dc->id);
				
		if ($objField->numRows)
		{
			$GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['eval']['tableColumn'] =
					$objField->itemTable.'.'.$objField->itemTableValueCol;
		}
		
		return $varValue;
	}
	

	/**
	 * Add the type of content element.
	 * 
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function listAttribute($arrRow)
	{
			return '
			<div class="cte_type">' . $GLOBALS['TL_LANG']['tl_product_attributes'][$arrRow['type']] . '</div>
			<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h64' : '') . ' block">
			' . $arrRow['name'] . '
			</div>' . "\n";
	}
	

	/**
	 * Get the data type name based on the datatype key.
	 * 
	 * @access public
	 * @param array $row
	 * @param string $label
	 * @return string
	 */
	public function getDataTypeName($row, $label)
	{
		$strLabelTemplate = '<span style="color:#b3b3b3; padding-left:3px;">[%s]</span>';

		return $label . sprintf($strLabelTemplate, $GLOBALS['TL_LANG']['tl_product_attributes'][$row['type']]);
	}
	
	
	/**
	 * Set the correct input type for use in providing the backend interface.
	 * 
	 * @todo This function does nothing. We might want to remove it.
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return mixed
	 */
	public function setInputType($varValue, DataContainer $dc)
	{
		return $varValue;
	}
	
	
	/**
	 * standardizeAndRenameColumn function.
	 * 
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return mixed
	 */
/*
	public function standardizeAndRenameColumn($varValue, DataContainer $dc)
	{
		$this->import('ProductCatalog');
			
		$fieldName = $this->ProductCatalog->mysqlStandardize($varValue);
							   
		$this->ProductCatalog->renameColumn(strtolower($fieldName), $dc);
		
		return $varValue;
	}
*/
	
	
	/**
	 * standardizeAndChangeColumnType function.
	 * 
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return mixed
	 */
/*
	public function standardizeAndChangeColumnType($varValue, DataContainer $dc)
	{
		$this->import('ProductCatalog');	
	
		$fieldName = $this->ProductCatalog->mysqlStandardize($varValue);
			
		$this->ProductCatalog->renameColumn(strtolower($fieldName), $dc);
		
		return $varValue;
	}
*/
	
	
	/**
	 * checkFieldLock function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return void
	 */
	public function checkFieldLock(DataContainer $dc)
	{
		$objDeleteLock = $this->Database->prepare("SELECT delete_locked FROM tl_product_attributes WHERE id=?")
										->limit(1)
										->execute($dc->id);
		
		if($objDeleteLock->numRows < 1)
		{
			return; 
		}
		
		if($objDeleteLock->delete_locked=='1')
		{
			$_SESSION['TL_ERROR'][] = $GLOBALS['TL_LANG']['ERR']['deleteLocked'];
			$this->reload();
		}
	}
	
	
	/**
	 * getTables function.
	 * 
	 * @access public
	 * @return array
	 */
	public function getTables()
    {
		$arrReturn = array();

		$objTables = $this->Database->prepare("SHOW TABLES FROM " . $GLOBALS['TL_CONFIG']['dbDatabase'])->execute();
		
		if($objTables->numRows > 0)
		{
			$arrTables = $objTables->fetchAllAssoc();
			
			foreach ($arrTables as $arrTable)
			{
				if($this->Database->fieldExists('pid',current($arrTable)))
				{
					$arrReturn[] = current($arrTable);
				}
			}
		}

		return $arrReturn;
    }
	
	
	/**
	 * getFields function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getFields(DataContainer $dc)
    {
        $objTable = $this->Database->prepare("SELECT list_source_table FROM tl_product_attributes WHERE id=?")
								   ->limit(1)
								   ->execute($dc->id);
         
        if ($objTable->numRows > 0 && $this->Database->tableExists($objTable->list_source_table))
        {
            $fields = $this->Database->listFields($objTable->list_source_table);
			
            return array_map(create_function('$x', 'return $x["name"];'), $fields);
        }
    }	
}

