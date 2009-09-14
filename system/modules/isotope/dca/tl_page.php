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
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] .= ';show_child_category_products';
$GLOBALS['TL_DCA']['tl_page']['palettes']['root'] .= ';{isotope_legend},isotopeStoreConfig';

/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_page']['fields']['show_child_category_products'] = array
(
	'label'                   => $GLOBALS['TL_LANG']['tl_page']['show_child_category_products'],
	'exclude'                 => true,
	'inputType'               => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_attribute_set'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_attribute_set'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'eval'                    => array('includeBlankOption'=>true,'submitOnChange'=>true),
	'options_callback'		  => array('Filters','getAttributeSets')
);

$GLOBALS['TL_DCA']['tl_page']['fields']['iso_filters'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['iso_filters'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'eval'                    => array('includeBlankOption'=>true),
	'options_callback'		  => array('Filters','getFilters')
);

$GLOBALS['TL_DCA']['tl_page']['fields']['isotopeStoreConfig'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_page']['isotopeStoreConfig'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_store.store_configuration_name',
	'eval'                    => array('includeBlankOption'=>true)
);


/**
 * Filters class.
 * 
 * @extends Backend
 */
class Filters extends Backend
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
		if(!$this->Input->get('id'))
		{
			return array();
		}
		
		$objAttributeSet = $this->Database->prepare("SELECT iso_attribute_set FROM tl_page WHERE id=?")
										  ->limit(1)
										  ->execute($this->Input->get('id'));
				
		if($objAttributeSet->numRows < 1)
		{
			return array();
		}
		
		$intAttributeSet = $objAttributeSet->iso_attribute_set;
		
		$objFilters = $this->Database->prepare("SELECT name FROM tl_product_attributes WHERE is_filterable=? AND pid=?")
									 ->execute(1, (int)$intAttributeSet);
		
		if($objFilters->numRows < 1)
		{
			return array();
		}
		
		$arrFilters = $objFilters->fetchAllAssoc();
		
		foreach($arrFilters as $filter)
		{
			$arrFilterList[$filter['name']] = $filter['name'];
		}	
		
		return $arrFilterList;
	}


	/**
	 * getAttributeSets function.
	 * 
	 * @access public
	 * @return array
	 */
	public function getAttributeSets()
	{
		$objAttributeSets = $this->Database->prepare("SELECT id, name FROM tl_product_attribute_sets")
										   ->execute();
			
		if($objAttributeSets->numRows < 1)
		{
			return array();
		}
	
		$arrSets = $objAttributeSets->fetchAllAssoc();
		
		foreach($arrSets as $set)
		{
			$arrAttributeSets[$set['id']] = $set['name'];		
		}
			
		return $arrAttributeSets;
	}
}
