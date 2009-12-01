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
		'enableVersioning'            => true,
		'onload_callback'			  => array
		(
			array('tl_tax_rate', 'addCurrencyRate'),
		),
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 1,
			'fields'                  => array('country', 'name'),
			'panelLayout'             => 'filter;search,limit',
		),
		'label' => array
		(
			'fields'				  => array('name'),
			'format'				  => '%s',
			'label_callback'		  => array('tl_tax_rate', 'listRow'),
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
		'default'                     => '{name_legend},name,label;{location_legend},country,subdivision,postcode,address;amount;store,rate,stop',
	),


	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['name'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['label'],
			'search'                  => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['country'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true, 'tl_class'=>'w50')
		),
		'subdivision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['subdivision'],
			'exclude'                 => true,
			'search'                  => true,
			'inputType'               => 'select',
			'options_callback'		  => array('tl_tax_rate', 'getSubdivisions'),
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
		'address' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['address'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'options'				  => array('billing', 'shipping'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_tax_rate'],
			'eval'                    => array('mandatory'=>true, 'multiple'=>true)
		),
		'store' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['store'],
			'inputType'               => 'select',
			'foreignKey'			  => 'tl_store.store_configuration_name',
			'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true),
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['rate'],
			'inputType'               => 'inputUnit',
			'options'				  => array('%'=>'%'),
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digits')
		),
		'amount' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_tax_rate']['amount'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'maxlength'=>10, 'rgxp'=>'digits', 'tl_class'=>'w50'),
		),
		'compound' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_tax_rate']['compound'],
			'inputType'					=> 'checkbox',
		),
		'stop' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_tax_rate']['stop'],
			'inputType'					=> 'checkbox',
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

	public function getSubdivisions(DataContainer $dc)
	{
		$objTaxRate = $this->Database->prepare("SELECT country FROM tl_tax_rate WHERE id=?")->limit(1)->execute($dc->id);
	
		if(!$objTaxRate->numRows || !strlen($objTaxRate->country))
			return array();
			
		$this->loadLanguageFile('subdivisions');
		
		if(array_key_exists($objTaxRate->country, $GLOBALS['TL_LANG']['DIV']))
		{
			return $GLOBALS['TL_LANG']['DIV'][$objTaxRate->country];
		}
	
		return array();
	}
	
	
	public function listRow($row)
	{
		$arrRate = deserialize($row['rate']);
		
		if ($row['store'] && !$arrRate['unit'])
		{
			$this->import('Isotope');
			$this->Isotope->overrideStore($row['store']);
			
			$strRate = $this->Isotope->formatPriceWithCurrency($arrRate['value']);
		}
		else
		{
			$strRate = $arrRate['value'] . '%';
		}
		
		return sprintf('%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>', $row['name'], $strRate);
	}
	
	
	public function addCurrencyRate($dc)
	{
		$objStore = $this->Database->prepare("SELECT tl_store.* FROM tl_tax_rate LEFT OUTER JOIN tl_store ON tl_store.id=tl_tax_rate.store WHERE tl_tax_rate.id=?")->execute($dc->id);
		
		if ($objStore->currency)
		{
			$GLOBALS['TL_DCA']['tl_tax_rate']['fields']['rate']['options'][''] = $objStore->currency;
		}
	}
}

