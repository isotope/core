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


class IsotopeFrontend extends Frontend
{

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;


	public function __construct()
	{
		parent::__construct();

		$this->import('Isotope');
	}


	/**
	 * Callback for add_to_cart button
	 *
	 * @access	public
	 * @param	object
	 * @return	void
	 */
	public function addToCart($objProduct, $objModule=null)
	{
		$intQuantity = ($objModule->iso_use_quantity && intval($this->Input->post('quantity_requested')) > 0) ? intval($this->Input->post('quantity_requested')) : 1;

		if ($this->Isotope->Cart->addProduct($objProduct, $intQuantity) !== false)
		{
			$this->jumpToOrReload($objModule->iso_addProductJumpTo);
		}
	}


	/**
	 * Replaces Isotope-specific InsertTags in Frontend.
	 *
	 * @access public
	 * @param string $strTag
	 * @return mixed
	 */
	public function replaceIsotopeTags($strTag)
	{
		$arrTag = trimsplit('::', $strTag);

		if (count($arrTag) == 2 && $arrTag[0] == 'isotope')
		{
			switch( $arrTag[1] )
			{
				case 'cart_items';
					return $this->Isotope->Cart->items;
					break;

				case 'cart_products';
					return $this->Isotope->Cart->products;
					break;

				case 'cart_items_label';
					$intCount = $this->Isotope->Cart->items;
					if (!$intCount)
						return '';

					return $intCount == 1 ? ('('.$GLOBALS['TL_LANG']['ISO']['productSingle'].')') : sprintf(('('.$GLOBALS['TL_LANG']['ISO']['productMultiple'].')'), $intCount);
					break;

				case 'cart_products_label';
					$intCount = $this->Isotope->Cart->products;
					if (!$intCount)
						return '';

					return $intCount == 1 ? ('('.$GLOBALS['TL_LANG']['ISO']['productSingle'].')') : sprintf(('('.$GLOBALS['TL_LANG']['ISO']['productMultiple'].')'), $intCount);
					break;

				case 'cart_total':
					return $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->grandTotal);
					break;

				case 'cart_subtotal':
					return $this->Isotope->formatPriceWithCurrency($this->Isotope->Cart->subTotal);
					break;
			}

			return '';
		}
		elseif ($arrTag[0] == 'isolabel')
		{
			return $this->Isotope->translate($arrTag[1], $arrTag[2]);
		}

		return false;
	}


	/**
	 * Apply a watermark to an image
	 */
	public function watermarkImage($image, $watermark, $position='br')
	{
		$image = urldecode($image);

		if (!is_file(TL_ROOT . '/' . $image) || !is_file(TL_ROOT . '/' . $watermark))
		{
			return $image;
		}

		$objFile = new File($image);

		$strCacheName = 'system/html/' . $objFile->filename . '-' . substr(md5($watermark . '-' . $position . '-' . $objFile->mtime), 0, 8) . '.' . $objFile->extension;

		// Return the path of the new image if it exists already
		if (file_exists(TL_ROOT . '/' . $strCacheName))
		{
			return $strCacheName;
		}

		// HOOK: add custom logic
		if (isset($GLOBALS['ISO_HOOKS']['watermarkImage']) && is_array($GLOBALS['ISO_HOOKS']['watermarkImage']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['watermarkImage'] as $callback)
			{
				$this->import($callback[0]);
				$return = $this->$callback[0]->$callback[1]($image, $watermark);

				if (is_string($return))
				{
					return $return;
				}
			}
		}

		$arrGdinfo = gd_info();
		$strGdVersion = preg_replace('/[^0-9\.]+/', '', $arrGdinfo['GD Version']);

		// Load image
		switch ($objFile->extension)
		{
			case 'gif':
				if ($arrGdinfo['GIF Read Support'])
				{
					$strImage = imagecreatefromgif(TL_ROOT . '/' . $image);
				}
				break;

			case 'jpg':
			case 'jpeg':
				if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support'])
				{
					$strImage = imagecreatefromjpeg(TL_ROOT . '/' . $image);
				}
				break;

			case 'png':
				if ($arrGdinfo['PNG Support'])
				{
					$strImage = imagecreatefrompng(TL_ROOT . '/' . $image);
				}
				break;
		}

		// Image could not be read
		if (!$strImage)
		{
			return $image;
		}

		// Load watermark
		$objWatermark = new File($watermark);
		switch ($objWatermark->extension)
		{
			case 'gif':
				if ($arrGdinfo['GIF Read Support'])
				{
					$strWatermark = imagecreatefromgif(TL_ROOT . '/' . $watermark);
				}
				break;

			case 'jpg':
			case 'jpeg':
				if ($arrGdinfo['JPG Support'] || $arrGdinfo['JPEG Support'])
				{
					$strWatermark = imagecreatefromjpeg(TL_ROOT . '/' . $watermark);
				}
				break;

			case 'png':
				if ($arrGdinfo['PNG Support'])
				{
					$strWatermark = imagecreatefrompng(TL_ROOT . '/' . $watermark);
				}
				break;
		}

		// Image could not be read
		if (!$strWatermark)
		{
			return $image;
		}

		switch( $position )
		{
			case 'tl':
				$x = 0;
				$y = 0;
				break;

			case 'tc':
				$x = ($objFile->width/2) - ($objWatermark->width/2);
				$y = 0;
				break;

			case 'tr':
				$x = $objFile->width - $objWatermark->width;
				$y = 0;
				break;

			case 'cc':
				$x = ($objFile->width/2) - ($objWatermark->width/2);
				$y = ($objFile->height/2) - ($objWatermark->height/2);
				break;

			case 'bl':
				$x = 0;
				$y = $objFile->height - $objWatermark->height;
				break;

			case 'bc':
				$x = ($objFile->width/2) - ($objWatermark->width/2);
				$y = $objFile->height - $objWatermark->height;
				break;

			case 'br':
			default:
				$x = $objFile->width - $objWatermark->width;
				$y = $objFile->height - $objWatermark->height;
				break;
		}

		imagecopy($strImage, $strWatermark, $x, $y, 0, 0, $objWatermark->width, $objWatermark->height);

		// Fallback to PNG if GIF ist not supported
		if ($objFile->extension == 'gif' && !$arrGdinfo['GIF Create Support'])
		{
			$objFile->extension = 'png';
		}

		// Create the new image
		switch ($objFile->extension)
		{
			case 'gif':
				imagegif($strImage, TL_ROOT . '/' . $strCacheName);
				break;

			case 'jpg':
			case 'jpeg':
				imagejpeg($strImage, TL_ROOT . '/' . $strCacheName, (!$GLOBALS['TL_CONFIG']['jpgQuality'] ? 80 : $GLOBALS['TL_CONFIG']['jpgQuality']));
				break;

			case 'png':
				imagepng($strImage, TL_ROOT . '/' . $strCacheName);
				break;
		}

		// Destroy the temporary images
		imagedestroy($strImage);
		imagedestroy($strWatermark);

		// Resize the original image
		if ($target)
		{
			$this->import('Files');
			$this->Files->rename($strCacheName, $target);

			return $target;
		}

		// Set the file permissions when the Safe Mode Hack is used
		if ($GLOBALS['TL_CONFIG']['useFTP'])
		{
			$this->import('Files');
			$this->Files->chmod($strCacheName, 0644);
		}

		// Return the path to new image
		return $strCacheName;
	}


	/**
	 * Hook callback for changelanguage extension to support language switching on product reader page
	 *
	 * @param  array
	 * @param  string
	 * @param  array
	 * @return array
	 */
	public function translateProductUrls($arrGet, $strLanguage, $arrRootPage)
	{
		if ($this->Input->get('product') != '')
		{
			$arrGet['url']['product'] = $this->Input->get('product');
		}
		elseif ($this->Input->get('step') != '')
		{
			$arrGet['url']['step'] = $this->Input->get('step');
		}
		elseif ($this->Input->get('uid') != '')
		{
			$arrGet['get']['uid'] = $this->Input->get('uid');
		}

		return $arrGet;
	}
	
	
	/**
	 * Use generatePage Hook to inject messages if they have not been included in a module.
	 */
	public function injectMessages()
	{
		$strMessages = '';
		$arrGroups = array('ISO_ERROR', 'ISO_CONFIRM', 'ISO_INFO');

		foreach ($arrGroups as $strGroup)
		{
			if (!is_array($_SESSION[$strGroup]))
			{
				continue;
			}

			$strClass = strtolower($strGroup);

			foreach ($_SESSION[$strGroup] as $strMessage)
			{
				$strMessages .= sprintf('<p class="%s">%s</p>', $strClass, specialchars($strMessage));
			}

			$_SESSION[$strGroup] = array();
		}

		$strMessages = trim($strMessages);

		if (strlen($strMessages))
		{
			$GLOBALS['TL_MOOTOOLS'][] = "
<script type=\"text/javascript\">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function()
{
	Isotope.displayBox('<div class=\"iso_message\">" . $strMessages . "</div>', true);
});
//--><!]]>
</script>" ;
		}
	}
}

