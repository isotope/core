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
 * Table tl_iso_tax_class
 */
$GLOBALS['TL_DCA']['tl_iso_tax_class'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'closed'					  => true,
		'onload_callback' => array
		(
			array('IsotopeBackend', 'initializeSetupModule'),
			array('Isotope\tl_iso_tax_class', 'checkPermission'),
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
			'fields'                  => array('name', 'fallback'),
			'format'                  => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>'
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'					=> 'mod=&table=',
				'class'					=> 'header_back',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_class']['new'],
				'href'					=> 'act=create',
				'class'					=> 'header_new',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'button_callback'     => array('Isotope\tl_iso_tax_class', 'copyTaxClass'),
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback'     => array('Isotope\tl_iso_tax_class', 'deleteTaxClass'),
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name,fallback;{rate_legend},includes,label,rates,applyRoundingIncrement',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['name'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'fallback' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_class']['fallback'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('fallback'=>true, 'tl_class'=>'w50 m12'),
		),
		'includes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['includes'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options_callback'		  => array('Isotope\tl_iso_tax_class', 'getTaxRates'),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['label'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
		),
		'rates' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_class']['rates'],
			'exclude'                 => true,
			'inputType'               => 'checkboxWizard',
			'options_callback'		  => array('Isotope\tl_iso_tax_class', 'getTaxRates'),
			'eval'                    => array('multiple'=>true, 'tl_class'=>'clr w50 w50h'),
		),
		'applyRoundingIncrement' => array
		(
			'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_class']['applyRoundingIncrement'],
			'exclude'				=> true,
			'inputType'				=> 'checkbox',
			'eval'					=> array('tl_class'=>'w50'),
		),
	)
);
