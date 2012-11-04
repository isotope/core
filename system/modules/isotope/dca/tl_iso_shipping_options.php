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

namespace Isotope;


/**
 * Table tl_iso_shipping_options
 */
$GLOBALS['TL_DCA']['tl_iso_shipping_options'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'					  => 'tl_iso_shipping_modules',
		'enableVersioning'            => true,
		'onload_callback'			  => array
		(
			array('tl_iso_shipping_options', 'getModulePalette'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('name'),
			'panelLayout'             => 'sort,filter;search,limit',
			'headerFields'            => array('name', 'type'),
			'disableGrouping'		  => true,
			'child_record_callback'   => array('tl_iso_shipping_options', 'listRow')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'

			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{general_legend},name',

	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('maxlength'=>255)
		),
		'minimum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['minimum_total'],
			'exclude'                 => true,
			'default'                 => 0,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'rgxp'=>'price', 'tl_class'=>'w50'),
		),
		'maximum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total'],
			'exclude'                 => true,
			'default'                 => 0,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'rgxp'=>'price', 'tl_class'=>'w50'),
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>'price', 'tl_class'=>'w50'),
		),
		'weight_from' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_from'],
			'exclude'                 => true,
			'default'                 => 0,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'weight_to' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['weight_to'],
			'exclude'                 => true,
			'default'                 => 0,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>32, 'rgxp'=>'digit', 'tl_class'=>'w50'),
		),
		'enabled' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['enabled'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
		),
	)
);


/**
 * Class tl_iso_shipping_options
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_iso_shipping_options extends Backend
{

	/**
	 * The current shipping class. Instantiated by the onload_callback.
	 * @param object
	 */
	protected $Shipping;


	/**
	 * Instantiate the shipping module and set the palette
	 * @param object
	 * @return void
	 */
	public function getModulePalette($dc)
	{
		if ($this->Input->get('act') == 'create')
		{
			return;
		}

		if (!strlen($this->Input->get('act')) && !strlen($this->Input->get('key')))
		{
			$objModule = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE id=".$dc->id);
		}
		else
		{
			$objModule = $this->Database->execute("SELECT m.* FROM tl_iso_shipping_modules m, tl_iso_shipping_options o WHERE o.pid=m.id AND o.id=".$dc->id);
		}

		$strClass = $GLOBALS['ISO_SHIP'][$objModule->type];

		if ($strClass != '' && $this->classFileExists($strClass))
		{
			$this->Shipping = new $strClass($objModule->row());
			$this->Shipping->moduleOptionsLoad();
		}
	}


	/**
	 * Get a formatted listing for this row from shipping module class
	 * @param array
	 * @return string
	 */
	public function listRow($row)
	{
		if (!is_object($this->Shipping))
		{
			return '';
		}

		return $this->Shipping->moduleOptionsList($row);
	}
}

