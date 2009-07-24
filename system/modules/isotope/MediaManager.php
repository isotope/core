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
 * @copyright  Leo Feyer 2005
 * @author     Leo Feyer <leo@typolight.org>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Class MediaManager
 *
 * Provide methods to handle radio button tables while setting a custom path for image source.
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss <fred@winanscreative.com>
 * @package    Controller
 */
class MediaManager extends Widget
{

	/**
	 * Submit user input
	 * @var boolean
	 */
	protected $blnSubmitInput = true;

	/**
	 * Columns
	 * @var integer
	 */
	protected $intCols = 4;

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'be_widget';


	/**
	 * 
	 
	/**
	 * Images
	 * @var array
	 */
	protected $arrImages = array();


	protected $dataContainer;


	/**
	 * Load database object
	 * @param array
	 */
	public function __construct($arrAttributes=false, $dc=null)
	{
		
		$this->import('Database');
		parent::__construct($arrAttributes);
        
        $this->dataContainer = $dc;
	}
	
	/**
	 * Add specific attributes
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'cols':
				if ($varValue > 0)
				{
					$this->intCols = $varValue;
				}
				break;

			case 'mandatory':
				$this->arrConfiguration['mandatory'] = $varValue ? true : false;
				break;
						
			default:
				parent::__set($strKey, $varValue);
				break;
		}
	}


	/**
	 * Generate the widget and return it as string
	 * @return string
	 */
	public function generate()
	{
		$this->import('Files');
		
		$this->import('BackendUser','User');
						
		//Get the to the images from the current product's directory
		$objCurrentProductImagePath = $this->Database->prepare("SELECT alias, main_image, old_images_list FROM " . $this->strTable . " WHERE id=?")
													 ->limit(1)
													 ->execute($this->Input->get('id'));
		
		if($objCurrentProductImagePath->numRows < 1)
		{
			//return $GLOBALS['TL_LANG']['MSC']['noImagesAssociatedWithProduct'];
			$blnShowImageManager = false;
		}else{
			$strProductPath = $objCurrentProductImagePath->alias;
			if(strlen($strProductPath) > 0)
			{
				$renameTempDir = true;
			}
		}

		//Scan the assets_for_import directory for all images.
		$this->arrImages = $this->getFiles($path, 'images');

		$arrOriginalProductImages = explode(',', $objCurrentProductImagePath->old_images_list);
		$strOriginalProductImage = $arrOriginalProductImages[0];
		$arrProductImages = explode(',', $objCurrentProductImagePath->main_image);
		$strProductImage = $arrProductImages[0];
		
		//compare size of original list and if larger then grab any images not in the directory
	
		if(strlen($strProductImage))
		{
			$blnDefaultImageSet = true;
		}
		
		//Copy files from temp folder
		foreach($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'] as $k=>$v)
		{

		//Change the temp directory to one that uses the product alias. Avoids errors when product is saved without an alias.
			if($renameTempDir)
			{
								
				if(is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($this->Input->get('id'), 0, 1) . '/' . $this->Input->get('id') . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder']))
				{
				
					if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($strProductPath, 0, 1) . '/' . $strProductPath . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder']))
					{
					
						//Create or new destination folder
						new Folder($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($strProductPath, 0, 1) . '/' . $strProductPath . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder']);
					}
					
					//Check to see if we need to copy over those temp dir. files.  	
					if($this->newDirectoryIsEmpty($GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($strProductPath, 0, 1) . '/' . $strProductPath . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder']))
					{
						$this->copyAllDirectoryFiles($GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($this->Input->get('id'), 0, 1) . '/' . $this->Input->get('id') . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder'], $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($strProductPath, 0, 1) . '/' . $strProductPath . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder'], $k, true);
					}	
									
				}else{
					new Folder($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($strProductPath, 0, 1) . '/' . $strProductPath . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder']);
				}
				
				//Need one for each asset type
				$strProductBasicPath = substr($strProductPath, 0, 1) . '/' . $strProductPath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'];
				$strProductFullPath = $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $strProductBasicPath;
				
			}else{
				//If the temp directory hasn't been created, create it now.
				if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($this->Input->get('id'), 0, 1) . '/' . $this->Input->get('id') . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder']))
				{
					new Folder($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . substr($this->Input->get('id'), 0, 1) . '/' . $this->Input->get('id') . '/' . $GLOBALS['TL_LANG']['MSC'][$k . 'Folder']);
				}
			
				//Need one for each asset type
				$strProductBasicPath = substr($this->Input->get('id'), 0, 1) . '/' . $this->Input->get('id') . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'];
				$strProductFullPath = $GLOBALS['TL_LANG']['MSC']['assetsImportBasePath'] . '/' . $strProductBasicPath;
		
			}
		}
		
		//Set the upload path to be specific to the current product id under the assets import/images/ path e.g. assets_for_import/images/1/
		$path = strtolower($strProductFullPath);
		$basicPath = strtolower($strProductBasicPath);
		$tmpPath = strtolower($strProductFullPath) . '/' . 'tmp';
								
		if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $path))
		{
			new Folder($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $path);
		}
				
		$return = ' <a href="typolight/media_upload.php?act=move&amp;mode=2&amp;pid=' . $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $path . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['fileManager']) . '" onclick="Backend.getScrollOffset(); this.blur(); Backend.openWindow(this, 750, 500); return false;">' . $this->generateImage('system/modules/isotope/html/upload.gif', $GLOBALS['TL_LANG']['MSC']['fileManager'], 'style="vertical-align:text-bottom;"') . '</a>';	
		
		
		//return $return;
		
		
		
		//Set custom path
		if (strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['path']))
		{
			$path = $GLOBALS['TL_DCA'][$this->strTable]['fields'][$this->strField]['eval']['path'];
		}
		
		
		$this->import('MediaManagement');
			
		//$this->arrImages = array_merge($arrProductImages, $this->getFiles($path, 'images'));
		
		
		//Check for the files listing existing under the root product_asset path
			
		//Scan the product_assets directory for all existing images.
		$existingPath = $this->MediaManagement->getCurrentProductPaths($strProductPath);
		$existingAssetsPath = $existingPath['file_destination_path'] . '/images/large_images';
		$this->arrExistingImages = $this->getExistingFiles($existingAssetsPath, 'images');
		
		
		//Scan the assets_for_import directory for all images.
		$this->arrImages = $this->getFiles($path, 'images');
		
			
		$strFallbackPath = $this->MediaManagement->getCurrentProductPaths($this->Input->get('id'));
		
		if(sizeof($this->arrExistingImages) || sizeof($this->arrImages))
		{
			$blnShowImageManager = true;
		}
		elseif($blnDefaultImageSet)
		{
			//look in the holding tank for the image instead.  if it exists then copy to std. assets_for_import directory and thumb for widget.
			
			$tmpPath = $path . '/tmp'; //Assign the destination as intended
			//reassign the source path as the import path
			
			//echo $strFallbackPath;
			
			if(!file_exists(TL_ROOT . '/' . $strFallbackPath . '/' . $strOriginalProductImage))
			{	
		
				$blnShowImageManager = false;
			}else{
				$blnShowImageManager = true;
				$fullDestFilePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strProductFullPath . '/' . $strOriginalProductImage;
				$fullSourceFilePath = $strFallbackPath . '/' . $strProductImage;
				
				$this->Files->chmod($fullSourceFilePath, 0755);
				$this->Files->copy($fullSourceFilePath, $fullDestFilePath);
				$this->Files->chmod($fullDestFilePath, 0755);
			}
			
			//get the files again now that we've actually got one in there.
			
		}
		
		
		
		if(sizeof($this->arrImages) > sizeof($this->arrExistingImages))
		{
			
			$blnCopyFromDefaultAssetImportPath = true;
			
		}
		
					
		if($blnShowImageManager)
		{
			if(sizeof($this->arrImages))
			{
				$rows = ceil(count($this->arrImages) / $this->intCols);
			}else{
				$rows = 1;
			}
			
			$return .= '<table cellspacing="0" cellpadding="0" id="ctrl_'.$this->strName.'" class="tl_radio_table'.(strlen($this->strClass) ? ' ' . $this->strClass : '').'" summary="">';
			
			
			//if($blnCopyFromDefaultAssetImportPath)
			//{
				//$dir = TL_ROOT . '/' . $strFallbackPath;
												
			//}else{
			$dir = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $path;
			//}
			
			//Find all source image paths from whatever origin, either from the upload assets_for_import/<letter>/<alias> directory or from the holding tank 			 			//below
			//Check for the files listed existing under the default directory
					
			/*	
			if($blnCopyFromDefaultAssetImportPath)
			{
				foreach($arrProductImages as $image)
				{
					$fallbackDir = TL_ROOT . '/' . $strFallbackPath;

					$currPath = $strFallbackPath . '/' . $image;
					$newPath = $tmpPath . '/' . $image;
									
					if(file_exists($fallbackDir . '/' . $image))
					{	
						if(!file_exists($dir . '/tmp/' . $image))	//Check for corresponding thumbnail and create if it doesn't exist.
						{
							$this->copyAndResizeForImageWidget($fallbackDir, $image, $currPath, $newPath, $tmpPath, true);
						}
					}else{
						//if the file is listed but doesn't exist here, add it to a array so we can search for it under the holding tank path.
						$arrImagesToFindInFallback[] = $image;
					}
				}
		
				foreach($arrImagesToFindInFallback as $image)
				{
					$fallbackDir = TL_ROOT . '/' . $strFallbackPath;
					
					$currPath = $strFallbackPath . '/' . $image; //relative path
					$newPath = $tmpPath . '/' . $image; //relative path
					
					if(file_exists($fallbackDir . '/' . $image))
					{
						$this->copyAndResizeForImageWidget($fallbackDir, $image, $currPath, $newPath, $tmpPath, true);
					}				
				}
			}*/
			
			//Copy from holding tank based on list provided in main_image, if any.
			if(is_dir($fallbackDir))
			{
				foreach($arrOriginalProductImages as $image)
				{
					$fallbackDir = TL_ROOT . '/' . $strFallbackPath;

					$currPath = $strFallbackPath . '/' . $image;
					$newPath = $tmpPath . '/' . $image;
									
					if(file_exists($fallbackDir . '/' . $image))
					{	
						$this->copyAndResizeForImageWidget($fallbackDir, $image, $currPath, $newPath, $tmpPath, true);
						
					}//else{
						//if the file is listed but doesn't exist here, add it to a array so we can search for it under the holding tank path.
						//$arrImagesToFindInFallback[] = $image;
					//}
				}
			}
			
			//Thumbnail any missing tmp thumbnails from what is in the directory
			if(is_dir($dir))
			{
				
				if ($dh = opendir($dir)) 
				{
									
					while (($file = readdir($dh)) !== false)
					{
						if (!is_dir($file)) //make sure we're not trying to handle a directory...
						{	
													
							$currPath = $path . '/' . $file;
							$newPath = $tmpPath . '/' . $file;
							
							if(!file_exists($dir . '/tmp/' . $file))	//Check for corresponding thumbnail and create if it doesn't exist.
							{					
								$this->copyAndResizeForImageWidget($dir, $file, $currPath, $newPath, $tmpPath);
							}
						}
					}
					
					
				}
			}
			
			
			//MOVE ANY EXISTING IMAGES OVER IF NECESSARY & THUMBNAIL
			
			//Opent the product_assets path and see if any files exist
			if(sizeof($this->arrExistingImages))
			{
				foreach($this->arrExistingImages as $image)
				{
					$basedir = $GLOBALS['TL_CONFIG']['isotope_base_path'] . '/' . substr($strProductPath, 0, 1) . '/' . $strProductPath . '/' . $GLOBALS['TL_LANG']['MSC']['imagesFolder'] . '/' . $GLOBALS['TL_LANG']['MSC']['large_images_folder'];
					$currImgPath = $basedir . '/' . $image;
					$newImgPath = $tmpPath . '/' . $image;
						
					if(file_exists($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $currImgPath))
					{	
						$this->copyAndResizeExistingForImageWidget($basedir, $image, $currImgPath, $newImgPath, $tmpPath);
					}
				}
			
			}
			
			//Check for the files listing existing under the root asset import path
			
			//Opent the assets_for_import path and see if any files exist
			/*if(sizeof($arrProductImages))
			{
				foreach($arrProductImages as $image)
				{
					$currPath = $strFallbackPath . '/' . $image;
					$newPath = $tmpPath . '/' . $image;
									
					if(file_exists($dir . '/' . $image))
					{	
						
						$this->copyAndResizeForImageWidget($dir, $image, $currPath, $newPath, $tmpPath, true);
					}
				}
			
			}elseif(is_dir($dir))
			{
				if ($dh = opendir($dir)) 
				{
									
					while (($file = readdir($dh)) !== false)
					{
						if (!is_dir($dir . '/' . $file)) //make sure we're not trying to handle a directory...
						{	
													
							$currPath = $path . '/' . $file;
							$newPath = $tmpPath . '/' . $file;
														
							$this->copyAndResizeForImageWidget($dir, $file, $currPath, $newPath, $tmpPath);
						
						}
					}
					
					
				}
			}*/
			

			//Look for image files in the assets_for_import/<letter>/<alias> directory.  By the time we do this we should have gathered all files from every source
			$arrTempImages = $this->getFiles($tmpPath, 'images');
			$arrOldImages = $this->getFiles($path, 'images');
			
			if(!$arrOldImages)
			{
				$arrOldImages = array();
			}
			
			if(!$arrTempImages)
			{
				$arrTempImages = array();
			}
			
			$arrDiff = array_diff($arrTempImages, $arrOldImages);
			
			$arrIntersect = array_intersect($arrTempImages, $arrOldImages);
			
			$this->arrImages = array_merge($arrIntersect, $arrDiff);
									
			$rows = ceil(count($this->arrImages) / $this->intCols);
				
			for ($i=0; $i<$rows; $i++)
			{
				$return .= '
    			<tr>';
    						
    			
				// Add cells
				for ($j=($i*$this->intCols); $j<(($i+1)*$this->intCols); $j++)
				{
									
					if(!empty($this->arrImages[$j]))
					{						
						$value = $this->arrImages[$j];
						$label = '<img src="../' . $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $tmpPath  . '/' . $this->arrImages[$j] . '" />';//$this->arrImages[$j]['label'];
						
						$return .= '
							<td><input type="radio" name="'.$this->strName.'" id="'.$this->strField.'_'.$i.'_'.$j.'" class="tl_radio" value="'.specialchars($value).'" onfocus="Backend.getScrollOffset();"'.$this->optionChecked($value, $this->value).$this->getAttributes().' /> <label for="'.$this->strField.'_'.$i.'_'.$j.'">'.$label.'</label><br /><br />[ <a href="typolight/media_upload.php?act=delete&special='. $basicPath .'&id=' . $this->arrImages[$j] . '">' . $GLOBALS['TL_LANG']['MSC']['deleteImage'] . '</a> ]</td>';
					}else{
						// Else return an empty cell
					
						$return .= '
     					<td></td>';		
					}

				}

					// Close row
					$return .= '
    			</tr>';

  			}

			return $return . '
  				</table>';
  		}else{
  		
  			return $return;
  		}
	}
	
	private function copyAndResizeForImageWidget($dir, $file, $strSourcePath, $strDestinationPath, $tmpFolderPath, $blnCopyFromDefaultAssetImportPath = false)
	{
		
		$this->import('Files');
		
		if($blnCopyFromDefaultAssetImportPath)
		{
			$fullFilePath = $strSourcePath;
						
			$fullDestFilePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . str_replace('/tmp', '', $strDestinationPath);

			//$this->Files->chmod($fullDestFilePath, 0755);
		
			$fullDestFilePathTmp = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strDestinationPath;
			
			if(!file_exists($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strDestinationPath))
			{					
				if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $tmpFolderPath))
				{
					new Folder($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $tmpFolderPath);			
				}
				
				
			}
			
		}else{
		
			$fullFilePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strSourcePath;	
			$fullDestFilePath = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strDestinationPath;
			$fullDestFilePathTmp = $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strDestinationPath;
		

			if(!file_exists($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strDestinationPath))
			{											
				if(!is_dir($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $tmpFolderPath))
				{
					new Folder($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $tmpFolderPath);
				}
				
			}
		}				
		
		//$this->Files->chmod($fullDestFilePathTmp, 0755);
		
				
		$this->Files->copy($fullFilePath, $fullDestFilePath);
		$this->Files->chmod($fullDestFilePath, 0755);
		$this->Files->copy($fullFilePath, $fullDestFilePathTmp);
	
		$arrImageSize = @getimagesize($GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strDestinationPath);
			
						
		if($arrImageSize[0] > 75)
		{
			$this->import('MediaManagement');
			//$this->Files->chmod($fullDestFilePathTmp, 0777);
			$this->MediaManagement->resizeProductImage($fullDestFilePathTmp, 75, 0);
			
			//$this->Files->chmod($fullDestFilePathTmp, 0755);
			
			
		}
		
		
	}
	
	
	//WILL NEED TO BE CONSOLIDATED WTH ABOVE
	
	protected function copyAndResizeExistingForImageWidget($dir, $file, $strSourcePath, $strDestinationPath, $tmpFolderPath)
	{
		
		$this->import('Files');
		
		//SET UP RELATIVE FILE PATHS
		$relFilePath = $GLOBALS['TL_CONFIG']['isotope_upload_path']. '/' . $strSourcePath;
		$relDestPath = $GLOBALS['TL_CONFIG']['isotope_upload_path']. '/' . $strDestinationPath;
		$relTempPath = $GLOBALS['TL_CONFIG']['isotope_upload_path']. '/' . $tmpFolderPath;
		
		//SET UP FULL FILE PATHS
		$fullFilePath = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strSourcePath;
		$fullDestFilePath = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strDestinationPath;
		$fullDestFilePathTmp = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $tmpFolderPath ;
		
				
		//CHECK IF FILE & TMP DIR EXISTS
		if(!file_exists($fullDestFilePath))
		{			
			// We reset this every time to make sure pull the latest info from the product_assets dir
			if(is_dir($fullDestFilePathTmp))
			{
				$this->Files->delete($relTempPath);
				new Folder($relTempPath);
			} else
			{
				new Folder($relTempPath);
			}
		}
		
		
		//COPY, CHMOD, & GET SIZE		
		$this->Files->copy($relFilePath, $relDestPath);
		$this->Files->chmod($relDestPath, 0755);	
		$arrImageSize = @getimagesize($fullDestFilePath);
		
		//RESIZE IF NEEDED
		if($arrImageSize[0] > 75)
		{
			$this->import('MediaManagement');
			$this->MediaManagement->resizeProductImage($relDestPath, 75, 0);			
		}
		
	}

	
	
	
	/*  Get files from the current import path
	 *  @param string
	 *  @param string
	 *  @return array
	 */
	public function getFiles($strPath, $strMediaType)
	{
		$dir = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strPath;
		
		$arrMediaTypes = array_keys($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes']);
		
		if(!in_array($strMediaType, $arrMediaTypes))
		{
			throw new Exception('invalid media type group "' . $strMediaType . '"');		
		}
		
		
		if(is_dir($dir))
		{
			if ($dh = opendir($dir . '/')) {
				while ($file = readdir($dh))
				{
					$fileExt = explode('.', $file);
					if(in_array(strtolower($fileExt[1]), $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strMediaType]))
					{
						$arrImages[] = $file;					
					}
				}
			}
		}
		
		return $arrImages;
			
	}
	
	
	/*  Get files from the existing product assets path - THIS SHOULD BE CONSOLIDATED
	 *  @param string
	 *  @param string
	 *  @return array
	 */
	public function getExistingFiles($strPath, $strMediaType)
	{
		
		$dir = $strPath;
		
		$arrMediaTypes = array_keys($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes']);
		
		if(!in_array($strMediaType, $arrMediaTypes))
		{
			throw new Exception('invalid media type group "' . $strMediaType . '"');		
		}
		
		
		if(is_dir($dir))		
		{
		
			if ($dh = opendir($dir . '/')) {
				while ($file = readdir($dh))
				{
				
					$fileExt = explode('.', $file);
					
					if(in_array($fileExt[1], $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strMediaType]))
					{
						
						$arrImages[] = $file;					
					}
				}
			}
		}
		
		return $arrImages;
			
	}

	
	
	public function newDirectoryIsEmpty($strPath)
	{
		$dir = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strPath;
		
		if(count(scan($dir . '/')))
		{
			return false;
		}	
		
		return true;

	}
	
	/**
	 *
	 *
	 *
	 *
	 */
	public function copyAllDirectoryFiles($strSourcePath, $strDestinationPath, $strMediaType, $blnDeleteOriginals = false)
	{	
		
		$arrMediaTypes = array_keys($GLOBALS['TL_LANG']['MSC']['validMediaFileTypes']);
		
				
		if(!in_array($strMediaType, $arrMediaTypes))
		{
			throw new Exception('invalid media type group "' . $strMediaType . '"');		
		}
		
		$dir = $GLOBALS['TL_CONFIG']['isotope_root'] . '/' . $strSourcePath;
		
		if(is_dir($dir))
		{
			if ($dh = opendir($dir . '/')) {
				while ($file = readdir($dh))
				{
					$fileExt = explode('.', $file);
				
					if(in_array($fileExt[1], $GLOBALS['TL_LANG']['MSC']['validMediaFileTypes'][$strMediaType]))
					{
						if($this->Files->copy($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strSourcePath . '/' . $file, $GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strDestinationPath . '/' . $file) !== false)
						{		
							//remove the old copy from the temp directory		
							if($blnDeleteOriginals)
							{
								$this->Files->delete($GLOBALS['TL_CONFIG']['isotope_upload_path'] . '/' . $strSourcePath);							
							}
						}
					}
				}
			}
			
			
			

		}

	
	}
	


}

?>