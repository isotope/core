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
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Yanick Witschi <yanick.witschi@certo-net.ch>
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
			$_SESSION['ISO_CONFIRM'][] = $GLOBALS['TL_LANG']['MSC']['addedToCart'];
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
	public static function watermarkImage($image, $watermark, $position='br')
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
				$objCallback = (in_array('getInstance', get_class_methods($callback[0]))) ? call_user_func(array($callback[0], 'getInstance')) : new $callback[0]();
				$return = $objCallback->$callback[1]($image, $watermark);

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
			$objFiles = Files::getInstance();
			$objFiles->rename($strCacheName, $target);

			return $target;
		}

		// Set the file permissions when the Safe Mode Hack is used
		if ($GLOBALS['TL_CONFIG']['useFTP'])
		{
			$objFiles = Files::getInstance();
			$objFiles->chmod($strCacheName, 0644);
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
		$strMessages = IsotopeFrontend::getIsotopeMessages();
		list(,$startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();

		if ($strMessages != '')
		{
			$GLOBALS['TL_MOOTOOLS'][] = "
$startScript
window.addEvent('domready', function()
{
	Isotope.displayBox('" . $strMessages . "', true);
});
$endScript";
		}
	}


	/**
	 * Return all error, confirmation and info messages as HTML.
	 * @param	void
	 * @return	string
	 */
	public static function getIsotopeMessages()
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
				$strMessages .= sprintf('<p class="%s">%s</p>%s', $strClass, $strMessage, "\n");
			}

			$_SESSION[$strGroup] = array();
		}

		$strMessages = trim($strMessages);

		if (strlen($strMessages))
		{
			// Automatically disable caching if a message is available
			global $objPage;
			$objPage->cache = 0;

			$strMessages = '<div class="iso_message">' . $strMessages . '</div>';
		}

		return $strMessages;
	}
	
	
	/**
	 * Get html & javascript tags depending on output format (Contao 2.10)
	 * @param boolean
	 * @return array
	 */
	public static function getElementAndScriptTags($blnAjax=false)
	{
		global $objPage;

		switch ($objPage->outputFormat)
		{
			case 'html5':
				return array('>', '<script>', '</script>');

			case 'xhtml':
			default:
				if ($blnAjax)
				{
					return array(' />', '<script type="text/javascript">', '</script>');
				}
				else
				{
					return array(' />', '<script type="text/javascript">'."\n".'<!--//--><![CDATA[//><!--', '//--><!]]>'."\n".'</script>');
				}
		}
	}
	
	
	/**
	 * Prepare form fields from a form generator form ID
	 * Useful if you want to give the user the possibility to use a custom form for a certain action (e.g. order conditions)
	 *
	 * @param	int		database ID
	 * @param	string	form id (FORM SUBMIT)
	 * @return	object
	 */
	public function prepareForm($intId, $strFormId)
	{
		$objForm = new stdClass();
		$objForm->arrHidden		= array();
		$objForm->arrFields		= array();
		$objForm->arrFormData	= array();
		$objForm->arrFiles		= array();
		$objForm->blnSubmitted	= false;
		$objForm->blnHasErrors	= false;
		$objForm->blnHasUploads	= false;

		$objForm->arrData		= $this->Database->execute("SELECT * FROM tl_form WHERE id=".(int)$intId)->fetchAssoc();
		
		// Form not found
		if (!$objForm->arrData['id'])
		{
			return null;
		}

		// Get all form fields
		$objFields = $this->Database->execute("SELECT * FROM tl_form_field WHERE pid={$objForm->arrData['id']} AND invisible='' ORDER BY sorting");

		$row = 0;
		$max_row = $objFields->numRows;

		while ($objFields->next())
		{
			$strClass = $GLOBALS['TL_FFL'][$objFields->type];

			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))
			{
				continue;
			}

			$arrData = $objFields->row();
			$arrData['decodeEntities'] = true;
			$arrData['allowHtml'] = $objForm->arrData['allowTags'];
			$arrData['rowClass'] = 'row_'.$row . (($row == 0) ? ' row_first' : (($row == ($max_row - 1)) ? ' row_last' : '')) . ((($row % 2) == 0) ? ' even' : ' odd');
			$arrData['tableless'] = $objForm->arrData['tableless'];

			// Increase the row count if its a password field
			if ($objFields->type == 'password')
			{
				++$row;
				++$max_row;

				$arrData['rowClassConfirm'] = 'row_'.$row . (($row == ($max_row - 1)) ? ' row_last' : '') . ((($row % 2) == 0) ? ' even' : ' odd');
			}

			$objWidget = new $strClass($arrData);
			$objWidget->required = $objFields->mandatory ? true : false;

			// HOOK: load form field callback
			if (isset($GLOBALS['TL_HOOKS']['loadFormField']) && is_array($GLOBALS['TL_HOOKS']['loadFormField']))
			{
				foreach ($GLOBALS['TL_HOOKS']['loadFormField'] as $callback)
				{
					$this->import($callback[0]);
					$objWidget = $this->$callback[0]->$callback[1]($objWidget, $strFormId, $objForm->arrData);
				}
			}

			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $strFormId)
			{
				$objForm->blnSubmitted = true;
				$objWidget->validate();

				// HOOK: validate form field callback
				if (isset($GLOBALS['TL_HOOKS']['validateFormField']) && is_array($GLOBALS['TL_HOOKS']['validateFormField']))
				{
					foreach ($GLOBALS['TL_HOOKS']['validateFormField'] as $callback)
					{
						$this->import($callback[0]);
						$objWidget = $this->$callback[0]->$callback[1]($objWidget, $strFormId, $objForm->arrData);
					}
				}

				if ($objWidget->hasErrors())
				{
					$objForm->blnHasErrors = true;
				}

				// Store current value in the session
				elseif ($objWidget->submitInput())
				{
					$objForm->arrFormData[$objFields->name]	= $objWidget->value;
					$_SESSION['FORM_DATA'][$objFields->name]		= $objWidget->value;

				}

				// Store file uploads
				elseif ($objWidget instanceof uploadable)
				{
					$objForm->arrFiles[$objFields->name]	= $_SESSION['FILES'][$objFields->name];
				}

				unset($_POST[$objFields->name]);
			}

			if ($objWidget instanceof uploadable)
			{
				$objForm->blnHasUploads = true;
			}

			if ($objWidget instanceof FormHidden)
			{
				--$max_row;
				$objForm->arrHidden[$arrData['name']]	= $objWidget;
				continue;
			}
			
			$objForm->arrFields[$arrData['name']]		= $objWidget;

			++$row;
		}

		// form attributes
		$strAttributes = '';
		$arrAttributes = deserialize($objForm->arrData['attributes'], true);
		if (strlen($arrAttributes[1]))
		{
			$strAttributes .= ' ' . $arrAttributes[1];
		}

		$objForm->attributes	= $strAttributes;
		$objForm->enctype		= $objForm->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

		return $objForm;
	}
}

