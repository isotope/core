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
 * Class ModuleProductReader
 *
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    Controller
 */
class ModuleProductReader extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_reader_product_single';

	/**
	 * Media Player Template
	 * @var string
	 */
	protected $strInternalMediaPlayerTemplate = 'ce_mediaplayer_internal';
	
	/**
	 * Media Player Template
	 * @var string
	 */
	protected $strExternalMediaPlayerTemplate = 'ce_mediaplayer_external';
	
	/**
	 * Main image raw import folder
	 * @var string
	 */
	protected $strMainImageImportFolder = '';
	
	/** 
	 * Main image base path
	 * @var string
	 */
	protected $strMainImageBasePath = '';
	
	/**
	 * Extra images flag
	 * @var boolean
	 */
	protected $hasExtraImages = false;
	
	/**
	 * Product Id	-  Using this limits us to showing a single product on the reader
	 * @var integer
	 */
	protected $intProductId;
	
	protected $intAttributeSetId;
	
	/**
	 * Main Image
	 * @var array
	 */
	protected $arrMainImage = array();
	
	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### ISOTOPE PRODUCT READER ###';

			return $objTemplate->parse();
		}

		// Return if no news item has been specified
		if (!$this->Input->get('product'))
		{
			return '';
		}

		if (!strlen($this->iso_reader_layout))
		{
			$this->iso_reader_layout = 'iso_reader_product_single';
		}

		/*if (!strlen($this->internal_media_player_template))
		{	
			$this->strInternalMediaPlayerTemplate = 'ce_mediaplayer_internal';
		}
		
		if(!strlen($this->external_media_player_template))
		{
			$this->strExternalMediaPlayerTemplate = 'ce_mediaplayer_external';
		}*/
		
		$this->strTemplate = $this->iso_reader_layout;
		
		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		global $objPage;
					
		$useLegacyMainImage = true;
		
		$arrErrorMessages = array();
		$this->import('MediaManagement');
				
		/*
		$this->Template->hasExtraImages
		$this->Template->extraProductImages
			- has_large_image
			- large_image_link
			- thumbnail
			- onThumbnailClickEvent
		$this->Template->productOptions
			- optionType
			- optionName
			- options
				- key
				- value
		*/
	
		$time = time();

		if(is_numeric($this->Input->get('asetid')))
		{
			//Get the source table for this CAP record
			$objCAPRecord = $this->Database->prepare("SELECT storeTable, attribute_set_id FROM tl_cap_aggregate WHERE id=?")
										   ->limit(1)
										   ->execute($this->Input->get('asetid'));
										   
			if($objCAPRecord->numRows < 1)
			{
				$isError = true;
				$errKey[] = 'invalidProductInformation';			
				
			}
			
			
			$this->strCurrentStoreTable = $objCAPRecord->storeTable;
			
			
			$objCurrentStoreConfiguration = $this->Database->prepare("SELECT store_id FROM tl_product_attribute_sets WHERE id=?")
														   ->limit(1)
														   ->execute($objCAPRecord->attribute_set_id);
			
			$this->intAttributeSetId = $objCAPRecord->attribute_set_id;
			
			if($objCurrentStoreConfiguration->numRows < 1)
			{
				$this->intStoreId = 1;
			}else{
				$this->intStoreId = $objCurrentStoreConfiguration->store_id;
			}
			
			
			$strMissingImagePlaceholder = $this->getMissingImagePlaceholder($this->intStoreId);
						
			$objProductData = $this->Database->prepare("SELECT * FROM " . $this->strCurrentStoreTable . " WHERE id=? OR product_alias=?")
									 ->limit(1)
									 ->execute((is_numeric($this->Input->get('product')) ? $this->Input->get('product') : 0), $this->Input->get('product'));

			if($objProductData->numRows < 1)
			{
				$isError = true;
				$errKey[] = 'invalidProductInformation';
			}			
								 
		}else{
			$isError = true;
			$errKey[] = 'invalidProductInformation';
		}
		
		if($isError)
		{
			foreach($errKey as $error)
			{
				$arrErrorMessages[$error] = $GLOBALS['TL_LANG']['MSC'][$error];
			}
			
			$this->Template->errorMessages = $arrErrorMessages;
			
		}else{
			
			$arrProductData =  $objProductData->fetchAllAssoc();
				
			/*$objProductAttributeData = $this->Database->prepare("SELECT is_embeddable_media FROM tl_product_attributes WHERE pid=? AND name=?")
													  ->limit(1)
													  ->execute($arrProductData['pid'], $arrProductData);
			*/			
			foreach($arrProductData as $product)
			{
				//For attributes that need pre-processing before renderering out to template.
				foreach($product as $k=>$v)
				{
			
					switch($k)
					{
						case "product_name":													
							$objPage->title = $v;
							$this->Template->productName = $v;
							break;
						case "product_description":
							$objPage->description = strip_tags($this->generateTeaser($v));
							break;
						case "use_product_price_override":
							if($v==1)
							{
								$product['price_string'] = $this->generatePrice($product['product_price_override'], $this->strPriceOverrideTemplate);
							}else{
								$product['price_string'] = $this->generatePrice($product['product_price']);
							}							
							break;							
				
						case "product_alias":
							$this->strMainImageBasePath = sprintf($GLOBALS['TL_CONFIG']['isotope_base_path'] . '/%s/%s/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'], substr($v, 0, 1), $v);
																	
							$arrProductPaths = $this->MediaManagement->getCurrentProductPaths($v);
							
							$arrSourceFiles = $this->MediaManagement->getMediaFilenames($arrProductPaths['file_source_path'], $GLOBALS['TL_LANG']['MSC']['imagesFolder'], 'source');
							
							$arrLiveFiles = $this->MediaManagement->getMediaFilenames($arrProductPaths['file_destination_path'], $GLOBALS['TL_LANG']['MSC']['imagesFolder'], 'destination');
						
							$arrNeededImages = array();
							if($blnForceRescale || (count($arrSourceFiles) > count($arrLiveFiles)))
							{
								//Find Different and thumbnail those new files.
								if(sizeof($arrSourceFiles) && sizeof($arrLiveFiles))
								{
									//Figure out if any new images need to be thumbnailed.
									if($blnForceRescale)
									{
										$arrNeededImages = $arrSourceFiles;
									}
									else
									{
										$arrNeededImages = array_diff($arrSourceFiles, $arrLiveFiles);
									}
								}
								else
								{
									//Check for a file or folder by that name in the main import folder as specified in store config.
									$arrAssetKeys = array($product['product_alias'], $product['product_sku']);
								
									//Get the default import folder from config
									$strFilePath = $this->MediaManagement->getRootAssetImportPathByStoreId($this->intStoreId);
								
									$arrNeededImages = $this->MediaManagement->getRelatedProductAssetFilenamesByType($arrAssetKeys, $strFilePath, 'image');
								}
								
							
							}
								
								
							$arrImageSizeConstraints = $this->MediaManagement->getImageSizeConstraints($this->strCurrentStoreTable, (is_numeric($this->Input->get('product')) ? $this->Input->get('product') : 0), $this->Input->get('product'));
								
							if (count($arrNeededImages))
								$this->MediaManagement->processImages($arrNeededImages, $arrImageSizeConstraints, $arrProductPaths, array('all'), $blnForceRescale);
								
																				
							if(is_dir($arrProductPaths['file_destination_path']))
							{	
								$arrImages = $this->getProductImages($arrProductPaths['file_destination_path'], $arrProductPaths['relative_destination_path'], $product, $GLOBALS['TL_LANG']['MSC']['imagesFolder'], $product['product_images'], $product['product_alias']);
								
								if(count($arrImages))
								{	
									foreach($arrImages as $imageRecord)
									{
										if($imageRecord['is_main_image'])
										{															
											$this->Template->mainImage = $imageRecord;
										}else{
											if($useLegacyMainImage)
											{
												$this->Template->mainImage = $arrImages[0];
											}
											$this->Template->hasExtraImages = true;
											$arrOtherImages[] = $imageRecord;
										}
										
										
									}
									

									if(count($arrOtherImages))
									{
										$this->Template->extraProductImages = $arrOtherImages;
									
									}
									
								}else{
									
									$this->arrMainImage['file_path'] = $strMissingImagePlaceholder;
									$this->arrMainImage['height'] = NULL;
									$this->arrMainImage['width'] = NULL;
									
									$this->Template->mainImage = $this->arrMainImage;
									
									
								}
							}else{
								$this->arrMainImage['file_path'] = $strMissingImagePlaceholder;
								$this->arrMainImage['height'] = NULL;
								$this->arrMainImage['width'] = NULL;
									
								$this->Template->mainImage = $this->arrMainImage;

							}
							
							break;
							
							case 'audio_source':
							case 'video_source':
								if(strlen($k > 0))
								{
									$objAudioPlayerTemplate = new FrontendTemplate($this->strInternalMediaPlayerTemplate);
									//var_dump($objAudioPlayerTemplate);
									if(!file_exists($absoluteAssetsFolderPath . '/' . 'mrss.xml'))
									{
										break;
									}
									
									$objAudioPlayerTemplate->playSoundMessage = $GLOBALS['TL_LANG']['MSC']['playSoundMessage'];
									
									$objAudioPlayerTemplate->productBasePath = $this->Environment->base . $relativeAssetsPath . '/';
															
									$this->Template->embeddedMedia = $objAudioPlayerTemplate->parse();
								}	
							break;
							
						default:
							$arrAttributeData = $this->getProductAttributeData($k, $this->intAttributeSetId);
							
							//var_dump($arrAttributeData);
							
							switch($arrAttributeData['type'])
							{
								case 'select':
									//check for a related label to go with the value.
									$arrOptions = deserialize($arrAttributeData['option_list']);
									$varValues = deserialize($v);
									
									foreach($arrOptions as $option)
									{
										if(is_array($varValues))
										{
											if(in_array($option['value'], $varValues))
											{
												$arrLabels[] = $option['label'];
											}
										}else{	
											
											if((int)$option['value']==(int)$v)
											{
												$arrLabels[] = $option['label'];
											}
										}
									
									}
									
																		
									$product[$k] = join(',', $arrLabels); 
									break;
									
								default:
									break;
							}
							
							break;
					}
					
					
									
				}
				
				$this->intProductId = $product['id'];
				
				$arrProductIDsAndAsetIDs[] = array
					(
						'id' => $product['id'], 
						'aset_id' => $this->Input->get('asetid'), 
						'product_name' => $product['product_name'],
						'product_alias' => $product['product_alias']
					);		
				
				
				$arrProducts[] = $product;	
		
			}

	
			//------------------------------------------------------------------------------------//
			//START BUTTON CODE//
			//Button code duplicated from lister.  This all needs to be moved into a button class.
			$arrButtonSettings[] = array('add_to_cart');	//Can also accommodate a custom template at ordinal 1. i.e. array(<button type>,<custom template>);
		
			foreach($arrButtonSettings as $buttonSetting)
			{
				//build the button type to product id array for all button types.
				foreach($arrProductIDsAndAsetIDs as $row)
				{
					$arrParams[$buttonSetting[0]][$row['id']] = array	//params are unique to the function of the button.
					(
						'aset_id'				=> $row['aset_id'],
						'quantity_requested'	=> 1,	
						'product_name'			=> $row['product_name'],
						'exclude'				=> array('product_name','exclude')
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
							'product_name'			=> $row['product_name'],
							'exclude'				=> array('product_name','exclude')
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
			$this->Template->productId = $this->intProductId;			
			$this->Template->buttonTypes = $arrButtonTypes;
			$this->Template->buttons = $arrButtons;
			
			//END BUTTON CODE//
			//------------------------------------------------------------------------------------//
								
			$this->Template->productDescriptionLabel = $GLOBALS['TL_LANG']['MSC']['productDescriptionLabel'];
			$this->Template->productDetailLabel = $GLOBALS['TL_LANG']['MSC']['productDetailLabel'];	
			$this->Template->productMediaLabel = $GLOBALS['TL_LANG']['MSC']['productMediaLabel'];
			
			$this->Template->messages = $this->getProductMessages($product['id']);	
			$this->Template->productCollection = $arrProducts;
			
		}
		
			
			/*$objProductAttributeData = $this->Database->prepare("SELECT is_embeddable_media FROM tl_product_attributes WHERE pid=? AND name=?")
													  ->limit(1)
													  ->execute($arrProductData['pid'], $arrProductData);
			*/			
			
	}
	
	private function getProductAttributeData($strFieldName, $intPid)
	{		
		$objAttributeData = $this->Database->prepare("SELECT id, type, option_list FROM tl_product_attributes WHERE field_name=? AND pid=?")
										   ->limit(1)
										   ->execute($strFieldName, $intPid);

		if($objAttributeData->numRows < 1)
		{
			return array();
		}
		
		return $objAttributeData->fetchAssoc();
	}
	
	/**
	 * Grabs a main image as well as any extra images and any related information and functionality pertaining to display of images on the front end.
	 *
	 * @param string
	 * @param string
	 * @param array
	 * @return array
	 *
	 */
	private function getProductImages($strAbsoluteAssetFolderPath, $strRelativeAssetPath, $arrProductData, $strAssetType, $strMainImageFilename, $strProductAlias)
	{ 
		//Originally this implied the existence of the file in the appropriate folders because all thumbnailing happened on import or product save.
		//Now with FE image caching, if we get a fail on file_exists then we look in the main holding tank for this specific file and process it accordingly.
		//The limitation for now is that this only handles the main image.  For additional images other conditions must be met (which need to be coded as of
		//2/9/2009 - find folder (sku named), find files (sku named)
		$blnHasAssignedMainImage = false;
		if(is_dir($strAbsoluteAssetFolderPath))
		{
			
			if ($dh = opendir($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'])) 
			{

				while (($file = readdir($dh)) !== false)
				{
					$extension = explode('.', $file);
				
					if(in_array($extension[1], $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes']['images']))
					{
						if(strcmp($file, $strMainImageFilename)==0)
						{
							$blnHasAssignedMainImage = true;
														
							if(file_exists($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file))
							{
								$hasLargeImage = true;
								$largeImageLink = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file; 
							}else{
								$hasLargeImage = false;
								$largeImageLink = false;
							}
							
														
							$arrImageSize = getimagesize($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $currentImageAssetFolder . '/' . $file);
							
							
							$arrImages[$extension[0]] = array
							(
								'file_path' 			=> $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $file,
								'width'					=> $arrImageSize[0],
								'height'				=> $arrImageSize[1],
								'alt'					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['product_name'], $arrProductData['product_teaser'])),
								'has_large_image'		=> $hasLargeImage,
								'large_image_link'		=> $largeImageLink,
								'on_thumbnail_click_event' => $GLOBALS['TL_LANG']['MSC']['thumbnailImageClickEvent'],
								'is_main_image' 		=> (strcmp($file, $strMainImageFilename)==0 ? true : false)
							);
							$i++;
							
						}else{ 
							if(file_exists($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file))
							{
								$hasLargeImage = true;
								$largeImageLink = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file; 
							}else{
								$hasLargeImage = false;
								$largeImageLink = false;
							}
							
							$arrImageSize = @getimagesize($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $file);	
															
							$arrImages[$extension[0]] = array
							(
								'file_path'				=> $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $file,
								'width'					=> $arrImageSize[0],
								'height'				=> $arrImageSize[1],
								'alt' 					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['product_name'], $arrProductData['product_teaser'])),
								'has_large_image' 		=> $hasLargeImage,
								'large_image_link'		=> $largeImageLink,
								'on_thumbnail_click_event' => $GLOBALS['TL_LANG']['MSC']['thumbnailImageClickEvent']
							);
						
						}
					}
					
				}
				
				//If we can't find an associated main image for the product, then we're going to grab the first one we find and do the work for our template.
				if(!$blnHasAssignedMainImage)
				{
					
					$file = $this->MediaManagement->getFirstOrdinalImage($this->strMainImageBasePath, $strProductAlias);
					
					if(!file_exists($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $file) || strlen($file) < 1)
					{
						
						$file = $this->getMissingImagePlaceholder($this->intStoreId);
						$strFinalFilePath = $file;
						
					}else{
						$strFinalFilePath = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $file;
					}
					
					$extension = explode('.', $file);
					
					if(file_exists($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file))
					{
						$hasLargeImage = true;
						$largeImageLink = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file; 
					}else{
						$hasLargeImage = false;
						$largeImageLink = false;
					}
							
					$arrImageSize = getimagesize($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $currentImageAssetFolder . '/' . $file);
					
						
					$arrImages[$extension[0]] = array
					(
						'file_path' 			=> $strFinalFilePath,
						'width'					=> $arrImageSize[0],
						'height'				=> $arrImageSize[1],
						'alt'					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['product_name'], $arrProductData['product_teaser'])),
						'has_large_image'		=> $hasLargeImage,
						'large_image_link'		=> $largeImageLink,
						'on_thumbnail_click_event' => $GLOBALS['TL_LANG']['MSC']['thumbnailImageClickEvent'],
						'is_main_image' 		=> true
					);
							
				}
				
			}
		}
	
		return $arrImages;	
	}
	
	
	//SWITCH TO CATCH-ALL 'getAsset' function to handle any file asset for a given product?  In: path data, asset type, product data.  Out: file path, asset specific attributes.
	//Asset specific attributes may be the reason *not* to consolidate this.
	
	
	/// ---- ORIGINAL ---- DEPRECATED ATM
	/**
	 * Grabs a main image as well as any extra images and any related information and functionality pertaining to display of images on the front end.
	 *
	 * @param string
	 * @param string
	 * @param array
	 * @return array
	 *
	 */
	/*
	private function getProductImages($strAbsoluteAssetFolderPath, $strRelativeAssetPath, $arrProductData, $strAssetType, $strMainImageFilename)
	{ 
		//$arrImageSizeConstraints = $this->getImageSizeConstraints();

		if(is_dir($strAbsoluteAssetFolderPath))
		{
			
			if ($dh = opendir($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'])) {
				$i = 1;
				while (($file = readdir($dh)) !== false)
				{
					$extension = explode('.', $file);
					
					if($extension[1]=='jpg' || $extension[1]=='jpeg')
					{
						
						if(strcmp($file, $strMainImageFilename)===0)
						{
							if(file_exists($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file))
							{
								$hasLargeImage = true;
								$largeImageLink = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file; 
							}else{
								$hasLargeImage = false;
								$largeImageLink = false;
							}
							
							$arrImageSize = getimagesize($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $currentImageAssetFolder . '/' . $file);
							
							
							$arrImages[] = array
							(
								'file_path' 			=> $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $file,
								'width'					=> $arrImageSize[0],
								'height'				=> $arrImageSize[1],
								'alt'					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['product_name'], $arrProductData['product_teaser'])),
								'has_large_image'		=> $hasLargeImage,
								'large_image_link'		=> $largeImageLink,
								'on_thumbnail_click_event' => $GLOBALS['TL_LANG']['MSC']['thumbnailImageClickEvent'],
								'is_main_image' 		=> (strcmp($file, $strMainImageFilename)==0 ? true : false)
							);
							
						}else{ 
							if(file_exists($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file))
							{
								$hasLargeImage = true;
								$largeImageLink = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file; 
							}else{
								$hasLargeImage = false;
								$largeImageLink = false;
							}
							
							$arrImageSize = @getimagesize($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $file);	
															
							$arrImages[] = array
							(
								'file_path'				=> $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $file,
								'width'					=> $arrImageSize[0],
								'height'				=> $arrImageSize[1],
								'alt' 					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['product_name'], $arrProductData['product_teaser'])),
								'has_large_image' 		=> $hasLargeImage,
								'large_image_link'		=> $largeImageLink,
								'on_thumbnail_click_event' => $GLOBALS['TL_LANG']['MSC']['thumbnailImageClickEvent']
							);
						}
					}
					
					
				}
			}
		}
	
		return $arrImages;	
	}*/
}

?>