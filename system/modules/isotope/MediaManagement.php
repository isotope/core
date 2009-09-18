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
	
	protected $intStoreId;
	
	protected $strRootAssetImportPath;
	
	/**
	 *
	 */
	protected $strCurrentProductBasePath;
		
	
	public function __construct()
	{
		parent::__construct();
				
		$this->import('Files');
		$this->import('Isotope');
				
		$blnForceDefault = (TL_MODE=='BE' ? true : false);
				
		$this->intStoreId = $_SESSION['isotope']['store_id'];
			
		$this->strRootAssetImportPath = $this->getRootAssetImportPath($this->intStoreId);
	
		$this->import('IsotopeStore','Store');
		
		
	}
	
	
	/** 
	 * Create the media storage base folder structure
	 * NOTE: $GLOBALS['TL_CONFIG']['isotope_root'] is not used, which is document root.  Is is $GLOBALS['TL_CONFIG']['isotope_root'] now.
	 *
	 */
	public function createMediaDirectoryStructure(DataContainer $dc)
	{
		if(!is_dir(TL_ROOT . '/' . 'isotope'))
		{
			$this->createIsotopeRootPath();
		}
		
		if(!is_dir(TL_ROOT . '/' . 'isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath']))
		{
			$this->createIsotopeUploadPath();
		}
		
		
		if(!is_dir(TL_ROOT . '/' . 'isotope' . '/' . 'product_assets'))
		{
			$this->createProductAssetsBasePath();
		}
		
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
	
	
	/*protected function createIsotopeRootPath()
	{
		
			
		if(!isset($GLOBALS['TL_CONFIG']['isotope_root']))
		{
			$this->Config->add("\$GLOBALS['TL_CONFIG']['isotope_root']", TL_ROOT . '/isotope');
		}
				
		new Folder('isotope');
		
		return;		
	}*/

	protected function createProductAssetsBasePath()
	{
		if(!isset($GLOBALS['TL_CONFIG']['isotope_root']))
		{
			$this->Config->add("\$GLOBALS['TL_CONFIG']['isotope_root']", TL_ROOT . '/isotope');
		}
		
		//Default to standard folder name			
		$strFolderName = 'product_assets';
			
		//Default assets base path - all subfolders are directly related to products.				
		if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strFolderName))
		{
			new Folder('isotope' . '/' . $strFolderName);
		}
		
		return;
	}	
	
	protected function createIsotopeUploadPath()
	{
		//The Default Import Folder for Isotope
		if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath']))
		{
			new Folder('isotope' . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath']);
		}	
			
		if(!isset($GLOBALS['TL_CONFIG']['isotope_upload_path']))
		{
			$this->Config->add("\$GLOBALS['TL_CONFIG']['isotope_upload_path']", 'isotope');
		}

		return;
	}
	
	
	/**
	 * Check for and create required product asset folders.  These folders are organized as such
	 * isotope/product_assets/<alphanumeric classifier>/<alias>/audio
	 * isotope/product_assets/<alphanumeric classifier>/<alias>/images
	 * 		isotope/product_assets/<alphanumeric classifier>/<alias>/gallery_thumbnail_images/
	 * 		isotope/product_assets/<alphanumeric classifier>/<alias>/thumbnail_images/
	 * 		isotope/product_assets/<alphanumeric classifier>/<alias>/medium_images/
	 * 		isotope/product_assets/<alphanumeric classifier>/<alias>/large_images/
	 * isotope/product_assets/<alphanumeric classifier>/<alias>/video
	 *
	 * @param variant
	 * @param object
	 * @return variant
	 */
	 
	public function createProductAssetFolders($varValue, DataContainer $dc, $strMode="")
	{	
		
		if($dc->field!='alias' && $strMode!="import")
		{
			return $varValue;
		}
					
		$this->strBasePath = $GLOBALS['TL_CONFIG']['isotope_base_path'];	//product_assets, for example
		
		
		//$this->import('Files');
		
		$char = strtolower(substr($varValue, 0, 1));		
		
		$this->strBasePath .= '/' . $char;
		
		// create the folder with the first letter of the alias
		$this->strIsotopeRoot = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $this->strBasePath;		
		
		if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . $this->strBasePath))
		{
			new Folder($this->strIsotopeRoot);
		}
		
		$this->strCurrentProductBasePath = $this->strBasePath . '/'. strtolower($varValue);		
		
		//Create the base product folder
		if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . $this->strBasePath . '/' . strtolower($varValue)))
		{
			new Folder($this->strIsotopeRoot . '/' . strtolower($varValue));
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
	public function thumbnailImages($varValue, DataContainer $dc)
	{		
		$objProduct = $this->Database->prepare("SELECT alias, sku FROM tl_product_data WHERE id=?")
									 ->limit(1)
									 ->execute($dc->id);
		
		if($objProduct->numRows < 1)
		{
			return $varValue;
		}
		else
		{
			$strAlias = $objProduct->alias;
			$strSKU = $objProduct->sku;
		}

		$arrProductPaths = $this->getCurrentProductPaths($strAlias);		
		
		
		$arrProductPaths['root_asset_import_path'] = $this->strRootAssetImportPath;
						
		//$arrImageSize = @getimagesize($arrProductPaths['file_source_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $varValue);
		
		$arrImageSizeConstraints = $this->getImageSizeConstraints($dc->id);
			
		//$arrImages[] = $varValue;
		$arrImages = $this->getMediaFilenames($arrProductPaths['file_source_path'], 'images', 'source');
				
		$arrImageSizeTypes = array('all');
		
		//Also set option true or false to override with first avail. image!
		
				
		$this->processImages($arrImages, $arrImageSizeConstraints, $arrProductPaths, $arrImageSizeTypes, true);		

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
			
			$arrImageTypes[0] = 'large' . '_images';
			$arrImageTypes[1] = 'medium' . '_images';
			$arrImageTypes[2] = 'thumbnail' . '_images';
			$arrImageTypes[3] = 'gallery_thumbnail' . '_images';
		}
		else
		{
			$arrImageTypes = array();
			
			foreach($arrImageSizeTypes as $sizeType)
			{
				$arrImageTypes[] = $sizeType . '_images';			
			}
		}
							
		$arrKeys = array_values($arrImageTypes);

		foreach($arrImages as $image)
		{
			//loop through each image type key
			foreach($arrKeys as $key)
			{			
				
				//reset the constraints array	
				$arrConstraints = array();
				
				//Get the current image type constraint.
				$arrConstraints[$key]['width'] = (int)$arrImageSizeConstraints[$key . '_width'];
				$arrConstraints[$key]['height'] = (int)$arrImageSizeConstraints[$key . '_height'];
			
				if($blnSourceFallback)
				{			
					//first time only
					//look in the import holding tank instead	
					$oldRelativeFilePath = $arrProductPaths['root_asset_import_path'] . '/' . $image;
					$isAlternateRootPath = true;
				}
				else
				{
					$oldRelativeFilePath = $arrProductPaths['relative_source_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $image;
				}
				
				$oldFullFilePath = $arrProductPaths['file_source_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $image;
				
				$newRelativeFilePath = $arrProductPaths['relative_destination_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $key . '/' . $image;
													
				//Alternate path, attempt to get it from here instead.
				if(!file_exists($oldFullFilePath))
				{
					//Attempt to get from the import holding tank folder
					$oldRelativeFilePath = $arrProductPaths['root_asset_import_path'] . '/' . $image;
					$isAlternateRootPath = true;
				}
				
				//echo $oldRelativeFilePath . ' :: ' . $isAlternateRootPath . '<br /><br />';
							
				$newFullFilePath = $arrProductPaths['file_destination_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $key . '/' . $image;			
													
				//Copy the file to the destination directory (original unresized version)	
				if(!file_exists($newFullFilePath) || $blnForceRescale)
				{
					$this->Files->copy($oldRelativeFilePath, $newRelativeFilePath);
					$this->Files->chmod($newRelativeFilePath, 0777);
				}
								
				//first time only!
				if(!$isAlternateRootPath)
				{
					$arrImageSize = @getimagesize($arrProductPaths['file_source_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $image);
				}
				else
				{
					$arrImageSize = @getimagesize(TL_ROOT . '/' . $arrProductPaths['root_asset_import_path'] . '/' . $image);
				}
											
				foreach($arrConstraints as $limit)
				{
					//If both limits exceed the dimensions of the file in question, then we don't need to do any resizes.
/*
					if($limit['width'] > $arrImageSize[0] && $limit['height'] > $arrImageSize[1])
					{		
						continue;
					}
*/
					if ($limit['width'] > $arrImageSize[0])
					{
						$limit['width'] = $arrImageSize[0];
					}
					if ($limit['height'] > $arrImageSize[1])
					{
						$limit['height'] = $arrImageSize[1];
					}
							
					// IF WIDTH > HEIGHT					
					if($arrImageSize[0] > $arrImageSize[1])
					{
					
						$aspectRatio = $limit['width']/$arrImageSize[0];
						$newImgW = $limit['width'];
						$newImgH = $arrImageSize[1]*$aspectRatio;
					
						//Resize
						$this->resizeProductImage($newRelativeFilePath, $newImgW, $newImgH);
			
					}
					else
					{
					
						$aspectRatio = $limit['height']/$arrImageSize[1];
						$newImgH = $limit['height'];
						$newImgW = $arrImageSize[0]*$aspectRatio;
						
						//Resize
						$this->resizeProductImage($newRelativeFilePath, $newImgW, $newImgH);
						
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
	public function getRootAssetImportPath($intStoreId)
	{
	
		$objStoreSettings = $this->Database->prepare("SELECT root_asset_import_path FROM tl_store WHERE id=?")
		  					     ->limit(1)
								 ->execute($intStoreId);
											
		if($objStoreSettings->numRows < 1)
		{
			return '';
		}
		
		return $objStoreSettings->root_asset_import_path;
	
	}
	
	
	public function getFilesByName($strProductImages, $strRelativePath)
	{
		$arrFilenames = explode(',', $strProductImages);
		
		if(sizeof($arrFilenames))
		{
			foreach($arrFilenames as $file)
			{
				if(file_exists(TL_ROOT . '/' . $strRelativePath . '/' . $file))
				{
					$arrFiles[] = $file;
				
				}
			
			}
			
			if(sizeof($arrFiles))
			{
				return $arrFiles;
			}
		}
		
		return false;
	
	}	
	
	public function getImageSizeConstraints($strTable, $intID, $strAlias='')
	{
		
						
			$objStoreSettings = $this->Database->prepare("SELECT gallery_thumbnail_image_width, gallery_thumbnail_image_height, thumbnail_image_width,thumbnail_image_height,medium_image_width,medium_image_height,large_image_width,large_image_height FROM tl_store WHERE id=?")
											   ->limit(1)
											   ->execute($this->intStoreId);
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

		
		return array();
	}
	

		
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
		return $this->getProductImage($image, $width, $height, $image, true) ? true : false;
	}


	/**
	 * Resize an image
	 * @param string
	 * @param integer
	 * @param integer
	 * @param string
	 * @return string
	 */
	public function getProductImage($image, $width, $height, $target=null, $isResizingCommand=false)
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
		//$strCacheName = $image;
						
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
					
					/*$th_bg_color = imagecolorallocate($image, 255, 255, 255);

					imagefill($image, 0, 0, $th_bg_color);
					imagecolortransparent($image, $th_bg_color);*/
			
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

		//if($isResizingCommand)
		//{
			//imageantialias($strNewImage, true);
						
			//$this->imageCopyResampleBicubic($strNewImage, $strSourceImage, $intPositionX, $intPositionY, 0, 0, $intWidth, $intHeight, $objFile->width, $objFile->height);
			
			//imagecopyresized($strNewImage, $strSourceImage, $intPositionX, $intPositionY, 0, 0, $intWidth, $intHeight, $objFile->width, $objFile->height);
		//}else{
			//$this->applyUnsharpMask($strNewImage, 99, 1, 1);

			imagecopyresampled($strNewImage, $strSourceImage, $intPositionX, $intPositionY, 0, 0, $intWidth, $intHeight, $objFile->width, $objFile->height);

			//$this->applyUnsharpMask($strNewImage, 0.15, 1, 1);

		//}
		
		// Fallback to PNG if GIF ist not supported
		if ($objFile->extension == 'gif' && !$arrGdinfo['GIF Create Support'])
		{
			$objFile->extension = 'png';
		}
		
		$this->Files->chmod($strCacheName, 0777);

		// Create new image
		switch ($objFile->extension)
		{
			case 'gif':
			
				
				imagegif($strNewImage, TL_ROOT . '/' . $strCacheName);
				break;

			case 'jpg':
			case 'jpeg':
								
				imagejpeg($strNewImage, TL_ROOT. '/' . $strCacheName, (!$GLOBALS['TL_CONFIG']['jpgQuality'] ? 90 : $GLOBALS['TL_CONFIG']['jpgQuality']));
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
	
		$objProductData = $this->Database->prepare("SELECT name, alias, teaser, product_thumbnail_image, audio_jumpTo, audio_url, video_jumpTo, video_url FROM " . $dc->table . " WHERE id=?")
										 ->limit(1)
										 ->execute($dc->id);
				
		if($objProductData->numRows < 1)
		{
			return;		
		}
	
		
		$arrProductData = $objProductData->fetchAssoc();
		
		$arrProductPaths = $this->getCurrentProductPaths($arrProductData['alias']);

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
					'title' 			=> $arrProductData['name'],
					'description' 		=> sprintf($GLOBALS['TL_LANG']['MSC']['playlistDescriptionTemplate'], $arrProductData['name'], $arrProductData['teaser']),
					'path'				=> $this->Environment->base . $strFieldData,
					'type'				=> $mediaType,
					'duration'			=> 33,
					'is_audio'			=> $isAudio/*,
					'thumbnail_image'	=> $this->Environment->base . $arrProductData['main_image']*/
				);
			}
				
		}
			
		
		$objTemplate->playlistTitle = $arrProductData['name'];
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
		$strPathName = strtolower($varValue);				
		$char = strtolower(substr($strPathName, 0, 1));
		
		$arrPaths = array
		(			
			'file_source_path' => $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $char . '/' . $strPathName,
			'relative_source_path' => $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $char . '/' . $strPathName,
			'file_destination_path' => $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . $char . '/' . $strPathName,
			'relative_destination_path' => $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . $char . '/' . $strPathName
		);

		return $arrPaths;
	
	}
	
	
	public function getMediaFilenames($strAbsoluteFilePath, $strMediaType, $strSourcePoint)
	{
		
		if(is_dir($strAbsoluteFilePath))
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
		else
		{
			return array();
		}
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
	 * Fallback to first image (by ordinals) in the directory, if there is one.
	 * @param string
	 * @return string
	 */
	public function getFirstOrdinalImage($strBasePath, $strProductAlias)
	{
		$strFilePath = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . sprintf($strBasePath, strtolower(substr($strProductAlias, 0, 1)), $strProductAlias);
	
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
			return array();
		}
		
		$arrFiles = array();
				
		//Using glob because we're pretty confident people won't abuse the whole too many images for a product issue. ;D
		foreach($arrAssetKeys as $key)
		{
			if (is_dir(TL_ROOT . '/' . $strFilePath . '/' . strtolower(substr($key, 0, 1)) . '/' . $key))
			{
				//Grab all files in this directory.
				foreach($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strAssetType] as $fileType)
				{
					$arrGlob = glob(TL_ROOT . '/' . $strFilePath . '/' . strtolower(substr($key, 0, 1)) . '/' . $key . '/' . '*.' . $fileType);
					
					if (is_array($arrGlob))
					{
						$arrFiles = array_merge($arrFiles, $arrGlob);
					}
				}
			}
			else
			{
				foreach($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strAssetType] as $fileType)
				{
					$arrGlob = glob(TL_ROOT . '/' . $strFilePath . '/' . strtolower(substr($key, 0, 1)) . '/' . $key . "*." . $fileType); //filenames are key plus the * wildcard
			
					if (is_array($arrGlob))
					{
						foreach($arrGlob as $fileName)
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
		}
		
		return $arrFiles;
	}
	
}

