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


class ModuleProductReader extends ModuleIsotopeBase
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_productreader';
	
	protected $strFormId = 'iso_product_reader';
	

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
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'typolight/main.php?do=modules&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Return if no product has been specified
		if (!strlen($this->Input->get('product')))
		{
			return '';
		}

		if (!strlen($this->iso_reader_layout))
		{
			$this->iso_reader_layout = 'iso_reader_default';
		}
		
		global $objPage;
		
		$this->iso_reader_jumpTo = $objPage->id;

		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
/*
		// For Continue Shopping button. This should be tripped every time people hit the product reader
		if($this->getReferer(ENCODE_AMPERSANDS) != ampersand($this->Environment->request, true))
		{
			$_SESSION['referringPage'] = $this->getReferer(ENCODE_AMPERSANDS);
		}
		$this->Template->referrer = $_SESSION['referringPage'];
*/	
		
		$arrProduct = $this->getProductByAlias($this->Input->get('product'));
		
		if (!$arrProduct)
		{
			$this->Template = new FrontendTemplate('mod_message');
			$this->Template->type = 'empty';
			$this->Template->message = $GLOBALS['TL_LANG']['MSC']['invalidProductInformation'];
			return;
		}
		
		// Buttons
		$arrButtons = array
		(
			'add_to_cart'		=> array('label'=>$GLOBALS['TL_LANG']['MSC']['buttonLabel']['add_to_cart'], 'callback'=>array('IsotopeCart', 'addProduct')),
		);
		
		if (isset($GLOBALS['TL_HOOKS']['isoReaderButtons']) && is_array($GLOBALS['TL_HOOKS']['isoReaderButtons']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isoReaderButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}
		}
		
		if ($this->Input->post('FORM_SUBMIT') == $this->strFormId && $this->Input->post('product_id') == $arrProduct['raw']['id'])
		{
			foreach( $arrButtons as $button => $data )
			{
				if (strlen($this->Input->post($button)))
				{
					if (is_array($data['callback']) && count($data['callback']) == 2)
					{
						$this->import($data['callback'][0]);
						$this->{$data['callback'][0]}->{$data['callback'][1]}($arrProduct['raw']['id'], $arrProduct);
					}
					break;
				}
			}
			
			$this->reload();
		}
		
		
		$this->Template->action = ampersand($this->Environment->request, true);
		$this->Template->formId = $this->strFormId;
		$this->Template->product = $this->generateProduct($arrProduct, $this->iso_reader_layout);
		$this->Template->buttons = $arrButtons;
		
		
		
		
		
		
		
		
		
		
/*		
	
		global $objPage;
					
		$useLegacyMainImage = true;
		
		$arrErrorMessages = array();
		$this->import('MediaManagement');
				
		
//		$this->Template->hasExtraImages
//		$this->Template->extraProductImages
//			- has_large_image
//			- large_image_link
//			- thumbnail
//			- onThumbnailClickEvent
//		$this->Template->productOptions
//			- optionType
//			- optionName
//			- options
//				- key
//				- value
		
		
		// FIXME
		$blnForceRescale = true;
	
		$time = time();

		$strMissingImagePlaceholder = $this->Isotope->Store->missing_image_placeholder;
							
		$objProductData = $this->Database->prepare("SELECT * FROM tl_product_data WHERE id=? OR alias=?")
								 ->limit(1)
								 ->execute((is_numeric($this->Input->get('product')) ? $this->Input->get('product') : 0), $this->Input->get('product'));
	
		if($objProductData->numRows < 1)
		{
			$isError = true;
			$errKey[] = 'invalidProductInformation';
		}
		
		if($isError)
		{
			foreach($errKey as $error)
			{
				$arrErrorMessages[$error] = $GLOBALS['TL_LANG']['MSC'][$error];
			}
			
			$this->Template->hasErrors = true;
			$this->Template->errorMessages = $arrErrorMessages;
		}
		else
		{
			
			$arrProductData =  $objProductData->fetchAllAssoc();
				
//			$objProductAttributeData = $this->Database->prepare("SELECT is_embeddable_media FROM tl_product_attributes WHERE pid=? AND name=?")
//													  ->limit(1)
//													  ->execute($arrProductData['pid'], $arrProductData);

			foreach($arrProductData as $product)
			{
				
				//For attributes that need pre-processing before renderering out to template.
				foreach($product as $k=>$v)
				{
					$v = deserialize($v);
					
					switch($k)
					{
						
						case "name":													
							$objPage->title = $v;
							$this->Template->productName = $v;
							break;
							
						case "description":
							$objPage->description = strip_tags($this->generateTeaser($v));
							break;
							
						case "use_price_override":
							if($v==1)
							{						
								$product['price_string'] = $this->generatePriceStringOverride($this->strPriceOverrideTemplate, $this->Isotope->calculatePrice($product[$this->Isotope->Store->priceOverrideField]));
							}
							else
							{
							
								$product['price_string'] = $this->generatePrice($this->Isotope->calculatePrice($product[$this->Isotope->Store->priceField]));
							}							
							break;							
				
						case "alias":
							$this->strMainImageBasePath = sprintf($GLOBALS['TL_CONFIG']['isotope_base_path'] . '/%s/%s/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'], substr($v, 0, 1), $v);
																	
							$arrProductPaths = $this->MediaManagement->getCurrentProductPaths($v);
							
							$arrSourceFiles = $this->MediaManagement->getMediaFilenames($arrProductPaths['file_source_path'], $GLOBALS['TL_LANG']['MSC']['imagesFolder'], 'source');
							
							$arrLiveFiles = $this->MediaManagement->getMediaFilenames($arrProductPaths['file_destination_path'], $GLOBALS['TL_LANG']['MSC']['imagesFolder'], 'destination');
													
							$arrNeededImages = array();
							if($blnForceRescale || (count($arrSourceFiles) > count($arrLiveFiles)))
							{
								//Find Different and thumbnail those new files.
								if(sizeof($arrSourceFiles))
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
									$arrAssetKeys = array($product['alias'], $product['sku']);
									
									$arrNeededImages = $this->MediaManagement->getRelatedProductAssetFilenamesByType($arrAssetKeys, $this->Isotope->Store->root_asset_import_path, 'images');
								}
							}
								
								
							if (count($arrNeededImages))
							{
								$arrImageSizeConstraints = $this->MediaManagement->getImageSizeConstraints($this->strCurrentStoreTable, (is_numeric($this->Input->get('product')) ? $this->Input->get('product') : 0), $this->Input->get('product'));
								
								$this->MediaManagement->processImages($arrNeededImages, $arrImageSizeConstraints, $arrProductPaths, array('all'), $blnForceRescale);
							}
								
																				
							if(is_dir($arrProductPaths['file_destination_path']))
							{	
								$arrImages = $this->getProductImages($arrProductPaths['file_destination_path'], $arrProductPaths['relative_destination_path'], $product, $GLOBALS['TL_LANG']['MSC']['imagesFolder'], $product['main_image'], $product['alias']);
								
								sort($arrImages);
								
								if(count($arrImages))
								{	
									foreach($arrImages as $imageRecord)
									{
										if($imageRecord['is_main_image'])
										{
											$this->arrMainImage = $imageRecord;
											$this->Template->mainImage = $this->arrMainImage;
											$useLegacyMainImage = false;
										}
										else
										{
											if($useLegacyMainImage)
											{
												$this->Template->mainImage = array_shift($arrImages);
											}
											
											$this->Template->hasExtraImages = true;
											$arrOtherImages[] = $imageRecord;
										}
									}
									

									if(count($arrOtherImages))
									{
										$this->Template->extraProductImages = $arrOtherImages;
									}
									
								}
								else
								{
									$this->arrMainImage['file_path'] = $strMissingImagePlaceholder;
									$this->arrMainImage['height'] = NULL;
									$this->arrMainImage['width'] = NULL;
									
									$this->Template->mainImage = $this->arrMainImage;
								}
							}
							else
							{
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
							$arrAttributeData = $this->getProductAttributeData($k);
							
							$blnIsMergedOptionSet = true;
							
							if($arrAttributeData['is_customer_defined'])
							{
								//does it have a value?
								if($product[$k])
								{
									$arrOptionFields[] = $k;
								}															
								
								if(!$blnIsMergedOptionSet)
								{
									$arrData = $this->getDCATemplate($arrAttributeData);	//Grab the skeleton DCA info for widget generation

									$product['options'][] = array
									(
										'name'			=> $k,
										'description'	=> $arrAttributeData['description'],									
										'html'			=> $this->generateProductOptionWidget($k, $arrData, $this->strFormId)
									);										
								}
							
							}else{
																					
								switch($arrAttributeData['type'])
								{
									case 'select':
									case 'radio':
									case 'checkbox':
										//check for a related label to go with the value.
										$arrOptions = deserialize($arrAttributeData['option_list']);
										$varValues = deserialize($v);
										$arrLabels = array();
										
										if($arrAttributeData['is_visible_on_front'])
										{
																				
											foreach($arrOptions as $option)
											{
												if(is_array($varValues))
												{
													if(in_array($option['value'], $varValues))
													{
														$arrLabels[] = $option['label'];
													}
												}else{	
													
													if($option['value']===$v)
													{
														$arrLabels[] = $option['label'];
													}
												}
											
											}
											
											if($arrLabels)
											{									
												$product[$k] = join(',', $arrLabels); 
											}
											
										}
										break;
																																				
									default:
										if($arrAttributeData['is_visible_on_front'])
										{
											//just direct render
											$product[$k] = $v;
										}
										break;
								}
							}							
							break;
					}
					
					
									
				}
						
				if($blnIsMergedOptionSet && sizeof($arrOptionFields))
				{
					
					//Create a special widget that combins all option value combos that are enabled.
					$arrData = array
					(
						'name'			=> 'subproducts',
						'description'	=> &$GLOBALS['TL_LANG']['tl_product_data']['product_options'],
						'inputType'		=> 'select',					
						'options'		=> $this->getSubproductOptionValues($product['id'], $arrOptionFields),
						'eval'			=> array()
					);
					
					//$arrData = $this->getDCATemplate($arrAttributeData);	//Grab the skeleton DCA info for widget generation

					$product['options'][] = array
					(
						'name'			=> $k,
						'description'	=> $arrAttributeData['description'],									
						'html'			=> $this->generateProductOptionWidget('product_variants', $arrData, $this->strFormId, $arrOptionFields)
					);	
				}
				
				$this->intProductId = $product['id'];
				
				$arrProductIDsAndAsetIDs[] = array
					(
						'id' => $product['id'], 
						'aset_id' => $this->Input->get('asetid'), 
						'name' => $product['name'],
						'alias' => $product['alias']
					);		
				
				if(sizeof($product['options'])>0)
				{
					$this->hasOptions = true;
				}else{
					$this->hasOptions = false;
				}
				
				$product['aset_id'] = $this->Input->get('asetid');
				
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
						'name'			=> $row['name'],
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
					
//			if(is_array($arrCustomButtonData))
//			{
//				$arrBaseButtonData = array_merge($arrBaseButtonData, $arrCustomBaseButtonData);
//			}
			
			$this->Template->action = ampersand($this->Environment->request, true);
			$this->Template->formId = $this->strFormId;
			$this->Template->method = 'post';
			$this->Template->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
			$this->Template->attributes = '';
			$this->Template->maxFileSize = $this->getMaxFileSize();
			$this->Template->hidden = '';
			$this->Template->tableless = true; //FIXME: make dynamic
							
			$arrButtons = $this->generateButtons($arrButtonData, $objPage->id);
			$this->Template->useQuantity = $this->iso_use_quantity;
			$this->Template->qtyLabel = $GLOBALS['TL_LANG']['MSC']['quantity'];
			$this->Template->qtyLabelModifier = ''; //TODO: build to utilize the given products' format string.
			$this->Template->productId = $this->intProductId;			
			$this->Template->buttonTypes = $arrButtonTypes;
			$this->Template->buttons = $arrButtons;
			$this->Template->optionFields = $arrOptionFields ? join(',', $arrOptionFields) : NULL;
			
			//END BUTTON CODE//
			//------------------------------------------------------------------------------------//
			
			$this->Template->hasOptions = $this->hasOptions;					
			$this->Template->productDescriptionLabel = $GLOBALS['TL_LANG']['MSC']['productDescriptionLabel'];
			$this->Template->productDetailLabel = $GLOBALS['TL_LANG']['MSC']['productDetailLabel'];	
			$this->Template->productMediaLabel = $GLOBALS['TL_LANG']['MSC']['productMediaLabel'];
			$this->Template->productOptionsLabel = $GLOBALS['TL_LANG']['MSC']['productOptionsLabel'];
			$this->Template->messages = $this->getProductMessages($product['id']);	
			$this->Template->productCollection = $arrProducts;
			
		}
		
			
//			$objProductAttributeData = $this->Database->prepare("SELECT is_embeddable_media FROM tl_product_attributes WHERE pid=? AND name=?")
//													  ->limit(1)
//													  ->execute($arrProductData['pid'], $arrProductData);
			
	*/		
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
/*
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
							}
							else
							{
								$hasLargeImage = false;
								$largeImageLink = false;
							}
							
														
							$arrImageSize = getimagesize($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $currentImageAssetFolder . '/' . $file);
							
							
							$arrImages[$extension[0]] = array
							(
								'file_path' 			=> $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $file,
								'width'					=> $arrImageSize[0],
								'height'				=> $arrImageSize[1],
								'alt'					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['name'], $arrProductData['teaser'])),
								'has_large_image'		=> $hasLargeImage,
								'large_image_link'		=> $largeImageLink,
								'on_thumbnail_click_event' => $GLOBALS['TL_LANG']['MSC']['thumbnailImageClickEvent'],
								'is_main_image' 		=> (strcmp($file, $strMainImageFilename)==0 ? true : false)
							);
							$i++;
							
						}
						else
						{ 
							if(file_exists($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file))
							{
								$hasLargeImage = true;
								$largeImageLink = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file; 
							}
							else
							{
								$hasLargeImage = false;
								$largeImageLink = false;
							}
							
							$arrImageSize = @getimagesize($strAbsoluteAssetFolderPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $file);	
															
							$arrImages[$extension[0]] = array
							(
								'file_path'				=> $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'] . '/' . $file,
								'width'					=> $arrImageSize[0],
								'height'				=> $arrImageSize[1],
								'alt' 					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['name'], $arrProductData['teaser'])),
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
					
					if(!strlen($file) || !file_exists(TL_ROOT . '/' . $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $file))
					{
						$file = $this->Isotope->Store->missing_image_placeholder;
						$strFinalFilePath = $file;
					}
					else
					{
						$strFinalFilePath = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/' . $file;
					}
					
					$extension = explode('.', $file);
					
					if(file_exists(TL_ROOT . '/' . $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file))
					{
						$hasLargeImage = true;
						$largeImageLink = $strRelativeAssetPath . '/' . $strAssetType . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'] . '/' . $file; 
					}
					else
					{
						$hasLargeImage = false;
						$largeImageLink = false;
					}
							
					$arrImageSize = getimagesize(TL_ROOT . '/' . $strFinalFilePath);
					
						
					$arrImages[$extension[0]] = array
					(
						'file_path' 			=> $strFinalFilePath,
						'width'					=> $arrImageSize[0],
						'height'				=> $arrImageSize[1],
						'alt'					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['name'], $arrProductData['teaser'])),
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
	
*/

	
	
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
								'alt'					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['name'], $arrProductData['teaser'])),
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
								'alt' 					=> strip_tags(sprintf($GLOBALS['TL_LANG']['MSC']['altTextFormat'], $arrProductData['name'], $arrProductData['teaser'])),
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

