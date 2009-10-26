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
		
		
		if($this->new_products_time_window < 1 && $this->featured_products < 1)
		{
			$strClauses = " pid IN(" . $strPageList . ")";
		}

		if(strlen($strClauses))
		{
			$strClauses = " WHERE " . $strClauses;
		}
		
		
		//Get the CAP aggregate sets 		
		$objAggregateSets = $this->Database->prepare("SELECT * FROM tl_product_to_category" . $strClauses)
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

			//Get the fields for the current attribute set for listing only.
			$objListingFields = $this->Database->prepare("SELECT id, name, field_name, type, is_filterable, is_searchable, is_listing_field FROM tl_product_attributes")
											   ->execute();
			
			
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
								
								$strFieldLabel = $field['name'];
								
								//END TEMPORARY WORKAROUND
					
								switch($field['type'])
								{
									
									case 'select':
									case 'checkbox':
									case 'radio':
										//Build filters
										$arrFilters[$field['field_name']] = array
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
								if($varFilterValue)
								{
									$arrFilterSQL[] = $field['field_name'] . "=?";
									$arrFilterValues[] = $varFilterValue;		
								}
								
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

			while($objAggregateSets->next())	//Get a literal list of products for this and any child pages, if applicable.
			{
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
				$strBaseClause = "id IN(" . $product_list . ") AND visibility=1 AND pid=0";
			}	
			
			$objTotal = $this->Database->prepare("SELECT COUNT(*) as count FROM tl_product_data WHERE " . $strBaseClause . $strFilterList . $strClauses)
									  ->execute($arrFilterValues);
			
			if($objTotal->numRows < 1)
			{
			
				$intTotalRows += 0;
			}
			else
			{
				$intTotalRows += $objTotal->count;
			}
					
			//Get the current collection of products based on the tl_product_to_category table data
			$objProductCollection = $this->Database->prepare("SELECT id, type, tstamp, use_price_override, main_image " . strtolower($field_list) . " FROM tl_product_data WHERE " . $strBaseClause . $strFilterList . $strClauses);
			
				
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
		
			if ($this->iso_jump_first && count($arrProducts))
			{
				$this->redirect($this->generateProductLink($arrProducts[0]['alias'], $arrProducts[0], $this->Store->productReaderJumpTo));
			}
			
			$i=0;
			$limit = count($arrProducts);
														
			foreach($arrProducts as $product)
			{
			
				//Even, odd, first, last classes
				if ($i == 0)
				{
					$class_row = 'first';
				}
				if ($i == ($limit - 1))
				{
					$class_row = 'last';
				}
				$class_eo = (($i % 2) == 0) ? ' even' : ' odd';
				$classStr = $class_row . $class_eo;
			
				$arrProductData[$i] = array
				(
					'name'			=> $product['name'],
					'alias'			=> $product['alias'],
					'link'			=> $this->generateProductLink($product['alias'], $product, $this->Store->productReaderJumpTo),
					'price_string'			=> ($product['use_price_override']==1 ? $this->generatePriceStringOverride($this->strPriceOverrideTemplate,$product['price_override']) : $this->generatePrice($product['price'], $this->strPriceTemplate)),
					'thumbnail'				=> $this->getThumbnailImage($product['id'], $product['alias'], $product['main_image'], $strMissingImagePlaceholder, $this->strFileBasePath),
					'id'			=> $product['id'],
					'class'         => $classStr,
				);
				
				
				$arrProductIdsAndAsetIds[] = array
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
			$arrProductIdsAndAsetIds = array();
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
		
		$this->Template->buttons = array();
		$this->Template->headline = ($this->headline ? $this->headline : $objPage->title);
		$this->Template->listformat = ($this->iso_list_format ? $this->iso_list_format : 'grid');									   
		$this->Template->messages = $arrMessages;
		$this->Template->labelPagerSectionTitle = $GLOBALS['TL_LANG']['MSC']['labelPagerSectionTitle'];
		$this->Template->labelOrderBy = $GLOBALS['TL_LANG']['MSC']['labelOrderBy'];
		$this->Template->labelPerPage = $GLOBALS['TL_LANG']['MSC']['labelPerPage'];
		$this->Template->labelSubmit = $GLOBALS['TL_LANG']['MSC']['labelSubmit'];
		$this->Template->showTeaser = $this->iso_show_teaser;
		//Assign the value back to the columns property if defaulting.
		$this->Template->columnLimit = $this->columns = (($this->columns < 1) ? 3 : $this->columns);
		$this->Template->perPageOptions = $this->getPerPageOptions($this->columns);
		
		$this->Template->filters = $arrFilters;
		
		$this->Template->searchFilterFields = $arrProductSearchTextFields;
		$this->Template->searchFilterText = $arrProductSearchText;		
		$this->Template->orderOptions = $this->getOrderByOptions();
		$this->Template->additionalFilters = '';
		
		if($this->featured_products!=1)
		{		
			$objPagination = new Pagination($intTotalRows, $per_page);
			$this->Template->pagination = $objPagination->generate("\n  ");
		}
	}
	
	/**
	 * Get the thumbnail image for the given product
	 * @var string
	 * @var string
	 * @var string
	 * @var string
	 * @return string;
	 */
	protected function getThumbnailImage($intProductId, $strProductAlias, $strProductImage, $strMissingImagePlaceholder, $strFilePath)
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
				
				$strFallbackPath = $this->MediaManagement->getRootAssetImportPath($intProductId);
							
				//THIS IS RETURNING:product_assets/a/alias/images/
				$strBaseImageDestinationPath = sprintf($this->strCurrentImagesBasePath, substr($strProductAlias, 0, 1), $strProductAlias);		
			
				$blnResult = $this->getInitialImages($strFallbackPath, $strBaseImageDestinationPath, $strProductImage, $strProductAlias, $intProductId);
				
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
	
	private function getInitialImages($strFallbackPath, $strBaseImageDestinationPath, $strProductImage, $strProductAlias, $intProductId)
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
			
			$arrImageSizeConstraints = $this->MediaManagement->getImageSizeConstraints($this->strCurrentStoreTable, $intProductId);
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
	 *  Get listing filter data from the cache (tl_filter_values_to_categories)
	 *
	 *  @param integer
	 *  @param integer
	 *  @param boolean
	 *  @return array
	 */
	private function getListingFilterData($intAttributeId, $blnUseCache = true)
	{	
		global $objPage;
		
		$intPageId = $objPage->id;
		
		$arrPages = array();
		$arrAssociatedPages = array();
		$arrRefinedValues = array();
				
		if($objPage->show_child_category_products)
		{
			$arrAssociatedPages = $this->getChildPages($intPageId);
						
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
			
			$arrPages[] = $intPageId;
			
		}else{
			$arrPages[] = $intPageId;
		}			
		
				
		$strPageList = join(",", $arrPages);
										
		$objListingFilterData = $this->Database->prepare("SELECT value_collection FROM tl_filter_values_to_categories WHERE attribute_id=? AND pid IN (" . $strPageList . ")")
												   ->execute($intAttributeId);
				
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
				
		$arrRefinedValues = $this->getFilterListData($intAttributeId, $arrUniqueValues);
				
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
	private function getAdditionalMessages($intPageId)
	{
		
		return;
	
	}
	
	private function getOrderByOptions()
	{
		$objOrderByAttributes = $this->Database->prepare("SELECT name, field_name, type FROM tl_product_attributes WHERE is_visible_on_front=? AND is_order_by_enabled=?")
											   ->execute(1, 1);
	
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

