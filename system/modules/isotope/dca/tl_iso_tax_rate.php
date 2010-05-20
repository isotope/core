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
 
 
// Load country sub-divisions
$this->loadLanguageFile('subdivisions');


/**
 * Table tl_iso_tax_rate
 */
$GLOBALS['TL_DCA']['tl_iso_tax_rate'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'enableVersioning'            => true,
		'closed'					  => true,
		'onload_callback'			  => array
		(
			array('tl_iso_tax_rate', 'checkPermission'),
			array('tl_iso_tax_rate', 'addCurrencyRate'),
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
			'label_callback'		  => array('tl_iso_tax_rate', 'listRow'),
		),
		'global_operations' => array
		(
			'back' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['MSC']['backBT'],
				'href'					=> 'table=',
				'class'					=> 'header_back',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
			'new' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['new'],
				'href'					=> 'act=create',
				'class'					=> 'header_new',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"',
			),
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['copy'],
				'href'                => 'act=copy',
				'icon'                => 'copy.gif'
			),
			'cut' => array
			(
				'label'					=> &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['cut'],
				'href'					=> 'act=paste&amp;mode=cut',
				'icon'					=> 'cut.gif',
				'attributes'			=> 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'default'                     => '{name_legend},name,label;{rate_legend},rate;{location_legend},address,country,subdivision,postal;{condition_legend},amount;{config_legend},config,stop',
	),


	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['name'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'label' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['label'],
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'mandatory'=>true, 'tl_class'=>'w50'),
		),
		'address' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['address'],
			'inputType'               => 'checkbox',
			'options'				  => array('billing', 'shipping'),
			'reference'				  => &$GLOBALS['TL_LANG']['tl_iso_tax_rate'],
			'eval'                    => array('mandatory'=>true, 'multiple'=>true)
		),
		'country' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['country'],
			'inputType'               => 'select',
			'options'                 => $this->getCountries(),
			'eval'                    => array('includeBlankOption'=>true, 'tl_class'=>'w50')
		),
		'subdivision' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['subdivision'],
			'exclude'                 => true,
			'sorting'                 => true,
			'inputType'               => 'conditionalselect',
			'options'				  => $GLOBALS['TL_LANG']['DIV'],
			'eval'                    => array('conditionField'=>'country', 'includeBlankOption'=>true, 'tl_class'=>'w50'),
		),
		'postal' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['postal'],
			'flag'                    => 1,
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'maxlength'=>10, 'rgxp'=>'digits', 'tl_class'=>'w50'),
		),
		'config' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['config'],
			'inputType'               => 'select',
			'foreignKey'			  => 'tl_iso_config.name',
			'eval'                    => array('includeBlankOption'=>true, 'submitOnChange'=>true),
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['rate'],
			'inputType'               => 'inputUnit',
			'options'				  => array('%'=>'%'),
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digits')
		),
		'amount' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['amount'],
			'inputType'               => 'text',
			'eval'                    => array('multiple'=>true, 'size'=>2, 'maxlength'=>10, 'rgxp'=>'digits', 'tl_class'=>'w50'),
		),
		'compound' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['compound'],
			'inputType'					=> 'checkbox',
		),
		'stop' => array
		(
			'label'						=> &$GLOBALS['TL_LANG']['tl_iso_tax_rate']['stop'],
			'inputType'					=> 'checkbox',
		),
	)
);


class tl_iso_tax_rate extends Backend
{

	public function checkPermission($dc)
	{
		if (strlen($this->Input->get('act')))
		{
			$GLOBALS['TL_DCA']['tl_iso_tax_rate']['config']['closed'] = false;
		}
	}
	
	
	public function getSubdivisions(DataContainer $dc)
	{
		$objTaxRate = $this->Database->prepare("SELECT country FROM tl_iso_tax_rate WHERE id=?")->limit(1)->execute($dc->id);
	
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
		
		if ($row['config'] && !$arrRate['unit'])
		{
			$this->import('Isotope');
			$this->Isotope->overrideConfig($row['config']);
			
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
		$objConfig = $this->Database->prepare("SELECT tl_iso_config.* FROM tl_iso_tax_rate LEFT OUTER JOIN tl_iso_config ON tl_iso_config.id=tl_iso_tax_rate.config WHERE tl_iso_tax_rate.id=?")->execute($dc->id);
		
		if ($objConfig->currency)
		{
			$GLOBALS['TL_DCA']['tl_iso_tax_rate']['fields']['rate']['options'][''] = $objConfig->currency;
		}
	}
}

