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
 * Table tl_product_data
 */
$GLOBALS['TL_DCA']['tl_product_data'] = array
(
	// Config
	'config' => array
	(
		'dataContainer'               => 'DynamicTable',
		'switchToEdit'                => false,
		'enableVersioning'            => false,
		'doNotCopyRecords'            => true,
		'doNotDeleteRecords'          => false,
		'oncreate_callback'			  => array
		(
			array('ProductCatalog', 'loadProductCatalogDCA'),
		),
		'onload_callback'			  => array
		(
			array('MediaManagement', 'createMediaDirectoryStructure')
		),
		'onsubmit_callback'			  => array
		(
			array('ProductCatalog', 'saveProduct')
		),
	),
	
	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('sorting'),
			'flag'                    => 1,
			'panelLayout'             => 'sort,filter;search,limit',
		),
		'label' => array
		(
			'fields'                  => array('product_name'),
			'format'                  => '%s',
		),
		'global_operations' => array
		(
			
			/*'export' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['export'],
				'href'                => 'act=export',
				'class'               => 'header_css_import', // for css icon
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),*/
			'all' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'                => 'act=select',
				'class'               => 'header_edit_all',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
		),
	),
);

