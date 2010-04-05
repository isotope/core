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
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoDonationsModule']		= '{title_legend},name,headline,type;{config_legend},iso_donationProduct;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_donationProduct'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_donationProduct'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'eval'					  => array('includeBlankOption'=>true, 'mandatory'=>true),
	'options_callback'        => array('tl_module_isotope_donations', 'getDonationProducts'),
);


class tl_module_isotope_donations extends Backend
{

	public function getDonationProducts()
	{
		$objProducts = $this->Database->prepare("SELECT id, name FROM tl_product_data WHERE alias LIKE ?")
									  ->execute('%donation%');
		
		if(!$objProducts->numRows)
		{
			return array();
		}
		
		while($objProducts->next())
		{
			$arrReturn[$objProducts->id] = $objProducts->name;
		}
		
		return $arrReturn;
		
	}
}

