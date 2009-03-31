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
 * Class MediaManagement 
 *
 * Parent class for Isotope Media Assets Handling
 * @copyright  Winans Creative/Fred Bliss 2008 
 * @author     Fred Bliss 
 * @package    Controller
 */

class MediaManagement extends Backend
{
	/**
	 * 
	*/
	protected $strBasePath;
	
	/**
	 *
	 */
	protected $strIsotopeRoot;
	
	/**
	 *
	 */
	protected $strCurrentProductBasePath;
		
	/**
	 * Import the back end user object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('Files');
	}
	/** 
	 * Create the media storage base folder structure
	 * NOTE: $GLOBALS['TL_CONFIG']['isotope_root'] is not used, which is document root.  Is is $GLOBALS['TL_CONFIG']['isotope_root'] now.
	 *
	 */
	public function createMediaDirectoryStructure(DataContainer $dc)
	{
		
		$this->strBasePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'];
						
		foreach(range(0, 9) as $number)
		{
			if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . $number))
			{	
				new Folder($this->strBasePath . '/' . $number);
			}
		}
		
		foreach(range('a','z') as $letter)
		{					
			if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . $letter))
			{
				new Folder($this->strBasePath . '/' . $letter);
			}
		}
		
		$strSourceBasePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'];
		
	}
	
	
	/**
	 * Check for and create required product asset folders.  These folders are organized as such
	 * isotope/product_assets/<alphanumeric classifier>/<product_alias>/audio
	 * isotope/product_assets/<alphanumeric classifier>/<product_alias>/images
	 * 		isotope/product_assets/<alphanumeric classifier>/<product_alias>/gallery_thumbnail_images/
	 * 		isotope/product_assets/<alphanumeric classifier>/<product_alias>/thumbnail_images/
	 * 		isotope/product_assets/<alphanumeric classifier>/<product_alias>/medium_images/
	 * 		isotope/product_assets/<alphanumeric classifier>/<product_alias>/large_images/
	 * isotope/product_assets/<alphanumeric classifier>/<product_alias>/video
	 *
	 * @param variant
	 * @param object
	 * @return variant
	 */
	 
	public function createProductAssetFolders($varValue, DataContainer $dc, $strMode="")
	{	
		if($dc->field!='product_alias' && $strMode!="import")
		{
			
			return $varValue;
		}
					
		$this->strBasePath = $GLOBALS['TL_CONFIG']['isotope_base_path'];	//product_assets, for example
		
		
		//$this->import('Files');
		
		$char = substr($varValue, 0, 1);		
		
		$this->strBasePath .= '/' . $char;
		
		// create the folder with the first letter of the alias
		$this->strIsotopeRoot = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $this->strBasePath;		
		
		if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . $this->strBasePath))
		{
			new Folder($this->strIsotopeRoot);
		}
		
		$this->strCurrentProductBasePath = $this->strBasePath . '/'. $varValue;		
		
		//Create the base product folder
		if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . $this->strBasePath . '/' . $varValue))
		{
			new Folder($this->strIsotopeRoot . '/' . $varValue);
		}
		
		$this->createProductAssetSubfolders($this->strCurrentProductBasePath);
		
		return $varValue;
		
	}
	
	public function createProductAssetSubfolders($strProductBasePath)
	{
		// WE ARE NOW CREATING SUBFOLDERS INSIDE product_assets/a/alias/images/thumbnail-images
		$strProductFullFilePath = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strProductBasePath;
		$strProductFullBasePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strProductBasePath;
		
		//Create the audio folder
		if(!is_dir($strProductFullFilePath . '/' . $GLOBALS['TL_LANG']['MSC']['audioFolder']))
		{
			new Folder($strProductFullBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['audioFolder']);
		}
				
		//Create the gallery thumbnail images folder
		if(!is_dir($strProductFullFilePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder']))
		{
			new Folder($strProductFullBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder']);
		}
		
		//Create the thumbnail images folder (listing module std. size)
		if(!is_dir($strProductFullFilePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['thumbnail_images_folder']))
		{
			new Folder($strProductFullBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['thumbnail_images_folder']);
		}
		
		//Create the medium images folder
		if(!is_dir($strProductFullFilePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder']))
		{
			new Folder($strProductFullBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder']);
		}
		
		//Create the large images folder
		if(!is_dir($strProductFullFilePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder']))
		{
			new Folder($strProductFullBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder']);
		}
		
		
		if(!is_dir($strProductFullFilePath . '/' . $GLOBALS['TL_LANG']['MSC']['videoFolder']))
		{
			new Folder($strProductFullBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['videoFolder']);
		}
		
	
	}
			
	/**
	 * Thumbnail the current image - takes the selected image from the media attribute and creates the corresponding listing thumbnail
	 * @param variant
	 * @param object
	 * @return void;
	 */
	public function thumbnailCurrentImageForListing($varValue, DataContainer $dc)
	{		

		$objProduct = $this->Database->prepare("SELECT product_alias, product_sku FROM " . $dc->table . " WHERE id=?")
									 ->limit(1)
									 ->execute($dc->id);
		
		if($objProduct->numRows < 1)
		{
			return $varValue;
		}else{
			$strAlias = $objProduct->product_alias;
			$strSKU = $objProduct->product_sku;
		}

		$arrProductPaths = $this->getCurrentProductPaths($strAlias);		
		
		$strRootAssetsImportPath = $this->getRootAssetImportPath($dc->table, $dc->id);
		
		$arrProductPaths['root_asset_import_path'] = $strRootAssetsImportPath;
						
		//$arrImageSize = @getimagesize($arrProductPaths['file_source_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $varValue);
		
		$arrImageSizeConstraints = $this->getImageSizeConstraints($dc->table, $dc->id);
			
		$arrImages[] = $varValue;
		$arrImageSizeTypes[] = 'thumbnail';
		
				
		//Also set option true or false to override with first avail. image!
		
				
		$this->processImages($arrImages, $arrImageSizeConstraints, $arrProductPaths, $arrImageSizeTypes, true, true);		

		return $varValue;
	}
	
	/**
	 * Check and resize an one or more images to fit specified constraints as defined in the store configuration
	 * @param string
	 * @param array
	 * @param array
	 * @param array
	 * @param string
	 * @return void
	 */
	public function processImages($arrImages, $arrImageSizeConstraints, $arrProductPaths, $arrImageSizeTypes, $blnForceRescale = false, $blnSourceFallback = false, $blnOrdinalFallback = false)
	{	
		
		if($arrImageSizeTypes[0] == 'all')
		{
			//reset the array and use all size types.  Move size types to language file.
			$arrImageTypes = array();
			
			$arrImageTypes[] = 'gallery_thumbnail' . '_images';
			$arrImageTypes[] = 'thumbnail' . '_images';
			$arrImageTypes[] = 'medium' . '_images';
			$arrImageTypes[] = 'large' . '_images';
		}else{
			$arrImageTypes = array();
			
			foreach($arrImageSizeTypes as $sizeType)
			{
				$arrImageTypes[] = $sizeType . '_images';			
			}
		}
		
						
		$arrKeys = array_values($arrImageTypes);
				
		foreach($arrImages as $image)
		{
			//reset the constraints array
			
			
			//loop through each image type key
			foreach($arrKeys as $key)
			{				
				$arrConstraints = array();
				
				//Get the current image type constraint.
				$arrConstraints[$key]['width'] = (int)$arrImageSizeConstraints[$key . '_width'];
				$arrConstraints[$key]['height'] = (int)$arrImageSizeConstraints[$key . '_height'];
			
				if($blnSourceFallback)
				{			
					//look in the import holding tank instead	
					$oldRelativeFilePath = $arrProductPaths['root_asset_import_path'] . '/' . $image;
					$isAlternateRootPath = true;
				}else{
					$oldRelativeFilePath = $arrProductPaths['relative_source_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $image;
				}
				
				$newRelativeFilePath = $arrProductPaths['relative_destination_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $key . '/' . $image;
																
				//Alternate path, attempt to get it from here instead.
				if(!file_exists($oldRelativeFilePath))
				{
					
					//Attempt to get from the import holding tank folder
					$oldRelativeFilePath = $arrProductPaths['root_assets_import_path'] . '/' . $image;
					$isAlternateRootPath = true;
				}
				
				if(!$isAlternateRootPath)
				{
					$arrImageSize = @getimagesize($arrProductPaths['file_source_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $image);
				}else{
					
					$arrImageSize = @getimagesize(TL_ROOT . '/' . $arrProductPaths['root_asset_import_path'] . '/' . $image);
				}
			
				$newFullFilePath = $arrProductPaths['file_destination_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $key . '/' . $image;
				
							
				//Copy the file to the destination directory (original unresized version)	
				if(!file_exists($newFullFilePath) || $blnForceRescale)
				{
					
					$this->Files->copy($oldRelativeFilePath, $newRelativeFilePath);
					$this->Files->chmod($newRelativeFilePath, 0777);
				}
							
				foreach($arrConstraints as $limit)
				{
					//If both limits exceed the dimensions of the file in question, then we don't need to do any resizes.
					if($limit['width'] > $arrImageSize[0] && $limit['height'] > $arrImageSize[1])
					{		
						continue;
					}
										
					if($arrImageSize[0] > $arrImageSize[1])
					{
						//Resize by width then height
						$this->resizeProductImage($newRelativeFilePath, $limit['width'], 0);
				
						$arrImageSizeRecheck = @getimagesize($newFullFilePath);
						
						//Check height, if still too large then resize by height					
						if($limit['height'] < $arrImageSizeRecheck[1])
						{
							$this->resizeProductImage($newRelativeFilePath, 0, $limit['height']);
						}
			
					}else{
					
						//Resize by height then width
						if($limit['height'] < $arrImageSize[1])
						{
							$this->resizeProductImage($newRelativeFilePath, 0, $limit['height']);
					
							$arrImageSizeRecheck = @getimagesize($newFullFilePath);
							
						}
						
						//Check width, if still too large then resize by width
						if($limit['width'] < $arrImageSizeRecheck[0])
						{
							$this->resizeProductImage($newRelativeFilePath, $limit['width'], 0);
						}
			
					}//resize conditional block
					
				} //arrConstraints loop
				
			}	//arrKeys loop		
			
		} //arrImages loop
	}
	
	
	/**
	 * Get root asset import path from the store configuration record
	 * @param string
	 * @param integer
	 * @param string
	 * @return string
	 */
	public function getRootAssetImportPath($strTable, $intID, $strAlias = '')
	{
	
		$intPID = $this->getPID($strTable, $intID, $strAlias);
		
		if($intPID!=0)
		{
			$objStoreSettingsID = $this->Database->prepare("SELECT store_id FROM tl_product_attribute_sets WHERE id=?")
												 ->limit(1)
												 ->execute($intPID);
			
			if($objStoreSettingsID->numRows < 1)
			{
				return '';
			}
			
			$objStoreSettings = $this->Database->prepare("SELECT root_asset_import_path FROM tl_store WHERE id=?")
											   ->limit(1)
											   ->execute($objStoreSettingsID->store_id);
			if($objStoreSettings->numRows < 1)
			{
				return '';
			}
			
			return $objStoreSettings->root_asset_import_path;
		}
		
		return '';
		
	
	}
	
	
	public function getImageSizeConstraints($strTable, $intID, $strAlias)
	{
	
		$intPID = $this->getPID($strTable, $intID, $strAlias);
		
		if($intPID!=0)
		{
			$objStoreSettingsID = $this->Database->prepare("SELECT store_id FROM tl_product_attribute_sets WHERE id=?")
												 ->limit(1)
												 ->execute($intPID);
			
			if($objStoreSettingsID->numRows < 1)
			{
				return array();
			}
			
			$objStoreSettings = $this->Database->prepare("SELECT gallery_thumbnail_image_width, gallery_thumbnail_image_height, thumbnail_image_width,thumbnail_image_height,medium_image_width,medium_image_height,large_image_width,large_image_height FROM tl_store WHERE id=?")
											   ->limit(1)
											   ->execute($objStoreSettingsID->store_id);
			if($objStoreSettings->numRows < 1)
			{
				return array();
			}
			
			$arrConstraints = array 
			(
				'gallery_thumbnail_images_width'	=> $objStoreSettings->gallery_thumbnail_image_width, 
				'gallery_thumbnail_images_height'	=> $objStoreSettings->gallery_thumbnail_image_height, 				
				'thumbnail_images_width' 			=> $objStoreSettings->thumbnail_image_width,
				'thumbnail_images_height'			=> $objStoreSettings->thumbnail_image_height,
				'medium_images_width'				=> $objStoreSettings->medium_image_width,
				'medium_images_height'				=> $objStoreSettings->medium_image_height,
				'large_images_width'				=> $objStoreSettings->large_image_width,
				'large_images_height'				=> $objStoreSettings->large_image_height
			);
			
			return $arrConstraints;
		}
		
		return array();
	}
	
	/*
	private function checkImage($varValue, DataContainer $dc, $imageSizeType, $isFirstImage, $id=0, $strProductAlias='')
	{	
	
		if($id!=0)
		{
			$intID = $id;
			$strMode = 'import';
		}else{
			$intID = $dc->id;
			$strMode = '';
		}
		
		//$this->import('Files');
				
		// Resize image if necessary
		if (($arrImageSize = @getimagesize($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $varValue)) !== false)
		{
	
			$objPID = $this->Database->prepare("SELECT pid, product_alias FROM " . $dc->table . " WHERE id=?")
											 ->limit(1)
											 ->execute($intID);
			
			if($objPID->numRows < 1)
			{
				return;
			}
			
			if(strlen($objPID->product_alias) > 1)
			{
				$strProductAlias = $objPID->product_alias;
			}
						
			$objStoreSettingsID = $this->Database->prepare("SELECT store_id FROM tl_product_attribute_sets WHERE id=?")
												 ->limit(1)
												 ->execute($objPID->pid);
			//echo $objStoreSettingsID->numRows;
			
			if($objStoreSettingsID->numRows < 1)
			{
				return;
			}

			$this->createProductAssetFolders($strProductAlias, $dc, $strMode);

			$objStoreSettings = $this->Database->prepare("SELECT gallery_thumbnail_image_width, gallery_thumbnail_image_height, thumbnail_image_width,thumbnail_image_height,medium_image_width,medium_image_height,large_image_width,large_image_height FROM tl_store WHERE id=?")
											   ->limit(1)
											   ->execute($objStoreSettingsID->store_id);
			
			//echo $objStoreSettings->numRows;
			
			if($objStoreSettings->numRows < 1)
			{
				return;
			}
			
			$arrImageSettings = $objStoreSettings->fetchAssoc();			
									
			$arrFilePathComponents = explode("/", $varValue);
			
			$i = sizeof($arrFilePathComponents);
						
			$fileName = $arrFilePathComponents[$i-1];
			
			$arrNewImagePath['gallery_thumbnail_image'] = $this->strCurrentProductBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['gallery_thumbnail_images_folder'];			
			$arrNewImagePath['thumbnail_image'] = $this->strCurrentProductBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['thumbnail_images_folder'];
			$arrNewImagePath['medium_image'] = $this->strCurrentProductBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'];
			$arrNewImagePath['large_image'] = $this->strCurrentProductBasePath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'];
			
			$arrImageDimensionLimits['thumbnail_image']['width'] = $arrImageSettings['thumbnail_image_width'];
			$arrImageDimensionLimits['thumbnail_image']['height'] = $arrImageSettings['thumbnail_image_height'];
			$arrImageDimensionLimits['medium_image']['width'] = $arrImageSettings['medium_image_width'];
			$arrImageDimensionLimits['medium_image']['height'] = $arrImageSettings['medium_image_height'];
			$arrImageDimensionLimits['large_image']['width'] = $arrImageSettings['large_image_width'];			
			$arrImageDimensionLimits['large_image']['height'] = $arrImageSettings['large_image_height'];
			
			$arrImageDimensionLimits['gallery_thumbnail_image']['width'] = $arrImageSettings['gallery_thumbnail_image_width'];
			$arrImageDimensionLimits['gallery_thumbnail_image']['height'] = $arrImageSettings['gallery_thumbnail_image_height'];
	
			//Copy a version of the original file to each 
			foreach($arrNewImagePath as $path)
			{
				$this->Files->chmod($path, 0755);
				  
				if(!file_exists($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $path . $fileName))
				{
					$this->Files->copy($varValue, $path . $fileName);
				}
			}
				
			//Check based on what size & type if the dimensions are correct.
			
			$arrKeys = array_keys($arrImageDimensionLimits);
			
			foreach($arrKeys as $key)
			{
				foreach($arrImageDimensionLimits as $limit)
				{	
					$fullFilePath = $arrNewImagePath[$key] . $fileName;
					
					//If both limits exceed the dimensions of the file in question, then we don't need to do any resizes.
					if(($limit['width'] > $arrImageSize[0]) && ($limit['height'] > $arrImageSize[1]))
					{
						$this->Files->copy($fullFilePath);
						continue;
					}
				
					//Get the full file path for this particular entry.
				
				
						
					if($arrImageSize[0] > $arrImageSize[1])
					{
						$this->resizeProductImage($fullFilePath, $limit['width'], 0);
				
						$arrImageSizeRecheck = @getimagesize($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $fullFilePath);
					
						$this->Files->chmod($fullFilePath, 0755);
					
						if($limit['height'] < $arrImageSizeRecheck[1])
						{
							$this->resizeProductImage($fullFilePath, 0, $limit['height']);
						}
				
					}else{
				
						if($limit['height'] < $arrImageSize[1])
						{
							$this->resizeProductImage($fullFilePath, 0, $limit['height']);
						
							$arrImageSizeRecheck = @getimagesize($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $fullFilePath);
						
						}
					
						$this->Files->chmod($fullFilePath, 0755);
					
						if($limit['width'] < $arrImageSizeRecheck[0])
						{
							$this->resizeProductImage($fullFilePath, $limit['width'], 0);
						}
				
					}
				}
			}
			
			
			if($isFirstImage)
			{
					//Drop in the thumbnail image
				$this->Database->prepare("UPDATE " . $dc->table . " SET product_thumbnail_image=? WHERE id=?")
							   ->execute($arrNewImagePath['thumbnail_image'] . $fileName, $intID);
			}		
			
			
		}
	
	}*/
		
	/**
	 * Resize or crop an image
	 * @param string
	 * @param integer
	 * @param integer
	 * @param string
	 * @return boolean
	 */
	public function resizeProductImage($image, $width, $height)
	{
		return $this->getProductImage($image, $width, $height, $image) ? true : false;
	}


	/**
	 * Resize an image
	 * @param string
	 * @param integer
	 * @param integer
	 * @param string
	 * @return string
	 */
	public function getProductImage($image, $width, $height, $target=null)
	{
		
		if (!strlen($image))
		{
			return null;
		}

		$image = urldecode($image);

		// Check whether file exists
		if (!file_exists(TL_ROOT . '/' . $image))
		{			
			$this->log('Image "' . $image . '" could not be found', 'Controller getImage()', TL_ERROR);
			return null;
		}
				
		$objFile = new File($image);
		
		
		$arrAllowedTypes = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['validImageTypes']));
						
		// Check file type
		if (!in_array($objFile->extension, $arrAllowedTypes))
		{
			$this->log('Image type "' . $objFile->extension . '" was not allowed to be processed', 'Controller getImage()', TL_ERROR);
			return null;
		}

		$strCacheName = 'system/html/' . md5('w' . $width . 'h' . $height . $image) . '.' . $objFile->extension;
			
		// Resize original image
		if ($target)
		{
			$strCacheName = $target;
		}

		// Return the path of the new image if it exists already
		elseif (file_exists(TL_ROOT . '/' . $strCacheName))
		{
			return $strCacheName;
		}
		
		// Return the path to the original image if GDlib cannot handle it
		if (!extension_loaded('gd') || !$objFile->isGdImage || (!$width && !$height) || $width > 2000 || $height > 2000)
		{	
			return $image;
		}
				
		$intPositionX = 0;
		$intPositionY = 0;
		$intWidth = $width;
		$intHeight = $height;

		// Resize width and height and crop the image if necessary
		if ($intWidth && $intHeight)
		{
			if (($intWidth * $objFile->height) != ($intHeight * $objFile->width))
			{
				$intWidth = ceil($objFile->width * $height / $objFile->height);
				$intPositionX = -intval(($intWidth - $width) / 2);

				if ($intWidth < $width)
				{
					$intWidth = $width;
					$intHeight = ceil($objFile->height * $width / $objFile->width);
					$intPositionX = 0;
					$intPositionY = -intval(($intHeight - $height) / 2);
				}
			}

			$strNewImage = imagecreatetruecolor($width, $height);
		}

		// Calculate height if only width is given
		elseif ($intWidth)
		{
			$intHeight = ceil($objFile->height * $width / $objFile->width);
			$strNewImage = imagecreatetruecolor($intWidth, $intHeight);
		}

		
		// Calculate width if only height is given
		elseif ($intHeight)
		{
			$intWidth = ceil($objFile->width * $height / $objFile->height);
			$strNewImage = imagecreatetruecolor($intWidth, $intHeight);
		}
		
		$arrGdinfo = gd_info();
		$strGdVersion = ereg_replace('[[:alpha:][:space:]()]+', '', $arrGdinfo['GD Version']);

		switch ($objFile->extension)
		{
			case 'gif':
				if ($arrGdinfo['GIF Read Support'])
				{
					$strSourceImage = imagecreatefromgif(TL_ROOT . '/' . $image);

					// Handle transparency
					$strBuffer = substr($objFile->getContent(), 0, 13);
					$intColorFlag = ord(substr($strBuffer, 10, 1)) >> 7;
					$intBackground = ord(substr($strBuffer, 11));

					if ($intColorFlag)
					{
						$strBuffer = substr($objFile->getContent(), 13, (($intBackground + 1) * 3));
						imagecolortransparent($strSourceImage, imagecolorallocate($strSourceImage, ord(substr($strBuffer, $intBackground * 3, 1)), ord(substr($strBuffer, $intBackground * 3 + 1, 1)), ord(substr($strBuffer, $intBackground * 3 + 2, 1))));
					}
				}
				break;

			case 'jpg':
			case 'jpeg':
				if ($arrGdinfo['JPG Support'])
				{
					$strSourceImage = imagecreatefromjpeg(TL_ROOT . '/' . $image);
				}
				break;

			case 'png':
				if ($arrGdinfo['PNG Support'])
				{
					$strSourceImage = imagecreatefrompng(TL_ROOT . '/' . $image);

					// Handle transparency (GDlib > 2.0.1 required)
					if (version_compare($strGdVersion, '2.0.1', '>='))
					{
						imageantialias($strNewImage, true);
						imagealphablending($strNewImage, false);
						imagesavealpha($strNewImage, true);
						imagefilledrectangle($strNewImage, $intPositionX, $intPositionY, $intWidth, $intHeight, imagecolorallocatealpha($strNewImage, 255, 255, 255, 127));
					}
				}
				break;
		}

		imagecopyresampled($strNewImage, $strSourceImage, $intPositionX, $intPositionY, 0, 0, $intWidth, $intHeight, $objFile->width, $objFile->height);

		// Fallback to PNG if GIF ist not supported
		if ($objFile->extension == 'gif' && !$arrGdinfo['GIF Create Support'])
		{
			$objFile->extension = 'png';
		}

		// Create new image
		switch ($objFile->extension)
		{
			case 'gif':
				imagegif($strNewImage, TL_ROOT . '/' . $strCacheName);
				break;

			case 'jpg':
			case 'jpeg':
	
				$this->Files->chmod($strCacheName, 0777);

				imagejpeg($strNewImage, TL_ROOT . '/' . $strCacheName, (!$GLOBALS['TL_CONFIG']['jpgQuality'] ? 80 : $GLOBALS['TL_CONFIG']['jpgQuality']));
				break;

			case 'png':
				imagepng($strNewImage, TL_ROOT. '/' . $strCacheName);
				break;
		}

		// Destroy temporary images
		imagedestroy($strSourceImage);
		imagedestroy($strNewImage);
				
		// Return path to new image
		return $strCacheName;
	}
	

	
	public function generateMediaPlayerRSSPlaylist(DataContainer $dc)
	{
		
		$objTemplate = new FrontendTemplate($GLOBALS['ISO_PLUGINS']['jwMediaPlayer']['mediaRSSPlaylist']);
	
		$objProductData = $this->Database->prepare("SELECT product_name, product_alias, product_teaser, product_thumbnail_image, audio_jumpTo, audio_url, video_jumpTo, video_url FROM " . $dc->table . " WHERE id=?")
										 ->limit(1)
										 ->execute($dc->id);
				
		if($objProductData->numRows < 1)
		{
			return;		
		}
	
		
		$arrProductData = $objProductData->fetchAssoc();
		
		$arrProductPaths = $this->getCurrentProductPaths($arrProductData['product_alias']);

		$arrMediaFields = array('audio_jumpTo','audio_url','video_jumpTo','video_url');
		
		foreach($arrMediaFields as $field)
		{
			$strFieldData = trim($arrProductData[$field]);
			
			if(strlen($strFieldData) > 0)
			{
				if($field=='audio_jumpTo' || $field=='video_jumpTo')
				{
					
				
				}
				
				$fileType = explode('.', $strFieldData);
				
				if(is_array($fileType) && $fileType[1]=='mp3')
				{
					$isAudio = true;
				}else{
					$isAudio = false;
				}
				
				$mediaType = $this->getMediaFileType($strFieldData);
								
				$arrFiles[] = array
				(
					'title' 			=> $arrProductData['product_name'],
					'description' 		=> sprintf($GLOBALS['TL_LANG']['MSC']['playlistDescriptionTemplate'], $arrProductData['product_name'], $arrProductData['product_teaser']),
					'path'				=> $this->Environment->base . $strFieldData,
					'type'				=> $mediaType,
					'duration'			=> 33,
					'is_audio'			=> $isAudio/*,
					'thumbnail_image'	=> $this->Environment->base . $arrProductData['product_images']*/
				);
			}
				
		}
			
		
		$objTemplate->playlistTitle = $arrProductData['product_name'];
		$objTemplate->baseURL = $this->Environment->base;
		$objTemplate->files = $arrFiles;

		$strFileContents = $objTemplate->parse();

		$this->Files->chmod($arrProductPaths['relative_destination_path'], 0755);
				
		$objFile = new File($arrProductPaths['relative_destination_path'] . '/' . 'mrss.xml');
		$objFile->write($strFileContents . "\n\n");
		$objFile->close();
	}
	
	
	/**
	 * Return the file path and the relative path for the current product
	 * E.G. file path: home/myhostingaccount/myclientfolder/public_html/isotope/assets_for_import/t/test-product/
	 * E.G. relative path: isotope/product_assets/t/test-product/
	 * @param string
	 * @param string
	 * @return array
	 */
	public function getCurrentProductPaths($varValue)
	{	
						
		$char = substr($varValue, 0, 1);		
		
		$arrPaths = array
		(			
			'file_source_path' => $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $char . '/' . $varValue,
			'relative_source_path' => $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $char . '/' . $varValue,
			'file_destination_path' => $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . $char . '/' . $varValue,
			'relative_destination_path' => $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . $char . '/' . $varValue
		);

		return $arrPaths;
	
	}
	
	
	public function getMediaFilenames($strAbsoluteFilePath, $strMediaType, $strSourcePoint)
	{
		
		switch($strSourcePoint)
		{
			case 'source':
				if(count(scan($strAbsoluteFilePath . '/' . $strMediaType . '/')))
				{
					if ($dh = opendir($strAbsoluteFilePath . '/' . $strMediaType . '/')) 
					{
						while ($file = readdir($dh))
						{
							$fileExt = explode('.', $file);
					
							if(in_array($fileExt[1], $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strMediaType]))
							{
								$arrImages[] = $file;					
							}
						}
					}
		
				}else{
					$arrImages = array();
				}
				break;
			
			case 'destination':
			
				if(count(scan($strAbsoluteFilePath . '/' . $strMediaType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/')))
				{
					if ($dh = opendir($strAbsoluteFilePath . '/' . $strMediaType . '/' . $GLOBALS['TL_LANG']['MSC']['medium_images_folder'] . '/')) 
					{
						while ($file = readdir($dh))
						{
							$fileExt = explode('.', $file);
					
							if(in_array($fileExt[1], $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strMediaType]))
							{
								$arrImages[] = $file;					
							}
						}
					}
		
				}else{
					$arrImages = array();
				}
				
				break;
			
			default:
				break;
		}
	
		return $arrImages;
	}
	
	/***** DEPRECATED *****
	private function getCurrentProductFilePath($varValue)
	{
		$strFilePath = $GLOBALS['TL_CONFIG']['isotope_root'];
		
		$char = substr($varValue, 0, 1);
		
		$strFilepath .= '/' . $char;
		
		$strProductFilePath = $strFilePath . '/' . $varValue;
		
		return $strProductFilePath;
		
	}*/
	
	/**
	 * Return the appropriate media file type for the Media RSS xml file
	 * @param string
	 * @return string
	 */
	private function getMediaFileType($strFile)
	{
		
		$fileType = (explode('.', $strFile));

		
		if(is_array($fileType) && $fileType[1]=="mp3")
		{
			$strFileType = $GLOBALS['TL_LANG']['MSC'][$fileType[1]];
		}
		
		
		
		if(is_array($fileType) && $fileType[1]=="flv")
		{
			$strFileType = $GLOBALS['TL_LANG']['MSC'][$fileType[1]];
		}
	
		return $strFileType;
	}
	
	/**
	 * Wrapper function to product an error in the backend.
	 * @param string
	 * @param string
	 * @return void
	 */
	private function generateError($strErrorMessage, $strMethod)
	{
		$this->log($strErrorMessage, 'MediaManagement ' . $strMethod, TL_ERROR);
		$this->redirect('typolight/main.php?act=error');
	}	
	
	
	/**
	 * Get the PID of a given record 
	 * @param string
	 * @param integer
	 * @return integer;
	 */
	private function getPID($strTableName, $intID, $strAlias = '')
	{
		$objPID = $this->Database->prepare("SELECT pid FROM " . $strTableName . " WHERE id=? OR product_alias=?")
								 ->limit(1)
								 ->execute($intID, $strAlias);
			
		if($objPID->numRows < 1)
		{
			return 0;
		}
		
		return $objPID->pid;
	
	}

	/**
	 * Fallback to first image (by ordinals) in the directory, if there is one.
	 * @param string
	 * @return string
	 */
	public function getFirstOrdinalImage($strBasePath, $strProductAlias)
	{
		$strFilePath = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . sprintf($strBasePath, substr($strProductAlias, 0, 1), $strProductAlias);
		
		if ($dh = opendir($strFilePath)) 
		{
				while (($file = readdir($dh)) !== false)
				{
					$extension = explode('.', $file);
					
					if(in_array($extension[1], $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes']['images']))
					{
						return $file;
					}
				}
		}
		
		return '';
	}
	
	/**
	 * Search for product assets in a given folder path using one or more related keys and searching for folders or files associated. Returns array of paths.
	 * @param array
	 * @param string
	 * @param string
	 * @return array 
	 */
	public function getRelatedProductAssetFilenamesByType($arrAssetKeys, $strFilePath, $strAssetType)
	{
	
		if(!array_key_exists($strAssetType, $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes']))
		{
			return '';
		}
		
		$arrFiles = array();
				
		//Using glob because we're pretty confident people won't abuse the whole too many images for a product issue. ;D
		foreach($arrAssetKeys as $key)
		{
			if ($dh = opendir(TL_ROOT . '/' . $strFilePath . '/' . $key))
			{
				//Grab all files in this directory.
				foreach($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strAssetType] as $fileType)
				{
					$arrFiles = array_merge($arrFiles, glob(TL_ROOT . '/' . $strFilePath . '/' . $key . '/' . '*.' . $fileType));					
				}
			}else{
				foreach($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strAssetType] as $fileType)
				{
					$arrFilesToSearch = glob(TL_ROOT . '/' . $strFilePath . '/' . $key . "*." . $fileType); //filenames are key plus the * wildcard
			
					foreach($arrFilesToSearch as $fileName)
					{
						//search for the file names such as myfile_1.jpg, myfile-2.jpg, etc.
						if(preg_match("/(" . $key . "( )).*?(\\d+)(." . $fileType . ")/is", $fileName))
						{
							//Grab files that have the asset key value in the filename
							$arrFiles[] = $fileName;
						}					
					}
				}
			}		
		}
		
		return $arrFiles;
	}
	
}

?>