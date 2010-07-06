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
			array('tl_iso_related_products', 'initDCA')
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
			'child_record_callback'		=> array('tl_iso_related_products', 'listRows')
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
			'eval'						=> array('mandatory'=>true, 'includeBlankOption'=>true),
		),
		'products' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_related_products']['products'],
			'exclude'					=> true,
			'inputType'					=> 'tableLookup',
			'eval' => array
			(
				'mandatory'				=> true,
				'tl_class'				=> 'clr',
				'foreignTable'			=> 'tl_iso_products',
				'listFields'			=> array('type'=>'(SELECT name FROM tl_iso_producttypes WHERE tl_iso_products.type=tl_iso_producttypes.id)', 'name', 'sku'),
				'searchFields'			=> array('name', 'alias', 'sku', 'description'),
				'sqlWhere'				=> 'pid=0',
				'searchLabel'			=> 'Search products',
			),
		),
	)
);


class tl_iso_related_products extends Backend
{

	/**
	 * Add an image to each record
	 * @param array
	 * @param string
	 * @return string
	 */
	public function listRows($row)
	{
		$strCategory = $this->Database->prepare("SELECT * FROM tl_iso_related_categories WHERE id=?")->execute($row['category'])->name;
		
		$strBuffer = '
<div class="cte_type" style="color:#666966"><strong>' . $GLOBALS['TL_LANG']['tl_iso_related_products']['category'][0] . ':</strong> ' . $strCategory . '</div>';
		
		$arrProducts = deserialize($row['products']);
		if (is_array($arrProducts) && count($arrProducts))
		{
			$strBuffer .= '<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h0' : '') . ' block"><ul>';
			
			$objProducts = $this->Database->execute("SELECT * FROM tl_iso_products WHERE id IN (" . implode(',', $arrProducts) . ") ORDER BY name");
			
			while( $objProducts->next() )
			{
				$strBuffer .= '<li>' . $objProducts->name . '</li>';
			}
			
			$strBuffer .= '</ul></div>' . "\n";
		}
		
		return $strBuffer;
	}
	
	
	public function initDCA($dc)
	{
		$arrCategories = array();
		$objCategories = $this->Database->prepare("SELECT * FROM tl_iso_related_categories WHERE id NOT IN (SELECT category FROM tl_iso_related_products WHERE pid=" . (strlen($this->Input->get('act')) ? "(SELECT pid FROM tl_iso_related_products WHERE id=?) AND id!=?" : '?') . ")")
										->execute($dc->id, $dc->id);
										
		while( $objCategories->next() )
		{
			$arrCategories[$objCategories->id] = $objCategories->name;
		}
		
		if (!count($arrCategories))
		{
			$GLOBALS['TL_DCA']['tl_iso_related_products']['config']['closed'] = true;
		}

		if ($this->Input->get('act') == 'edit')
		{
			unset($GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['category']['foreignKey']);
			$GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['category']['options'] = $arrCategories;
			$GLOBALS['TL_DCA']['tl_iso_related_products']['fields']['products']['eval']['allowedIds'] = $this->Database->prepare("SELECT id FROM tl_iso_products WHERE pid=0 AND id!=(SELECT pid FROM tl_iso_related_products WHERE id=?)")->execute($dc->id)->fetchEach('id');
		}
	}
}

