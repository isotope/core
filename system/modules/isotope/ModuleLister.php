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
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    IsotopeBase 
 * @license    Commercial 
 * @filesource
 */


/**
 * Class ModuleLister
 *
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    Controller
 */
class ModuleLister extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_productlisting';

	protected $arrHandleCollection = array();
	/**
	 * Generate module
	 */
	protected function compile()
	{
		
		foreach($GLOBALS['FE_MOD']['isoLister']['TPL_COLL'] as $k=>$v)
		{
			if(!empty($v))
			{
				$objTemplate->$k = $v;
			}
			
			switch 
				
		
		}
		/*
			labelPagerSectionTitle (language file)
			labelSortBy (language file)
			sortByOptions (array repeater)
				- url
				- label
			products (array repeater)
				- thumbnail
				- product_name
				- product_link
				- product_teaser
				- price_string (pregenerated string)
			buttons (array repeater)
				- button_class
				- button_object
			labelPagerSectionTitle (language file)
			pagination (object)
		*/
	}
}

?>