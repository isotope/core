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
 * Table tl_shipping_rates
 */
$GLOBALS['TL_DCA']['tl_shipping_rates'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'					  => 'tl_shipping_modules',
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'sort,filter;search,limit',
			'headerFields'            => array('name', 'tstamp'),
			'child_record_callback'   => array('tl_shipping_rates', 'listrates')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_rates']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_rates']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'

			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_rates']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_rates']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_rates']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => 'description;rate;upper_limit;dest_country,dest_region,dest_zip'
	),

	// Fields
	'fields' => array
	(
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_rates']['description'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'upper_limit' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_rates']['upper_limit'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>digit)
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_rates']['rate'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>digit)
		),
		'dest_zip' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_zip'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64)
		),
		'dest_country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_country'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64)
		),
		'dest_region' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_rates']['dest_region'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>64)
		),
	)
);


/**
 * tl_shipping_rates class.
 * 
 * @extends Backend
 */
class tl_shipping_rates extends Backend
{

	/**
	 * Import the back end user object.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}


	/**
	 * Add the type of input field.
	 * 
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function listrates($arrRow)
	{

		return '
<div class="cte_type ' . $key . '"><strong>' . $arrRow['description'] . '</strong></div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h52' : '') . ' block">
'. $arrRow['rate'] .' for '. $arrRow['upper_limit'] . ' based on ' . $arrRow['dest_country'] .', '. $arrRow['dest_region'] . ', ' . $arrRow['dest_zip'] . '</div>' . "\n";
	}
}

