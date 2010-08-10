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


class IsotopeGallery extends Frontend
{
	
	/**
	 * Data storage
	 * @var array
	 */
	protected $arrData = array();
	
	/**
	 * Files
	 * @var array
	 */
	protected $arrFiles = array();
	
	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;
	
	
	/**
	 * Construct the object
	 */
	public function __construct($strName, $arrFiles)
	{
		parent::__construct();
		
		$this->import('Isotope');
		
		$this->name = $strName;
		$this->files = $arrFiles;
	}
	
	
	/**
	 * Set a value
	 */
	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{
			case 'files':
				$this->arrFiles = array();
				$varValue = deserialize($varValue);

				if(is_array($varValue) && count($varValue))
				{
					foreach( $varValue as $k => $file )
					{
						$strFile = 'isotope/' . substr($file['src'], 0, 1) . '/' . $file['src'];

						if (is_file(TL_ROOT . '/' . $strFile))
						{
							$objFile = new File($strFile);

							if ($objFile->isGdImage)
							{
								$arrSizes = deserialize($this->Isotope->Config->watermarkSizes, true);
								
								foreach( array('large', 'medium', 'thumbnail', 'gallery') as $type )
								{
									$size = $this->Isotope->Config->{$type . '_size'};
						
									if($this->Isotope->Config->enableWatermark && in_array($type, $arrSizes))
									{
										$strImage = $this->watermarkImage($strFile, $this->Isotope->Config->watermarkImage, $size);
									}
									else
									{
										$strImage = $this->getImage($strFile, $size[0], $size[1], $size[2]);
									}
									
									$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
																								
									$file[$type] = $strImage;

									if (is_array($arrSize) && strlen($arrSize[3]))
									{
										$file[$type . '_size'] = $arrSize[3];
									}
								}

								$this->arrFiles[] = $file;
							}
						}
					}
				}
				
				// No image available, add default image
				if (!count($this->arrFiles) && is_file(TL_ROOT . '/' . $this->Isotope->Config->missing_image_placeholder))
				{
					foreach( array('large', 'medium', 'thumbnail', 'gallery') as $type )
					{
						$size = $this->Isotope->Config->{$type . '_size'};
						$strImage = $this->getImage($this->Isotope->Config->missing_image_placeholder, $size[0], $size[1], $size[2]);
						$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);

						$file[$type] = $strImage;

						if (is_array($arrSize) && strlen($arrSize[3]))
						{
							$file[$type . '_size'] = $arrSize[3];
						}
					}

					$this->arrFiles[] = $file;
				}
				break;
			
			default:
				$this->arrData[$strKey] = $varValue;
				break;
		}
	}
	
	protected function watermarkImage($strImage, $strWatermarkImage, $arrDimensions)
	{	
		$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
		$arrWmSize = @getimagesize(TL_ROOT . '/' . $strWatermarkImage);	
		
		$objFile = new File(TL_ROOT.'/'.$strImage);
		
		if($arrSize['mime']=='image/gif')
			return $strImage;
	
		$strCacheName = 'system/html/' . $objFile->filename . '-' . substr(md5('-w' . $arrDimensions[0] . '-h' . $arrDimensions[1] . '-' . $strImage . '-' . $arrDimensions[2] . '-' . $objFile->mtime), 0, 8) . '.' . $objFile->extension;
		
		//Get the destination image handle
		$resultImage = $this->isoGetImage($strImage, $arrDimensions[0], $arrDimensions[1], $arrDimensions[2]);	
	
		//what is the size of the watermark in proportion to the actual image
		$intProportionFactorW = $arrSize[0]/$arrDimensions[0];
		$intProportionFactorH = $arrSize[1]/$arrDimensions[1];
		
		$intScaledWatermarkWidth = $arrWmSize[0]/$intProportionFactorW;
		$intScaledWatermarkHeight = $arrWmSize[1]/$intProportionFactorH;
			
		//Get the watermark image handle
		$watermarkImage = $this->isoGetImage($strWatermarkImage, round($intScaledWatermarkWidth), round($intScaledWatermarkHeight), $arrWmSize[2]);
		
		//$this->watermark($resultImage, $watermarkImage, $arrDimensions[0], $arrDimensions[1], $intScaledWatermarkWidth, $intScaledWatermarkHeight);			
		imagecopy($resultImage, $watermarkImage, 5, 5, 0, 0, round($intScaledWatermarkWidth), round($intScaledWatermarkHeight));  
		
		switch($arrSize['mime'])
		{
			case 'image/jpg':
			case 'image/jpeg':
				imagejpeg($resultImage, TL_ROOT . '/' . $strCacheName, (!$GLOBALS['TL_CONFIG']['jpgQuality'] ? 80 : $GLOBALS['TL_CONFIG']['jpgQuality']));								 				break;	
			case 'image/png':
				imagepng($resultImage, TL_ROOT . '/' . $strCacheName);
				break;
		}
		
		imagedestroy($resultImage);
		imagedestroy($watermarkImage);
		
		return $strCacheName;	
		
	}

	/** 
	 * TODO : get this into usable state for its positioning support
	 */
	protected function watermark (&$dst_image, $src_image, $dst_w, $dst_h, $src_w, $src_h, $position='bottom-left') 
	{ 
    	//imagealphablending($dst_image,true); 
    	//imagealphablending($src_image,true); 
		
		switch ($position) 
		{ 
			case 'top-right': 
			case 'right-top': 
			case 1: 
				imagecopy($dst_image, $src_image, ($dst_w-$src_w), 0, 0, 0, $src_w, $src_h); 
			break; 
			case 'top-left': 
			case 'left-top': 
			case 2: 
				imagecopy($dst_image, $src_image, 0, 0, 0, 0, $src_w, $src_h); 
			break; 
			case 'bottom-right': 
			case 'right-bottom': 
			case 3: 
				imagecopy($dst_image, $src_image, ($dst_w-$src_w), ($dst_h-$src_h), 0, 0, $src_w, $src_h); 
			break; 
			case 'bottom-left': 
			case 'left-bottom': 
			case 4: 
				imagecopy($dst_image, $src_image, 0 , ($dst_h-$src_h), 0, 0, $src_w, $src_h); 
			break; 
			case 'center': 
			case 5: 
				imagecopy($dst_image, $src_image, (($dst_w/2)-($src_w/2)), (($dst_h/2)-($src_h/2)), 0, 0, $src_w, $src_h); 
			break; 
			case 'top': 
			case 6: 
				imagecopy($dst_image, $src_image, (($dst_w/2)-($src_w/2)), 0, 0, 0, $src_w, $src_h); 
			break; 
			case 'bottom': 
			case 7: 
				imagecopy($dst_image, $src_image, (($dst_w/2)-($src_w/2)), ($dst_h-$src_h), 0, 0, $src_w, $src_h); 
			break; 
			case 'left': 
			case 8: 
				imagecopy($dst_image, $src_image, 0, (($dst_h/2)-($src_h/2)), 0, 0, $src_w, $src_h); 
			break; 
			case 'right': 
			case 9: 
				imagecopy($dst_image, $src_image, ($dst_w-$src_w), (($dst_h/2)-($src_h/2)), 0, 0, $src_w, $src_h); 
			break; 
		} 
	}
		/** 
	 * Returns an image handle with proper image processing.  From this we can watermark images.
	 */
	protected function isoGetImage($image, $width, $height, $mode='', $target=null)
	{
		if (!strlen($image))
		{
			return null;
		}

		$image = urldecode($image);

		// Check whether the file exists
		if (!file_exists(TL_ROOT . '/' . $image))
		{
			$this->log('Image "' . $image . '" could not be found', 'Controller getImage()', TL_ERROR);
			return null;
		}

		$objFile = new File($image);
		$arrAllowedTypes = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['validImageTypes']));

		// Check the file type
		if (!in_array($objFile->extension, $arrAllowedTypes))
		{
			$this->log('Image type "' . $objFile->extension . '" was not allowed to be processed', 'Controller getImage()', TL_ERROR);
			return null;
		}

		// No resizing required
		if ($objFile->width == $width && $objFile->height == $height)
		{
			return $image;
		}

		$strCacheName = 'system/html/' . $objFile->filename . '-' . substr(md5('-w' . $width . '-h' . $height . '-' . $image . '-' . $mode . '-' . $objFile->mtime), 0, 8) . '.' . $objFile->extension;

		// Return the path of the new image if it exists already
		if (file_exists(TL_ROOT . '/' . $strCacheName))
		{
			return $strCacheName;
		}
	
		// Return the path to the original image if the GDlib cannot handle it
		if (!extension_loaded('gd') || !$objFile->isGdImage || $objFile->width > 3000 || $objFile->height > 3000 || (!$width && !$height) || $width > 1200 || $height > 1200)
		{
			return $image;
		}

		$intPositionX = 0;
		$intPositionY = 0;
		$intWidth = $width;
		$intHeight = $height;

		// Mode-specific changes
		if ($intWidth && $intHeight)
		{
			switch ($mode)
			{
				case 'proportional':
					if ($objFile->width >= $objFile->height)
					{
						unset($height, $intHeight);
					}
					else
					{
						unset($width, $intWidth);
					}
					break;

				case 'box':					
					if (ceil($objFile->height * $width / $objFile->width) <= $intHeight)
					{
						unset($height, $intHeight);
					}
					else
					{
						unset($width, $intWidth);
					}
					break;
			}
		}

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

		// Calculate the height if only the width is given
		elseif ($intWidth)
		{
			$intHeight = ceil($objFile->height * $width / $objFile->width);
			$strNewImage = imagecreatetruecolor($intWidth, $intHeight);
		}

		// Calculate the width if only the height is given
		elseif ($intHeight)
		{
			$intWidth = ceil($objFile->width * $height / $objFile->height);
			$strNewImage = imagecreatetruecolor($intWidth, $intHeight);
		}

		$arrGdinfo = gd_info();
		$strGdVersion = preg_replace('/[^0-9\.]+/', '', $arrGdinfo['GD Version']);

		switch ($objFile->extension)
		{
			case 'gif':
				if ($arrGdinfo['GIF Read Support'])
				{
					$strSourceImage = imagecreatefromgif(TL_ROOT . '/' . $image);
					$intTranspIndex = imagecolortransparent($strSourceImage);

					// Handle transparency
					if ($intTranspIndex >= 0 && $intTranspIndex < imagecolorstotal($strSourceImage))
					{
						$arrColor = imagecolorsforindex($strSourceImage, $intTranspIndex);
						$intTranspIndex = imagecolorallocate($strNewImage, $arrColor['red'], $arrColor['green'], $arrColor['blue']);
						imagefill($strNewImage, 0, 0, $intTranspIndex);
						imagecolortransparent($strNewImage, $intTranspIndex);
					}
				}
				break;

			case 'jpg':
			case 'jpeg':
				if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support'])
				{
					$strSourceImage = imagecreatefromjpeg(TL_ROOT . '/' . $image);
				}
				break;

			case 'png':
				if ($arrGdinfo['PNG Support'])
				{
					$strSourceImage = imagecreatefrompng(TL_ROOT . '/' . $image);

					// Handle transparency (GDlib >= 2.0 required)
					if (version_compare($strGdVersion, '2.0', '>='))
					{
						imagealphablending($strNewImage, false);
						$intTranspIndex = imagecolorallocatealpha($strNewImage, 0, 0, 0, 127);
						imagefill($strNewImage, 0, 0, $intTranspIndex);
						imagesavealpha($strNewImage, true);
					}
				}
				break;
		}

		// The new image could not be created
		if (!$strSourceImage)
		{
			$this->log('Image "' . $image . '" could not be processed', 'Controller getImage()', TL_ERROR);
			return null;
		}

		imagecopyresampled($strNewImage, $strSourceImage, $intPositionX, $intPositionY, 0, 0, $intWidth, $intHeight, $objFile->width, $objFile->height);

		// Fallback to PNG if GIF ist not supported
		if ($objFile->extension == 'gif' && !$arrGdinfo['GIF Create Support'])
		{
			$objFile->extension = 'png';
		}
	
		return $strNewImage;
	
	}
	
	/**
	 * Get a value
	 */
	public function __get($strKey)
	{
		switch( $strKey )
		{
			case 'main_image':
				return reset($this->arrFiles);
				break;

			default:
				return $this->arrData[$strKey];
		}
	}
	
	
	/**
	 * If the class is echoed, return the main image
	 */
	public function __toString()
	{
		return $this->generateMainImage();
	}
	
	
	/**
	 * Generate main image
	 */
	public function generateMainImage($strType='medium')
	{
		if (!count($this->arrFiles))
			return '<span id="' . $this->name . '_' . $strType . 'size"> </span>';
			
		$arrFile = reset($this->arrFiles);
		
		$this->injectAjax();
		
		$objTemplate = new FrontendTemplate('iso_gallery_default');
			
		$objTemplate->setData($arrFile);
		$objTemplate->type = $strType;
		$objTemplate->href_reader = $this->href_reader;
		
		return '<span id="' . $this->name . '_' . $strType . 'size">'.$objTemplate->parse().'</span>';
	}
	
	
	/**
	 * Generate gallery
	 */
	public function generateGallery()
	{
		$strGallery = '';
		
		reset($this->arrFiles);
		
		while( $arrFile = next($this->arrFiles) )
		{
			$objTemplate = new FrontendTemplate('iso_gallery_default');
			
			$objTemplate->setData($arrFile);
			$objTemplate->type = 'gallery';
			$objTemplate->href_reader = $this->href_reader;
			
			$strGallery .= $objTemplate->parse();
		}
		
		$this->injectAjax();
		return '<span id="' . $this->name . '_gallery">' . $strGallery . '</span>';
	}
	
	
	protected function injectAjax()
	{
		$GLOBALS['TL_MOOTOOLS'][get_class($this).'_ajax'] = "
<script type=\"text/javascript\">
<!--//--><![CDATA[//><!--
window.addEvent('ajaxready', function() {
  Mediabox ? Mediabox.scanPage() : Lightbox.scanPage();
});
//--><!]]>
</script>
";
	}
}

