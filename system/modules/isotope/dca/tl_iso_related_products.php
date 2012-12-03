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
 */


/**
 * Table tl_iso_related_products
 */
$GLOBALS['TL_DCA']['tl_iso_related_products'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_iso_products',
		'onload_callback' => array
		(
			array('Isotope\tl_iso_related_products', 'initDCA')
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('category'),
			'flag'						=> 1,
			'panelLayout'				=> 'filter,limit',
			'headerFields'				=> array('type', 'name', 'alias', 'sku'),
			'child_record_callback'		=> array('Isotope\tl_iso_related_products', 'listRows')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			)
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{category_legend},category;{products_legend},products',
	),

	// Fields
	'fields' => array
	(
		'category' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['category'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'select',
			'foreignKey'				=> 'tl_iso_related_categories.name',
			'eval'						=> array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true)
		),
		'products' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['products'],
			'exclude'					=> true,
			'inputType'					=> 'productTree',
			'eval'						=> array('mandatory'=>true, 'fieldType'=>'checkbox', 'variants'=>true, 'tl_class'=>'clr'),
		),
	)
);
