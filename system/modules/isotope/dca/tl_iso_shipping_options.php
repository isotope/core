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


// Load country sub-divisions
$this->loadLanguageFile('subdivisions');


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
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'

			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
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
		'default'                     => '',
		
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
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit'),
		),
		'maximum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit'),
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>digit)
		)
	)
);


/**
 * tl_iso_shipping_options class.
 * 
 * @extends Backend
 */
class tl_iso_shipping_options extends Backend
{

	/**
	 * The current shipping class. Instantiated by the onload callback.
	 */
	protected $Shipping;

	
	/**
	 * Instantiate the shipping module and set the palette.
	 */	
	public function getModulePalette($dc)
	{
		if ($this->Input->get('act') == 'create')
			return;
			
		$objModule = $this->Database->execute("SELECT m.* FROM tl_iso_shipping_modules m, tl_iso_shipping_options o WHERE o.pid=m.id AND o.id=".$dc->id);
		$strClass = $GLOBALS['ISO_SHIP'][$objModule->type];
		
		if ($this->classFileExists($strClass))
		{
			$this->Shipping = new $strClass($objModule->row());
			$GLOBALS['TL_DCA']['tl_iso_shipping_options']['palettes']['default'] = $this->Shipping->moduleOptionsPalette();
		}
	}
	
	
	/**
	 * Get a formatted listing for this row from shipping module class.
	 */
	public function listRow($arrRow)
	{
		if (!is_object($this->Shipping))
			return '';
		
		return $this->Shipping->moduleOptionsList($row);
	}
}

