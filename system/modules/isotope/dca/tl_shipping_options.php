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
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Table tl_shipping_options
 */
$GLOBALS['TL_DCA']['tl_shipping_options'] = array
(

	// Config
	'config' => array
	(
		'dataContainer'               => 'Table',
		'ptable'					  => 'tl_shipping_modules',
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
			'headerFields'            => array('name', 'tstamp'),
			'child_record_callback'   => array('tl_shipping_options', 'listrates')
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
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_options']['edit'],
				'href'                => 'act=edit',
				'icon'                => 'edit.gif'
			),
			'copy' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_options']['copy'],
				'href'                => 'act=paste&amp;mode=copy',
				'icon'                => 'copy.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'

			),
			'cut' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_options']['cut'],
				'href'                => 'act=paste&amp;mode=cut',
				'icon'                => 'cut.gif',
				'attributes'          => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_options']['delete'],
				'href'                => 'act=delete',
				'icon'                => 'delete.gif',
				'attributes'          => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_shipping_options']['show'],
				'href'                => 'act=show',
				'icon'                => 'show.gif'
			)
		)
	),

	// Palettes
	'palettes' => array
	(
		'__selector__'				  => array('option_type'),
		'default'                     => 'name,description;option_type;',
		'ot_tier'					  => 'name,description;option_type;rate;limit_type,limit_value;groups;dest_countries,dest_regions,dest_postalcodes',
		'surcharge'					  => 'name,description;option_type;mandatory;rate;groups;dest_countries,dest_regions,dest_postalcodes'
		
	),
	
	'subpalettes' => array
	(
		'override'					  => 'override_message'	
	),

	// Fields
	'fields' => array
	(
		'name' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['name'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'maxlength'=>255)
		),
		'description' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['description'],
			'exclude'                 => true,
			'inputType'               => 'textarea',
			'eval'                    => array('maxlength'=>255)
		),
		'option_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['option_type'],
			'default'                 => 'ot_tier',
			'exclude'                 => true,
			'filter'                  => true,
			'inputType'               => 'select',
			'options'				  => array_keys($GLOBALS['TL_LANG']['tl_shipping_options']['types']),
			'reference'               => &$GLOBALS['TL_LANG']['tl_shipping_options']['types'],
			'eval'                    => array('helpwizard'=>true, 'submitOnChange'=>true)
		),
		'limit_type' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['limit_type'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'options'				  => array_keys($GLOBALS['TL_LANG']['tl_shipping_options']['limit']),
			'eval'                    => array('mandatory'=>true,'includeBlankOption'=>true),
			'reference'				  => $GLOBALS['TL_LANG']['tl_shipping_options']['limit']
		),
		'limit_value' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['limit_value'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('rgxp'=>digit)
		),
		'override' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['override'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'eval'					  => array('submitOnChange'=>true)
		),
		'override_rule' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['override_rule'],
			'exclude'                 => true,
			'inputType'               => 'select',
			//'eval'                    => array('multiple'=>true, 'size'=>8),
			'options_callback'		  => array('tl_shipping_options','getExistingRules')
		),
		'override_message' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['override_message'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('maxlength'=>255)
		),
		'groups' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['groups'],
			'exclude'                 => true,
			'inputType'               => 'checkbox',
			'foreignKey'              => 'tl_member_group.name',
			'eval'                    => array('multiple'=>true)
		),
		'rate' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['rate'],
			'exclude'                 => true,
			'inputType'               => 'text',
			'eval'                    => array('mandatory'=>true, 'rgxp'=>digit)
		),
		'mandatory' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['mandatory'],
			'exclude'                 => true,
			'inputType'               => 'checkbox'
		),
		'dest_postalcodes' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['dest_postalcodes'],
			'exclude'                 => true,
			'inputType'               => 'textarea'
		),
		'dest_countries' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['dest_countries'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('multiple'=>true, 'size'=>8),
			'options_callback'		  => array('tl_shipping_options','getAllowedCountries')
		),
		'dest_regions' => array
		(
			'label'                   => &$GLOBALS['TL_LANG']['tl_shipping_options']['dest_regions'],
			'exclude'                 => true,
			'inputType'               => 'select',
			'eval'                    => array('multiple'=>true, 'size'=>8),
			'options_callback'		  => array('tl_shipping_options','getAllowedRegions')
		)
	)
);


/**
 * tl_shipping_options class.
 * 
 * @extends Backend
 */
class tl_shipping_options extends Backend
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
		$objPid = $this->Database->prepare("SELECT pid FROM tl_shipping_options WHERE id=?")
								 ->limit(1)
								 ->execute($dc->id);
		if($objPid->numRows < 1)
		{
			return array();
		}
		
		$intPid = $objPid->pid;	
								 
		$objRules = $this->Database->prepare("SELECT id, name FROM tl_shipping_options WHERE pid=?")
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
				
		$objPid = $this->Database->prepare("SELECT pid FROM tl_shipping_options WHERE id=?")
								 ->limit(1)
								 ->execute($dc->id);
		
		if($objPid->numRows < 1)
		{
			return array();
		}
		
		$intPid = $objPid->pid;
		
		$objModuleAllowedCountries = $this->Database->prepare("SELECT countries FROM tl_shipping_modules WHERE id=?")
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
	 * Get allowed regions for a given country
	 *
	 * @access public
	 * @return array
	 */
	public function getAllowedRegions()
	{
		return array();
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
'. $GLOBALS['TL_LANG']['tl_shipping_options']['option_type'][0] . ': ' . $GLOBALS['TL_LANG']['tl_shipping_options']['types'][$arrRow['option_type']] . '<br /><br />' . $arrRow['rate'] .' for '. $arrRow['upper_limit'] . ' based on ' . $arrRow['dest_country'] .', '. $arrRow['dest_region'] . ', ' . $arrRow['dest_zip'] . '</div>' . "\n";
	}
}

