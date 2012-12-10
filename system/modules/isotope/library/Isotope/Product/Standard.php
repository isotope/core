<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Isotope\Product;

use Isotope\Interfaces\IsotopeProduct;


/**
 * Class Product
 *
 * Provide methods to handle Isotope products.
 * @copyright  Isotope eCommerce Workgroup 2009-2012
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */
class Standard extends \Controller implements IsotopeProduct
{

	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_iso_products';

	/**
	 * Data array
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Product type
	 * @var array
	 */
	protected $arrType = array();

	/**
	 * Attributes assigned to this product type
	 * @var array
	 */
	protected $arrAttributes = array();

	/**
	 * Variant attributes assigned to this product type
	 * @var array
	 */
	protected $arrVariantAttributes = array();

	/**
	 * Product Options
	 * @var array
	 */
	protected $arrOptions = array();

	/**
	 * Product Options of all variants
	 * @var array
	 */
	protected $arrVariantOptions = null;

	/**
	 * Downloads for this product
	 * @var array
	 */
	protected $arrDownloads = null;

	/**
	 * Unique form ID
	 * @var string
	 */
	protected $formSubmit = 'iso_product';

	/**
	 * Name of the Javascript class
	 * @var string
	 */
	protected $ajaxClass = 'IsotopeProduct';

	/**
	 * For option widgets, helps determine the encoding type for a form
	 * @var boolean
	 */
	protected $hasUpload = false;

	/**
	 * For option widgets, don't submit if certain validation(s) fail
	 * @var boolean
	 */
	protected $doNotSubmit = false;

	/**
	 * Lock products from changes and don't calculate prices
	 * @var boolean
	 */
	protected $blnLocked = false;

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;


	/**
	 * Construct the object
	 * @param array
	 * @param array
	 * @param boolean
	 */
	public function __construct($arrData, $arrOptions=null, $blnLocked=false, $intQuantity=1)
	{
		parent::__construct();

		$this->Database = \Database::getInstance();
		$this->Isotope = \Isotope\Isotope::getInstance();

		if (FE_USER_LOGGED_IN === true)
		{
			$this->User = \FrontendUser::getInstance();
		}

		$this->blnLocked = $blnLocked;

		if ($arrData['pid'] > 0)
		{
			$this->arrData = $this->Database->execute("SELECT * FROM tl_iso_products WHERE id={$arrData['pid']}")->fetchAssoc();
		}
		else
		{
			$this->arrData = $arrData;
		}

		$this->arrOptions = is_array($arrOptions) ? $arrOptions : array();

		if (!$this->arrData['type'])
		{
			return;
		}

		$this->formSubmit = 'iso_product_' . $this->arrData['id'];
		$this->arrType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id=".(int)$this->arrData['type'])->fetchAssoc();
		$this->arrAttributes = $this->getSortedAttributes($this->arrType['attributes']);
		$this->arrVariantAttributes = $this->hasVariants() ? $this->getSortedAttributes($this->arrType['variant_attributes']) : array();
		$this->arrCache['list_template'] = $this->arrType['list_template'];
		$this->arrCache['reader_template'] = $this->arrType['reader_template'];
		$this->arrCache['quantity_requested'] = $intQuantity;

		// !HOOK: allow to customize attributes
		if (isset($GLOBALS['ISO_HOOKS']['productAttributes']) && is_array($GLOBALS['ISO_HOOKS']['productAttributes']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['productAttributes'] as $callback)
			{
				$objCallback = \System::importStatic($callback[0]);
				$objCallback->$callback[1]($this->arrAttributes, $this->arrVariantAttributes, $this);
			}
		}

		// Remove attributes not in this product type
		foreach ($this->arrData as $attribute => $value)
		{
			if (!in_array($attribute, $this->arrAttributes) && !in_array($attribute, $this->arrVariantAttributes) && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['legend'] != '')
			{
				unset($this->arrData[$attribute]);
			}
		}

		if ($arrData['pid'] > 0)
		{
			$this->loadVariantData($arrData);
		}
		elseif (!$this->isLocked())
		{
			// findPrice() is called by loadVariantData()
			$this->findPrice();
		}

		// Make sure the locked attributes are set
		if ($this->isLocked())
		{
			$this->arrData['sku']	= $arrData['sku'];
			$this->arrData['name']	= $arrData['name'];
			$this->arrData['price']	= $arrData['price'];
		}
	}


	/**
	 * Get a property
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'id':
			case 'pid':
			case 'href_reader':
				return $this->arrData[$strKey];

			case 'variant_ids':
				return $this->getVariantIds();
				break;

			case 'formSubmit':
				return $this->formSubmit;

			case 'original_price':
				return $this->isLocked() ? $this->arrData['price'] : $this->Isotope->calculatePrice($this->arrData['price'], $this, 'original_price', $this->arrData['tax_class']);

			case 'price':
				return $this->isLocked() ? $this->arrData['price'] : $this->Isotope->calculatePrice($this->arrData['price'], $this, 'price', $this->arrData['tax_class']);

			case 'total_price':
				return $this->quantity_requested * $this->price;

			case 'tax_free_price':
				$fltPrice = $this->isLocked() ? $this->arrData['price'] : $this->Isotope->calculatePrice($this->arrData['price'], $this, 'price');

				if ($this->arrData['tax_class'] > 0)
				{
					$objIncludes = $this->Database->prepare("SELECT r.* FROM tl_iso_tax_rate r LEFT JOIN tl_iso_tax_class c ON c.includes=r.id WHERE c.id=?")->execute($this->arrData['tax_class']);

					if ($objIncludes->numRows)
					{
						$arrTaxRate = deserialize($objIncludes->rate);

						// Final price / (1 + (tax / 100)
						if (strlen($arrTaxRate['unit']))
						{
							$fltTax = $fltPrice - ($fltPrice / (1 + (floatval($arrTaxRate['value']) / 100)));
						}

						// Full amount
						else
						{
							$fltTax = floatval($arrTaxRate['value']);
						}

						$fltPrice -= $fltTax;
					}
				}

				return $fltPrice;

			case 'tax_free_total_price':
				return $this->quantity_requested * $this->tax_free_price;

			case 'quantity_requested':
				if (!$this->arrCache[$strKey] && \Input::post('FORM_SUBMIT') == $this->formSubmit)
				{
					$this->arrCache[$strKey] = (int) \Input::post('quantity_requested');
				}

				return $this->arrCache[$strKey] ? $this->arrCache[$strKey] : 1;

			case 'shipping_exempt':
				return ($this->arrData['shipping_exempt'] || $this->arrType['shipping_exempt']) ? true : false;

			case 'available':
				return $this->isAvailable();

			case 'hasDownloads':
				return $this->hasDownloads();

			case 'show_price_tiers':
				return (bool) $this->arrType['show_price_tiers'];

			case 'description_meta':
				return $this->arrData['description_meta'] != '' ? $this->arrData['description_meta'] : ($this->arrData['teaser'] != '' ? $this->arrData['teaser'] : $this->arrData['description']);

			default:
				// Initialize attribute
				if (!isset($this->arrCache[$strKey]))
				{
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['inputType'] == 'mediaManager')
					{
						$strClass = $GLOBALS['ISO_GAL'][(strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery'] : $this->Isotope->Config->gallery)];

						if (!class_exists($strClass))
						{
							$strClass = 'Isotope\Gallery\Standard';
						}

						$objGallery = new $strClass($this->formSubmit . '_' . $strKey, deserialize($this->arrData[$strKey]));
						$objGallery->product_id = ($this->pid ? $this->pid : $this->id);
						$objGallery->href_reader = $this->href_reader;
						$this->arrCache[$strKey] = $objGallery;
					}
					else
					{
						switch ($strKey)
						{
							case 'formatted_price':
								$this->arrCache[$strKey] = $this->Isotope->formatPriceWithCurrency($this->price, false);
								break;

							case 'formatted_original_price':
								$this->arrCache[$strKey] = $this->Isotope->formatPriceWithCurrency($this->original_price, false);
								break;

							case 'formatted_total_price':
								$this->arrCache[$strKey] = $this->Isotope->formatPriceWithCurrency($this->total_price, false);
								break;

							case 'categories':
								$this->arrCache[$strKey] = $this->Database->execute("SELECT page_id FROM tl_iso_product_categories WHERE pid=" . ($this->pid ? $this->pid : $this->id) . " ORDER BY sorting")->fetchEach('page_id');
								break;

							default:
								return isset($this->arrData[$strKey]) ? deserialize($this->arrData[$strKey]) : null;
						}
					}
				}

				return $this->arrCache[$strKey];
		}
	}


	/**
	 * Set a property
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		switch ($strKey)
		{
			case 'reader_jumpTo':

				// Remove the target URL if no page ID is given
				if ($varValue == '' || $varValue < 1)
				{
					$this->arrData['href_reader'] = '';
					break;
				}

				global $objPage;
				$strUrlKey = $this->arrData['alias'] ? $this->arrData['alias'] : ($this->arrData['pid'] ? $this->arrData['pid'] : $this->arrData['id']);

				// make sure the page object is loaded because of the url language feature (e.g. when rebuilding the search index in the back end or ajax actions)
				if (!$objPage)
				{
					$objTargetPage = $this->getPageDetails($varValue);

					if ($objTargetPage === null)
					{
						$this->arrData['href_reader'] = '';
						break;
					}

					$strUrl  = $this->generateFrontendUrl($objTargetPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/product/') . $strUrlKey, $objTargetPage->rootLanguage);
				}
				else
				{
					$strUrl = $this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($varValue)->fetchAssoc(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/product/') . $strUrlKey, $objPage->rootLanguage);
				}

				if ($this->arrData['pid'] > 0)
				{
					$arrOptions = array();

					foreach( $this->arrOptions as $k => $v )
					{
						$arrOptions[] = $k . '=' . urlencode($v);
					}

					$strUrl .= (strpos('?', $strUrl) === false ? '?' : '&amp;') . implode('&amp;', $arrOptions);
				}

				$this->arrData['href_reader'] = $strUrl;
				break;

			case 'reader_jumpTo_Override':
				$this->arrData['href_reader'] = $varValue;
				break;

			case 'sku':
			case 'name':
			case 'price':
				$this->arrData[$strKey] = $varValue;
				break;

			case 'quantity_requested':
				$this->arrCache[$strKey] = $varValue;

				if (!$this->isLocked())
				{
					$this->findPrice();
				}
				break;

			default:
				$this->arrCache[$strKey] = $varValue;
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
	 * Return the current data as associative array
	 * @return array
	 */
	public function getData()
	{
		return $this->arrData;
	}


	/**
	 * Return the product type configuration
	 * @return array
	 */
	public function getType()
	{
		return $this->arrType;
	}


	/**
	 * Return the product attributes
	 * @return array
	 */
	public function getProductAttributes()
	{
		return $this->arrAttributes;
	}


	/**
	 * Return the product variant attributes
	 * @return array
	 */
	public function getVariantAttributes()
	{
		return $this->arrVariantAttributes;
	}


	/**
	 * Return all product and variant attributes
	 * @return array
	 */
	public function getProductAndVariantAttributes()
	{
		return array_unique(array_merge($this->arrAttributes, $this->arrVariantAttributes));
	}


	/**
	 * Return all attributes for this product as array
	 * @return array
	 */
	public function getAttributes()
	{
		$arrData = array();

		foreach ($this->getProductAndVariantAttributes() as $attribute)
		{
			$arrData[$attribute] = $this->$attribute;
		}

		return $arrData;
	}


	/**
	 * Return variant options data
	 * @return array|false
	 */
	public function getVariantOptions()
	{
		if (!$this->hasVariants())
		{
			return false;
		}

		if (!is_array($this->arrVariantOptions))
		{
			$time = time();
			$this->arrVariantOptions = array('current'=>array());

			// Find all possible variant options
			$objVariant = clone $this;
			$objVariants = $this->Database->execute(static::getSelectStatement() . " WHERE p1.pid={$this->arrData['id']} AND p1.language=''"
													. (BE_USER_LOGGED_IN === true ? '' : " AND p1.published='1' AND (p1.start='' OR p1.start<$time) AND (p1.stop='' OR p1.stop>$time)"));

			while ($objVariants->next())
			{
				$objVariant->loadVariantData($objVariants->row(), false);

				if ($objVariant->isAvailable())
				{
					$arrVariantOptions = $objVariant->getOptions(true);

					$this->arrVariantOptions['ids'][] = $objVariant->id;
					$this->arrVariantOptions['options'][$objVariant->id] = $arrVariantOptions;
					$this->arrVariantOptions['variants'][$objVariant->id] = $objVariants->row();

					foreach ($arrVariantOptions as $attribute => $value)
					{
						if (!in_array((string) $value, (array) $this->arrVariantOptions['attributes'][$attribute], true))
						{
							$this->arrVariantOptions['attributes'][$attribute][] = (string) $value;
						}
					}
				}
			}
		}

		return $this->arrVariantOptions;
	}


	/**
	 * Return all available variant IDs of this product
	 * @return array|false
	 */
	public function getVariantIds()
	{
		$arrVariantOptions = $this->getVariantOptions();

		if ($arrVariantOptions === false)
		{
			return false;
		}

		return (array) $arrVariantOptions['ids'];
	}


	/**
	 * Return all downloads for this product
	 * @todo Confirm that files are available
	 * @return array
	 */
	public function getDownloads()
	{
		if (!$this->arrType['downloads'])
		{
			$this->arrDownloads = array();
		}

		// Cache downloads for this product
		elseif (!is_array($this->arrDownloads))
		{
			$this->arrDownloads = $this->Database->execute("SELECT * FROM tl_iso_downloads WHERE pid={$this->arrData['id']} OR pid={$this->arrData['pid']}")->fetchAllAssoc();
		}

		return $this->arrDownloads;
	}


	/**
	 * Return all options, either the raw array or prepared for product listing
	 * @todo I dislike the listing approach, we might find a better solution
	 * @param boolean
	 * @return array
	 */
	public function getOptions($blnRaw=false)
	{
		if ($blnRaw)
		{
			return $this->arrOptions;
		}

		$arrOptions = array();

		foreach ($this->arrOptions as $field => $value)
		{
			if ($value == '' || $value == '-')
				continue;

			$arrOptions[$field] = array
			(
				'label'	=> $this->Isotope->formatLabel('tl_iso_products', $field),
				'value'	=> $this->Isotope->formatValue('tl_iso_products', $field, $value),
			);
		}

		return $arrOptions;
	}


	/**
	 * Set options data
	 * @param array
	 */
	public function setOptions(array $arrOptions)
	{
		$this->arrOptions = $arrOptions;
	}


	/**
	 * Returns true if variants are enabled in the product type, otherwise returns false
	 * @return bool
	 */
	public function hasVariants()
	{
		return (bool) $this->arrType['variants'];
	}


	/**
	 * Returns true if product has variants, and the price is a variant attribute
	 * @return bool
	 */
	public function hasVariantPrices()
	{
		if ($this->hasVariants() && in_array('price', $this->arrVariantAttributes))
		{
			return true;
		}

		return false;
	}


	/**
	 * Returns true if advanced prices are enabled in the product type, otherwise returns false
	 * @return bool
	 */
	public function hasAdvancedPrices()
	{
		return (bool) $this->arrType['prices'];
	}


	/**
	 * Check if a product has downloads
	 * @todo Confirm that files are available
	 * @return array
	 */
	public function hasDownloads()
	{
		// Cache downloads if not yet done
		$this->getDownloads();

		return !empty($this->arrDownloads);
	}


	/**
	 * Returns true if the product is published, otherwise returns false
	 * @bool
	 */
	public function isPublished()
	{
		if (!$this->arrData['published'])
		{
			return false;
		}
		elseif ($this->arrData['start'] > 0 && $this->arrData['start'] > time())
		{
			return false;
		}
		elseif ($this->arrData['stop'] > 0 && $this->arrData['stop'] < time())
		{
			return false;
		}

		return true;
	}


	/**
	 * Returns true if the product is locked (price should not be calculated, e.g. in orders), otherwise returns false
	 * @return bool
	 */
	public function isLocked()
	{
		return $this->blnLocked;
	}


	/**
	 * Returns true if the product is available, otherwise returns false
	 * @return bool
	 */
	public function isAvailable()
	{
		if ($this->isLocked())
		{
			return true;
		}

		if (BE_USER_LOGGED_IN !== true && !$this->isPublished())
		{
			return false;
		}

		// Show to guests only
		if ($this->arrData['guests'] && FE_USER_LOGGED_IN === true && BE_USER_LOGGED_IN !== true && !$this->arrData['protected'])
		{
			return false;
		}

		// Protected product
		if (BE_USER_LOGGED_IN !== true && $this->arrData['protected'])
		{
			if (FE_USER_LOGGED_IN !== true)
			{
				return false;
			}

			$groups = deserialize($this->arrData['groups']);

			if (!is_array($groups) || empty($groups) || !count(array_intersect($groups, $this->User->groups)))
			{
				return false;
			}
		}

		// Check if "advanced price" is available
		if ($this->arrData['price'] === null && (in_array('price', $this->arrAttributes) || in_array('price', $this->arrVariantAttributes)))
		{
			return false;
		}

		return true;
	}


	/**
	 * Generate a product template
	 * @param string
	 * @param object
	 * @return string
	 */
	public function generate($strTemplate, &$objModule)
	{
		global $objPage;

		$this->formSubmit = (($objModule instanceof \ContentElement) ? 'cte' : 'fmd') . $objModule->id . '_product_' . ($this->pid ? $this->pid : $this->id);
		$this->validateVariant();

		$objTemplate = new \Isotope\Template($strTemplate);
		$arrProductOptions = array();
		$arrAjaxOptions = array();
		$arrToGenerate = array();

		foreach ($this->getProductAndVariantAttributes() as $attribute)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute];

			if ($arrData['attributes']['customer_defined'] || $arrData['attributes']['variant_option'])
			{
				$objTemplate->hasOptions = true;
				$arrProductOptions[$attribute] = array_merge($arrData, array
				(
					'name'	=> $attribute,
					'html'	=> $this->generateProductOptionWidget($attribute),
				));

				if ($arrData['attributes']['variant_option'] || $arrData['attributes']['ajax_option'])
				{
					$arrAjaxOptions[] = $attribute;
				}
			}
			else
			{
				$arrToGenerate[] = $attribute;
			}
		}

		foreach($arrToGenerate as $attribute)
		{
			$objTemplate->$attribute = $this->generateAttribute($attribute, $this->$attribute);
		}

		$arrButtons = array();

		// !HOOK: retrieve buttons
		if (isset($GLOBALS['ISO_HOOKS']['buttons']) && is_array($GLOBALS['ISO_HOOKS']['buttons']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback)
			{
				$objCallback = \System::importStatic($callback[0]);
				$arrButtons = $objCallback->$callback[1]($arrButtons);
			}

			$arrButtons = array_intersect_key($arrButtons, array_flip(deserialize($objModule->iso_buttons, true)));
		}

		if (\Input::post('FORM_SUBMIT') == $this->formSubmit && !$this->doNotSubmit)
		{
			foreach ($arrButtons as $button => $data)
			{
				if (strlen(\Input::post($button)))
				{
					if (is_array($data['callback']) && count($data['callback']) == 2)
					{
						$objCallback = \System::importStatic($data['callback'][0]);
						$objCallback->{$data['callback'][1]}($this, $objModule);
					}
					break;
				}
			}
		}

		$objTemplate->buttons = $arrButtons;
		$objTemplate->quantityLabel = $GLOBALS['TL_LANG']['MSC']['quantity'];
		$objTemplate->useQuantity = $objModule->iso_use_quantity;
		$objTemplate->quantity_requested = $this->quantity_requested;
		$objTemplate->raw = array_merge($this->arrData, $this->arrCache);
		$objTemplate->raw_options = $this->arrOptions;
		$objTemplate->href_reader = $this->href_reader;
		$objTemplate->label_detail = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
		$objTemplate->options = \Isotope\Frontend::generateRowClass($arrProductOptions, 'product_option');
		$objTemplate->hasOptions = !empty($arrProductOptions) ? true : false;
		$objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$objTemplate->formId = $this->formSubmit;
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->formSubmit = $this->formSubmit;
		$objTemplate->product = $this;

		list(,$startScript, $endScript) = \Isotope\Frontend::getElementAndScriptTags();
		$GLOBALS['TL_MOOTOOLS'][] = $startScript."\nnew {$this->ajaxClass}('{$objModule->id}', '" . ($this->pid ? $this->pid : $this->id) . "', '{$this->formSubmit}', ['ctrl_" . implode("_".$this->formSubmit."', 'ctrl_", $arrAjaxOptions) . "_".$this->formSubmit."'], {language: '{$GLOBALS['TL_LANGUAGE']}', action: '".($objModule instanceof \Module ? 'fmd' : 'cte')."', page: {$objPage->id}, loadMessage:'" . specialchars($GLOBALS['ISO_LANG']['MSC']['loadingProductData']) . "'});\n".$endScript;

		// !HOOK: alter product data before output
		if (isset($GLOBALS['ISO_HOOKS']['generateProduct']) && is_array($GLOBALS['ISO_HOOKS']['generateProduct']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateProduct'] as $callback)
			{
				$objCallback = \System::importStatic($callback[0]);
				$objCallback->$callback[1]($objTemplate, $this);
			}
		}

		return $objTemplate->parse();
	}



	/**
	 * Generate the product data on ajax update
	 * @param object
	 * @return array
	 */
	public function generateAjax(&$objModule)
	{
		$this->formSubmit = (($objModule instanceof \ContentElement) ? 'cte' : 'fmd') . $objModule->id . '_product_' . ($this->pid ? $this->pid : $this->id);
		$this->validateVariant();

		$arrOptions = array();
		$arrToGenerate = array();

		foreach ($this->getProductAndVariantAttributes() as $attribute)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute];

			if ($arrData['attributes']['variant_option'] || $arrData['attributes']['ajax_option'])
			{
				$arrOptions[] = array_merge($arrData, array
				(
					'id'	=> ('ctrl_' . $attribute . '_' . $this->formSubmit),
					'name'	=> $attribute,
					'html'	=> $this->generateProductOptionWidget($attribute, true),
				));
			}
			elseif (in_array($attribute, $this->arrVariantAttributes))
			{
				$arrToGenerate[] = $attribute;
			}
		}

		foreach($arrToGenerate as $attribute)
		{
			$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute];

			if ($arrData['inputType'] == 'mediaManager')
			{
				$objGallery = $this->$attribute;

				foreach ((array) $this->Isotope->Config->imageSizes as $size)
				{
					$arrOptions[] = array_merge($arrData, array
					(
						'id'	=> ($this->formSubmit . '_' . $attribute . '_' . $size['name'] . 'size'),
						'name'	=> $attribute,
						'html'	=> $objGallery->generateMainImage($size['name']),
					));
				}

				$arrOptions[] = array_merge($arrData, array
				(
					'id' => ($this->formSubmit . '_' . $attribute . '_gallery'),
					'name'	=> $attribute,
					'html' => $objGallery->generateGallery(),
				));
			}
			else
			{
				$arrOptions[] = array_merge($arrData, array
				(
					'id' => ($this->formSubmit . '_' . $attribute),
					'name'	=> $attribute,
					'html' => $this->generateAttribute($attribute, $this->$attribute),
				));
			}
		}

		// !HOOK: alter product data before ajax output
		if (isset($GLOBALS['ISO_HOOKS']['generateAjaxProduct']) && is_array($GLOBALS['ISO_HOOKS']['generateAjaxProduct']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateAjaxProduct'] as $callback)
			{
				$objCallback = \System::importStatic($callback[0]);
				$arrOptions = $objCallback->$callback[1]($arrOptions, $this);
			}
		}

		return $arrOptions;
	}


	/**
	 * Generate an atrtibute and return it as HTML string
	 * @param string
	 * @param mixed
	 * @return string|\Isotope\Gallery\Default
	 */
	protected function generateAttribute($attribute, $varValue)
	{
		$strBuffer = '';
		$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute];

		// Return the \Isotope\Gallery object
		if ($arrData['inputType'] == 'mediaManager')
		{
			return $this->$attribute;
		}

		// Calculate the prices
		elseif ($attribute == 'price')
		{
			$fltPrice = $varValue;
			$fltOriginalPrice = $this->original_price;

			if ($this->arrCache['from_price'] !== null)
			{
				$fltPrice = $this->Isotope->calculatePrice($this->arrCache['from_price'], $this, 'price', $this->arrData['tax_class']);
				$fltOriginalPrice = $this->Isotope->calculatePrice($this->arrCache['from_price'], $this, 'original_price', $this->arrData['tax_class']);

				$strBuffer = sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $this->Isotope->formatPriceWithCurrency($fltPrice));
				$strOriginalPrice = sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $this->Isotope->formatPriceWithCurrency($fltOriginalPrice));
			}
			else
			{
				$strBuffer = $this->Isotope->formatPriceWithCurrency($fltPrice);
				$strOriginalPrice = $this->Isotope->formatPriceWithCurrency($fltOriginalPrice);
			}

			if ($fltPrice != $fltOriginalPrice)
			{
				$strBuffer = '<div class="original_price"><strike>' . $strOriginalPrice . '</strike></div><div class="price">' . $strBuffer . '</div>';
			}
		}

		// Calculate base price
		elseif ($attribute == 'baseprice')
		{
			if (is_array($varValue) && $varValue['unit'] > 0 && $varValue['value'] != '')
			{
				$objBasePrice = $this->Database->execute("SELECT * FROM tl_iso_baseprice WHERE id=" . (int) $varValue['unit']);

				if ($objBasePrice->numRows)
				{
					$strBuffer = sprintf($objBasePrice->label, $this->Isotope->formatPriceWithCurrency($this->price / $varValue['value'] * $objBasePrice->amount), $varValue['value']);
				}
			}
		}

		// Convert line breaks in textarea to <br> tags
		elseif ($arrData['inputType'] == 'textarea' && $arrData['eval']['rte'] == '')
		{
			$strBuffer = nl2br($varValue);
		}

		// Generate download attributes
		elseif ($arrData['inputType'] == 'downloads')
		{
			$IsotopeFrontend = \System::importStatic('IsotopeFrontend');
			$strBuffer = $IsotopeFrontend->generateDownloadAttribute($attribute, $arrData, $varValue);
		}

		// Generate a HTML table for associative arrays
		elseif (is_array($varValue) && !array_is_assoc($varValue) && is_array($varValue[0]))
		{
			$arrFormat = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['tableformat'];

			$last = count($varValue[0])-1;

			$strBuffer = '
<table class="'.$attribute.'">
  <thead>
    <tr>';

			foreach (array_keys($varValue[0]) as $i => $name)
			{
				if ($arrFormat[$name]['doNotShow'])
				{
					continue;
				}

				$label = $arrFormat[$name]['label'] ? $arrFormat[$name]['label'] : $name;

				$strBuffer .= '
      <th class="head_'.$i.($i==0 ? ' head_first' : '').($i==$last ? ' head_last' : ''). (!is_numeric($name) ? ' '.standardize($name) : '').'">' . $label . '</th>';
			}

			$strBuffer .= '
    </tr>
  </thead>
  <tbody>';

			foreach ($varValue as $r => $row)
			{
				$strBuffer .= '
    <tr class="row_'.$r.($r==0 ? ' row_first' : '').($r==$last ? ' row_last' : '').' '.($r%2 ? 'odd' : 'even').'">';

    			$c = -1;

    			foreach ($row as $name => $value)
    			{
    				if ($arrFormat[$name]['doNotShow'])
    				{
    					continue;
    				}

    				if ($arrFormat[$name]['rgxp'] == 'price')
    				{
    					$value = $this->Isotope->formatPriceWithCurrency($value);
    				}
    				else
    				{
    					$value = $arrFormat[$name]['format'] ? sprintf($arrFormat[$name]['format'], $value) : $value;
    				}

    				$strBuffer .= '
      <td class="col_'.++$c.($c==0 ? ' col_first' : '').($c==$i ? ' col_last' : '').' '.standardize($name).'">' . $value . '</td>';
    			}

    			$strBuffer .= '
    </tr>';
			}

			$strBuffer .= '
  </tbody>
</table>';
		}

		// Generate ul/li listing for simpley arrays
		elseif (is_array($varValue))
		{
			$strBuffer = '
<ul>';

			$current = 0;
			$last = count($varValue)-1;
			foreach( $varValue as $value )
			{
				$class = trim(($current == 0 ? 'first' : '') . ($current == $last ? ' last' : ''));

				$strBuffer .= '
  <li'.($class != '' ? ' class="'.$class.'"' : '').'>' . $value . '</li>';
			}

			$strBuffer .= '
</ul>';
		}
		else
		{
			$strBuffer = $this->Isotope->formatValue('tl_iso_products', $attribute, $varValue);
		}

		// !HOOK: allow for custom attribute types to modify their output
		if (isset($GLOBALS['ISO_HOOKS']['generateAttribute']) && is_array($GLOBALS['ISO_HOOKS']['generateAttribute']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateAttribute'] as $callback)
			{
				$objCallback = \System::importStatic($callback[0]);
				$strBuffer = $objCallback->$callback[1]($attribute, $varValue, $strBuffer, $this);
			}
		}

		// Apply <div> ID to variant attributes so we can replace it with javascript/ajax
		if (in_array($attribute, $this->arrVariantAttributes))
		{
			return '<div class="iso_attribute ' . $attribute . '" id="' . $this->formSubmit . '_' . $attribute . '">' . $strBuffer . '</div>';
		}
		else
		{
			return $strBuffer;
		}
	}


	/**
	 * Return a widget object based on a product attribute's properties
	 * @param string
	 * @param boolean
	 * @return string
	 */
	protected function generateProductOptionWidget($strField, $blnAjax=false)
	{
		$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField];

		$arrData['eval']['mandatory'] = ($arrData['eval']['mandatory'] && !$blnAjax) ? true : false;
		$arrData['eval']['required'] = $arrData['eval']['mandatory'];

		// Make sure variant options are initialized
		$this->getVariantOptions();

		if ($arrData['attributes']['variant_option'] && is_array($arrData['options']))
		{
			if ((count((array)$this->arrVariantOptions['attributes'][$strField]) == 1) && !$this->arrType['force_variant_options'])
			{
				$this->arrOptions[$strField] = $this->arrVariantOptions['attributes'][$strField][0];
				$this->arrVariantOptions['current'][$strField] = $this->arrVariantOptions['attributes'][$strField][0];
				$arrData['default'] = $this->arrVariantOptions['attributes'][$strField][0];

				if (!$blnAjax)
				{
					return '';
				}
			}

			if ($arrData['inputType'] == 'select')
			{
				$arrData['eval']['includeBlankOption'] = true;
			}

			$arrField = $this->prepareForWidget($arrData, $strField, $arrData['default']);

			// Unset if no variant has this option
			foreach ($arrField['options'] as $k => $option)
			{
				// Keep groups and blankOptionLabels
				if (!$option['group'] && $option['value'] != '')
				{
					// Unset option if no attribute has this option at all (in any enabled variant)
					if (!in_array((string) $option['value'], (array) $this->arrVariantOptions['attributes'][$strField], true))
					{
						unset($arrField['options'][$k]);
					}

					// Check each variant if it is found trough the url
					else
					{
						$blnValid = false;

						foreach ((array) $this->arrVariantOptions['options'] as $arrVariant)
						{
							if ($arrVariant[$strField] == $option['value'] && count($this->arrVariantOptions['current']) == count(array_intersect_assoc($this->arrVariantOptions['current'], $arrVariant)))
							{
								$blnValid = true;
							}
						}

						if (!$blnValid)
						{
							unset($arrField['options'][$k]);
						}
					}
				}
			}

			$arrField['options'] = array_values($arrField['options']);

			if (\Input::get($strField) != '' && \Input::post('FORM_SUBMIT') != $this->formSubmit)
			{
				if (in_array(\Input::get($strField), (array)$this->arrVariantOptions['attributes'][$strField], true))
				{
					$arrField['value'] = \Input::get($strField);
					$this->arrVariantOptions['current'][$strField] = \Input::get($strField);
				}
			}
			elseif ($this->pid > 0)
			{
				$arrField['value'] = $this->arrOptions[$strField];
				$this->arrVariantOptions['current'][$strField] = $this->arrOptions[$strField];
			}
		}
		else
		{
			if (is_array($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']) && !empty($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']))
			{
				foreach ($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback'] as $callback)
				{
					$objCallback = \System::importStatic($callback[0]);
					$arrData = $objCallback->{$callback[1]}($strField, $arrData, $this);
				}
			}

			$arrField = $this->prepareForWidget($arrData, $strField, $arrData['default']);
		}

		// Translate widget
		$arrField['label'] = $this->Isotope->translate($arrField['label']);

		// Translate widget options
		if (is_array($arrField['options']))
		{
			foreach ($arrField['options'] as $k => $v)
			{
				if ($v['label'])
				{
					$arrField['options'][$k]['label'] = $this->Isotope->translate($v['label']);
				}
				elseif (is_array($v))
				{
					foreach ($v as $kk => $vv)
					{
						if ($k['label'])
						{
							$arrField['options'][$k][$kk]['label'] = $this->Isotope->translate($vv['label']);
						}
					}
				}
			}
		}

		$strClass = strlen($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['class']) ? $GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['class'] : $GLOBALS['TL_FFL'][$arrData['inputType']];

		// Continue if the class is not defined
		if (!$this->classFileExists($strClass))
		{
			return '';
		}

		$objWidget = new $strClass($arrField);

		$objWidget->storeValues = true;
		$objWidget->tableless = true;
		$objWidget->id .= "_" . $this->formSubmit;

		// Validate input
		if (\Input::post('FORM_SUBMIT') == $this->formSubmit)
		{
			$objWidget->validate();

			if ($objWidget->hasErrors())
			{
				$this->doNotSubmit = true;
			}

			// Store current value
			elseif ($objWidget->submitInput() || $objWidget instanceof \uploadable)
			{
				$varValue = $objWidget->value;

				// Convert date formats into timestamps
				if ($varValue != '' && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new \Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}

				// Trigger the save_callback
				if (is_array($arrData['save_callback']))
				{
					foreach ($arrData['save_callback'] as $callback)
					{
						$objCallback = \System::importStatic($callback[0]);

						try
						{
							$varValue = $objCallback->$callback[1]($varValue, $this, $objWidget);
						}
						catch (Exception $e)
						{
							$objWidget->class = 'error';
							$objWidget->addError($e->getMessage());
							$this->doNotSubmit = true;
						}
					}
				}

				if (!$objWidget->hasErrors())
				{
					$this->arrOptions[$strField] = $varValue;

					if ($arrData['attributes']['variant_option'] && $varValue != '')
					{
						$this->arrVariantOptions['current'][$strField] = $varValue;
					}
				}
			}
		}

		$wizard = '';

		// Datepicker
		if ($arrData['eval']['datepicker'])
		{
			$GLOBALS['TL_JAVASCRIPT'][] = 'plugins/datepicker/datepicker.js';
			$GLOBALS['TL_CSS'][] = 'plugins/datepicker/dashboard.css';

			$rgxp = $arrData['eval']['rgxp'];
			$format = Date::formatToJs($GLOBALS['TL_CONFIG'][$rgxp.'Format']);

			switch ($rgxp)
			{
				case 'datim':
					$time = ",\n      timePicker:true";
					break;

				case 'time':
					$time = ",\n      pickOnly:\"time\"";
					break;

				default:
					$time = '';
					break;
			}

			$wizard .= ' <img src="plugins/datepicker/icon.gif" width="20" height="20" alt="" id="toggle_' . $objWidget->id . '" style="vertical-align:-6px">
  <script>
  window.addEvent("domready", function() {
    new Picker.Date($$("#ctrl_' . $objWidget->id . '"), {
      draggable:false,
      toggle:$$("#toggle_' . $objWidget->id . '"),
      format:"' . $format . '",
      positionOffset:{x:-197,y:-182}' . $time . ',
      pickerClass:"datepicker_dashboard",
      useFadeInOut:!Browser.ie,
      startDay:' . $GLOBALS['TL_LANG']['MSC']['weekOffset'] . ',
      titleFormat:"' . $GLOBALS['TL_LANG']['MSC']['titleFormat'] . '"
    });
  });
  </script>';
		}

		// Add a custom wizard
		if (is_array($arrData['wizard']))
		{
			foreach ($arrData['wizard'] as $callback)
			{
				$objCallback = \System::importStatic($callback[0]);
				$wizard .= $objCallback->$callback[1]($this);
			}
		}

		if ($objWidget instanceof \uploadable)
		{
			$this->hasUpload = true;
		}

		return $objWidget->parse() . $wizard;
	}


	/**
	 * Find price for the current product/variant
	 */
	protected function findPrice()
	{
		$arrPrice = \Isotope\ProductPriceFinder::findPrice($this);

		$this->arrData['price'] = $arrPrice['price'];
		$this->arrData['tax_class'] = $arrPrice['tax_class'];
		$this->arrCache['from_price'] = $arrPrice['from_price'];

		// Add "price_tiers" to attributes, so the field is available in the template
		if ($this->hasAdvancedPrices())
		{
			$this->arrAttributes[] = 'price_tiers';

			// Add "price_tiers" to variant attributes, so the field is updated through ajax
			if ($this->hasVariantPrices())
			{
				$this->arrVariantAttributes[] = 'price_tiers';
			}

			$this->arrCache['price_tiers'] = $arrPrice['price_tiers'];
		}
	}


	/**
	 * Load data of a product variant if the options match one
	 */
	protected function validateVariant()
	{
		if (!$this->hasVariants())
		{
			return;
		}

		// Make sure variant options are initialized
		$this->getVariantOptions();

		$arrOptions = array();

		foreach ($this->arrAttributes as $attribute)
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
			{
				if (\Input::post('FORM_SUBMIT') == $this->formSubmit && in_array(\Input::post($attribute), (array)$this->arrVariantOptions['attributes'][$attribute], true))
				{
					$arrOptions[$attribute] = \Input::post($attribute);
				}
				elseif (\Input::post('FORM_SUBMIT') == '' && in_array(\Input::get($attribute), (array)$this->arrVariantOptions['attributes'][$attribute], true))
				{
					$arrOptions[$attribute] = \Input::get($attribute);
				}
				elseif (count((array) $this->arrVariantOptions['attributes'][$attribute]) == 1)
				{
					$arrOptions[$attribute] = $this->arrVariantOptions['attributes'][$attribute][0];
				}
			}
		}

		$intOptions = count($arrOptions);

		if ($intOptions > 0)
		{
			$intVariant = false;

			foreach ((array) $this->arrVariantOptions['options'] as $id => $arrVariant)
			{
				if ($intOptions == count($arrVariant) && $intOptions == count(array_intersect_assoc($arrOptions, $arrVariant)))
				{
					if ($intVariant === false)
					{
						$intVariant = $id;
					}
					else
					{
						$this->doNotSubmit = true;
						return;
					}
				}
			}

			// Variant not found
			if ($intVariant === false || !is_array($this->arrVariantOptions['variants'][$intVariant]))
			{
				$this->doNotSubmit = true;
				return;
			}

			// Variant already loaded
			if ($intVariant == $this->id)
			{
				return;
			}

			$this->loadVariantData($this->arrVariantOptions['variants'][$intVariant]);
		}
	}


	/**
	 * Load variant data basing on provided data
	 * @param array
	 * @param array
	 */
	public function loadVariantData($arrData, $arrInherit=false)
	{
		$arrInherit = deserialize($arrData['inherit'], true);

		$this->arrData['id'] = $arrData['id'];
		$this->arrData['pid'] = $arrData['pid'];

		foreach ($this->arrVariantAttributes as $attribute)
		{
			if (in_array($attribute, $arrInherit) || ($this->isLocked() && in_array($attribute, array('sku', 'name', 'price'))))
			{
				continue;
			}

			$this->arrData[$attribute] = $arrData[$attribute];

			if (is_array($this->arrCache) && isset($this->arrCache[$attribute]))
			{
				unset($this->arrCache[$attribute]);
			}
		}

		if (!$this->isLocked() && $this->hasVariantPrices())
		{
			$this->findPrice();
		}

		// Load variant options
		$this->arrOptions = array_merge($this->arrOptions, array_intersect_key($arrData, array_flip(array_intersect($this->arrAttributes, $GLOBALS['ISO_CONFIG']['variant_options']))));

		// Unset arrDownloads cache
		$this->arrDownloads = null;
	}


	/**
	 * Return select statement to load product data including multilingual fields
	 * @param array an array of columns
	 * @return string
	 */
	public static function getSelectStatement($arrColumns=false)
	{
		static $strSelect = '';

		if ($strSelect == '' || $arrColumns !== false)
		{
			$arrSelect = ($arrColumns !== false) ? $arrColumns : array('p1.*');
			$arrSelect[] = "'".$GLOBALS['TL_LANGUAGE']."' AS language";

			foreach ($GLOBALS['ISO_CONFIG']['multilingual'] as $attribute)
			{
				if ($arrColumns !== false && !in_array('p1.'.$attribute, $arrColumns))
					continue;

				$arrSelect[] = "IFNULL(p2.$attribute, p1.$attribute) AS {$attribute}";
			}

			$strQuery = "
SELECT
	" . implode(', ', $arrSelect) . ",
	t.class AS product_class,
	c.sorting
FROM tl_iso_products p1
INNER JOIN tl_iso_producttypes t ON t.id=p1.type
LEFT OUTER JOIN tl_iso_products p2 ON p1.id=p2.pid AND p2.language='" . $GLOBALS['TL_LANGUAGE'] . "'
LEFT OUTER JOIN tl_iso_product_categories c ON p1.id=c.pid";

			if ($arrColumns !== false)
			{
				return $strQuery;
			}

			$strSelect = $strQuery;
		}

		return $strSelect;
	}


	/**
	 * Sort the attributes based on their position (from wizard) and return their names only
	 * @param mixed
	 * @return array
	 */
	protected function getSortedAttributes($varValue)
	{
		$arrAttributes = deserialize($varValue, true);

		uasort($arrAttributes, create_function('$a,$b', 'return $a["position"] > $b["position"];'));

		return array_keys($arrAttributes);
	}
}

