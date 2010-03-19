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
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class ProductCatalog extends Backend
{

	protected $arrSelectors = array();


	public function __construct()
	{	
		parent::__construct();
		
		$this->import('Isotope');
	}

	
	/**
	 * Initialize the tl_product_data DCA
	 */	
	public function loadProductCatalogDCA($strTable)
	{
		if ($strTable != 'tl_product_data')
			return;
		
		// FIXME: should we exclude "globally disabled" fields?
		$objAttributes = $this->Database->execute("SELECT * FROM tl_product_attributes");
		
		
		// add DCA for form fields
		while ( $objAttributes->next() )
		{
			$arrData = array
			(
				'label'				=> array($objAttributes->name, $objAttributes->description),
				'inputType'			=> ((TL_MODE == 'BE' && strlen($GLOBALS['ISO_ATTR'][$objAttributes->type]['backend'])) ? $GLOBALS['ISO_ATTR'][$objAttributes->type]['backend'] : ((TL_MODE == 'FE' && strlen($GLOBALS['ISO_ATTR'][$objAttributes->type]['frontend'])) ? $GLOBALS['ISO_ATTR'][$objAttributes->type]['frontend'] : $objAttributes->type)),
				'attributes'		=> $objAttributes->row(),
				'save_callback'		=> array
				(
					array('ProductCatalog','saveField'),
				),
			);
			
			if ($objAttributes->is_required) $arrData['eval']['mandatory'] = 'true';
			if ($objAttributes->rgxp) $arrData['eval']['rgxp'] = $objAttributes->rgxp;
			if ($objAttributes->multiple) $arrData['eval']['multiple'] = $objAttributes->multiple;
			
			// check for options lookup 
			switch ($objAttributes->type)
			{
				case 'datetime':
					$arrData['eval']['rgxp'] = 'date';
					$arrData['eval']['datepicker'] = $this->getDatePickerString();
					break;
					
				case 'text':
					$arrData['eval']['tl_class'] = 'long';
					break;
			
				case 'textarea':
					if($objAttributes->use_rich_text_editor)
					{
						$arrData['eval']['rte'] = 'tinyMCE';
					}
					break;

				case 'file':
				case 'media':
					$arrData['eval']['cols'] = 4;
					//if($objAttributes->show_files) $arrData['eval']['files'] = true;
					//$arrData['eval']['fieldType'] = 'radio';
					break;
					
				case 'options':
				case 'select':
					$arrData['eval']['multiple'] = $objAttributes->type == 'options' ? false : $arrData['eval']['multiple'];
					if ($objAttributes->use_alternate_source && strlen($objAttributes->list_source_table) > 0 && strlen($objAttributes->list_source_field) > 0)
					{
						$strForeignKey = $objAttributes->list_source_table . '.' . $objAttributes->list_source_field;
					}
					else
					{
						$arrValues = array();
						$arrOptionsList = deserialize($objAttributes->option_list);
						
						if (is_array($arrOptionsList) && count($arrOptionsList))
						{
							foreach ($arrOptionsList as $arrOptions)
							{
								$arrValues[$arrOptions['value']] = $arrOptions['label'];
							}
							
							$arrData['options'] = array_keys($arrValues);
							$arrData['reference'] = $arrValues;
						}
					}

					break;
			}
			
			
			if ($objAttributes->add_to_product_variants)
			{
				if($this->Input->get('id') && $this->Input->get('do')=='product_manager')
				{
					$objPid = $this->Database->prepare("SELECT pid FROM tl_product_data WHERE id=?")
											 ->limit(1)
											 ->execute($this->Input->get('id'));
					
					if($objPid->numRows && $objPid->pid>0)
					{
						$arrData['inputType'] = 'text';
					}
				}
			}
			
			if (is_array($GLOBALS['ISO_ATTR'][$objAttributes->type]['callback']) && count($GLOBALS['ISO_ATTR'][$objAttributes->type]['callback']))
			{
				foreach( $GLOBALS['ISO_ATTR'][$objAttributes->type]['callback'] as $callback )
				{
					$this->import($callback[0]);
					$arrData = $this->{$callback[0]}->{$callback[1]}($arrData, $objAttributes->row());
				}
			}
			
			$GLOBALS['TL_DCA']['tl_product_data']['fields'][$objAttributes->field_name] = $arrData;
		}
		
		
		// Add palettes
		$arrProductTypePalettes = $this->getProductTypePalettes();

		if(count($arrProductTypePalettes))
		{
			$GLOBALS['TL_DCA']['tl_product_data']['palettes'] = $GLOBALS['TL_DCA']['tl_product_data']['palettes'] + $arrProductTypePalettes;
				
			$arrAdditionalSelectors = $this->arrSelectors;
		
			$GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'] = array_merge($GLOBALS['TL_DCA']['tl_product_data']['palettes']['__selector__'], $arrAdditionalSelectors);
		}
		
		if($this->Input->get('id') && $this->Input->get('do')=='product_manager' && !$this->Input->get('mode') && $this->Input->get('table') != 'tl_product_categories')
		{
			//Check and update to load the palette
			$objProductType = $this->Database->prepare("SELECT d.type AS selfType, (SELECT type FROM tl_product_data p WHERE p.id=d.pid AND d.pid!=0) AS parentType FROM tl_product_data d WHERE d.id=?")
											 ->limit(1)
											 ->execute($this->Input->get('id'));
			
			if(!$objProductType->numRows || $objProductType->selfType==0)
			{
			
				//set the type for the subproduct
				$this->Database->prepare("UPDATE tl_product_data SET type=" . $objProductType->parentType . " WHERE id=?")
							   ->set($arrSet)
							   ->execute($this->Input->get('id'));
	
			}
		}
				
	}
	
	
	protected function getProductType($intProductId)
	{
		$objProductType = $this->Database->prepare("SELECT type FROM tl_product_data WHERE id=?")
										 ->limit(1)
										 ->execute($intProductId);
		
		if($objProductType->numRows < 1)
		{
			throw new Exception('no product type returned for this product!');	//TODO: Add to language array
		}		
		
		return $objProductType->type;
		
	}
	
	
	public function saveField($varValue, DataContainer $dc)
	{
		$objAttribute = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE field_name=?")
									   ->limit(1)
									   ->execute($dc->field);
		
		if($objAttribute->numRows < 1)
		{
			throw new Exception('Not a valid record id!');
		}
		
		if($objAttribute->is_filterable)
		{
			$this->saveFilterValuesToCategories($varValue, $dc);
		}
		
		return $varValue;
	}

	
	protected function getProductTypePalettes()
	{
		$objProductTypes = $this->Database->prepare("SELECT * FROM tl_product_types")->execute();
		
		if (!$objProductTypes->numRows)
		{
			return array();
		}
		
		while($objProductTypes->next())
		{
			$arrFieldCollection = array();
			$strAttributes = "";
			
			$objPid = $this->Database->prepare("SELECT pid FROM tl_product_data WHERE id=?")
									 ->limit(1)
									 ->execute($this->Input->get('id'));
			
			if(!$objPid->numRows)
			{
				$strAttributeField = 'attributes';
			}
			else
			{
				if($objPid->pid!=0)
				{
					$strAttributeField = 'variant_attributes';
				}
				else
				{
					$strAttributeField = 'attributes';
				}
			}
						
			$arrFieldCollection = deserialize($objProductTypes->$strAttributeField);
			
			if(is_array($arrFieldCollection) && count($arrFieldCollection) > 0)
			{
				$objHiddenAttributes = $this->Database->execute("SELECT field_name FROM tl_product_attributes WHERE is_hidden_on_backend='1'");
						
				if($objHiddenAttributes->numRows > 0)
				{				
					while($objHiddenAttributes->next())
					{
						$i = 0;
						
						foreach($arrFieldCollection as $field)
						{
							if($field==$objHiddenAttributes->field_name && $strAttributeField=='attributes')
							{
								unset($arrFieldCollection[$i]);
							}
							
							$i++;
						}
					}
				}
								
				$strAttributes = $this->buildPaletteString($arrFieldCollection);
				
				$arrPalettes[$objProductTypes->id] = $strAttributes;					
			}
		}

		return $arrPalettes;
	}
	
	
	private function buildPaletteString($arrFields, $strAppendToLegend = '', $arrExtraFields = array())
	{
		if (!is_array($arrFields) || !count($arrFields))
			return '';
		
		$arrPalette = array();
	
		foreach( $arrFields as $field )
		{
			// Field does not exist
			if (!is_array($GLOBALS['TL_DCA']['tl_product_data']['fields'][$field]))
				continue;
				
			$arrAttributes = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$field]['attributes'];
	
			/*if($arrAttributes['legend'] == 'options_legend' && (!is_array($arrPalette[$arrAttributes['legend']]) || !in_array('option_set_source', $arrPalette[$arrAttributes['legend']])))
			{
				$arrPalette[$arrAttributes['legend']][] = 'option_set_source';
			}*/

			if(count($arrExtraFields))
			{
				foreach($arrExtraFields as $extrafield)
				{				
					if(is_array($arrPalette[$strAppendToLegend]) && !in_array($extrafield, $arrPalette[$strAppendToLegend]))
						$arrPalette[$strAppendToLegend][] = $extrafield;
				}
			}

			//To do - detemine if product can support variants.  This would be determined by any customer defined attributes being a part of the given palette or not.
			/*if($arrAttributes['legend'] == 'options_legend' && !in_array('options_set_source', $arrPalette[$arrAttributes['legend']]))
			{
				if(!in_array('option_set_source', $this->arrSelectors))
				{
					$this->arrSelectors[] = 'option_set_source';
				}
								
				if(!in_array('option_set_source', $arrPalette[$arrAttributes['legend']]))
				{
					$arrPalette[$arrAttributes['legend']][] = 'option_set_source';
				}
			}*/
						
			$arrPalette[$arrAttributes['legend']][] = $field;
		}
		
		/*		
		if(!in_array('option_set_source', $this->arrSelectors))
		{
			$this->arrSelectors[] = 'option_set_source';
		}
		*/

		//Build
		$arrLegends = array();
		foreach($arrPalette as $legend=>$fields)
		{
			$arrLegends[] = '{' . $legend . '},' . implode(',', $fields);
		}

		return implode(';', $arrLegends);
	}
	
		
	/**
	 * Wrapper for the Product-Filter Collection associative table logic.  Grabs all necessary values in order to update the PFC table.
	 *
	 * @param string
	 * @param object
	 * @return string
	 */
	public function saveFilterValuesToCategories($varValue, DataContainer $dc, $id=0)
	{		
		if(is_null($varValue) || (is_int($varValue) && $varValue == 0))
		{
			return $varValue;
		}
		
		//For import needs, this is an override of the current record ID because when importing we're
		//not utlizing the DataContainer.  We should separate these functions with an intermediary function so that this logic
		//which is repeated across various other functions can be fed just an integer value instead of the more specific
		//DataContainer and its corresponding values.	
		if($id!=0)
		{
			$intId = $id;
		}
		else
		{
			$intId = $dc->id;
		}
						
		//Get the current attribute set		
		$objAttributeID = $this->Database->prepare("SELECT id FROM tl_product_attributes WHERE field_name=?")
										 ->limit(1)
										 ->execute($dc->field);
		
		if($objAttributeID->numRows < 1)
		{
			return $varValue;
		}
		
		$attributeID = $objAttributeID->id;
		
		
		//Gather all records pertaining to the current attribute set in the aggregate table
		$objAllPageInfo = $this->Database->prepare("SELECT pid, value_collection FROM tl_filter_values_to_categories WHERE attribute_id=?")->execute($attributeID);
		
		if($objAllPageInfo->numRows > 0)
		{
			//Contains pid which is the reference to a given page, and attribute_id which is the reference to a given filter.
			$arrAllPageInfo = $objAllPageInfo->fetchAllAssoc();
		}
			
		//Get the value submitted for this particular attribute
		$objRecordValues = $this->Database->prepare("SELECT pages, " . $dc->field . " FROM tl_product_data WHERE id=?")
													->limit(1)
													->execute($dc->id);
		if($objRecordValues->numRows < 1)
		{
			return $varValue;
		}
		
		$arrNewPageList = deserialize($objRecordValues->pages);
		
		if(is_string($arrNewPageList))
		{
			$arrNewPageList = array();
		}
				
		$this->updateFilterValuesToCategories($arrNewPageList, $arrAllPageInfo, $dc, $attributeID, $varValue);
	
		return $varValue;
	}
	
	
	
	/**
	 * updatePFCAggregate - Update our aggregate reference table which is used to build collections of products out of multiple attribute sets. This logic maintains the records by page of associated products and storeTables.
	 *
	 * @param variant
	 * @param object
	 * @param string
	 *
	 */
	private function updateFilterValuesToCategories($arrPageList, $arrAllPageInfo, DataContainer $dc, $attributeID, $varCurrValue)
	{		
		if(sizeof($arrPageList) < 1)
		{
			$arrPageList[] = 0;
		}
		
		if(empty($varCurrValue) || (is_int($varCurrValue) && $varCurrValue==0))
		{
			return;
		}
		
		$arrCurrValues[] = $varCurrValue;
		
		//Check Existing records first to avoid duplicate entries
		$objPFCInfo = $this->Database->prepare("SELECT id, pid, attribute_id, value_collection FROM tl_filter_values_to_categories WHERE pid IN (" . join(",", $arrPageList) . ") AND attribute_id=?")
									->execute($attributeID);
		
		if($objPFCInfo->numRows < 1)
		{
			
			// If there is no existing PFC record, then we just insert. Insert into table the association
			foreach($arrPageList as $intPageNum)
			{				
				$arrSet = array();
				
				$arrSet = array(
					'pid'					=> $intPageNum,
					'attribute_id'			=> $attributeID,
					'value_collection'		=> $arrCurrValues,
				);
				
				$this->Database->prepare("INSERT INTO tl_filter_values_to_categories %s")->set($arrSet)->execute();
			}
			
			return;
		}
		
		
		$arrPFCInfo = $objPFCInfo->fetchAllAssoc();	//Existing records are stored in an array
		
		$arrProducts = array();
		
		$arrPIDs = array();
		
		foreach($arrPFCInfo as $row)	//PIDs that already exist in the tl_filter_values_to_categories table
		{
			$arrPIDs[] = $row['pid'];
		}
		
		
		// For each existing page that DID in the past have this product ID associated with it, but NOW the submitted list does not include that page id, remove it
		foreach($arrAllPageInfo as $page)
		{
			$arrExistingValues = array();
			
			//Get the product ID collection of the current existing page
			$arrExistingValues = deserialize($page['value_collection']);
			
			//If the current existing page id does not exist in the list of pages collected from the form submit, then 
			//remove the product id from the page in question.			
			
			//If the product id exists in the product list for this page, which is not part of the product page list now...  Remove from the product_ids collection and update.
						
				/** TO DO - REWRITE & HANDLE MULITPLE FILTER VALUES IF ATTRIBUTE DOES MULTIPLE **/
					
				if(in_array($varCurrValue, $arrExistingValues))		//Does this need to be more strict - that is, bound to a particular pid when comparing?
				{
									
					$key = array_search($varCurrValue, $arrExistingValues); //get the corresponding key.
										
					//If we find that the product id submitted does, in fact exist in the existing product collection for this page, then we remove it.
				
					//Do any other products in this category share the filter value?  If not then we can safely remove it
					$objProductsAssociatedWithFilterValue = $this->Database->prepare("SELECT id, pages FROM tl_product_data WHERE " . $dc->field . "=?")->execute($varCurrValue);
					
											
					if($objProductsAssociatedWithFilterValue->numRows < 1)	//if there are no occurrences of this filter value in any product, then ok.
					{
						unset($arrExistingValues[$key]);
					}
					else
					{
						$arrOtherProductsPages = $objProductsAssociatedWithFilterValue->fetchEach('pages');	
													
						$blnPreserveFilterValue = false;		//reset every row.  if we end up false at the end we need to unset.
						
						foreach($arrOtherProductsPages as $pageRow)
						{	
							$rowInfo = deserialize($pageRow);
							
							foreach($arrPageList as $currPage)
							{				
								if(in_array($currPage, $rowInfo))
								{
									$blnPreserveFilterValue = true;
									break;
								}
							}
						}
						
						if(!$blnPreserveFilterValue) //if this filter value is used by any other product in any of the categories associated
						{	
							//with the given product, then we cannot remove the filter value from the record.							
							unset($arrExistingValues[$key]);
						}
					}						
					
					if(is_array($arrExistingValues) && sizeof($arrExistingValues)>0)
					{
						 $this->Database->prepare("UPDATE tl_filter_values_to_categories SET value_collection=? WHERE pid=? AND attribute_id=?")
								   		->execute(serialize($arrExistingValues), $page['pid'], $attributeID);
					}
				}

			
			//For each page record already in the table, we grab the product id list and modify it to include this product ID if it isn't existing in the product ID collection.
			
			foreach($arrPFCInfo as $page)
			{
				//Each page record we start with a fresh products array to update the record.
				$arrExistingValues = array();
				
				$arrExistingPages[] = $page['pid'];
				// Since these are serialized, we have to deserialize them before we can do any work on the record.
				$arrExistingValues = deserialize($page['value_collection']);
									
				foreach($arrPageList as $pageToBeUpdated)
				{
					if((int)$pageToBeUpdated==$page['pid'])	//If this page 
					{
						//If the product ID doesn't not already have an association to the current page, then add it to the list of product IDs for that page.
						if(!in_array($varCurrValue, $arrExistingValues))
						{
							$arrExistingValues[] = $varCurrValue;	//add the product id in.
						}
					}				
									
					// Update existing association
					$this->Database->prepare("UPDATE tl_filter_values_to_categories SET value_collection=? WHERE pid=? AND attribute_id=?")
								   ->execute(serialize($arrExistingValues), $page['pid'], $attributeID);
				}			
			}
		
		
		}
		
		//New Pages to add that aren't in the current collection
		foreach($arrPageList as $intPageNum)
		{	
			if(!in_array((int)$intPageNum, $arrExistingPages))
			{
				
				$arrSet = array();
				$arrValues = array();
				
				$arrValues[] = $varCurrValue;
				
				$arrSet = array(
					'value_collection'		=> serialize($arrValues),
					'pid'					=> $intPageNum,
					'attribute_id'			=> $attributeID
				);
				
				$this->Database->prepare("INSERT INTO tl_filter_values_to_categories %s")->set($arrSet)->execute();
			}
		}
	}
}

