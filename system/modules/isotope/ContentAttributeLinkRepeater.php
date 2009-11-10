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


/**
 * Class ContentAttributeLinkRepeater
 *
 * Front end content element "link repeater".
 * @copyright  Winans Creative 2009
 * @author     Fred Bliss <fred@winanscreative.com>
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
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
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
				$arrLinkData[] = array
				(
					'value'		=> $value[$objAttributeData->id],
					'title'		=> $value[$objAttributeData->list_source_field]
				);
			
			}
			
		}else{
		
			$this->import('ProductCatalog');
			
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			$filter_name = strtolower($this->ProductCatalog->mysqlStandardize($objAttributeData->name));
			
			foreach($arrLinkValues as $value)
			{
				$arrLinkData[] = array
				(
					'value'		=> $value['value'],
					'title'		=> $value['label']
				);
			
			}
		}
		
			
		foreach($arrLinkData as $link)
		{
								
			$arrLinks[] = array
			(
				'url'		=>	$this->url . '?' . $filter_name . '=' . $link['value'], //$this->addToURL(),
				'link'		=>	$link['title'],
				'title'		=> 	$link['title']
			);
		}	
			
			
		$this->Template->links = $arrLinks; 
		
		
	}
}

