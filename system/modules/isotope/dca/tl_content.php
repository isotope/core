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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['attributeLinkRepeater'] = 'type,headline;iso_filters;url,target;guests,protected;align,space,cssID';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['iso_filters'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_filters'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'eval'                    => array('includeBlankOption'=>true),
	'options_callback'		  => array('PageFilters','getFilters')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_reader_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_reader_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio'),
);

$GLOBALS['TL_DCA']['tl_content']['fields']['iso_list_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_content']['iso_list_layout'],
	'default'                 => 'iso_list_default',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => (version_compare(VERSION.BUILD, '2.9.0', '>=')) ? $this->getTemplateGroup('iso_list_', $dc->activeRecord->pid) : $this->getTemplateGroup('iso_list_'),
	'eval'					  => array('includeBlankOption'=>true),
);


/**
 * PageFilters class.
 * 
 * @extends Backend
 */
class PageFilters extends Backend
{
	/**
	 * getFilters function.
	 * 
	 * @access public
	 * @param object DataContainer $dc
	 * @return array
	 */
	public function getFilters(DataContainer $dc)
	{
		$objAttributeSet = $this->Database->prepare("SELECT iso_attribute_set FROM tl_content WHERE id=?")
										  ->limit(1)
										  ->execute($dc->id);
				
		if($objAttributeSet->numRows < 1)
		{
			return '';
		}
		
		$intAttributeSet = $objAttributeSet->iso_attribute_set;
		
		$objFilters = $this->Database->prepare("SELECT id, name FROM tl_iso_attributes WHERE is_filterable=? AND pid=?")
									 ->execute(1, (int)$intAttributeSet);
		
		if($objFilters->numRows < 1)
		{
			return array();
		}
		
		$arrFilters = $objFilters->fetchAllAssoc();
		
		foreach($arrFilters as $filter)
		{
			$arrFilterList[$filter['id']] = $filter['name'];
		}	
		
		return $arrFilterList;
	}


}

