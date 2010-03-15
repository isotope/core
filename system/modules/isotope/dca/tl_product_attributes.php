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
 * Table tl_product_attributes 
 */
$GLOBALS['TL_DCA']['tl_product_attributes'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'onsubmit_callback'			  => array
		(
			array('ProductCatalog','changeFieldType')
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('legend', 'name'),
			'flag'					  => 1,
			'panelLayout'             => 'sort,filter,search,limit'
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
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_attributes']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
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
        '__selector__'				=> array('type','use_alternate_source','is_customer_defined'),
		'default'					=> '{general_legend},name,description,field_name;{type_legend},type,legend;',
		'text'                     	=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined;{validation_legend},is_required;{search_filters_legend},is_searchable,is_order_by_enabled;rgxp;{developer_tools_legend:hide},load_callback,save_callback',
		/*'shorttext'               	=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined;{validation_legend},is_required;{search_filters_legend},is_searchable,is_order_by_enabled;rgxp;{developer_tools_legend:hide},load_callback,save_callback',*/
		'integer'					=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined;{validation_legend},is_required;{search_filters_legend},is_filterable,is_order_by_enabled,is_used_for_price_rules,{developer_tools_legend:hide},load_callback,save_callback',
		'decimal'					=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;is_customer_defined;is_required,is_filterable,is_order_by_enabled,is_used_for_price_rules,is_listing_field;{developer_tools_legend:hide},load_callback,save_callback',
		'longtext'					=> '{general_legend},name,description,field_name,use_rich_text_editor;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined;{validation_legend},is_required;{search_filters_legend},is_searchable,is_order_by_enabled;rgxp;{developer_tools_legend:hide},load_callback,save_callback',
		'datetime'					=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{validation_legend},is_required;{search_filters_legend},is_order_by_enabled;{developer_tools_legend:hide},load_callback,save_callback',
		'select'				=> '{general_legend},name,description,field_name;{type_legend},type,legend;{options_legend},option_list,use_alternate_source;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined,is_multiple_select;{validation_legend},is_required;{search_filters_legend},is_filterable,is_order_by_enabled;{developer_tools_legend:hide},load_callback,save_callback',
		/*'boolean'					=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined,is_multiple_select;{validation_legend},is_required;{search_filters_legend},is_filterable,is_order_by_enabled;{developer_tools_legend:hide},load_callback,save_callback',*/
		'options'				=> '{general_legend},name,description,field_name;{type_legend},type,legend;{options_legend},option_list,{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined,is_multiple_select;{validation_legend},is_required;{search_filters_legend},is_filterable,is_order_by_enabled;{developer_tools_legend:hide},load_callback,save_callback',
		'fileattach'				=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined;{validation_legend},is_required;{developer_tools_legend:hide},load_callback,save_callback',
		'filetree'					=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined,is_multiple_select,show_files;{validation_legend},is_required,{search_filters_legend},is_filterable;{developer_tools_legend:hide},load_callback,save_callback',
		'media'						=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},show_files;{validation_legend},is_required;{developer_tools_legend:hide},load_callback,save_callback',
		'checkbox'					=> '{general_legend},name,description,field_name;{type_legend},type,legend;{visibility_legend},is_listing_field,is_visible_on_front,is_hidden_on_backend,disabled;{use_mode_legend},is_customer_defined;{validation_legend},is_required;{search_filters_legend},is_filterable,is_order_by_enabled;{developer_tools_legend:hide},load_callback,save_callback'


    ),

    // Subpalettes
    'subpalettes' => array
    (
       'use_alternate_source'		=> 'list_source_table,list_source_field',
    	'is_customer_defined'		=> 'add_to_product_variants'
    ),

    // Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['name'],
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
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
			'options'				  => &$GLOBALS['ISO_ATTR'],
			'reference'				  => &$GLOBALS['TL_LANG']['ATTR'],
			'eval'                    => array('mandatory'=>true,'includeBlankOption'=>true,'submitOnChange'=>true),
		),
		'legend' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['legend'],
			'inputType'               => 'select',
			'eval'                    => array('mandatory'=>true,'includeBlankOption'=>false),
			'options_callback'		  => array('tl_product_attributes','getLegends')
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
		),
		'add_to_product_variants' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['add_to_product_variants'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		)
		/*,
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
		'text_collection_rows' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['text_collection_rows'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit')	
		),
		'is_customer_defined' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_customer_defined'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('submitOnChange'=>true)
		),
		'is_visible_on_front' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_visible_on_front'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'is_hidden_on_backend' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_hidden_on_backend'],
			'exclude'                 => true,
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
			'inputType'               => 'checkbox'
		),
		'is_searchable' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_searchable'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'is_order_by_enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_order_by_enabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'is_used_for_price_rules' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_used_for_price_rules'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'is_multiple_select' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_multiple_select'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'use_rich_text_editor' => array
		(
			'label'					  => &$GLOBALS['TL_LANG']['tl_product_attributes']['use_rich_text_editor'],
			'exclude'				  => true,
			'inputType'				  => 'checkbox'
		),
		'is_listing_field' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['is_listing_field'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'use_alternate_source' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['use_alternate_source'],
			'exclude'                 => true,
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
//				array('ProductCatalog','importAlternateSourceToCollection')
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
		),
		'disabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_product_attributes']['disabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
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

	public function getLegends()
	{
		$this->loadLanguageFile('tl_product_data');
		
		foreach($GLOBALS['TL_LANG']['tl_product_data'] as $k=>$v)
		{
			if(preg_match('(_legend)', $k))
			{
				$arrGroups[$k] = $v;			
			}
		
		}
		
		return $arrGroups;
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
    
    
    public function deleteAttribute($dc)
    {
    	if ($this->Database->fieldExists($dc->activeRecord->fieldName, 'tl_product_data'))
    	{
    		$this->Database->executeUncached("ALTER TABLE tl_product_data DROP COLUMN " . $dc->activeRecord->fieldName);
    	}
    }
}

