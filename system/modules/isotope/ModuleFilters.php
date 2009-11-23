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


class ModuleFilters extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_filters';


	/**
	 * Generate module
	 */
	protected function compile()
	{
		//We're looking for filter, sort, search and limit functionality
		//Filters are rendered individually, sorts are compiled into a single sort by widget, serach also compiled, and limit is stock on/off
		$objFilters = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE (is_filterable='1' OR is_searchable='1' OR is_order_by_enabled='1')")
									 ->execute();
		
		if(!$objFilters->numRows)
		{
			return '';
		}
		
		$objFilters->fetchAllAssoc();
		
		while($objFilters->next())
		{
				if($objFilters->is_filterable)
				{
					//Render as a select widget, for now.  Perhaps make flexible in the future.
				}
				
				if($objFilters->is_searchable)
				{
					//Add to the clause for text search.  
				}
				
				if($objFilters->is_order_by_enabled)
				{
					//Produce an option 
				}
		
		}
		
		if($this->enableLimit)
		{
			//Generate the limits per page... used to be derived from the number of columns in grid format, but not in list format.  For now, just a standard list.
		}
		
		
		$this->Template->filters = $arrFilters;	
		$this->Template->action = '';
		$this->Template->order_by = '';
		$this->Template->search = '';
		$this->Template->sort = '';
		$this->Template->per_page = '';
		$this->Template->per_page_label = '';
		$this->Template->for = '';
		$this->Template->keywords_label = '';
		$this->Template->search_label = '';

	}
	
	
}

