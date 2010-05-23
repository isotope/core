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


class ContentAttributeLinkRepeater extends ContentElement
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_link_repeater';


	/**
	 * Generate content element
	 */
	protected function compile()
	{
		
		//global $objPage;
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field FROM tl_iso_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($this->iso_filters);
		
		
		if($objAttributeData->numRows < 1)
		{
			return '';
		}
		
		if($objAttributeData->use_alternate_source==1)
		{
			$objLinkData = $this->Database->prepare("SELECT id, " . $objAttributeData->list_source_field . " FROM " . $objAttributeData->list_source_table)
										  ->execute();
			
			if($objLinkData->numRows < 1)
			{
				return array();
			}
			
			$arrLinkValues = $objLinkData->fetchAllAssoc();
			
			$filter_name = $objAttributeData->list_source_field;
						
			foreach($arrLinkValues as $value)
			{
				$arrLinkData[$value[$objAttributeData->id]] = $value[$objAttributeData->list_source_field];
			
			}
			
		}
		else
		{
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			$filter_name = standardize($objAttributeData->name);
			
			foreach($arrLinkValues as $value)
			{
				$arrLinkData[$value['value']] = $value['label'];
			
			}
		}
		
		if($blnSortAlpha) //just a temporary thing. Need to make this an option to set.
		{
			asort($arrLinkData);
		}
			
		foreach($arrLinkData as $k=>$v)
		{
								
			$arrLinks[] = array
			(
				'url'		=>	$this->url . '?' . $filter_name . '=' . $k, //$this->addToURL(),
				'link'		=>	$v,
				'title'		=> 	$v
			);
		}	
			
			
		$this->Template->links = $arrLinks; 
		
		
	}
}

