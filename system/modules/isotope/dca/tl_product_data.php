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
		'ctables'					  => array('tl_product_downloads'),
		'oncreate_callback'			  => array
		(
			array('ProductCatalog', 'loadProductCatalogDCA'),
		),
		'onload_callback'			  => array
		(
			array('tl_product_data', 'checkPermission'),
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
			'archived' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['archived'],
				'href'                => 'key=archived',
				'attributes'          => 'onclick="Backend.getScrollOffset();"',
				'button_callback'     => array('tl_product_data', 'archivedButton'),
			),
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
			'downloads' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_product_data']['downloads'],
				'href'                => 'table=tl_product_downloads',
				'icon'                => 'system/modules/isotope/html/attach.png',
				'button_callback'	  => array('tl_product_data', 'downloadsButton'),
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
		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE alias=?")
								  ->limit(1)
								  ->execute($row['type']);

		return ($objType->downloads) ? '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ' : '';
	}
	
	
	public function archivedButton($href, $label, $title, $attributes, $table)
	{
		$this->import('BackendUser', 'User');
		
		return ($this->User->isAdmin) ? '&nbsp;&nbsp;::&nbsp;&nbsp;<a href="'.$this->addToUrl($href).'" class="header_archived_products" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ' : '';
	}
	
	
	/**
	 * Archive products if it has been ordered
	 */
	public function deleteOrArchiveProduct($dc)
	{
		$objProduct = $this->Database->prepare("SELECT archived FROM tl_iso_order_items o LEFT OUTER JOIN tl_product_data p ON p.id=o.product_id WHERE p.id=?")->limit(1)->execute($dc->id);
		
		if ($objProduct->archived)
		{
			$this->Database->prepare("UPDATE tl_product_data SET archived='' WHERE id=?")->execute($dc->id);
		}
		elseif ($objProduct->numRows)
		{
			$this->Database->prepare("UPDATE tl_product_data SET archived=1 WHERE id=?")->execute($dc->id);
		}
		else
		{
			$this->redirect(str_replace('key=delete', 'act=delete', $this->Environment->request));
		}
		
		$this->redirect('typolight/main.php?do=product_manager');
	}
	
	
	/**
	 * Only list non-archived prodcts
	 */
	public function checkPermission($dc)
	{
		$this->import('BackendUser', 'User');
		
		$arrTypes = array();
		$objTypes = $this->Database->execute("SELECT * FROM tl_product_types");
		
		while( $objTypes->next() )
		{
			if ($objTypes->protected && !$this->User->isAdmin)
			{
				$arrGroups = deserialize($objTypes->groups, true);
				
				if (!is_array($this->User->groups) || !count(array_intersect($arrGroups, $this->User->groups)))
					continue;
			}
			
			$arrTypes[] = $objTypes->alias;
		}
		
		$arrProducts = $this->Database->execute("SELECT id FROM tl_product_data WHERE type IN ('" . implode("','", $arrTypes) . "')" . ($this->Input->get('key') == 'archived' ? '' : " AND archived=''"))->fetchEach('id');
		
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

