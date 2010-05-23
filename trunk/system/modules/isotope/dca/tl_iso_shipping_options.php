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
 * Table tl_iso_shipping_options
 */
$GLOBALS['TL_DCA']['tl_iso_shipping_options'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'					  => 'tl_iso_shipping_modules',
		'enableVersioning'            => true
	),

	// List
	'list' => array
	(
		'sorting' => array
		(
			'mode'                    => 4,
			'fields'                  => array('name'),
			'panelLayout'             => 'sort,filter;search,limit',
			'headerFields'            => array('name', 'type'),
			'child_record_callback'   => array('tl_iso_shipping_options', 'listrates')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'

			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				  => array(''),
		'default'                     => '{general_legend},name,description;{configuration_legend},rate,minimum_total,maximum_total;',
		
	),
	
	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['description'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('maxlength'=>255)
		),
		'minimum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['minimum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit'),
		),
		'maximum_total' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['maximum_total'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255, 'rgxp'=>'digit'),
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_iso_shipping_options']['rate'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>digit)
		)
	)
);


/**
 * tl_iso_shipping_options class.
 * 
 * @extends Backend
 */
class tl_iso_shipping_options extends Backend
{

	/**
	 * Import the back end user object.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}
	
	public function getExistingRules(DataContainer $dc)
	{
		$objPid = $this->Database->prepare("SELECT pid FROM tl_iso_shipping_options WHERE id=?")
								 ->limit(1)
								 ->execute($dc->id);
		if($objPid->numRows < 1)
		{
			return array();
		}
		
		$intPid = $objPid->pid;	
								 
		$objRules = $this->Database->prepare("SELECT id, name FROM tl_iso_shipping_options WHERE pid=?")
								   ->execute($intPid);
	
		if($objRules->numRows < 1)
		{
			return array();
		}
		
		while($objRules->next())
		{
			$arrRules[$objRules->id] = $objRules->name;
		}
		
		return $arrRules;
	}
	
	public function getAllowedCountries(DataContainer $dc)
	{
				
		$objPid = $this->Database->prepare("SELECT pid FROM tl_iso_shipping_options WHERE id=?")
								 ->limit(1)
								 ->execute($dc->id);
		
		if($objPid->numRows < 1)
		{
			return array();
		}
		
		$intPid = $objPid->pid;
		
		$objModuleAllowedCountries = $this->Database->prepare("SELECT countries FROM tl_iso_shipping_modules WHERE id=?")
													->limit(1)
													->execute($intPid);
		
		if($objModuleAllowedCountries->numRows < 1)
		{
			return array();
		}
		
		$arrCountries = $objModuleAllowedCountries->fetchEach('countries');

		if(sizeof($arrCountries)<1)
		{
			return $this->getCountries();
		}
		
		$arrCountryKeys = deserialize($arrCountries[0]);
		
		$arrCountryLabels = $this->getCountries();
		
		foreach($arrCountryKeys as $country)
		{
			$arrCountryData[$country] = $arrCountryLabels[$country];
		}
		
		return $arrCountryData;
	
	}
		
	
	/**
	 * Add the type of input field.
	 * 
	 * @access public
	 * @param array $arrRow
	 * @return string
	 */
	public function listrates($arrRow)
	{
		
		return '
<div class="cte_type ' . $key . '"><strong>' . $arrRow['name'] . '</strong></div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h52' : '') . ' block">
'. $GLOBALS['TL_LANG']['tl_iso_shipping_options']['option_type'][0] . ': ' . $GLOBALS['TL_LANG']['tl_iso_shipping_options']['types'][$arrRow['option_type']] . '<br /><br />' . $arrRow['rate'] .' for '. $arrRow['upper_limit'] . ' based on ' . $arrRow['dest_country'] .', '. $arrRow['dest_region'] . ', ' . $arrRow['dest_zip'] . '</div>' . "\n";
	}
}

