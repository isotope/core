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


/**
 * Load language file for field legends
 */
$this->loadLanguageFile('tl_iso_products');


/**
 * Table tl_iso_attributes 
 */
$GLOBALS['TL_DCA']['tl_iso_attributes'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'				=> 'Table',
		'enableVersioning'			=> true,
		'closed'					=> true,
		'onload_callback'			=> array
		(
			array('tl_iso_attributes', 'checkPermission'),
			array('tl_iso_attributes', 'disableFieldName'),
		),
		'onsubmit_callback' => array
		(
			array('tl_iso_attributes', 'modifyColumn'),
			array('tl_iso_attributes', 'cleanFieldValues'),
		),
		'ondelete_callback' => array
		(
			array('tl_iso_attributes', 'deleteAttribute'),
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'					=> 1,
			'fields'				=> array('legend', 'name'),
			'flag'					=> 1,
			'panelLayout'			=> 'sort,filter,search,limit'
		),
		'label' => array
		(
			'fields'				=> array('name', 'type'),
			'format'				=> '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>'
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'				=> 'mod=',
				'class'				=> 'header_back',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['new'],
				'href'				=> 'act=create',
				'class'				=> 'header_new',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"',
			),
			'all' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'				=> 'act=select',
				'class'				=> 'header_edit_all',
				'attributes'		=> 'onclick="Backend.getScrollOffset();"'
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['edit'],
				'href'				=> 'act=edit',
				'icon'				=> 'edit.gif'
			),
			'delete' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['delete'],
				'href'				=> 'act=delete',
				'icon'				=> 'delete.gif',
				'attributes'		=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['tl_iso_attributes']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['show'],
				'href'				=> 'act=show',
				'icon'				=> 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				=> array('type', 'multiple', 'variant_option'),
		'default'					=> '{attribute_legend},name,field_name,type,legend',
		'text'						=> '{attribute_legend},name,field_name,type,legend,is_customer_defined;{description_legend:hide},description;{config_legend},rgxp,maxlength,mandatory,multilingual;{search_filters_legend},is_searchable,is_order_by_enabled,is_be_searchable',
		'textarea'					=> '{attribute_legend},name,field_name,type,legend,is_customer_defined;{description_legend:hide},description;{config_legend},rgxp,rte,mandatory,multilingual;{search_filters_legend},is_searchable,is_order_by_enabled,is_be_searchable',
		'select'					=> '{attribute_legend},name,field_name,type,legend,variant_option,is_customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory,multiple;{search_filters_legend},is_filterable,is_order_by_enabled,is_be_filterable',
		'selectvariant_option'		=> '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},options',
		'radio'						=> '{attribute_legend},name,field_name,type,legend,variant_option,is_customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory;{search_filters_legend},is_filterable,is_order_by_enabled',
		'radiovariant_option'		=> '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},options',
		'checkbox'					=> '{attribute_legend},name,field_name,type,legend,is_customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory;{search_filters_legend},is_filterable,is_order_by_enabled',
		'conditionalselect'			=> '{attribute_legend},name,field_name,type,legend,is_customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},conditionField,mandatory,multiple;{search_filters_legend},is_filterable,is_order_by_enabled',
		'mediaManager'				=> '{attribute_legend},name,field_name,type,legend,is_customer_defined;{description_legend:hide},description;{config_legend},gallery,extensions,mandatory',
    ),

    // Subpalettes
    'subpalettes' => array
    (
		'multiple'					=> 'size',
    ),

    // Fields
	'fields' => array
	(
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['name'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'field_name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'],
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>30, 'unique'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
			'save_callback'			=> array
			(
				array('tl_iso_attributes', 'createColumn'),
			),
		),
		'type' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['type'],
			'inputType'				=> 'select',
			'options'				=> array_keys($GLOBALS['ISO_ATTR']),
			'eval'					=> array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50'),
			'reference'				=> &$GLOBALS['TL_LANG']['ATTR'],
		),
		'legend' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['legend'],
			'inputType'				=> 'select',
			'default'				=> 'options_legend',
			'options'				=> array('general_legend', 'meta_legend', 'pricing_legend', 'inventory_legend', 'shipping_legend', 'options_legend', 'media_legend', 'publish_legend'),
			'reference'				=> &$GLOBALS['TL_LANG']['tl_iso_products'],
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50'),
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['description'],
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>255, 'tl_class'=>'clr long'),
		),
		'options' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['options'],
			'exclude'				=> true,
			'inputType'				=> 'optionWizard',
			'eval'					=> array('tl_class'=>'clr'),
		),
		'foreignKey' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>64),
			'save_callback' => array
			(
				array('tl_iso_attributes', 'validateForeignKey'),
			),
		),
		'variant_option' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['variant_option'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'w50'),
		),
		'is_be_searchable' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['is_be_searchable'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'is_be_filterable' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['is_be_filterable'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
		),
		'is_customer_defined' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['is_customer_defined'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'mandatory' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['mandatory'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'is_filterable' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['is_filterable'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'is_searchable' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['is_searchable'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'is_order_by_enabled' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['is_order_by_enabled'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'multiple' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'clr'),
		),
		'size' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['size'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'default'				=> 5,
			'eval'					=> array('rgxp'=>'digit'),
		),
		'extensions' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['extensions'],
			'exclude'				=> true,
			'default'				=> 'jpg,jpeg,gif,png',
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'extnd', 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'rte' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['rte'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options_callback'		=> array('tl_iso_attributes', 'getRTE'),
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'multilingual' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['multilingual'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'rgxp' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['rgxp'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options'				=> array('digit', 'alpha', 'alnum', 'extnd', 'date', 'time', 'datim', 'phone', 'email', 'url', 'price', 'discount'),
			'reference'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes'],
			'eval'					=> array('helpwizard'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'maxlength' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['maxlength'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('rgxp'=>'digit', 'tl_class'=>'w50')
		),
		'conditionField' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['conditionField'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options_callback'		=> array('tl_iso_attributes', 'getConditionFields'),
			'eval'					=> array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'gallery' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'default'				=> 'default',
			'options'				=> array_keys($GLOBALS['ISO_GAL']),
			'reference'				=> &$GLOBALS['TL_LANG']['GAL'],
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50', 'helpwizard'=>true),
		),
	)
);


class tl_iso_attributes extends Backend
{

	public function checkPermission($dc)
	{
		if (strlen($this->Input->get('act')))
		{
			$GLOBALS['TL_DCA']['tl_iso_attributes']['config']['closed'] = false;
		}
	}
	
	
	public function deleteAttribute($dc)
	{
		if ($dc->id)
		{
			$objAttribute = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");
			
			if ($this->Database->fieldExists($objAttribute->field_name, 'tl_iso_products'))
			{
				$this->import('IsotopeDatabase');
				$this->IsotopeDatabase->delete($objAttribute->field_name);
			}
		}
	}
	
	
	public function disableFieldName($dc)
	{
		if ($dc->id)
		{
			$objAttribute = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");
			
			if ($objAttribute->field_name != '')
			{
				$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['disabled'] = true;
				$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['mandatory'] = false;
			}
		}
	}
	
	
	public function createColumn($varValue, $dc)
	{
		$varValue = standardize($varValue);
		
		if (in_array($varValue, array('id', 'pid', 'sorting', 'tstamp')))
		{
			throw new Exception($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue);
			return '';
		}
		
		if (strlen($varValue) && !$this->Database->fieldExists($varValue, 'tl_iso_products'))
		{
			$strType = strlen($GLOBALS['ISO_ATTR'][$this->Input->post('type')]['sql']) ? $this->Input->post('type') : 'text';
			
			$this->Database->query(sprintf("ALTER TABLE tl_iso_products ADD %s %s", $varValue, $GLOBALS['ISO_ATTR'][$strType]['sql']));
			
			$this->import('IsotopeDatabase');
			$this->IsotopeDatabase->add($varValue, $GLOBALS['ISO_ATTR'][$strType]['sql']);
		}
		
		return $varValue;
	}
	
	
	public function modifyColumn($dc)
	{
		$objAttribute = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");
		
		if ($objAttribute->type != $dc->activeRecord->type && strlen($dc->activeRecord->type) && strlen($GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['sql']) && $this->Database->fieldExists($dc->activeRecord->field_name, 'tl_iso_products'))
		{
			$this->Database->query(sprintf("ALTER TABLE tl_iso_products MODIFY %s %s", $objAttribute->field_name, $GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['sql']));
		}
	}
	
	
	/**
	 * Remove field that are not available in certain attributes and could cause unwanted results
	 */
	public function cleanFieldValues($dc)
	{
		$strPalette = $GLOBALS['TL_DCA']['tl_iso_attributes']['palettes'][$dc->activeRecord->type];
		
		if ($dc->activeRecord->variant_option && $GLOBALS['TL_DCA']['tl_iso_attributes']['palettes'][$dc->activeRecord->type.'variant_option'] != '')
		{
			$strPalette = $GLOBALS['TL_DCA']['tl_iso_attributes']['palettes'][$dc->activeRecord->type.'variant_option'];
		}
		
		$arrFields = array_keys($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']);
		$arrKeep = trimsplit(',|;', $strPalette);
		
		$arrClean = array_diff($arrFields, $arrKeep);
		
		$this->Database->execute("UPDATE tl_iso_attributes SET " . implode("='', ", $arrClean) . "='' WHERE id={$dc->id}");
	}
	
	
	/**
	 * Returns an array of select-attributes
	 */
	public function getConditionFields($dc)
	{
		$this->loadDataContainer('tl_iso_products');
		
		$arrFields = array();
		
		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['inputType'] == 'select')
			{
				$arrFields[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}
		
		return $arrFields;
	}
	
	
	/**
	 * Returns a list of available rte config files
	 */
	public function getRTE($dc)
	{
		$arrOptions = array();
		
		foreach( scan(TL_ROOT . '/system/config') as $file )
		{
			if (is_file(TL_ROOT . '/system/config/' . $file) && strpos($file, 'tiny') === 0)
			{
				$arrOptions[] = basename($file, '.php');
			}
		}
		
		return $arrOptions;
	}
	
	
	/**
	 * Validate table and field of foreignKey
	 */
	public function validateForeignKey($varValue, $dc)
	{
		if ($varValue != '')
		{
			list($strTable, $strField) = explode('.', $varValue);
			
			$this->Database->query("SELECT $strField FROM $strTable");
		}
		
		return $varValue;
	}
}

