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
 * Class ModuleFilters
 *
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    Controller
 */
class ModuleFilters extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_filter';


	/**
	 * Generate module
	 */
	protected function compile()
	{
		$objFilters = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE is_filterable='1'")
									 ->execute();
		
		if($objFIlters->numRows < 1)
		{
			return '';
		}
		
		$arrFilters = $objFilters->fetchAllAssoc();
		
		foreach($arrFilters as $filter)
		{
		
		
		}
		
		$arrGroup = array
		(
			'filterName' => $filterName,
			'filterValues'	=> array
				(
					'value'		=> $value,
					'label'		=> $label
				)
		);

		$this->Template->filterGroup = $arrGroup;	
	
	}
	
	
}

?>