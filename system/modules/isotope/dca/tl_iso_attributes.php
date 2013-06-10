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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Load tl_iso_products language file for field legends
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
			array('IsotopeBackend', 'initializeSetupModule'),
			array('tl_iso_attributes', 'disableFieldName'),
			array('tl_iso_attributes', 'prepareForVariantOptions'),
		),
		'onsubmit_callback' => array
		(
			array('tl_iso_attributes', 'modifyColumn'),
			array('tl_iso_attributes', 'cleanFieldValues'),
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
			'fields'				=> array('name', 'field_name', 'type'),
			'format'				=> '<div style="float:left; width:200px">%s</div><div style="float:left; width:200px; color:#b3b3b3;">%s</div><div style="color:#b3b3b3">[%s]</div>'
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'				=> 'mod=&table=',
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
			'copy' => array
			(
				'label'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['copy'],
				'href'				=> 'act=copy',
				'icon'				=> 'copy.gif'
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
		'__selector__'				=> array('type', 'variant_option', 'storeFile', 'files'),
		'default'					=> '{attribute_legend},name,field_name,type,legend',
		'text'						=> '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},rgxp,maxlength,mandatory,multilingual,datepicker;{search_filters_legend},fe_search,fe_sorting,be_search',
		'textarea'					=> '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{config_legend},rgxp,rte,mandatory,multilingual;{search_filters_legend},fe_search,fe_sorting,be_search',
		'select'					=> '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory,multiple,size;{search_filters_legend},fe_filter,fe_sorting,be_filter,fe_search',
		'selectvariant_option'		=> '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},options,foreignKey;{search_filters_legend},fe_filter,fe_sorting,be_filter',
		'radio'						=> '{attribute_legend},name,field_name,type,legend,variant_option,customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory;{search_filters_legend},fe_filter,fe_sorting',
		'radiovariant_option'		=> '{attribute_legend},name,field_name,type,legend,variant_option;{description_legend:hide},description;{options_legend},options,foreignKey;{search_filters_legend},fe_filter,fe_sorting,be_filter',
		'checkbox'					=> '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory,multiple;{search_filters_legend},fe_filter,fe_sorting',
		'conditionalselect'			=> '{attribute_legend},name,field_name,type,legend,customer_defined;{description_legend:hide},description;{options_legend},options,foreignKey;{config_legend},mandatory,multiple,size,conditionField;{search_filters_legend},fe_filter,fe_sorting',
		'mediaManager'				=> '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},gallery,extensions,mandatory',
		'fileTree'					=> '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,extensions,path,mandatory,files',
		'downloads'					=> '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,extensions,sortBy,path,mandatory,files',
		'upload'					=> '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,maxlength,mandatory;{store_legend:hide},storeFile',
    ),

	// Subpalettes
	'subpalettes' => array
	(
		'storeFile'					=> 'uploadFolder,useHomeDir,doNotOverwrite',
		'files'                     => 'filesOnly',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['name'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'field_name' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['field_name'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('mandatory'=>true, 'maxlength'=>30, 'unique'=>true, 'doNotCopy'=>true, 'doNotSaveEmpty'=>true, 'tl_class'=>'w50'),
			'save_callback'			=> array
			(
				array('tl_iso_attributes', 'createColumn'),
			),
		),
		'type' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['type'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options'				=> array_keys($GLOBALS['ISO_ATTR']),
			'eval'					=> array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'helpwizard'=>true, 'tl_class'=>'w50', 'chosen'=>true),
			'reference'				=> &$GLOBALS['ISO_LANG']['ATTR']
		),
		'legend' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['legend'],
			'exclude'				=> true,
			'default'				=> 'options_legend',
			'inputType'				=> 'select',
			'options'				=> array('general_legend', 'meta_legend', 'pricing_legend', 'inventory_legend', 'shipping_legend', 'options_legend', 'media_legend', 'expert_legend', 'publish_legend'),
			'reference'				=> &$GLOBALS['TL_LANG']['tl_iso_products'],
			'eval'					=> array('mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'description' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['description'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'eval'					=> array('maxlength'=>255, 'tl_class'=>'clr long'),
		),
		'options' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['options'],
			'exclude'				=> true,
			'inputType'				=> 'multiColumnWizard',
			'eval'					=> array
			(
				'tl_class'			=> 'clr',
				'columnFields' => array
				(
					'value' => array
					(
						'label'		=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['value'],
						'inputType'	=> 'text',
						'eval'		=> array('class'=>'tl_text_2'),
					),
					'label' => array
					(
						'label'		=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['label'],
						'inputType'	=> 'text',
						'eval'		=> array('class'=>'tl_text_2'),
					),
					'default' => array
					(
						'label'		=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['default'],
						'inputType'	=> 'checkbox',
						'eval'		=> array('columnPos'=>2),
					),
					'group' => array
					(
						'label'		=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['options']['group'],
						'inputType'	=> 'checkbox',
						'eval'		=> array('columnPos'=>3),
					),
				),
			),
		),
		'foreignKey' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['foreignKey'],
			'exclude'				=> true,
			'inputType'				=> 'textarea',
			'eval'					=> array('style'=>'height:80px', 'decodeEntities'=>true),
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
		'be_search' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['be_search'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'be_filter' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['be_filter'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
		),
		'customer_defined' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['customer_defined'],
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
		'fe_filter' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_filter'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'fe_search' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_search'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'fe_sorting' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['fe_sorting'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox'
		),
		'multiple' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['multiple'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'size' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['size'],
			'exclude'				=> true,
			'inputType'				=> 'text',
			'default'				=> 5,
			'eval'					=> array('rgxp'=>'digit', 'tl_class'=>'w50'),
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
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
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
			'options'				=> array('digit', 'alpha', 'alnum', 'extnd', 'date', 'time', 'datim', 'phone', 'email', 'url', 'price', 'discount', 'surcharge'),
			'reference'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes'],
			'eval'					=> array('helpwizard'=>true, 'includeBlankOption'=>true, 'tl_class'=>'w50', 'chosen'=>true)
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
			'eval'					=> array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'gallery' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'default'				=> 'default',
			'options'				=> array_keys($GLOBALS['ISO_GAL']),
			'reference'				=> &$GLOBALS['ISO_LANG']['GAL'],
			'eval'					=> array('includeBlankOption'=>true, 'tl_class'=>'w50', 'helpwizard'=>true),
		),
		'fieldType' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['fieldType'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options'				=> array('checkbox', 'radio'),
			'reference'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes'],
			'eval'					=> array('tl_class'=>'w50'),
		),
		'files' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['files'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true, 'tl_class'=>'w50'),
		),
		'filesOnly' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['filesOnly'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
		'sortBy' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['sortBy'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'options'				=> array('name_asc', 'name_desc', 'date_asc', 'date_desc', 'meta', 'random'),
			'reference'				=> &$GLOBALS['TL_LANG']['tl_iso_attributes'],
			'eval'					=> array('tl_class'=>'w50')
		),
		'path' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['path'],
			'exclude'				=> true,
			'inputType'				=> 'fileTree',
			'eval'					=> array('fieldType'=>'radio', 'tl_class'=>'clr')
		),
		'storeFile' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['storeFile'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('submitOnChange'=>true)
		),
		'uploadFolder' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['uploadFolder'],
			'exclude'				=> true,
			'inputType'				=> 'fileTree',
			'eval'					=> array('fieldType'=>'radio', 'tl_class'=>'clr')
		),
		'useHomeDir' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['useHomeDir'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50')
		),
		'doNotOverwrite' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['doNotOverwrite'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50')
		),
		'datepicker' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['datepicker'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
			'save_callback'			=> array
			(
				array('tl_iso_attributes', 'validateDatepicker'),
			),
		),
	),
);


/**
 * Class tl_iso_attribuets
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_attributes extends Backend
{

	/**
	 * Disable the internal field name field if it is not empty.
	 * @param object
	 * @return	void
	 */
	public function disableFieldName($dc)
	{
		// Hide the field in editAll & overrideAll mode (Thanks to Yanick Witschi)
		if ($this->Input->get('act') == 'editAll' || $this->Input->get('act') == 'overrideAll')
		{
			$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['doNotShow'] = true;
		}
		elseif ($dc->id)
		{
			$objAttribute = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");

			if ($objAttribute->field_name != '')
			{
				$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['disabled'] = true;
				$GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['field_name']['eval']['mandatory'] = false;
			}
		}
	}


	/**
	 * Hide certain options if this is a variant option
	 * @param DataContainer
	 */
	public function prepareForVariantOptions($dc)
	{
		$objAttribute = $this->Database->prepare("SELECT * FROM tl_iso_attributes WHERE id=?")->execute($dc->id);

		if ($objAttribute->variant_option)
		{
			unset($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['options']['eval']['columnFields']['default']);
			unset($GLOBALS['TL_DCA']['tl_iso_attributes']['fields']['options']['eval']['columnFields']['group']);
		}
	}


	/**
	 * Create an attribute column in tl_iso_products table
	 * @param mixed
	 * @param object
	 * @return mixed
	 * @throws Exception
	 */
	public function createColumn($varValue, $dc)
	{
		$varValue = standardize($varValue);

		if (in_array($varValue, array('id', 'pid', 'tstamp', 'dateAdded', 'type', 'language', 'pages', 'inherit', 'minimum_quantity')))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $varValue));
		}

		if ($varValue != '' && !$this->Database->fieldExists($varValue, 'tl_iso_products'))
		{
			$strType = $GLOBALS['ISO_ATTR'][$this->Input->post('type')]['sql'] == '' ? 'text' : $this->Input->post('type');

			$this->Database->query(sprintf("ALTER TABLE tl_iso_products ADD %s %s", $varValue, $GLOBALS['ISO_ATTR'][$strType]['sql']));
		}

		return $varValue;
	}


	/**
	 * Alter an attribtue column in tl_iso_products table
	 * @param object
	 * @return void
	 */
	public function modifyColumn($dc)
	{
		$objAttribute = $this->Database->execute("SELECT * FROM tl_iso_attributes WHERE id={$dc->id}");

		if ($objAttribute->field_name == '')
		{
			return;
		}

		if ($dc->activeRecord->type != '' && $objAttribute->type != $dc->activeRecord->type && $GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['sql'] != '' && $this->Database->fieldExists($dc->activeRecord->field_name, 'tl_iso_products'))
		{
			$this->Database->query(sprintf("ALTER TABLE tl_iso_products MODIFY %s %s", $objAttribute->field_name, $GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['sql']));
		}

		if ($objAttribute->fe_filter && $GLOBALS['ISO_ATTR'][$dc->activeRecord->type]['useIndex'])
		{
			$arrFields = $this->Database->listFields('tl_iso_products');

			if ($arrFields[$objAttribute->field_name]['type'] != 'index')
			{
				$this->Database->query("ALTER TABLE `tl_iso_products` ADD KEY `{$objAttribute->field_name}` (`{$objAttribute->field_name}`);");
			}
		}
	}


	/**
	 * Remove field that are not available in certain attributes and could cause unwanted results
	 * @param object
	 * @return void
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
		$arrSubpalettes = trimsplit(',', implode(',', (array)$GLOBALS['TL_DCA']['tl_iso_attributes']['subpalettes']));

		$arrClean = array_diff($arrFields, $arrKeep, $arrSubpalettes, array('pid', 'sorting'));

		$this->Database->execute("UPDATE tl_iso_attributes SET " . implode("='', ", $arrClean) . "='' WHERE id={$dc->id}");
	}


	/**
	 * Return an array of select-attributes
	 * @param object
	 * @return array
	 */
	public function getConditionFields($dc)
	{
		$this->loadDataContainer('tl_iso_products');
		$arrFields = array();

		foreach ($GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData)
		{
			if ($arrData['inputType'] == 'select' || ($arrData['inputType'] == 'conditionalselect' && $field != $dc->activeRecord->field_name))
			{
				$arrFields[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrFields;
	}


	/**
	 * Return a list of available rte config files
	 * @param object
	 * @return array
	 */
	public function getRTE($dc)
	{
		$arrOptions = array();

		foreach (scan(TL_ROOT . '/system/config') as $file)
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
	 * @param mixed
	 * @param object
	 * @return mixed
	 */
	public function validateForeignKey($varValue, $dc)
	{
		if ($varValue != '')
		{
			$arrLines = trimsplit('@\r\n|\n|\r@', $varValue);

			foreach ($arrLines as $foreignKey)
			{
				if ($foreignKey == '' || strpos($foreignKey, '#') === 0)
				{
					continue;
				}

				if (strpos($foreignKey, '=') === 2)
				{
					$foreignKey = substr($foreignKey, 3);
				}

				list($strTable, $strField) = explode('.', $foreignKey, 2);
				$this->Database->execute("SELECT $strField FROM $strTable");
			}
		}

		return $varValue;
	}


	/**
	 * To enable date picker, the rgxp must be date, time or datim
	 * @param mixed
	 * @param object
	 * @return mixed
	 */
	public function validateDatepicker($varValue, $dc)
	{
		if ($varValue && !in_array($dc->activeRecord->rgxp, array('date', 'time', 'datim')))
		{
			throw new Exception($GLOBALS['ISO_LANG']['ERR']['datepickerRgxp']);
		}

		return $varValue;
	}
}

