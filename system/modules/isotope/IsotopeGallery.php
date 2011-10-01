<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
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
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
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
								foreach( (array)$this->Isotope->Config->imageSizes as $size )
								{
									$strImage = $this->getImage($strFile, $size['width'], $size['height'], $size['mode']);

									if ($size['watermark'] != '')
									{
										$strImage = IsotopeFrontend::watermarkImage($strImage, $size['watermark'], $size['position']);
									}

									$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);
									if (is_array($arrSize) && strlen($arrSize[3]))
									{
										$file[$size['name'] . '_size'] = $arrSize[3];
									}

									$file['alt'] = specialchars($file['alt']);
									$file['desc'] = specialchars($file['desc']);

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
					foreach( (array)$this->Isotope->Config->imageSizes as $size )
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
	 * Check whether a property is set
	 * @param string
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
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
			return '<div class="iso_attribute" id="' . $this->name . '_' . $strType . 'size"> </div>';

		$arrFile = reset($this->arrFiles);

		$this->injectAjax();

		$objTemplate = new IsotopeTemplate($this->strTemplate);

		$objTemplate->setData($arrFile);
		$objTemplate->id = 0;
		$objTemplate->mode = 'main';
		$objTemplate->type = $strType;
		$objTemplate->name = $this->name;
		$objTemplate->product_id = $this->product_id;
		$objTemplate->href_reader = $this->href_reader;

		list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);

		return '<div class="iso_attribute" id="' . $this->name . '_' . $strType . 'size">'.$objTemplate->parse().'</div>';
	}


	/**
	 * Generate gallery
	 */
	public function generateGallery($strType='gallery', $intSkip=1)
	{
		$strGallery = '';

		foreach( $this->arrFiles as $i => $arrFile )
		{
			if ($i < $intSkip)
				continue;

			$objTemplate = new IsotopeTemplate($this->strTemplate);

			$objTemplate->setData($arrFile);
			$objTemplate->id = $i;
			$objTemplate->mode = 'gallery';
			$objTemplate->type = $strType;
			$objTemplate->name = $this->name;
			$objTemplate->product_id = $this->product_id;
			$objTemplate->href_reader = $this->href_reader;

			list($objTemplate->link, $objTemplate->rel) = explode('|', $arrFile['link']);

			$strGallery .= $objTemplate->parse();
		}

		$this->injectAjax();
		return '<div class="iso_attribute" id="' . $this->name . '_gallery">' . $strGallery . '</div>';
	}


	protected function injectAjax()
	{
		list(,$startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();

		$GLOBALS['TL_MOOTOOLS'][get_class($this).'_ajax'] = "
$startScript
window.addEvent('ajaxready', function() {
  Mediabox ? Mediabox.scanPage() : Lightbox.scanPage();
});
$endScript
";
	}
}

