<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
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
			array('Isotope\Backend', 'initializeSetupModule'),
			array('Isotope\tl_iso_attributes', 'disableFieldName'),
			array('Isotope\tl_iso_attributes', 'prepareForVariantOptions'),
		),
		'onsubmit_callback' => array
		(
			array('Isotope\tl_iso_attributes', 'modifyColumn'),
			array('Isotope\tl_iso_attributes', 'cleanFieldValues'),
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
		'__selector__'				=> array('type', 'variant_option', 'storeFile'),
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
		'fileTree'					=> '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,extensions,path,files,filesOnly,mandatory',
		'downloads'					=> '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},fieldType,extensions,sortBy,path,files,filesOnly,mandatory',
		'upload'					=> '{attribute_legend},name,field_name,type,legend;{description_legend:hide},description;{config_legend},extensions,maxlength,mandatory;{store_legend:hide},storeFile',
    ),

	// Subpalettes
	'subpalettes' => array
	(
		'storeFile'					=> 'uploadFolder,useHomeDir,doNotOverwrite',
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
				array('Isotope\tl_iso_attributes', 'createColumn'),
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
				array('Isotope\tl_iso_attributes', 'validateForeignKey'),
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
			'options_callback'		=> array('Isotope\tl_iso_attributes', 'getRTE'),
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
			'options_callback'		=> array('Isotope\tl_iso_attributes', 'getConditionFields'),
			'eval'					=> array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50', 'chosen'=>true)
		),
		'gallery' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_attributes']['gallery'],
			'exclude'				=> true,
			'inputType'				=> 'select',
			'default'				=> 'standard',
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
			'eval'					=> array('tl_class'=>'w50'),
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
				array('Isotope\tl_iso_attributes', 'validateDatepicker'),
			),
		),
	),
);
