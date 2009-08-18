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
 * Class ModuleProductLister
 *
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 */
class ModuleProductLister extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_list_productlisting';

	/**
	 * Button Form Template
	 * @var string
	 */
	protected $strButtonFormTemplate = 'iso_form_simple';
	
	/**
	 * Button Template
	 * @var string
	 */
	protected $strButtonTemplate = 'form_submit_ajax';
	
	/**
	 * Base File Path for checking file existence and basic file ops.
	 * @var string
	 */
	protected $strFileBasePath = '';
	
	/** 
	 * Current Thumbnail Base Path
	 * @var string
	 */
	protected $strCurrentThumbnailBasePath = '';
	 
	/**
	 *  
	 *
	 */
	protected $arrHandleCollection = array();
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE PRODUCT LISTING ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;
			
			return $objTemplate->parse();
		}


	
		// Fallback template
		if (!strlen($this->iso_list_layout))
		{
			$this->iso_list_layout = 'iso_list_productlisting';
		}

//		$this->arrJumpToValues = $this->getStoreJumpToValues($this->store_id);	//Deafult keys are "product_reader", "shopping_cart", and "checkout"

		$this->strTemplate = $this->iso_list_layout;
		return parent::generate();
	}
	
	
	/**
	 * Generate module
	 */
	protected function compile()
	{
		global $objPage;
		
			
		$this->strFileBasePath = $GLOBALS['TL_CONFIG']['isotope_root'];
		
		// WE NEED TO FIX THIS... IT IS THE RIGHT DIRECTORY, BUT WE ARE CREATING IT TWICE
		$this->strCurrentThumbnailBasePath = $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/%s/%s/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['thumbnail_images_folder'];
		
		
		$this->strCurrentImagesBasePath = $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/%s/%s/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'];
		
		
		$arrMessages = array();
					
		if($objPage->show_child_category_products==1)
		{
			$objChildPages = $this->Database->prepare("SELECT id FROM tl_page WHERE pid=?")
											->execute($objPage->id);
											
			if($objChildPages->numRows < 1)
			{
				$strPageList = $objPage->id;
			}
			else
			{
				$arrChildPages = $objChildPages->fetchEach('id');
								
				$strPageList = $objPage->id . "," . implode(",", $arrChildPages);
			}
		}
		else
		{
			$strPageList = $objPage->id;
		}
		
				
		if($this->getRequestData('title'))
		{
			$objPage->title = $this->getRequestData('title');
			$this->Template->title = $this->getRequestData('title');
			$this->headline = $objPage->title;
		}
		
		if($this->getRequestData('pas_id'))
		{
			$strClauses = " attribute_set_id='" . $this->getRequestData('pas_id') . "'";
			$this->Template->pas_id = $this->getRequestData('pas_id');
		}
				
		$this->Template->ignore_page_id = $this->getRequestData('ignore_page_id');
		
		if($this->getRequestData('ignore_page_id')!=1 && $this->new_products_time_window < 1 && $this->featured_products < 1)
		{
			$strClauses = " pid IN(" . $strPageList . ")";
		}

		if(strlen($strClauses))
		{
			$strClauses = " WHERE " . $strClauses;
		}
		
		//Get the CAP aggregate sets 		
		$objAggregateSets = $this->Database->prepare("SELECT * FROM tl_cap_aggregate" . $strClauses)
										  ->execute();
		$strClauses = '';
				
		$intTotalRows = 0;
		
		$page = $this->getRequestData('page') ? $this->getRequestData('page') : 1;
		
		if(!is_int((int)$page))
		{
			$page = 1;
		}
		
		// Order by
		if ($this->getRequestData('order_by'))
		{			
			$this->Template->order_by = $this->getRequestData('order_by');
			
			$arrOrderBy = explode('-', $this->getRequestData('order_by'));
			$strClauses .= " ORDER BY " . htmlspecialchars_decode($arrOrderBy[0]) . ' ' . $arrOrderBy[1];
		}
		else
		{
			$strClauses .= " ORDER BY sorting";
		}
			
		
		$per_page = ($this->getRequestData('per_page') ? $this->getRequestData('per_page') : $this->perPage);
		
		// FIXME: will always be int...
		if(!is_int((int)$per_page) || (int)$per_page==0)
		{
			if($per_page==0)
			{
				$per_page = $this->columns * 4;	//POTENTIAL FIXME: Number of rows?
			}else{
				$per_page = $this->perPage;
			}
		}
		
		// FIXME: Template does not expect a 0-value
		$this->Template->per_page = $per_page;
		
		$rows_left = 0;
		
		$arrProductData = array();
	
		$arrEnabledFilters = deserialize($this->listing_filters);
		if(!is_array($arrEnabledFilters))
		{
			$arrEnabledFilters = array();
		}
		
		
		if(!$objAggregateSets->numRows)
		{
			$arrMessages['noProducts'] = $GLOBALS['TL_LANG']['MSC']['noProducts'];
				
			$this->Template->noProducts = true;
		}
		else
		{			
			$strMissingImagePlaceholder = $this->Store->missing_image_placeholder;
				
			$i = 0;
			
			$product_list = "";
			$arrProductList = array();
			$arrFilters = array();

												
			while( $objAggregateSets->next() )
			{	
									
				$this->strCurrentStoreTable = $objAggregateSets->storeTable;
				
				$intAttributeSetId = ($objAggregateSets->attribute_set_id ? $objAggregateSets->attribute_set_id : 0);
				
				//Get the fields for the current attribute set for listing only.
				$objListingFields = $this->Database->prepare("SELECT id, name, field_name, type, is_filterable, is_searchable, is_listing_field FROM tl_product_attributes WHERE pid=?")
												   ->execute($intAttributeSetId);
				
				
				if($objListingFields->numRows < 1)
				{
					continue;
				}
				
									
				$arrFields = $objListingFields->fetchAllAssoc();
									
				$arrListingFields = array();								
				
				$arrSearchFilterOptions = array();
							
				foreach($arrFields as $field)
				{
					if($field['is_listing_field']==1)
					{
						
						if(!is_array($arrFilterFields) || !in_array($field['field_name'], $arrFilterFields))
						{
							$arrListingFields[] = $field['field_name'];
											
							if($field['is_filterable']==1)
							{
								
								if(sizeof($arrEnabledFilters) && in_array($field['id'], $arrEnabledFilters))
								{														
									$varFilterValue = ($this->getRequestData($field['field_name']) ? $this->getRequestData($field['field_name']) : NULL);
									
									//TEMPORARY WORKAROUND FOR KOLBO - ADD LABEL OVERRIDE FIELD TO ATTRIBUTE MODEL
									if($field['field_name']=="author")
									{
										$strFieldLabel = $field['name'] . ' / Artist';
									}else{
										$strFieldLabel = $field['name'];
									}
									//END TEMPORARY WORKAROUND
						
									switch($field['type'])
									{
										
										case 'select':
										case 'checkbox':
										case 'radio':
											//Build filters
											$arrFilters[] = array
											(
												'name'				=> $field['field_name'],
												'type'				=> 'select',
												'label'				=> $strFieldLabel,
												'current_value'		=> $varFilterValue,
												'options'			=> $this->getListingFilterData($field['id'], true)
											);
											break;
									
									}
									
									//Build SQL filter string
									/*if($varFilterValue)
									{
								
										switch($field['type'])
										{
											case 'shortext':
											case 'text':
											case 'longtext':
												$arrFilterSQL[] = $field['field_name'] . " LIKE ?";
												$arrFilterValues[] = "%" . $varFilterValue . "%";
												break;
											default:
												$arrFilterSQL[] = $field['field_name'] . "=?";
												$arrFilterValues[] = $varFilterValue;
												break;
											
										
										}
										
										
									}*/
									
								}
							}							
							
						}//end if(!in_array($field['field_name'], $arrFilterFields))
						
						//get searchable fields.  TODO - set which fields can be searched in product listing module def.
						if($field['is_searchable'])
						{
							
						
								switch($field['type'])
								{
									case 'shortext':
									case 'text':
									case 'longtext':
										$arrSearchFilterFields[$field['field_name']] = $field['name'];
										//Build SQL filter string
										break;
									default:
										break;	
								}
						}

					}
				}
				
				//Get the data for the search text box.								
				$arrProductSearchText = array
				(
					'name'				=> 'product_search_text',
					'type'				=> 'text',
					'label'				=> $GLOBALS['TL_LANG']['MSC']['searchTextBoxLabel'],
					'current_value'		=> $this->getRequestData('product_search_text')
				
				);
				

/*
				$arrProducts = deserialize($objAggregateSets->product_ids);
				if (is_array($arrProducts) && count($arrProducts))
				{
					$arrProductList = array_merge($arrProductList, $arrProducts);
				}
*/
				
				if( $objAggregateSets->product_id > 0 )
				{
					$arrProductList[] = $objAggregateSets->product_id;
				}
				
				if(!count($arrProductList))
				{
					 continue;
				}
				 		
			
			}
			
			//Text fields are handled differently than auto filter fields because they are consolidated into a single SELECT
			//box so that one (or more) fields can be searched for certain terms.

				

			if(is_array($arrSearchFilterFields) && sizeof($arrSearchFilterFields))
			{
				
					foreach($arrSearchFilterFields as $filter=>$name)
					{
				
						$strFilterValue = $this->getRequestData('product_search_text');
						
						if(strlen($strFilterValue))
						{
							$arrFilterSQL[] = $filter . " LIKE ?";
							$arrFilterValues[] = "%" . $strFilterValue . "%";
							
						}
					}
				
					
				//Build one or more filter=value pairs for SQL querying
				if (is_array($arrFilterSQL) && count($arrFilterSQL))
				{
					$filter_list = "(" . join(" OR ", $arrFilterSQL) . ")";
				}
			}
								
			$product_list = (is_array($arrProductList) && count($arrProductList)) ? join(",", $arrProductList) : '0';
			
			$field_list = '';
			
			if (is_array($arrListingFields) && count($arrListingFields))
			{
				$field_list = ',' . join(",", $arrListingFields);
			}

			if(strlen($filter_list))
			{
				$strFilterList = " AND " . $filter_list;
			}		
					
			if($this->new_products_time_window > 0)
			{
				$arrDate = getdate();
				
				$strFilterList .= " AND date_added>=" . ($arrDate[0] - ((int)$this->new_products_time_window * 86400));
				$strBaseClause = "visibility=1";
			}
			elseif($this->featured_products==1)
			{
				$strFilterList .= " AND featured_product=1";
				$strClauses = " ORDER BY RAND() LIMIT " . $per_page;
				$strBaseClause = "visibility=1";
			}
			else
			{
				$strBaseClause = "id IN(" . $product_list . ") AND visibility=1";
			}	
			
				
			$objTotal = $this->Database->prepare("SELECT COUNT(*) as count FROM " . $this->strCurrentStoreTable . " WHERE " . $strBaseClause . $strFilterList . $strClauses)
									  ->execute($arrFilterValues);
			
			if($objTotal->numRows < 1)
			{
			
				$intTotalRows += 0;
			}
			else
			{
				$intTotalRows += $objTotal->count;
			}
			//echo "SELECT DISTINCT id, tstamp, use_price_override, " . strtolower($field_list) . " FROM " . $this->strCurrentStoreTable . " WHERE " . $strBaseClause . $strFilterList . $strClauses;
					
			//Get the current collection of products based on the tl_cap_aggregate table data
			$objProductCollection = $this->Database->prepare("SELECT id, tstamp, use_price_override, main_image " . strtolower($field_list) . " FROM " . $this->strCurrentStoreTable . " WHERE " . $strBaseClause . $strFilterList . $strClauses);
			
				
			if ($per_page > 0 && $this->featured_products!=1)
			{
				
				
				$objProductCollection->limit($per_page, ($page - 1) * $per_page);
			}
			
			$objProductCollection = $objProductCollection->execute($arrFilterValues);
			
			//calculate a remainder for the next attribute set if any
			if($objProductCollection->numRows < $per_page)
			{					
				$rows_left = $per_page - $objProductCollection->numRows;
			}
			
			
			$arrProducts = $objProductCollection->fetchAllAssoc();
		
			if ($this->iso_jump_first && !strlen($this->getRequestData('asetid')) && !strlen($this->getRequestData('aset_id')) && count($arrProducts))
			{
				$this->redirect($this->generateProductLink($arrProducts[0]['alias'], $arrProducts[0], $this->Store->productReaderJumpTo, $objAggregateSets->attribute_set_id));
			}
			
			$i=0;
														
			foreach($arrProducts as $product)
			{
				$arrProductData[$i] = array
				(
					'name'			=> $product['name'],
					'alias'			=> $product['alias'],
					'link'			=> $this->generateProductLink($product['alias'], $product, $this->Store->productReaderJumpTo, $objAggregateSets->attribute_set_id),
					'price_string'			=> ($product['use_price_override']==1 ? $this->generatePriceStringOverride($this->strPriceOverrideTemplate,$product['price_override']) : $this->generatePrice($product['price'], $this->strPriceTemplate)),
					'thumbnail'				=> $this->getThumbnailImage($product['id'], $product['alias'], $product['main_image'], $strMissingImagePlaceholder, $this->strFileBasePath),
					'id'			=> $product['id'],
					'aset_id'		=> $objAggregateSets->id,
				);
				
				
				$arrProductIDsAndAsetIDs[] = array
				(
					'id' => $product['id'], 
					'aset_id' => $objAggregateSets->id, 
					'name' => $product['name']
				);
				
				
				$arrAdditionalFieldList = explode(',', $field_list);
				
				//Assign for template usage.
				foreach($arrAdditionalFieldList as $field)
				{
					switch($field)
					{	//Skip these fields. Not sure why anymore except duplicates.
						case 'name':
						case 'alias':
						case 'thumbnail_image':
							continue;
							break;
						default:
							$arrAdditionalFields[$field] = $product[$field];
							break;
					}
				}
				
				$arrProductData[$i] = array_merge($arrProductData[$i], $arrAdditionalFields);
				
				$i++;
			}

			$this->Template->products = $arrProductData;
		}
				
		if($intTotalRows < 1)
		{
			$arrMessages['noProducts'] = $GLOBALS['TL_LANG']['MSC']['noProducts'];
					
			$this->Template->noProducts = true;
			$arrProductIDsAndAsetIDs = array();
		}
		
						
		$arrAdditionalMessages = $this->getAdditionalMessages($objPage->id);
		
	
		$this->Template->action = ampersand($this->Environment->request, ENCODE_AMPERSAND);

			
		if($this->getRequestData($strFilterName))
		{
			$this->Template->$strFilterName = $this->getRequestData($strFilterName);
		}
		
		// GIFT REGISTRY BUTTON
		//Check if they are logged in
		if (FE_USER_LOGGED_IN)
		{
			$registryData = $this->userRegistryExists($this->User->id);
			
			//Check if they have a registry
			if(strlen($registryData)>0)
			{
				$this->Template->useReg = true;
			}
		}
		
		$this->Template->headline = ($this->headline ? $this->headline : $objPage->title);														   
		$this->Template->messages = $arrMessages;
		$this->Template->labelPagerSectionTitle = $GLOBALS['TL_LANG']['MSC']['labelPagerSectionTitle'];
		$this->Template->labelOrderBy = $GLOBALS['TL_LANG']['MSC']['labelOrderBy'];
		$this->Template->labelPerPage = $GLOBALS['TL_LANG']['MSC']['labelPerPage'];
		$this->Template->labelSubmit = $GLOBALS['TL_LANG']['MSC']['labelSubmit'];
		$this->Template->showTeaser = $this->show_teaser;
		//Assign the value back to the columns property if defaulting.
		$this->Template->columnLimit = $this->columns = (($this->columns < 1) ? 3 : $this->columns);
		$this->Template->perPageOptions = $this->getPerPageOptions($this->columns);
				
		$this->Template->filters = $arrFilters;
		
		$this->Template->searchFilterFields = $arrProductSearchTextFields;
		$this->Template->searchFilterText = $arrProductSearchText;

						
		/*
		if(sizeof($arrManufacturerData) > 0)
		{
			$this->Template->hasManufacturers = true;
			$this->Template->productManufacturers = $arrManufacturerData;
			$this->Template->labelProductManufacturer = $strManufacturerLabel;
			$this->Template->manufacturerFilterName = $strFilterName;
		}*/
		
		$arrButtonSettings[] = array();//array('add_to_cart');	//Can also accommodate a custom template at ordinal 1. i.e. array(<button type>,<custom template>);
		
		foreach($arrButtonSettings as $buttonSetting)
		{
			//build the button type to product id array for all button types.
			foreach($arrProductIDsAndAsetIDs as $row)
			{
				$arrParams[$buttonSetting[0]][$row['id']] = array	//params are unique to the function of the button.
				(
					'aset_id'				=> $row['aset_id'],
					'quantity_requested'	=> 1,	
					'name'					=> $row['name'],
					'exclude'				=> array('name','exclude')
				);
			}
		}
		
		//$arrButtonTypes[] = array('add_to_wishlist','iso_product_button');
		//$arrButtonTypes[] = array('add_to_registry');
		
		// Call isotope_generate_custom_link_string for an action that hasn't been accounted for.
		// $GLOBALS['ISO_ACTIVE_CUSTOM_PRODUCT_BUTTONS'][] = array('add_to_registry','iso_registry_button_template');
		//
		// Step 1: Gather custom buttons to be rendered, by type, by product id and finally grabbing any additiona parameters needed in the product link.
		if (is_array($GLOBALS['ISO_ACTIVE_CUSTOM_PRODUCT_BUTTONS']))
		{
			foreach ($GLOBALS['ISO_ACTIVE_CUSTOM_PRODUCT_BUTTONS'] as $button)
			{
				$strButtonTemplate = (strlen($button[1]) ? $button[1] : 'iso_product_button');
				
				$arrCustomButtonTypes[] = array($button[0], $strButtonTemplate);
			
				$arrParams = array();
				
				// Call isotope_generate_custom_link_string for an action that hasn't been accounted for.
				// this needs to add parameters required to product a button.  Those parameters are
				// * id
				// * label
				// * action_string
				// * button_template
				// * params
				// * params contain and pertinent data required in the query string to handle the button action
				// * e.g. product_id => 1, quantity => 5 in associative array format.
				
				//Build the button type to product id array for custom button types.
				//There may be more parameters specific to a product ID than just aset id. Therefore this array gets passed to the
				//isotope_load_custom_button_properties hook so that we can attach additional params by product id.
				foreach($arrProductIDsAndAsetIDs as $row)
				{
					$arrDefaultParams[$button[0]][$row['id']] = array
					(
						'aset_id'				=> $row['aset_id'],
						'quantity_requested'	=> 1,
						'name'			=> $row['name'],
						'exclude'				=> array('name','exclude')
					);
				}
				
				//In addition to the required params, load any additional parameters required & specified in the hooked method.
				if (is_array($GLOBALS['TL_HOOKS']['isotope_load_custom_button_properties']))
				{
					foreach ($GLOBALS['TL_HOOKS']['isotope_load_custom_button_properties'] as $callback)
					{
						if (is_array($callback))
						{
							$this->import($callback[0]);
							$arrCustomParams = $this->$callback[0]->$callback[1]($button[0], $arrDefaultParams); 
							//We pass arrParms because that way we can use those to cycle through and attach additional product-specific values to the button.
						}
						
						if(sizeof($arrCustomParams))
						{
							foreach($arrCustomParams as $buttonType)
							{
								//merge with default parameters to create the combined collection of parameters
								$arrParams[$buttonType] = array_merge($arrParams, $arrCustomParams);
							}
						}
					}
				}
			}
		}
			
		foreach($arrButtonSettings as $buttonSetting)
		{
			//This button data remains the same across all product IDs.
			$arrButtonData[] = array
			(
				'button_type'		=> $buttonSetting[0],
				'button_id'			=> 'button_' . $buttonSetting[0] . '_',
				'button_label'		=> $this->generateImage(sprintf('system/modules/isotope/html/%s.gif', $buttonSetting[0])), //$GLOBALS['TL_LANG']['MSC']['buttonLabel'][$buttonSetting[0]],
				'action_string'		=> $GLOBALS['TL_LANG']['MSC']['buttonActionString'][$buttonSetting[0]],
				'button_template'   => (strlen($buttonSetting[1]) ? $buttonSetting[1] : 'iso_product_button'),
				'params'			=> $arrParams[$buttonSetting[0]]	//All product IDs button params
			);
			
			$arrButtonTypes[] = $buttonSetting[0];
		}
				
		/*if(is_array($arrCustomButtonData))
		{
			$arrBaseButtonData = array_merge($arrBaseButtonData, $arrCustomBaseButtonData);
		}*/
			
		$arrButtons = $this->generateButtons($arrButtonData, $objPage->id);
		
		$this->Template->buttonTypes = $arrButtonTypes;
		$this->Template->buttons = $arrButtons;
		
		$this->Template->orderOptions = $this->getOrderByOptions($objAggregateSets->attribute_set_id);
		$this->Template->additionalFilters = '';
		
		if($this->featured_products!=1)
		{		
			$objPagination = new Pagination($intTotalRows, $per_page);
			$this->Template->pagination = $objPagination->generate("\n  ");
		}
		//$this->Template->sortByOptions = '';		
		
		/*$this->Template->buttons = $this->ProductButtons->getButtons();
				
		
	
		/*
			labelPagerSectionTitle (language file)
			labelSortBy (language file)
			sortByOptions (array repeater)
				- url
				- label
			buttons (array repeater)
				- button_class
				- button_object
			pagination (object)
		*/
	}
	
	/**
	 * Get the thumbnail image for the given product
	 * @var string
	 * @var string
	 * @var string
	 * @var string
	 * @return string;
	 */
	protected function getThumbnailImage($intProductID, $strProductAlias, $strProductImage, $strMissingImagePlaceholder, $strFilePath)
	{
		$arrImages = explode(',', $strProductImage);
		
		$strProductImage = $arrImages[0];
				 		
		$strImagePath = (strlen($strProductImage) > 0 ? $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . sprintf($this->strCurrentThumbnailBasePath, substr($strProductAlias, 0, 1), $strProductAlias) . '/' . $strProductImage : $strMissingImagePlaceholder);
				
		if(!file_exists(TL_ROOT . '/' . $strImagePath))
		{
			//echo "yes";
			//attempt to thumbnail if a value exists
			if(strlen($strProductImage) > 0)
			{	
				$this->import('MediaManagement');
				
				$strFallbackPath = $this->MediaManagement->getRootAssetImportPath($this->strCurrentStoreTable, $intProductID);
							
				//THIS IS RETURNING:product_assets/a/alias/images/
				$strBaseImageDestinationPath = sprintf($this->strCurrentImagesBasePath, substr($strProductAlias, 0, 1), $strProductAlias);		
			
				$blnResult = $this->getInitialImages($strFallbackPath, $strBaseImageDestinationPath, $strProductImage, $strProductAlias, $intProductID);
				
				if(!$blnResult)
				{	
					
					return $strMissingImagePlaceholder;
				}
				else
				{
					return $strImagePath;
				}
				
			}
			else
			{
				return $strMissingImagePlaceholder;
			}
		}
		else
		{
			return $strImagePath;
		}
	}
	
	private function getInitialImages($strFallbackPath, $strBaseImageDestinationPath, $strProductImage, $strProductAlias, $intProductID)
	{
		if(!file_exists(TL_ROOT . '/' . $strFallbackPath . '/' . $strProductImage))
		{	
			
			return false;
		}
		else
		{
			
			$this->import('Files');
			$this->import('MediaManagement');
			
			$arrImages = array();
			$arrImageSizeTypes = array();
			
			$fullDestFilePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strDestinationPath . '/' . $strProductImage;
			$fullSourceFilePath = $strFallbackPath . '/' . $strProductImage;
										
			$this->Files->chmod($fullSourceFilePath, 0777);
			$this->Files->copy($fullSourceFilePath, $fullDestFilePath);
			$this->Files->chmod($fullDestFilePath, 0777);
			
			//echo $strDestinationPath . '<br /><br />';
			//exit;
			
			//NOW WE ARE CREATING SUBFOLDERS FOR product_assets/a/alias/images/thumbnail-images
			$this->MediaManagement->createProductAssetSubfolders($strBaseImageDestinationPath . '/gallery_thumbnail_images');
			$this->MediaManagement->createProductAssetSubfolders($strBaseImageDestinationPath . '/thumbnail_images');
			$this->MediaManagement->createProductAssetSubfolders($strBaseImageDestinationPath . '/medium_images');
			$this->MediaManagement->createProductAssetSubfolders($strBaseImageDestinationPath . '/large_images');

			//$this->MediaManagement->createProductAssetSubfolders($GLOBALS)
			
			$arrImageSizeConstraints = $this->MediaManagement->getImageSizeConstraints($this->strCurrentStoreTable, $intProductID);
			$arrProductPaths = $this->MediaManagement->getCurrentProductPaths($strProductAlias);
			
			$arrProductPaths['root_asset_import_path'] = $strFallbackPath;
			
			$arrImages[] = $strProductImage;
			$arrImageSizeTypes[] = 'all';
		
			/*
			echo 'image names: ' . var_dump($arrImages);
			echo "<br /><br />";
			echo 'constraints: ' . var_dump($arrImageSizeConstraints);
			echo "<br /><br />";
			echo "product paths: " . var_dump($arrProductPaths);
			echo "<br /><br />";
			echo "Image Size Types: " . var_dump($arrImageSizeTypes);
			exit;
			*/
			
			$this->MediaManagement->processImages($arrImages, $arrImageSizeConstraints, $arrProductPaths, $arrImageSizeTypes, true, true);
			
			//$this->Files->chmod($fullDestFilePath, 0755);
			
			return true;
		}
	
	}
	
	/**
	 *  Get listing filter data from the cache (tl_pfc_aggregate)
	 *
	 *  @param integer
	 *  @param integer
	 *  @param boolean
	 *  @return array
	 */
	private function getListingFilterData($intAttributeID, $blnUseCache = true)
	{	
		global $objPage;
		
		$intPageID = $objPage->id;
		
		$arrPages = array();
		$arrAssociatedPages = array();
		$arrRefinedValues = array();
				
		if($objPage->show_child_category_products)
		{
			$arrAssociatedPages = $this->getChildPages($intPageID);
						
			if(sizeof($arrAssociatedPages))
			{	
				foreach($arrAssociatedPages as $pageCollection)
				{
					
					foreach($pageCollection as $page)
					{
						$arrPages[] = $page;
					}					
				}
								
			}
			
			$arrPages[] = $intPageID;
			
		}else{
			$arrPages[] = $intPageID;
		}			
		
				
		$strPageList = join(",", $arrPages);
										
		$objListingFilterData = $this->Database->prepare("SELECT value_collection FROM tl_pfc_aggregate WHERE attribute_id=? AND pid IN (" . $strPageList . ")")
												   ->execute($intAttributeID);
				
		if($objListingFilterData->numRows < 1)
		{
			return array();
		}
						
		$arrListingFilterData = $objListingFilterData->fetchEach('value_collection');
		
				
		foreach($arrListingFilterData as $listingDataCollection)
		{
			$arrValuesReturned = deserialize($listingDataCollection); 
						
			foreach($arrValuesReturned as $value)
			{	
				$arrValues[] = $value;
			}
			
		}
		
		$arrUniqueValues = array_unique($arrValues);
				
		$arrRefinedValues = $this->getFilterListData($intAttributeID, $arrUniqueValues);
				
		return $arrRefinedValues;
	
	}
	
	
	
	/**
	 *	Calculate the per-page options based on the number of product columns specified.  The first option is always * 4 rows
	 *  for example, 5 wide * 4 rows = default option of 20 per page.
	 *
	 *	@param integer
	 *	@return array
	 */
	private function getPerPageOptions($intColumns)
	{
		//
		$arrPerPageOptions[] = ($intColumns * 4) * 1;
		$arrPerPageOptions[] = ($intColumns * 4) * 2;
		$arrPerPageOptions[] = ($intColumns * 4) * 3;
		$arrPerPageOptions[] = ($intColumns * 4) * 5;
		$arrPerPageOptions[] = ($intColumns * 4) * 10;
	
		return $arrPerPageOptions;
	}
	/**
	 * Future feature - allows admin to display a message within a template to appear if an announcement is to be made for example, a promotion for a certain category - this could be used to display that promotion easily.
	 * 
	 * @param string
	 * @return string (formatted html)
	 *
	 */
	private function getAdditionalMessages($intPageID)
	{
		
		return;
	
	}
	
	private function getOrderByOptions($intAttributeSetID)
	{
		$objOrderByAttributes = $this->Database->prepare("SELECT name, field_name, type FROM tl_product_attributes WHERE is_visible_on_front='1' AND is_order_by_enabled='1' AND pid=?")
											   ->execute($intAttributeSetID);
	
		if($objOrderByAttributes->numRows < 1)
		{
			return array();
		}
		
		$arrAttributes = $objOrderByAttributes->fetchAllAssoc();
		
		foreach($arrAttributes as $attribute)
		{
			$arrSortingDirections = $this->generateSortingDirections($attribute['type']);
			
			$arrOptions[] = array
			(
				'value'		=> $attribute['field_name'] . '-ASC',
				'label'		=> $attribute['name'] . ' ' . $arrSortingDirections['ASC']
			);
			
			$arrOptions[] = array
			(
				'value'		=> $attribute['field_name'] . '-DESC',
				'label'		=> $attribute['name'] . ' ' . $arrSortingDirections['DESC']
			);
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
			case 'longtext':
			
				return array('ASC' => $GLOBALS['TL_LANG']['MSC']['a_to_z'], 'DESC' => $GLOBALS['TL_LANG']['MSC']['z_to_a']);
				break;
			
			default:
				return;
				break;
		}
	
	}
	
	/* NOTE - THIS FUNCTION IS DUPED IN SEVERAL PLACES (CART, REGISTRY)*/
	// FIXME
	protected function userRegistryExists($strUserId)
	{
		return false;
		
		$strClause = $this->determineUserIdType($strUserId);
						
		$objUserCart = $this->Database->prepare("SELECT id FROM tl_cart WHERE cart_type_id=? AND " . $strClause)
									  ->limit(1)
									  ->execute(2);	//again this will vary later.
		
		if($objUserCart->numRows > 0)
		{
			return true;
			
		}
				
		return false;
	
	}
	
	
}

?>