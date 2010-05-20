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
 * Table tl_product_categories
 */
$GLOBALS['TL_DCA']['tl_product_categories'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'TablePageId',
		'ptable'						=> 'tl_page',
		'closed'						=> true,
		'notEditable'					=> true,
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('sorting'),
			'headerFields'				=> array('title', 'type'),
			'child_record_callback'		=> array('tl_product_categories', 'listRows')
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'					=> 'act=select',
				'class'					=> 'header_edit_all',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
		),
		'operations' => array
		(
			'cut' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_categories']['cut'],
				'href'					=> 'act=paste&amp;mode=cut',
				'icon'					=> 'cut.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
		)
	),
	
	// Fields Array must not be empty or we get a foreach error.
	'fields' => array()
);



class tl_product_categories extends Backend
{

	public function listRows($row)
	{
		$this->loadDataContainer('tl_iso_products');
		$this->loadLanguageFile('tl_iso_products');
		
		$objProduct = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE id=?")->limit(1)->execute($row['pid']);
		
		$this->import('tl_iso_products');
		return '<div style="margin-top: -' . ($this->Input->get('act')=='select' ? 15 : 20) . 'px; margin-bottom:-8px">'.$this->tl_iso_products->getRowLabel($objProduct->row()).'</div>';
	}
}

