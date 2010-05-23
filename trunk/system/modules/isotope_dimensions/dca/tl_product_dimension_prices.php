<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005-2009 Leo Feyer
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
 * @copyright  Andreas Schempp 2010
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @version    $Id$
 */


/**
 * Table tl_product_dimension_prices
 */
$GLOBALS['TL_DCA']['tl_product_dimension_prices'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'					=> 'Table',
		'enableVersioning'				=> true,
		'ptable'						=> 'tl_product_dimensions',
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'						=> 4,
			'fields'					=> array('dimension_x', 'dimension_y'),
			'flag'						=> 1,
			'panelLayout'				=> 'filter;search,limit',
			'headerFields'				=> array('name'),
			'child_record_callback'		=> array('tl_product_dimension_prices', 'listPrice')
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
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['edit'],
				'href'					=> 'act=edit',
				'icon'					=> 'edit.gif'
			),
			'copy' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['copy'],
				'href'					=> 'act=copy',
				'icon'					=> 'copy.gif'
			),
			'delete' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['delete'],
				'href'					=> 'act=delete',
				'icon'					=> 'delete.gif',
				'attributes'			=> 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['show'],
				'href'					=> 'act=show',
				'icon'					=> 'show.gif'
			),
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'						=> '{dimension_legend},dimension_x,dimension_y;{price_legend},price;{publish_legend},published,start,stop',
	),

	// Fields
	'fields' => array
	(
		'dimension_x' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['dimension_x'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>12, 'rgxp'=>'digits', 'tl_class'=>'w50'),
		),
		'dimension_y' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['dimension_y'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>12, 'rgxp'=>'digits', 'tl_class'=>'w50'),
		),
		'price' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['price'],
			'exclude'					=> true,
			'search'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('mandatory'=>true, 'maxlength'=>255, 'rgxp'=>'digits', 'tl_class'=>'clr'),
		),
		'published' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['published'],
			'exclude'					=> true,
			'filter'					=> true,
			'inputType'					=> 'checkbox',
			'eval'						=> array('doNotCopy'=>true),
		),
		'start' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['start'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
		),
		'stop' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_product_dimension_prices']['stop'],
			'exclude'					=> true,
			'inputType'					=> 'text',
			'eval'						=> array('rgxp'=>'date', 'datepicker'=>$this->getDatePickerString(), 'tl_class'=>'w50 wizard'),
		),
	)
);


class tl_product_dimension_prices extends Backend
{

	public function listPrice($row)
	{
		$this->import('Isotope');
		
		$image = 'published';

		if (!$row['published'] || (strlen($row['start']) && $row['start'] > time()) || (strlen($row['stop']) && $row['stop'] < time()))
		{
			$image = 'un'.$image;
		}
		
		$strStartStop = '';
		if (strlen($row['start']) && strlen($row['stop']))
		{
			$strStartStop = ' <span style="color:#b3b3b3; padding-left:3px;">[' . sprintf($GLOBALS['TL_LANG']['tl_product_dimension_prices']['labelStartStop'], $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $row['start']), $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $row['stop'])) . ']</span>';
		}
		elseif (strlen($row['start']))
		{
			$strStartStop = ' <span style="color:#b3b3b3; padding-left:3px;">[' . sprintf($GLOBALS['TL_LANG']['tl_product_dimension_prices']['labelStart'], $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $row['start'])) . ']</span>';
		}
		elseif (strlen($row['stop']))
		{
			$strStartStop = ' <span style="color:#b3b3b3; padding-left:3px;">[' . sprintf($GLOBALS['TL_LANG']['tl_product_dimension_prices']['labelStop'], $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $row['stop'])) . ']</span>';
		}

		return sprintf('<div class="list_icon" style="margin-top:-20px; margin-bottom:-8px; background-image:url(\'system/themes/%s/images/%s.gif\');">%s x %s: %s%s</div>', $this->getTheme(), $image, round($row['dimension_x'], 3), round($row['dimension_y'], 3), $this->Isotope->formatPriceWithCurrency($row['price']), $strStartStop);
	}
}

