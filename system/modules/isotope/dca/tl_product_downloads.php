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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_product_downloads
 */
$GLOBALS['TL_DCA']['tl_product_downloads'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_product_data',
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('title'),
			'flag'						=> 1,
			'panelLayout'				=> 'filter;search,limit',
			'headerFields'				=> array('id'),
			'child_record_callback'		=> array('tl_product_downloads', 'listRows')
		),
		'label' => array
		(
			'fields'					=> array('title'),
			'format'					=> '%s',
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
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_downloads']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_downloads']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_downloads']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_downloads']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{file_legend},singleSRC;{name_legend},title,description;{limit_legend},downloads_allowed',
	),

	// Fields
	'fields' => array
	(
		'singleSRC' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_downloads']['singleSRC'],
			'exclude'					=> true,
			'inputType'					=> 'fileTree',
			'eval'						=> array('mandatory'=>true, 'fieldType'=>'radio', 'files'=>true, 'filesOnly'=>true, 'extensions'=>$GLOBALS['TL_CONFIG']['allowedDownload']),
		),
		'title' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_downloads']['title'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'long'),
		),
		'description' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_downloads']['description'],
			'exclude'					=> true,
			'inputType'					=> 'textarea',
			'eval'						=> array('rte'=>'tinyMCE'),
		),
		'downloads_allowed' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_downloads']['downloads_allowed'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>5, 'rgxp'=>'digits', 'tl_class'=>'w50'),
		),
	)
);


class tl_product_downloads extends Backend
{

	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function listRows($row)
	{
		return $row['title'];
	}
}

