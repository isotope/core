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
 * Table tl_tax_rate
 */
$GLOBALS['TL_DCA']['tl_tax_rate'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'					  => 'tl_tax_class',
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('sorting'),
			'panelLayout'             => 'filter;search,limit',
			'headerFields'            => array('name'),
			'child_record_callback'	  => array('tl_tax_rate','getRowLabel')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_tax_rate']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tax_rate']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_tax_rate']['cut'],
				'href'					=> 'act=paste&amp;mode=cut',
				'icon'					=> 'cut.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tax_rate']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_tax_rate']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{location_legend},country_id,region_id,postcode,address;{total_legend},total_start,total_stop;code;rate',
	),


	// Fields
	'fields' => array
	(
		'country_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['country_id'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50')
		),
		'region_id' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['region_id'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'select',
			'options_callback'		  => array('tl_tax_rate','getRegions'),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'postcode' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['postcode'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
/*
		'address' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['address'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'				  => array('billing', 'shipping'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_tax_rate'],
			'eval'                    => array('mandatory'=>true, 'multiple'=>true)
		),
*/
		'code' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['code'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['rate'],
			'exclude'                 => true,
			'search'                  => true,
			'sorting'                 => true,
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digits')
		),
		'total_start' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['total_start'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'rgxp'=>'digits', 'tl_class'=>'w50')
		),
		'total_stop' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['total_stop'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>10, 'rgxp'=>'digits', 'tl_class'=>'w50')
		),
	)
);


/**
 * Class tl_tax_rate
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Controller
 */
class tl_tax_rate extends Backend
{

	/**
	 * getRegions function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return string
	 */
	public function getRegions(DataContainer $dc)
	{
		$objCountryId = $this->Database->prepare("SELECT country_id FROM tl_tax_rate WHERE id=?")
									   ->limit(1)
									   ->execute($dc->id);
	
		if($objCountryId->numRows < 1)
		{
			return '';
		}
		
		if(sizeof($GLOBALS['TL_LANG']['MSC']['REGIONS'][$objCountryId->country_id]))
		{
			// yes we have regions;
			return $GLOBALS['TL_LANG']['MSC']['REGIONS'][$objCountryId->country_id];
		}
	
		return '';
	}
	
	/**
	 * getRowLabel function.
	 * 
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function getRowLabel($arrRow)
	{
		return $GLOBALS['TL_LANG']['CNT'][$arrRow['country_id']] . (strlen($arrRow['region_id']) > 0 ? ', ' . $arrRow['region_id'] : NULL) . (strlen($arrRow['code']) > 0 ? ', ' . $arrRow['code'] : NULL) . ' - %' . $arrRow['rate'];
	}
}

