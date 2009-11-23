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
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE FILTER MODULE ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}		
		
		return parent::generate();
	}
	
	/**
	 * Compile module
	 */
	protected function compile()
	{
		
		$arrFilterFields = deserialize($this->iso_filterFields);
		$arrOrderByFields = deserialize($this->iso_orderByFields);
		$arrSearchFields = deserialize($this->iso_searchFields);
		$arrLimit = array();
		
		$this->loadLanguageFile('tl_product_data');
		
		$arrOrderByFields[] = array
		(
			'type'			=> 'text',
			'field_name'	=> 'name',
			'label'			=> $GLOBALS['TL_LANG']['tl_product_data']['name'][0]
		);
		
		$arrOrderByFields[] = array
		(
			'type'			=> 'decimal',
			'field_name'	=> 'price',
			'label'			=> $GLOBALS['TL_LANG']['tl_product_data']['price'][0]
		);
		
		$arrSearchFields = array('name','description');
		
		foreach($arrFilterFields as $field)
		{
				
			//Render as a select widget, for now.  Perhaps make flexible in the future.
			$arrFilters[] = array
			(
				'html'		=> ''	//render filter widget
			);
		}
		
				
		if($arrOrderByFields)
		{
			$arrOrderByOptions = $this->getOrderByOptions($arrOrderByFields);
		}
		
		if($this->iso_enableLimit)
		{
			//Generate the limits per page... used to be derived from the number of columns in grid format, but not in list format.  For now, just a standard list.
			$arrLimit = array(10,20,50,100,200);
		}

		$this->Template->perPage = $this->iso_enableLimit;
		$this->Template->limit = $arrLimit;
		$this->Template->filters = $arrFilters;	
		$this->Template->action = $this->Environment->request;
		$this->Template->orderBy = $arrOrderByOptions;
		$this->Template->order_by = $this->Input->get('order_by');
		$this->Template->per_page = $this->Input->get('per_page');
		$this->Template->per_page_label = $GLOBALS['TL_LANG']['MSC']['perPage'];
		$this->Template->for = $this->Input->get('keywords');
		$this->Template->keywords_label = '';
		$this->Template->search_label = $GLOBALS['TL_LANG']['MSC']['search'];

	}
	
	private function getOrderByOptions($arrAttributes)
	{		
		
		foreach($arrAttributes as $attribute)
		{
			$arrSortingDirections = $this->generateSortingDirections($attribute['type']);
			
			$arrOptions[$attribute['field_name'] . '-ASC'] = $attribute['label'] . ' ' . $arrSortingDirections['ASC'];
			$arrOptions[$attribute['field_name'] . '-DESC'] = $attribute['label'] . ' ' . $arrSortingDirections['DESC'];
	
		}
		
		return $arrOptions;
	}
	
	private function generateSortingDirections($strType)
	{
		switch($strType)
		{
			case 'integer':
			case 'decimal':
			
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['low_to_high'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['high_to_low']);
				break;
			
			case 'text':
			
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['a_to_z'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['z_to_a']);
				break;
			case 'datetime':
				
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['old_to_new'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['new_to_old']);
				break;
			default:
				return;
				break;
		}
	
	}
	
	/**
	 *	Calculate the per-page options based on the number of product columns specified.  The first option is always * 4 rows
	 *  for example, 5 wide * 4 rows = default option of 20 per page.
	 *
	 *	@param integer
	 *	@return array
	 */
	private function getPerPageOptions($intColumns, $intRows = 4)
	{
		$arrPerPageOptions[] = ($intColumns * $intRows) * 1;
		$arrPerPageOptions[] = ($intColumns * $intRows) * 2;
		$arrPerPageOptions[] = ($intColumns * $intRows) * 3;
		$arrPerPageOptions[] = ($intColumns * $intRows) * 5;
		$arrPerPageOptions[] = ($intColumns * $intRows) * 10;
	
		return $arrPerPageOptions;
	}
}

