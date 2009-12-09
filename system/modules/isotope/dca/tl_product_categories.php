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
 * Table tl_product_categories
 */
$GLOBALS['TL_DCA']['tl_product_categories'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'TablePageId',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_page',
		'closed'						=> true,
		'onload_callback' => array
		(
			array('tl_product_categories', 'checkVersion'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('sorting'),
//			'flag'						=> 1,
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
			)
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

	// Palettes
	'palettes' => array
	(
		'default'						=> '{name_legend},name;{publish_legend},published,start,stop',
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_categories']['name'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
		),
		'published' => array
		(
			'exclude'					=> true,
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_categories']['published'],
			'inputType'					=> 'checkbox',
			'eval'						=> array('doNotCopy'=>true),
		),
		'start' => array
		(
			'exclude'					=> true,
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_categories']['start'],
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
		),
		'stop' => array
		(
			'exclude'					=> true,
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_categories']['stop'],
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
		),
	)
);



class tl_product_categories extends Backend
{

	public function listRows($row)
	{
		$this->loadDataContainer('tl_product_data');
		$this->loadLanguageFile('tl_product_data');
		
		$objProduct = $this->Database->prepare("SELECT * FROM tl_product_data WHERE id=?")->limit(1)->execute($row['pid']);
		
		$this->import('tl_product_data');
		return '<div class="cte_type" style="margin-top: -23px;margin-bottom:-8px">'.$this->tl_product_data->getRowLabel($objProduct->row()).'</div>';
	}
	
	public function checkVersion()
	{
		if (version_compare(VERSION . '.' . BUILD, '2.7.5', '<='))
		{
			unset($GLOBALS['TL_DCA']['tl_product_categories']['list']['global_operations']['all']);
		}
	}
}

