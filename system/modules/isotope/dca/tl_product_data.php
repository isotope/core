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
 * @author     Andreas Schempp <andreas@schempp.ch>
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
		'ctables'					  => array('tl_product_downloads'),
		'oncreate_callback'			  => array
		(
			array('ProductCatalog', 'loadProductCatalogDCA'),
		)/*,
		'onload_callback'			  => array
		(
			array('tl_product_data', 'checkPermission'),
			array('MediaManagement', 'createMediaDirectoryStructure')
		)*/,
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
			'fields'                  => array('type', 'name'),
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
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['delete'],
				'href'                => 'key=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			),
			'downloads' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['downloads'],
				'href'                => 'table=tl_product_downloads',
				'icon'                => 'system/modules/isotope/html/attach.png',
				'button_callback'	  => array('tl_product_data', 'downloadsButton'),
			),
		),
	),
);


class tl_product_data extends Backend
{

	/**
	 * Show/hide the downloads button
	 */
	public function downloadsButton($row, $href, $label, $title, $icon, $attributes)
	{
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")
								  ->limit(1)
								  ->execute($row['type']);

		if (!$objType->downloads)
			return '';
			
		$objDownloads = $this->Database->prepare("SELECT COUNT(*) AS total FROM tl_product_downloads WHERE pid=?")->execute($row['id']);
			
		return '<p style="padding-top:8px"><a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).' '.sprintf($GLOBALS['TL_LANG']['MSC']['downloadCount'], $objDownloads->total).'</a></p>';
	}
	
	
	/**
	 * Only list non-archived prodcts
	 */
	public function checkPermission($dc)
	{
		$this->import('BackendUser', 'User');
		
		if ($this->User->isAdmin)
			return;
		
		$arrTypes = is_array($this->User->iso_product_types) ? $this->User->iso_product_types : array(0);
		
		$arrProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE type IN ('','" . implode("','", $arrTypes) . "')")->fetchEach('id');
		
		if (!is_array($arrProducts) || !count($arrProducts))
		{
			$arrProducts = array(0);
		}
		
		$GLOBALS['TL_DCA']['tl_product_data']['list']['sorting']['root'] = $arrProducts;
		
		if (strlen($this->Input->get('id')) && !in_array($this->Input->get('id'), $arrProducts))
		{
			$this->redirect('typolight/main.php?act=error');
		}
	}
}

