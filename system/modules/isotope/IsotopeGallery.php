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
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'iso_gallery_default';
	
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
						$strFile = 'isotope/' . strtolower(substr($file['src'], 0, 1)) . '/' . $file['src'];

						if (is_file(TL_ROOT . '/' . $strFile))
						{
							$objFile = new File($strFile);

							if ($objFile->isGdImage)
							{
								foreach( $this->Isotope->Config->imageSizes as $size )
								{
									$strImage = $this->getImage($strFile, $size['width'], $size['height'], $size['mode']);
									
									if ($strImage != $strFile && $size['watermark'] != '')
									{
										$this->import('IsotopeFrontend');
										$strImage = $this->IsotopeFrontend->watermarkImage($strImage, $size['watermark'], $size['position']);
									}
									
									$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
									if (is_array($arrSize) && strlen($arrSize[3]))
									{
										$file[$size['name'] . '_size'] = $arrSize[3];
									}
									
									$file[$size['name']] = $strImage;
								}

								$this->arrFiles[] = $file;
							}
						}
					}
				}
				
				// No image available, add default image
				if (!count($this->arrFiles) && is_file(TL_ROOT . '/' . $this->Isotope->Config->missing_image_placeholder))
				{
					foreach( $this->Isotope->Config->imageSizes as $size )
					{
						$strImage = $this->getImage($this->Isotope->Config->missing_image_placeholder, $size['width'], $size['height'], $size['mode']);
						
						$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
						if (is_array($arrSize) && strlen($arrSize[3]))
						{
							$file[$size['name'] . '_size'] = $arrSize[3];
						}
						
						$file[$size['name']] = $strImage;
					}

					$this->arrFiles[] = $file;
				}
				break;
			
			default:
				$this->arrData[$strKey] = $varValue;
				break;
		}
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
		
		$objTemplate = new FrontendTemplate($this->strTemplate);
			
		$objTemplate->setData($arrFile);
		$objTemplate->mode = 'main';
		$objTemplate->type = $strType;
		$objTemplate->name = $this->name;
		$objTemplate->product_id = $this->product_id;
		$objTemplate->href_reader = $this->href_reader;
		
		list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);
		
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
			$objTemplate = new FrontendTemplate($this->strTemplate);
			
			$objTemplate->setData($arrFile);
			$objTemplate->mode = 'gallery';
			$objTemplate->type = 'gallery';
			$objTemplate->name = $this->name;
			$objTemplate->product_id = $this->product_id;
			$objTemplate->href_reader = $this->href_reader;
			
			list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);
			
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

