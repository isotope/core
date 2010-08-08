<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
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
 * @copyright  Winans Creative 2009, Intelligent Spark 2010, iserv.ch GmbH 2010
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


$this->loadLanguageFile('subdivisions');


/**
 * Table tl_iso_rule
 */
$GLOBALS['TL_DCA']['tl_iso_rule'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'ctables'						=> array('tl_iso_rule_usage'),
		'enableVersioning'				=> false,
		/*'onload_callback' => array
		(
		//	array('tl_iso_rule', 'checkPermission')
		)*/
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 2,
			'flag'						=> 1,
			'panelLayout'				=> 'filter;sort,search,limit'
		),
		'label'	  => array
		(
			'fields'					=> array('title', 'code'),
			'label'						=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			//'label_callback'          => array('tl_iso_rule', 'getCouponLabel')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			)
		)
	),
	
	// Palettes
	'palettes' => array
	(
		'__selector__'			=> array('type','collectionTypeRestrictions','memberRestrictions','productRestrictions','ruleRestrictions','enableCode','dateRestrictions','timeRestrictions'),
		'default'				=> '{type_legend},type',
		'product'				=> '{type_legend},type,title,description;{general_legend},discount,enableCode;{restriction_legend},numUses,dateRestrictions,timeRestrictions,ruleRestrictions,memberRestrictions,productRestrictions;{enabled_legend},enabled',
		'product_collection'	=> '{type_legend},type,title,description;{general_legend},discount,enableCode;{restriction_legend},numUses,collectionTypeRestrictions,dateRestrictions,timeRestrictions,minSubTotal,minCartQuantity,maxCartQuantity,ruleRestrictions,memberRestrictions;{enabled_legend},enabled'
	),
	'subpalettes' => array
	(
		'enableCode'						=> 'code',
		'memberRestrictions_groups'			=> 'groups',
		'memberRestrictions_members'		=> 'members',
		'productRestrictions_producttypes'	=> 'producttypes,minItemQuantity', //,maxItemQuantity',
		'productRestrictions_pages'			=> 'pages,minItemQuantity', //,maxItemQuantity',
		'productRestrictions_products'		=> 'products,minItemQuantity', //,maxItemQuantity',
		'ruleRestrictions_rules'			=> 'rules',
		'dateRestrictions'					=> 'startDate,endDate',
		'timeRestrictions'					=> 'startTime,endTime',	
		'collectionTypeRestrictions'		=> 'collectionType'
	),
	
	// Fields
	'fields' => array
	(
		'type' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['type'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'select',
			'options'					=> array('product','product_collection'),
			'eval'						=> array('includeBlankOption'=>true,'mandatory'=>true,'submitOnChange'=>true),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['type']
			
		),
		'collectionType' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['collectionType'],
			'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'options'					=> &$GLOBALS['ISO_PRODUCTCOLLECTION'],
			'eval'						=> array('multiple'=>true,'mandatory'=>true),
			'reference'					=> &$GLOBALS['TL_LANG']['ISO_PRODUCTCOLLECTION']
		),
		'title' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['title'],
            'exclude'					=> true,
            'search'					=> true,
            'sorting'					=> true,
            'flag'						=> 1,
            'inputType'					=> 'text',
            'eval'						=> array('mandatory'=>true, 'maxlength'=>255)
        ),
		'description' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['description'],
            'exclude'					=> true,
            'search'					=> true,
            'flag'						=> 1,
            'inputType'					=> 'textarea',
			'eval'						=> array('style'=>'height:80px;')
        ),
		'discount' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['discount'],
			'exclude'					=> true,
			'search'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true,'rgxp'=>'calc')
		),
		'enableCode' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['enableCode'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'checkbox',
			'eval'						=> array('submitOnChange'=>true)
		),
		'code' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['code'],
			'exclude'					=> true,
			'search'					=> true,
			'flag'						=> 1,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255)
		),
       'numUses' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['numUses'],
            'exclude'					=> true,
            'flag'						=> 1,
			'default'					=> 1,
			'inputType'					=> 'inputUnit',
			'options'					=> array('customer','store'),
            'eval'						=> array('mandatory'=>false, 'rgxp'=>'digit', 'maxlength'=>255),
     		'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['numUses']
	   	),
	  	'minSubTotal' => array
      	(
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['minSubTotal'],
            'exclude'					=> true,
            'search'					=> true,
            'sorting'					=> true,
            'flag'						=> 1,
            'inputType'					=> 'text',
            'eval'						=> array('rgxp'=>'digit', 'maxlength'=>255)
      	),
	  	'minCartQuantity' => array
      	(
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['minCartQuantity'],
            'exclude'					=> true,
            'search'					=> true,
            'sorting'					=> true,
            'flag'						=> 1,
            'inputType'					=> 'text',
            'eval'						=> array('rgxp'=>'digit', 'maxlength'=>255)
      	),
	  	'maxCartQuantity' => array
      	(
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['maxCartQuantity'],
            'exclude'					=> true,
            'search'					=> true,
            'sorting'					=> true,
            'flag'						=> 1,
            'inputType'					=> 'text',
            'eval'						=> array('rgxp'=>'digit', 'maxlength'=>255)
      	),
	  	'minItemQuantity' => array
      	(
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['minItemQuantity'],
            'exclude'					=> true,
            'search'					=> true,
            'sorting'					=> true,
            'flag'						=> 1,
            'inputType'					=> 'text',
            'eval'						=> array('rgxp'=>'digit', 'maxlength'=>255)
      	),
	  	'maxItemQuantity' => array
      	(
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['maxItemQuantity'],
            'exclude'					=> true,
            'search'					=> true,
            'sorting'					=> true,
            'flag'						=> 1,
            'inputType'					=> 'text',
            'eval'						=> array('rgxp'=>'digit', 'maxlength'=>255)
      	),
       'startDate' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['startDate'],
			'exclude'					=> true,
			'flag'						=> 8,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard')
		),
		'endDate' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['endDate'],
			'exclude'					=> true,
			'flag'						=> 8,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard')
		),
		'startTime' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['startTime'],
			'exclude'					=> true,
			'flag'						=> 8,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'time', 'mandatory'=>true, 'tl_class'=>'w50')
		),
		'endTime' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['endTime'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'time', 'tl_class'=>'w50'),
			'save_callback' => array
			(
				array('tl_iso_rule', 'setEmptyEndTime')
			)
		),
		'collectionTypeRestrictions'	=> array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['collectionTypeRestrictions'],
			'inputType'					=> 'checkbox',
			'eval'						=> array('submitOnChange'=>true)
		),
		'dateRestrictions' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['dateRestrictions'],
			'inputType'					=> 'checkbox',
			'eval'						=> array('submitOnChange'=>true)
		),
		'timeRestrictions' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['timeRestrictions'],
			'inputType'					=> 'checkbox',
			'eval'						=> array('submitOnChange'=>true)
		),
        'memberRestrictions' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions'],
			'inputType'					=> 'radio',
			'default'					=> 'none',
			'exclude'					=> true,
			'filter'					=> true,
			'options'					=> array('none','groups','members'),
			'eval'						=> array('submitOnChange'=>true),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['memberRestrictions']
		
		),
        'productRestrictions' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions'],
			'inputType'					=> 'radio',
			'default'					=> 'none',
			'exclude'					=> true,
			'filter'					=> true,
			'options'					=> array('none','producttypes','pages','products'),
			'eval'						=> array('submitOnChange'=>true),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['productRestrictions']
		
		),
        'ruleRestrictions' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions'],
			'inputType'					=> 'radio',
			'default'					=> 'none',
			'exclude'					=> true,
			'filter'					=> true,
			'options'					=> array('none','all','rules'),
			'eval'						=> array('submitOnChange'=>true),
			'reference'					=> &$GLOBALS['TL_LANG']['tl_iso_rule']['ruleRestrictions']
		
		),
		'rules' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['rules'],
            'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'options_callback'			=> array('tl_iso_rule', 'getCoupons'),
			'eval'						=> array('multiple'=>true, 'mandatory'=>true, 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rule', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rule', 'saveRestrictions'),
			),
        ),
		'groups' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['groups'],
            'exclude'					=> true,
			'inputType'					=> 'checkboxWizard',
			'foreignKey'				=> 'tl_member_group.name',
			'eval'						=> array('multiple'=>true, 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rule', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rule', 'saveRestrictions'),
			),
        ),
        'producttypes' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['producttypes'],
            'exclude'					=> true,
			'inputType'					=> 'checkbox',
			'foreignKey'				=> 'tl_iso_producttypes.name',
			'eval'						=> array('multiple'=>true, 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rule', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rule', 'saveRestrictions'),
			),
        ),
		'pages' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['pages'],
			'exclude'					=> true,
			'inputType'					=> 'pageTree',
			'foreignKey'				=> 'tl_page.title',
			'eval'						=> array('multiple'=>true, 'fieldType'=>'checkbox', 'doNotSaveEmpty'=>true),
			'load_callback' => array
			(
				array('tl_iso_rule', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rule', 'saveRestrictions'),
			),
		),
		'products' 	=> array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['products'],
			'exclude'					=> true,
			'inputType'					=> 'tableLookup',
			'eval' => array
			(
				'mandatory'				=> true,
				'doNotSaveEmpty'		=> true,
				'tl_class'				=> 'clr',
				'foreignTable'			=> 'tl_iso_products',
				'listFields'			=> array('type'=>'(SELECT name FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id)', 'name', 'sku'),
				'searchFields'			=> array('name', 'alias', 'sku', 'description'),
				'sqlWhere'				=> 'pid=0',
				'searchLabel'			=> 'Search products',
			),
			'load_callback' => array
			(
				array('tl_iso_rule', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rule', 'saveRestrictions'),
			),
		),		
		'members' => array
        (
            'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['members'],
            'exclude'					=> true,
            'inputType'					=> 'tableLookup',
			'eval' => array
			(
				'mandatory'				=> true,
				'doNotSaveEmpty'		=> true,
				'tl_class'				=> 'clr',
				'foreignTable'			=> 'tl_member',
				'listFields'			=> array('firstname', 'lastname', 'username','email'),
				'searchFields'			=> array('firstname', 'lastname', 'username','email'),
				'sqlWhere'				=> '',
				'searchLabel'			=> 'Search members',
			),
			'load_callback' => array
			(
				array('tl_iso_rule', 'loadRestrictions'),
			),
			'save_callback' => array
			(
				array('tl_iso_rule', 'saveRestrictions'),
			),
        ),
		'enabled'	=> array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_rule']['enabled'],
			'inputType'					=> 'checkbox',
			'exclude'					=> true,
			'filter'					=> true,
		)
	)
);


class tl_iso_rule extends Backend
{
	public function __construct()
	{
		parent::__construct();
		
		$this->import('BackendUser','User');
		$this->import('Isotope');
	}
	
	public function getCoupons()
	{
		$objCoupons = $this->Database->query("SELECT id, title FROM tl_iso_rule WHERE enabled='1'");
		
		if(!$objCoupons->numRows)
			return array();
		
		while($objCoupons->next())
		{
			if($objCoupons->id==$this->Input->get('id'))
				continue;
				
			$arrReturn[$objCoupons->id] = $objCoupons->title;
		}
		
		return $arrReturn;
	
	}
	
	
	/**
	 * Automatically set the end time if not set
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function setEmptyEndTime($varValue, DataContainer $dc)
	{
		if ($varValue == '')
		{
			$varValue = $dc->activeRecord->startTime;
		}

		return $varValue;
	}


	/**
	 * Load rule restrictions from linked table
	 */
	public function loadRestrictions($varValue, $dc)
	{
		return $this->Database->execute("SELECT object_id FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'")->fetchEach('object_id');
	}
	
	
	/**
	 * Save rule restrictions to linked table. Only update what necessary to prevent the IDs from increasing on every save_callback
	 */
	public function saveRestrictions($varValue, $dc)
	{
		$arrNew = deserialize($varValue);
		
		if (!is_array($arrNew) || !count($arrNew))
		{
			$this->Database->query("DELETE FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'");
		}
		else
		{
			$arrOld = $this->Database->execute("SELECT object_id FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}'")->fetchEach('object_id');
			
			$arrInsert = array_diff($arrNew, $arrOld);
			$arrDelete = array_diff($arrOld, $arrNew);
			
			if (count($arrDelete))
			{
				$this->Database->query("DELETE FROM tl_iso_rule_restrictions WHERE pid={$dc->activeRecord->id} AND type='{$dc->field}' AND object_id IN (" . implode(',', $arrDelete) . ")");
			}
			
			if (count($arrInsert))
			{
				$time = time();
				$this->Database->query("INSERT INTO tl_iso_rule_restrictions (pid,tstamp,type,object_id) VALUES ({$dc->id}, $time, '{$dc->field}', " . implode("), ({$dc->id}, $time, '{$dc->field}', ", $arrInsert) . ")");
			}
		}
		
		//!@todo drop the serialized data and return '' here.
		return $varValue;
	}
}

