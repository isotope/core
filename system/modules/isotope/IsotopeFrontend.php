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
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class IsotopeFrontend
 * 
 * Provide methods to handle Isotope front end components.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 * @author     Yanick Witschi <yanick.witschi@certo-net.ch>
 */
class IsotopeFrontend extends Frontend
{

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;
	
	/**
	 * Cached reader page id's
	 * @var array
	 */
	protected static $arrReaderPageIds = array();


	/**
	 * Import the Isotope object
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('Isotope');
	}


	/**
	 * Get shipping and payment surcharges from cart object
	 * @param array
	 * @return array
	 */
	public function getShippingAndPaymentSurcharges($arrSurcharges)
	{
		if ($this->Isotope->Cart->hasShipping)
		{
			$arrSurcharge = $this->Isotope->Cart->Shipping->getSurcharge($this->Isotope->Cart);
			
			if ($arrSurcharge !== false)
			{
				$arrSurcharges[] = $arrSurcharge;
			}
		}

		if ($this->Isotope->Cart->hasPayment)
		{
			$arrSurcharge = $this->Isotope->Cart->Payment->getSurcharge($this->Isotope->Cart);
			
			if ($arrSurcharge !== false)
			{
				$arrSurcharges[] = $arrSurcharge;
			}
		}

		return $arrSurcharges;
	}


	/**
	 * Callback for add_to_cart button
	 * @param object
	 * @param object
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
	 * Replaces Isotope-specific InsertTags in Frontend
	 * @param string
	 * @return mixed
	 */
	public function replaceIsotopeTags($strTag)
	{
		$arrTag = trimsplit('::', $strTag);

		if ($arrTag[0] == 'isotope')
		{
			switch ($arrTag[1])
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
					{
						return '';
					}

					return $intCount == 1 ? ('('.$GLOBALS['TL_LANG']['ISO']['productSingle'].')') : sprintf(('('.$GLOBALS['TL_LANG']['ISO']['productMultiple'].')'), $intCount);
					break;

				case 'cart_products_label';
					$intCount = $this->Isotope->Cart->products;

					if (!$intCount)
					{
						return '';
					}

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
		elseif ($arrTag[0] == 'order')
		{
			$objOrder = new IsotopeOrder();
			
			if ($objOrder->findBy('uniqid', $this->Input->get('uid')))
			{				
				return $objOrder->{$arrTag[1]};
			}
			
			return '';
		}
		elseif ($arrTag[0] == 'product')
		{
			// 2 possible use cases:
			// {{product::attribute}}				- gets the data of the current product (GET parameter "product")
			// {{product::attribute::product_id}}	- gets the data of the specified product ID
			
			$objProduct = (count($arrTag) == 3) ? self::getProduct($arrTag[2]) : self::getProductByAlias($this->Input->get('product'));
			
			return ($objProduct !== null) ? $objProduct->{$arrTag[1]} : '';
		}

		return false;
	}


	/**
	 * Add the navigation trail CSS class to pages belonging to the active product
	 * @param object
	 * @link http://www.contao.org/hooks.html#parseTemplate
	 */
	public function fixNavigationTrail(&$objTemplate)
	{
		// Unset hook to prevent further execution on non-reader pages
		if ($this->Input->get('product') == '')
		{
			unset($GLOBALS['TL_HOOKS']['parseTemplate'][array_search(array('IsotopeFrontend', 'fixNavigationTrail'), $GLOBALS['TL_HOOKS']['parseTemplate'])]);
			return;
		}
		
		if (substr($objTemplate->getName(), 0, 4) == 'nav_')
		{
			static $arrTrail = null;

			// Only fetch the product once. getProductByAlias will return null if the product is not found.
			if ($arrTrail == null)
			{
				$arrTrail = array();
				$objProduct = self::getProductByAlias($this->Input->get('product'));

				foreach ($objProduct->categories as $pageId)
				{
					$objPage = $this->getPageDetails($pageId);
					$arrTrail = array_merge($arrTrail, $objPage->trail);
				}

				$arrTrail = array_unique($arrTrail);
			}

			if (count($arrTrail))
			{
				$arrItems = $objTemplate->items;

				foreach ($arrItems as $k => $arrItem)
				{
					if (in_array($arrItem['id'], $arrTrail) && strpos($arrItem['class'], 'trail') === false)
					{
						$arrItems[$k]['class'] .= ' trail';
					}
				}

				$objTemplate->items = $arrItems;
			}
		}
	}


	/**
	 * Apply a watermark to an image
	 * @param string
	 * @param string
	 * @param string
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

		$objWatermark = new File($watermark);

		// Load watermark
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

		switch ($position)
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
	 * @param array
	 * @param string
	 * @param array
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
	 * Use generatePage Hook to inject messages if they have not been included in a module
	 */
	public function injectMessages()
	{
		$strMessages = IsotopeFrontend::getIsotopeMessages();

		if ($strMessages != '')
		{
			list(,$startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();

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
	 * Return all error, confirmation and info messages as HTML string
	 * @return string
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
	 * @param integer Database ID
	 * @param string Form ID (FORM SUBMIT)
	 * @param array	Form config that gets merged with the form data from the database
	 * @return object|null
	 */
	public function prepareForm($intId, $strFormId, $arrConfig=array())
	{
		$this->loadDataContainer('tl_form');
		$this->loadDataContainer('tl_form_field');
		
		$objForm = new stdClass();
		$objForm->arrHidden     = array();
		$objForm->arrFields	    = array();
		$objForm->arrFormData   = array();
		$objForm->arrFiles      = array();
		$objForm->blnSubmitted  = false;
		$objForm->blnHasErrors  = false;
		$objForm->blnHasUploads	= false;

		$objForm->arrData = array_merge($this->Database->execute("SELECT * FROM tl_form WHERE id=".(int)$intId)->fetchAssoc(), $arrConfig);

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
			
			// make sure "name" is set because not all form fields do need it and it would thus overwrite the array indexes
			$arrData['name'] = ($arrData['name']) ? $arrData['name'] : 'field_' . $arrData['id'];
			
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

		$strAttributes = '';
		$arrAttributes = deserialize($objForm->arrData['attributes'], true);

		// Form attributes
		if (strlen($arrAttributes[1]))
		{
			$strAttributes .= ' ' . $arrAttributes[1];
		}

		$objForm->attributes = $strAttributes;
		$objForm->enctype = $objForm->blnHasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';

		return $objForm;
	}


	/**
	 * Generate download attributes
	 * @param string
	 * @param array
	 * @param array
	 * @return string
	 * @see IsotopeProduct::generateAttribute()
	 */
	public function generateDownloadAttribute($attribute, $arrData, $arrFiles)
	{
		// Return if there are no files
		if (!is_array($arrFiles) || count($arrFiles) < 1)
		{
			return '';
		}

		$file = $this->Input->get('file', true);

		// Send the file to the browser
		if ($file != '' && (in_array($file, $arrFiles) || in_array(dirname($file), $arrFiles)) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file)))
		{
			$this->sendFileToBrowser($file);
		}

		$files = array();
		$auxDate = array();

		$allowedDownload = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['allowedDownload']));

		// Get all files
		foreach ($arrFiles as $file)
		{
			if (isset($files[$file]) || !file_exists(TL_ROOT . '/' . $file))
			{
				continue;
			}

			// Single files
			if (is_file(TL_ROOT . '/' . $file))
			{
				$objFile = new File($file);

				if (in_array($objFile->extension, $allowedDownload) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($file)))
				{
					$this->parseMetaFile(dirname($file), true);
					$arrMeta = $this->arrMeta[$objFile->basename];

					if ($arrMeta[0] == '')
					{
						$arrMeta[0] = specialchars($objFile->basename);
					}

					$files[$file] = array
					(
						'link' => $arrMeta[0],
						'title' => $arrMeta[0],
						'href' => $this->Environment->request . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($this->Environment->request, '?') !== false) ? '&amp;' : '?') . 'file=' . $this->urlEncode($file),
						'caption' => $arrMeta[2],
						'filesize' => $this->getReadableSize($objFile->filesize, 1),
						'icon' => TL_FILES_URL . 'system/themes/' . $this->getTheme() . '/images/' . $objFile->icon,
						'mime' => $objFile->mime,
						'meta' => $arrMeta
					);

					$auxDate[] = $objFile->mtime;
				}

				continue;
			}

			$subfiles = scan(TL_ROOT . '/' . $file);
			$this->parseMetaFile($file);

			// Folders
			foreach ($subfiles as $subfile)
			{
				if (is_dir(TL_ROOT . '/' . $file . '/' . $subfile))
				{
					continue;
				}

				$objFile = new File($file . '/' . $subfile);

				if (in_array($objFile->extension, $allowedDownload) && !preg_match('/^meta(_[a-z]{2})?\.txt$/', basename($subfile)))
				{
					$arrMeta = $this->arrMeta[$objFile->basename];

					if ($arrMeta[0] == '')
					{
						$arrMeta[0] = specialchars($objFile->basename);
					}

					$files[$file . '/' . $subfile] = array
					(
						'link' => $arrMeta[0],
						'title' => $arrMeta[0],
						'href' => $this->Environment->request . (($GLOBALS['TL_CONFIG']['disableAlias'] || strpos($this->Environment->request, '?') !== false) ? '&amp;' : '?') . 'file=' . $this->urlEncode($file . '/' . $subfile),
						'caption' => $arrMeta[2],
						'filesize' => $this->getReadableSize($objFile->filesize, 1),
						'icon' => 'system/themes/' . $this->getTheme() . '/images/' . $objFile->icon,
						'meta' => $arrMeta
					);

					$auxDate[] = $objFile->mtime;
				}
			}
		}

		// Sort array
		switch ($arrData['eval']['sortBy'])
		{
			default:
			case 'name_asc':
				uksort($files, 'basename_natcasecmp');
				break;

			case 'name_desc':
				uksort($files, 'basename_natcasercmp');
				break;

			case 'date_asc':
				array_multisort($files, SORT_NUMERIC, $auxDate, SORT_ASC);
				break;

			case 'date_desc':
				array_multisort($files, SORT_NUMERIC, $auxDate, SORT_DESC);
				break;

			case 'meta':
				$arrFiles = array();
				foreach ($this->arrAux as $k)
				{
					if (strlen($k))
					{
						$arrFiles[] = $files[$k];
					}
				}
				$files = $arrFiles;
				break;
		}

		$objTemplate = new FrontendTemplate('ce_downloads');
		$objTemplate->class = $attribute;
		$objTemplate->files = array_values($files);

		return $objTemplate->parse();
	}


	/**
	 * Shortcut for a single product by ID or from database result
	 * @param Database_Result|int
	 * @param integer
	 * @param boolean
	 * @return IsotopeProduct|null
	 */
	public static function getProduct($objProductData, $intReaderPage=0, $blnCheckAvailability=true)
	{
		if (is_numeric($objProductData))
		{
			$time = time();
			$Database = Database::getInstance();
			
			$objProductData = $Database->prepare(IsotopeProduct::getSelectStatement() . "
													WHERE p1.language='' AND p1.id=?"
													. (BE_USER_LOGGED_IN ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)"))
									   ->execute($objProductData);
		}

		if (!($objProductData instanceof Database_Result) || !$objProductData->numRows)
		{
			return null;
		}

		$strClass = $GLOBALS['ISO_PRODUCT'][$objProductData->product_class]['class'];

		try
		{
			$objProduct = new $strClass($objProductData->row());
		}
		catch (Exception $e)
		{
			return null;
		}

		if ($blnCheckAvailability && !$objProduct->available)
		{
			return null;
		}

		$objProduct->reader_jumpTo = $intReaderPage;
		return $objProduct;
	}


	/**
	 * Shortcut for a single product by alias (from url?)
	 * @param string
	 * @param integer
	 * @param boolean
	 * @return IsotopeProduct|null
	 */
	public static function getProductByAlias($strAlias, $intReaderPage=0, $blnCheckAvailability=true)
	{
		$time = time();
		$Database = Database::getInstance();

		$objProductData = $Database->prepare(IsotopeProduct::getSelectStatement() . "
												WHERE p1.pid=0 AND p1.language='' AND p1." . (is_numeric($strAlias) ? 'id' : 'alias') . "=?"
												. (BE_USER_LOGGED_IN ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)"))
								   ->limit(1)
								   ->executeUncached($strAlias);

		return self::getProduct($objProductData, $intReaderPage, $blnCheckAvailability);
	}


	/**
	 * Generate products from database result or array of IDs
	 * @param Database_Result|array
	 * @param integer
	 * @param boolean
	 * @param array
	 * @param array
	 * @return array
	 */
	public static function getProducts($objProductData, $intReaderPage=0, $blnCheckAvailability=true, array $arrFilters=array(), array $arrSorting=array())
	{
		// $objProductData can also be an array of product ids
		if (is_array($objProductData) && count($objProductData))
		{
			$time = time();
			$Database = Database::getInstance();

			$objProductData = $Database->execute(IsotopeProduct::getSelectStatement() . "
													WHERE p1.language='' AND p1.id IN (" . implode(',', array_map('intval', $objProductData)) . ")"
													. (BE_USER_LOGGED_IN ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)") . "
													ORDER BY p1.id=" . implode(' DESC, p1.id=', $objProductData) . " DESC");
		}

		if (!($objProductData instanceof Database_Result) || !$objProductData->numRows)
		{
			return array();
		}

		$arrProducts = array();

		while ($objProductData->next())
		{
			$objProduct = IsotopeFrontend::getProduct($objProductData, $intReaderPage, $blnCheckAvailability);

			if ($objProduct !== null)
			{
				$arrProducts[$objProductData->id] = $objProduct;
			}
		}

		if (!empty($arrFilters))
		{
			global $filterConfig;
			$filterConfig = $arrFilters;
			$arrProducts = array_filter($arrProducts, array(self, 'filterProducts'));
		}

		// $arrProducts can be empty if the filter removed all records
		if (!empty($arrSorting) && !empty($arrProducts))
		{
			$arrParam = array();
			$arrData = array();

			foreach ($arrSorting as $strField => $arrConfig)
			{
				foreach ($arrProducts as $id => $objProduct)
				{
					// Both SORT_STRING and SORT_REGULAR are case sensitive, strings starting with a capital letter will come before strings starting with a lowercase letter.
					// To perform a case insensitive search, force the sorting order to be determined by a lowercase copy of the original value.
					$arrData[$strField][$id] = strtolower(str_replace('"', '', $objProduct->$strField));
				}

				$arrParam[] = &$arrData[$strField];
				
				foreach( $arrConfig as $k => $v )
				{
					$arrParam[] = $v;
				}
			}
			
			$strEval = '';
			foreach( $arrParam as $k => $v )
			{
				$strEval .= '$arrParam[' . $k . '], ';
			}
			
			// Add product array as the last item. This will sort the products array based on the sorting of the passed in arguments.
			eval('array_multisort(' . $strEval . '$arrProducts);');
		}

		return $arrProducts;
	}


	/**
	 * Callback function to filter products
	 * @param object
	 * @return boolean
	 * @see array_filter()
	 */
	private static function filterProducts($objProduct)
	{
		global $filterConfig;

		if (!is_array($filterConfig) || !count($filterConfig))
		{
			return true;
		}

		$arrGroups = array();

		foreach ($filterConfig as $filter)
		{
			$varValue = $objProduct->{$filter['attribute']};
			$blnMatch = false;

			// If the attribute is not set for this product, we will ignore this attribute
			if ($varValue === null)
			{
				continue;
			}
			elseif (is_array($varValue))
			{
				$varValue = http_build_query($varValue);
			}

			$operator = self::convertFilterOperator($filter['operator'], 'PHP');

			switch( $operator )
			{
				case 'stripos':
					if (stripos($varValue, $filter['value']) !== false)
					{
						$blnMatch = true;
					}
					break;

				default:
					if (eval('return $varValue '.$operator.' $filter[\'value\'];'))
					{
						$blnMatch = true;
					}
					break;
			}

			if ($filter['group'])
			{
				$arrGroups[$filter['group']] = $arrGroups[$filter['group']] ? $arrGroups[$filter['group']] : $blnMatch;
			}
			elseif (!$blnMatch)
			{
				return false;
			}
		}

		if (count($arrGroups) && in_array(false, $arrGroups))
		{
			return false;
		}

		return true;
	}


	/**
	 * Convert a filter operator for PHP or SQL
	 * @param string
	 * @param string
	 * @return string
	 */
	public static function convertFilterOperator($operator, $mode='PHP')
	{
		switch ($operator)
		{
			case 'like':
			case 'search':
				return $mode == 'SQL' ? 'REGEXP' : 'stripos';

			case '>':
			case 'gt':
				return '>';

			case '<':
			case 'lt':
				return '<';

			case '>=':
			case '=>':
			case 'gte':
				return '>=';

			case '<=':
			case '=<':
			case 'lte':
				return '<=';

			case '!=':
			case 'neq':
			case 'not':
				return '!=';

			case '=':
			case '==':
			case 'eq':
			default:
				return $mode == 'SQL' ? '=' : '==';
		}
	}
	
	
	/**
	 * Generate row class for an array
	 * @param array data rows
	 * @param string class prefix (e.g. "product")
	 * @param int number of columns
	 * @return array
	 */
	public static function generateRowClass($arrData, $strClass='', $strKey='rowClass', $intColumns=0, $options=125)
	{
		$strClassPrefix = $strClass == '' ? '' : $strClass.'_';
		$hasColumns = ($intColumns > 1);
		$total = count($arrData) - 1;
		$current = 0;

		if ($hasColumns)
		{
			$row = 0;
			$col = 0;
			$rows = ceil(count($arrData) / $intColumns) - 1;
			$cols = $intColumns - 1;			
		}

		foreach ($arrData as $k => $varValue)
		{
			if ($hasColumns && $current > 0 && $current % $intColumns == 0)
			{
				++$row;
				$col = 0;
			}

			$class = '';

			if ($options & ISO_CLASS_NAME)
			{
				$class .= ' ' . $strClass;
			}
			
			if ($options & ISO_CLASS_KEY)
			{
				$class .= ' ' . $strClassPrefix . $k;
			}
			
			if ($options & ISO_CLASS_COUNT)
			{
				$class .= ' ' . $strClassPrefix . $current;
			}
			
			if ($options & ISO_CLASS_EVENODD)
			{
				$class .= ' ' . (($options & ISO_CLASS_NAME || $options & ISO_CLASS_ROW) ? $strClassPrefix : '') . ($current%2 ? 'even' : 'odd');
			}
			
			if ($options & ISO_CLASS_FIRSTLAST)
			{
				$class .= ($current == 0 ? ' ' . $strClassPrefix . 'first' : '') . ($current == $total ? ' ' . $strClassPrefix . 'last' : '');
			}
			
			if ($hasColumns && $options & ISO_CLASS_ROW)
			{
				$class .= ' row_'.$row . ($row%2 ? ' row_even' : ' row_odd') . ($row == 0 ? ' row_first' : '') . ($row == $rows ? ' row_last' : '');
			}
			
			if ($hasColumns && $options & ISO_CLASS_COL)
			{
				$class .= ' col_'.$col . ($col%2 ? ' col_even' : ' col_odd') . ($col == 0 ? ' col_first' : '') . ($col == $cols ? ' col_last' : '');
			}

			if (is_array($varValue))
			{
				$arrData[$k][$strKey] = trim($arrData[$k][$strKey] . ' ' . $class);
			}
			elseif (is_object($varValue))
			{
				$varValue->$strKey = trim($varValue->$strKey . ' ' . $class);
				$arrData[$k] = $varValue;
			}
			else
			{
				$arrData[$k] = '<span class="' . $class . '">' . $varValue . '</span>';
			}
			
			++$col;
			++$current;
		}
		
		return $arrData;
	}


	/**
	 * Format surcharge prices
	 * @param array
	 * @return array
	 */
	public static function formatSurcharges($arrSurcharges)
	{
		$Isotope = Isotope::getInstance();

		foreach ($arrSurcharges as $k => $arrSurcharge)
		{
			$arrSurcharges[$k]['price']			= $Isotope->formatPriceWithCurrency($arrSurcharge['price']);
			$arrSurcharges[$k]['total_price']	= $Isotope->formatPriceWithCurrency($arrSurcharge['total_price']);
			$arrSurcharges[$k]['rowClass']		= trim('foot_'.($k+1) . ' ' . $arrSurcharge[$k]['rowClass']);
		}
		
		return $arrSurcharges;
	}


	/**
	 * Adds the product urls to the array so they get indexed when the search index is being rebuilt in the maintenance module
	 * @param array absolute page urls
	 * @param int root page id
	 * @return array extended array of absolute page urls
	 */
	public function addProductsToSearchIndex($arrPages, $intRootPageId=0)
	{
		$time = time();
		$arrIsotopeProductPages = array();
		
		// get all products available
		$objProducts = $this->Database->execute(IsotopeProduct::getSelectStatement() . " WHERE p1.language='' AND p1.pid=0 AND p1.published=1 AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)");
		$arrProducts = self::getProducts($objProducts);
		
		if (!count($arrProducts))
		{
			return;
		}
		
		// if we have a root page id (sitemap.xml e.g.) we have to make sure we only consider categories in this tree
		$arrAllowedPageIds = array();
		if ($intRootPageId > 0)
		{
			$arrAllowedPageIds = $this->getChildRecords($intRootPageId, 'tl_page');
		}
		
		// get all the categories for every product
		foreach ($arrProducts as $objProduct)
		{
			$arrCategories = $objProduct->categories;
			
			// filter those that are allowed
			$arrCategories = array_intersect($arrCategories, $arrAllowedPageIds);
			
			if (!is_array($arrCategories) || !count($arrCategories))
			{
				continue;
			}
			
			if (!is_array($arrCategories) || !count($arrCategories))
			{
				continue;
			}
			
			$objCategoryPages = $this->Database->execute('SELECT * FROM tl_page WHERE id IN(' . implode(',', $arrCategories) . ')');
			
			if (!$objCategoryPages->numRows)
			{
				continue;
			}
			
			while($objCategoryPages->next())
			{
				// set the reader jump to page
				$objProduct->reader_jumpTo = self::getReaderPageId($objCategoryPages);
				
				// generate the front end url
				$arrIsotopeProductPages[] = $this->Environment->base . $objProduct->href_reader;
			}
		}
		
		// the reader page id can be the same for several categories so we have to make sure we only index the product once
		$arrIsotopeProductPages = array_unique($arrIsotopeProductPages);
		
		return array_merge($arrPages, $arrIsotopeProductPages);
	}


	/**
	 * Gets the product reader of a certain page
	 * @param Database_Result|int	page object or page ID
	 * @param int	override setting from a module or content element
	 * @return int	reader page id
	 */
	public static function getReaderPageId($objOriginPage=null, $intOverride=0)
	{
		if ($intOverride > 0)
		{
			return $intOverride;
		}
		
		if ($objOriginPage === null)
		{
			global $objPage;
			$objOriginPage = $objPage;
		}
		
		$intPage = is_object($objOriginPage) ? (int) $objOriginPage->id : (int) $objOriginPage;
		
		// return from cache
		if (isset(self::$arrReaderPageIds[$intPage]))
		{
			return self::$arrReaderPageIds[$intPage];
		}
		
		$objDatabase = Database::getInstance();
		
		if (!is_object($objOriginPage))
		{
			$objOriginPage = $objDatabase->execute("SELECT * FROM tl_page WHERE id=" . $intPage);
		}

		// if the reader page is set on the current page id we return this one
		if ($objOriginPage->iso_setReaderJumpTo > 0)
		{
			self::$arrReaderPageIds[$intPage] = $objOriginPage->iso_readerJumpTo;
			return $objOriginPage->iso_readerJumpTo;
		}
		
		// now move up the page tree until we find a page where the reader is set
		$trail = array();
		$pid = $objOriginPage->pid;
		
		do
		{
			$objParentPage = $objDatabase->execute("SELECT * FROM tl_page WHERE id=" . $pid);

			if ($objParentPage->numRows < 1)
			{
				break;
			}
			
			$trail[] = $objParentPage->id;
			
			if ($objParentPage->iso_setReaderJumpTo > 0)
			{
				// cache the reader page for all trail pages
				self::$arrReaderPageIds = array_merge(self::$arrReaderPageIds, array_fill_keys($trail, $objParentPage->iso_readerJumpTo));

				return $objParentPage->iso_readerJumpTo;
			}

			$pid = $objParentPage->pid;
		}
		while ($pid > 0 && $objParentPage->type != 'root');
		
		// if there is no reader page set at all, we take the current page object
		global $objPage;
		self::$arrReaderPageIds[$intPage] = $objPage->id;
		
		return $objPage->id;
	}
	
	
	/**
	 * Get postal codes from CSV and ranges
	 * @param string
	 * @return array
	 */
	public static function parsePostalCodes($strPostalCodes)
	{
		$arrCodes = array();
		
		foreach (trimsplit(',', $strPostalCodes) as $strCode)
		{
			$arrCode = trimsplit('-', $strCode);
			
			// Ignore codes with more than 1 range
			switch (count($arrCode))
			{
				case 1:
					$arrCodes[] = $arrCode[0];
					break;
					
				case 2:
					$arrCodes = array_merge($arrCodes, range($arrCode[0], $arrCode[1]));
					break;
			}
		}
		
		return $arrCodes;
	}
	
	
	/**
	 * Wait for it
	 * @return bool
	 */
	public static function setTimeout($intSeconds=5, $intRepeat=12)
	{
		if (!isset($_SESSION['ISO_TIMEOUT']))
		{
			$_SESSION['ISO_TIMEOUT'] = $intRepeat;
		}
		else
		{
			$_SESSION['ISO_TIMEOUT'] = $_SESSION['ISO_TIMEOUT'] - 1;
		}

		if ($_SESSION['ISO_TIMEOUT'] > 0)
		{
			// Reload page every 5 seconds
			$GLOBALS['TL_HEAD'][] = '<meta http-equiv="refresh" content="' . $intSeconds . ',' . $this->Environment->base . $this->Environment->request . '">';
	
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Cancel the timeout (clear session)
	 */
	public static function clearTimeout()
	{
		unset($_SESSION['ISO_TIMEOUT']);
	}
}

