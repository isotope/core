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


class IsotopeProduct extends Controller
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
	 */
	protected $arrVariantOptions = array();

	/**
	 * Downloads for this product
	 */
	protected $arrDownloads = null;

	/**
	 * Cache properties, cache is dropped when serializing
	 */
	protected $arrCache = array();

	/**
	 * for option widgets, helps determine the encoding type for a form
	 * @var boolean
	 */
	protected $hasUpload = false;

	/**
	 * for option widgets, don't submit if certain validation(s) fail
	 * @var boolean
	 */
	protected $doNotSubmit = false;

	/**
	 * Lock products from changes and don't calculate prices
	 */
	protected $blnLocked = false;

	/**
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;


	/**
	 * Construct the object
	 */
	//!@todo arrData['type'] is not available if recovering from non-existing product
	public function __construct($arrData, $arrOptions=null, $blnLocked=false)
	{
		parent::__construct();
		$this->import('Database');
		$this->import('Isotope');

		$this->blnLocked = $blnLocked;

		if (!$this->blnLocked && $arrData['pid'] > 0)
		{
			$this->arrData = $this->Database->execute("SELECT * FROM tl_iso_products WHERE id={$arrData['pid']}")->fetchAssoc();
		}
		else
		{
			$this->arrData = $arrData;
		}

		if (!$this->arrData['type'])
		{
			return;
		}

		$this->arrType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id=".(int)$this->arrData['type'])->fetchAssoc();
		$this->arrAttributes = deserialize($this->arrType['attributes'], true);
		$this->arrCache['list_template'] = $this->arrType['list_template'];
		$this->arrCache['reader_template'] = $this->arrType['reader_template'];
		$this->arrVariantAttributes = $this->arrType['variants'] ? deserialize($this->arrType['variant_attributes']) : array();
		$this->arrOptions = is_array($arrOptions) ? $arrOptions : array();

		// Remove attributes not in this product type
		foreach( $this->arrData as $attribute => $value )
		{
			if (!in_array($attribute, $this->arrAttributes) && is_array($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']) && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['legend'] != '')
			{
				unset($this->arrData[$attribute]);
			}
		}


		// Find all possible variant options
		$objVariant = clone $this;
		$objVariants = $this->Database->execute("SELECT * FROM tl_iso_products WHERE pid={$this->arrData['id']} AND language=''");
		while( $objVariants->next() )
		{
			$objVariant->loadVariantData($objVariants->row(), false);

			if ($objVariant->available)
			{
				$arrVariantOptions = $objVariant->getOptions(true);

				$this->arrVariantOptions['variants'][] = $arrVariantOptions;

				foreach( $arrVariantOptions as $attribute => $value )
				{
					if (!in_array((string)$value, (array)$this->arrVariantOptions['attributes'][$attribute], true))
					{
						$this->arrVariantOptions['attributes'][$attribute][] = (string)$value;
					}
				}
			}
		}

		// Find lowest price
		if (!$this->blnLocked && $this->arrType['variants'] && in_array('price', $this->arrVariantAttributes))
		{
			if ($this->arrType['prices'])
			{
				$time = time();

				$objProduct = $this->Database->execute("SELECT
														(
															SELECT price
															FROM tl_iso_price_tiers
															WHERE pid IN
															(
																SELECT id
																FROM (SELECT * FROM tl_iso_prices ORDER BY config_id DESC, member_group DESC, start DESC, stop DESC) AS p
																WHERE
																	(config_id={$this->Isotope->Config->id} OR config_id=0)
																	AND (member_group=".(int)$this->User->price_group." OR member_group=0)
																	AND (start='' OR start<$time)
																	AND (stop='' OR stop>$time)
																	AND pid IN
																	(
																		SELECT id
																		FROM tl_iso_products
																		WHERE pid=" . ($this->arrData['pid'] ? $this->arrData['pid'] : $this->arrData['id']) . "
																	)
																GROUP BY pid
															)
															ORDER BY min ASC, price ASC LIMIT 1
														) AS low_price,
														(
															SELECT price
															FROM tl_iso_price_tiers
															WHERE pid IN
															(
																SELECT id
																FROM (SELECT * FROM tl_iso_prices ORDER BY config_id DESC, member_group DESC, start DESC, stop DESC) AS p
																WHERE
																	(config_id={$this->Isotope->Config->id} OR config_id=0)
																	AND (member_group=".(int)$this->User->price_group." OR member_group=0)
																	AND (start='' OR start<$time)
																	AND (stop='' OR stop>$time)
																	AND pid IN
																	(
																		SELECT id
																		FROM tl_iso_products
																		WHERE pid=" . ($this->arrData['pid'] ? $this->arrData['pid'] : $this->arrData['id']) . "
																	)
																GROUP BY pid
															)
															ORDER BY min ASC, price DESC LIMIT 1
														) AS high_price");
			}
			else
			{
				$objProduct = $this->Database->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price FROM tl_iso_products WHERE pid=" . ($this->arrData['pid'] ? $this->arrData['pid'] : $this->arrData['id']) . " AND published='1' AND language='' GROUP BY pid");
			}

			if ($objProduct->low_price < $objProduct->high_price)
			{
				$this->arrCache['low_price'] = $objProduct->low_price;
			}
			else
			{
				$this->arrData['price'] = $objProduct->low_price;
			}

			if(isset($GLOBALS['TL_HOOKS']['iso_addAttributes']) && is_array($GLOBALS['TL_HOOKS']['iso_addAttributes']))
			{
				foreach ($GLOBALS['TL_HOOKS']['iso_addAttributes'] as $callback)
				{
					$this->import($callback[0]);
					$this->arrAttributes[] = $this->$callback[0]->$callback[1]($this);
				}
			}

			if(isset($GLOBALS['TL_HOOKS']['iso_addVariantAttributes']) && is_array($GLOBALS['TL_HOOKS']['iso_addVariantAttributes']))
			{
				foreach ($GLOBALS['TL_HOOKS']['iso_addVariantAttributes'] as $callback)
				{
					$this->import($callback[0]);
					$this->arrVariantAttributes[] = $this->$callback[0]->$callback[1]($this);
				}
			}
		}


		if (!$this->blnLocked && in_array('price', $this->arrAttributes))
		{
			$this->findPrice();
			$this->arrData['original_price'] = $this->arrData['price'];
		}

		$this->loadLanguage();

		if ($arrData['pid'] > 0)
		{
			$this->loadVariantData($arrData);
		}
	}


	/**
	 * Get a property
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch( $strKey)
		{
			case 'id':
			case 'pid':
			case 'href_reader':
				return $this->arrData[$strKey];

			case 'original_price':
				return $this->blnLocked ? $this->arrData['original_price'] : $this->Isotope->calculatePrice($this->arrData['original_price'], $this, 'original_price', $this->arrData['tax_class']);

			case 'price':
				if ($this->arrType['variants'] && $this->arrData['pid'] == 0 && $this->arrCache['low_price'])
				{
					return $this->blnLocked ? $this->arrData['low_price'] : $this->Isotope->calculatePrice($this->arrCache['low_price'], $this, 'low_price', $this->arrData['tax_class']);
				}

				return $this->blnLocked ? $this->arrData['price'] : $this->Isotope->calculatePrice($this->arrData['price'], $this, 'price', $this->arrData['tax_class']);

			case 'total_price':
				return $this->quantity_requested * $this->price;

			case 'quantity_requested':
				return ($this->arrCache[$strKey] ? $this->arrCache[$strKey] : 1);

			case 'available':
				if ($this->blnLocked)
					return true;

				if (!BE_USER_LOGGED_IN && (!$this->arrData['published'] || ($this->arrData['start'] > 0 && $this->arrData['start'] > time()) || ($this->arrData['stop'] > 0 && $this->arrData['stop'] < time())))
					return false;

				// Check if "advanced price" is available
				if ($this->arrType['prices'] && (($this->pid > 0 && in_array('price', $this->arrVariantAttributes)) || in_array('price', $this->arrAttributes)) && $this->arrData['price'] === null)
					return false;

				// Check if the product is in any category in the current store (page tree)
				global $objPage;
				if (TL_MODE == 'FE' && is_object($objPage))
				{
					$arrCategories = $this->getChildRecords($objPage->rootId, 'tl_page', false);

					if (!count($arrCategories) || !$this->Database->execute("SELECT COUNT(*) AS available FROM tl_iso_product_categories WHERE pid=" . ($this->pid ? $this->pid : $this->id) . " AND page_id IN (" . implode(',', $arrCategories) . ")")->available)
						return false;
				}

				return true;
				break;

			case 'hasDownloads':
				return count($this->getDownloads()) ? true : false;

			case 'description_meta':
				return $this->arrData['description_meta'] != '' ? $this->arrData['description_meta'] : ($this->arrData['teaser'] != '' ? $this->arrData['teaser'] : $this->arrData['description']);
				break;

			default:
				// Initialize attribute
				if (!isset($this->arrCache[$strKey]))
				{
					if (is_array($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]) && $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['inputType'] == 'mediaManager')
					{
						$strClass = $GLOBALS['ISO_GAL'][(strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery'] : $this->Isotope->Config->gallery)];

						if (!strlen($strClass) || !$this->classFileExists($strClass))
							$strClass = 'IsotopeGallery';

						$varValue = new $strClass($strKey.'_'.($this->pid ? $this->pid : $this->id), deserialize($this->arrData[$strKey]));
						$varValue->product_id = ($this->pid ? $this->pid : $this->id);
						$varValue->href_reader = $this->href_reader;
					}

					switch( $strKey )
					{
						case 'formatted_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->price, false);
							break;

						case 'formatted_original_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->original_price, false);
							break;

						case 'formatted_total_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->total_price, false);
							break;

						case 'categories':
							$varValue = $this->Database->execute("SELECT page_id FROM tl_iso_product_categories WHERE pid=" . ($this->pid ? $this->pid : $this->id) . " ORDER BY sorting")->fetchEach('page_id');
							break;
					}

					$this->arrCache[$strKey] = $varValue ? $varValue : deserialize($this->arrData[$strKey]);
				}

				return $this->arrCache[$strKey] ? $this->arrCache[$strKey] : '';
		}
	}


	/**
	 * Set a property
	 */
	public function __set($strKey, $varValue)
	{
		switch( $strKey )
		{
			case 'reader_jumpTo':
				$strUrlKey = $this->arrData['alias'] ? $this->arrData['alias'] : ($this->arrData['pid'] ? $this->arrData['pid'] : $this->arrData['id']);
				$strUrl = $this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($varValue)->fetchAssoc(), '/product/' . $strUrlKey);

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
				$this->findPrice();
				break;

			default:
				$this->arrCache[$strKey] = $varValue;
		}

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
	 * Return all downloads for this product
	 */
	//!@todo: Confirm that files are available
	public function getDownloads()
	{
		if (!$this->arrType['downloads'])
			return array();

		// Cache downloads for this product
		if (!is_array($this->arrDownloads))
		{
			$this->arrDownloads = $this->Database->execute("SELECT * FROM tl_iso_downloads WHERE pid={$this->arrData['id']} OR pid={$this->arrData['pid']}")->fetchAllAssoc();
		}

		return $this->arrDownloads;
	}


	/**
	 * Return all options, either the raw array or prepared for product listing
	 */
	//!@todo I dislike the listing approach, we might find a better solution
	public function getOptions($blnRaw=false)
	{
		if ($blnRaw)
		{
			return $this->arrOptions;
		}

		$arrOptions = array();

		foreach( $this->arrOptions as $field => $value )
		{
			$arrOptions[] = array
			(
				'label'		=> $this->Isotope->formatLabel('tl_iso_products', $field),
				'value'		=> $this->Isotope->formatValue('tl_iso_products', $field, $value),
			);
		}

		return $arrOptions;
	}


	/**
	 * Set options data
	 */
	public function setOptions(array $arrOptions)
	{
		$this->arrOptions = $arrOptions;
	}


	/**
	 * Return all attributes for this product
	 */
	public function getAttributes()
	{
		$arrData = array();
		$arrAttributes = array_unique(array_merge($this->arrAttributes, $this->arrVariantAttributes));

		foreach( $arrAttributes as $attribute )
		{
			$arrData[$attribute] = $this->$attribute;
		}

		return $arrData;
	}


	/**
	 * Generate a product template
	 */
	public function generate($strTemplate, &$objModule)
	{
		global $objPage;

		$this->validateVariant();

		$this->arrOptions = array();

		$objTemplate = new IsotopeTemplate($strTemplate);

		$arrProductOptions = array();
		$arrAjaxOptions = array();
		$arrAttributes = $this->getAttributes();

		foreach( $arrAttributes as $attribute => $varValue )
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['is_customer_defined'] || $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
			{
				$objTemplate->hasOptions = true;
				$arrProductOptions[$attribute] = $this->generateProductOptionWidget($attribute);

				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
				{
					$arrAjaxOptions[] = $attribute;
				}
			}
			else
			{
				$objTemplate->$attribute = $this->generateAttribute($attribute, $varValue);
			}
		}

		// Buttons
		$arrButtons = array();
		if (isset($GLOBALS['TL_HOOKS']['isoButtons']) && is_array($GLOBALS['TL_HOOKS']['isoButtons']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isoButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}

			$arrButtons = array_intersect_key($arrButtons, array_flip(deserialize($objModule->iso_buttons, true)));
		}

		if ($this->Input->post('FORM_SUBMIT') == 'iso_product_'.($this->pid ? $this->pid : $this->id) && !$this->doNotSubmit)
		{
			foreach( $arrButtons as $button => $data )
			{
				if (strlen($this->Input->post($button)))
				{
					if (is_array($data['callback']) && count($data['callback']) == 2)
					{
						$this->import($data['callback'][0]);
						$this->{$data['callback'][0]}->{$data['callback'][1]}($this, $objModule);
					}
					break;
				}
			}
		}

		$objTemplate->buttons = $arrButtons;
		$objTemplate->quantityLabel = $GLOBALS['TL_LANG']['MSC']['quantity'];
		$objTemplate->useQuantity = $objModule->iso_use_quantity;


		$objTemplate->raw = $this->arrData;
		$objTemplate->href_reader = $this->href_reader;

		$objTemplate->label_detail = $GLOBALS['TL_LANG']['MSC']['detailLabel'];

		$objTemplate->options = $arrProductOptions;
		$objTemplate->hasOptions = count($arrProductOptions) ? true : false;

		$objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$objTemplate->formId = 'iso_product_'.($this->pid ? $this->pid : $this->id);
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->formSubmit = 'iso_product_'.($this->pid ? $this->pid : $this->id);

		$GLOBALS['TL_MOOTOOLS'][] = "<script type=\"text/javascript\">new IsotopeProduct('" . $objModule->id . "', '" . ($this->pid ? $this->pid : $this->id) . "', ['ctrl_" . implode("_".($this->pid ? $this->pid : $this->id)."', 'ctrl_", $arrAjaxOptions) . "_".($this->pid ? $this->pid : $this->id)."'], {language: '" . $GLOBALS['TL_LANGUAGE'] . "', page: " . $objPage->id . "});</script>";

		// HOOK for altering product data before output
		if (isset($GLOBALS['TL_HOOKS']['iso_generateProduct']) && is_array($GLOBALS['TL_HOOKS']['iso_generateProduct']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_generateProduct'] as $callback)
			{
				$this->import($callback[0]);
				$objTemplate = $this->$callback[0]->$callback[1]($objTemplate, $this);
			}
		}

		return $objTemplate->parse();
	}



	/**
	 * Generate the product data on ajax update
	 */
	public function generateAjax()
	{
		$this->validateVariant();

		$arrOptions = array();
		$arrAttributes = $this->getAttributes();

		$this->arrOptions = array();

		foreach( $arrAttributes as $attribute => $varValue )
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
			{
				$arrOptions[] = array
				(
					'id'		=> ('ctrl_' . $attribute . '_' . ($this->pid ? $this->pid : $this->id)),
					'html'		=> $this->generateProductOptionWidget($attribute, true),
				);
			}
			elseif (in_array($attribute, $this->arrVariantAttributes))
			{
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['inputType'] == 'mediaManager')
				{
					$objGallery = $this->$attribute;

					foreach( (array)$this->Isotope->Config->imageSizes as $size )
					{
						$arrOptions[] = array
						(
							'id'		=> ($attribute . '_' . ($this->pid ? $this->pid : $this->id) . '_' . $size['name'] . 'size'),
							'html'		=> $objGallery->generateMainImage($size['name']),
						);
					}

					$arrOptions[] = array
					(
						'id'		=> ($attribute . '_' . ($this->pid ? $this->pid : $this->id) . '_gallery'),
						'html'		=> $objGallery->generateGallery(),
					);
				}
				else
				{
					$arrOptions[] = array
					(
						'id'		=> ($attribute . '_' . ($this->pid ? $this->pid : $this->id)),
						'html'		=> $this->generateAttribute($attribute, $varValue),
					);
				}
			}
		}

		// HOOK for altering product data before output
		if (isset($GLOBALS['TL_HOOKS']['iso_generateAjaxProduct']) && is_array($GLOBALS['TL_HOOKS']['iso_generateAjaxProduct']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_generateAjaxProduct'] as $callback)
			{
				$this->import($callback[0]);
				$arrOptions = $this->$callback[0]->$callback[1]($arrOptions, $this);
			}
		}

		return $arrOptions;
	}


	protected function generateAttribute($attribute, $varValue)
	{
		$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute];

		// Return the IsotopeGallery object
		if ($arrData['inputType'] == 'mediaManager')
		{
			return $this->$attribute;
		}

		$strBuffer = $this->Isotope->formatValue('tl_iso_products', $attribute, $varValue);

		if ($arrData['inputType'] == 'textarea' && $arrData['eval']['rte'] == '')
		{
			$strBuffer = nl2br($varValue);
		}
		elseif ($arrData['eval']['rgxp'] == 'price')
		{
			if ($this->arrType['variants'] && $this->arrData['pid'] == 0 && $this->arrCache['low_price'])
			{
				$strBuffer = sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $this->Isotope->formatPriceWithCurrency($varValue));
			}
			else
			{
				$strBuffer = $this->Isotope->formatPriceWithCurrency($varValue);

				if ($varValue != $this->original_price)
				{
					$strBuffer = '<div class="original_price"><strike>' . $this->formatted_original_price . '</strike></div><div class="price">' . $strBuffer . '</div>';
				}
			}
		}

		// Allow for custom attribute types to modify their output.
		if (isset($GLOBALS['TL_HOOKS']['iso_generateAttribute']) && is_array($GLOBALS['TL_HOOKS']['iso_generateAttribute']))
		{
			foreach ($GLOBALS['TL_HOOKS']['iso_generateAttribute'] as $callback)
			{
				$this->import($callback[0]);
				$strBuffer = $this->$callback[0]->$callback[1]($attribute, $varValue, $strBuffer, $this);
			}
		}

		// Apply <span> to variant attributes so we can replace it with javascript/ajax
		if ($this->arrType['variants'] && in_array($attribute, $this->arrVariantAttributes))
		{
			return '<span id="' . $attribute . '_' . ($this->pid ? $this->pid : $this->id) . '">' . $strBuffer . '</span>';
		}
		else
		{
			return $strBuffer;
		}
	}


	/**
	 * Return a widget object based on a product attribute's properties.
	 */
	protected function generateProductOptionWidget($strField, $blnAjax=false)
	{
		$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField];
		$strClass = strlen($GLOBALS['ISO_ATTR'][$arrData['inputType']]['class']) ? $GLOBALS['ISO_ATTR'][$arrData['inputType']]['class'] : $GLOBALS['TL_FFL'][$arrData['inputType']];

		// Continue if the class is not defined
		if (!$this->classFileExists($strClass))
		{
			return '';
		}

		$arrData['eval']['mandatory'] = ($arrData['eval']['mandatory'] && !$blnAjax) ? true : false;
		$arrData['eval']['required'] = $arrData['eval']['mandatory'];

		if ($arrData['attributes']['variant_option'] && is_array($arrData['options']))
		{
			if (count((array)$this->arrVariantOptions['attributes'][$strField]) == 1)
			{
				$this->arrOptions[$strField] = $this->arrVariantOptions['attributes'][$strField][0];
				$this->Input->setPost($strField, $this->arrVariantOptions['attributes'][$strField][0]);

				if (!$blnAjax)
				{
					return '';
				}
			}

			if ($arrData['inputType'] == 'select')
			{
				$arrData['eval']['includeBlankOption'] = true;
			}

			$arrField = $this->prepareForWidget($arrData, $strField);

			// Unset if no variant has this option
			foreach( $arrField['options'] as $k => $option )
			{
				// Keep groups and blankOptionLabels
				if (!$option['group'] && $option['value'] != '')
				{
					// Unset option if no attribute has this option at all (in any enabled variant)
					if (!in_array((string)$option['value'], (array)$this->arrVariantOptions['attributes'][$strField], true))
					{
						unset($arrField['options'][$k]);
					}

					// Check each variant if it is found trough the url
					else
					{
						$blnValid = false;

						foreach( (array)$this->arrVariantOptions['variants'] as $arrVariant )
						{
							if ($arrVariant[$strField] == $option['value'] && count($this->arrOptions) == count(array_intersect_assoc($this->arrOptions, $arrVariant)))
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

			if ($this->Input->get($strField) != '' && $this->Input->post('FORM_SUBMIT') != 'iso_product_'.($this->pid ? $this->pid : $this->id))
			{
				if (in_array($this->Input->get($strField), (array)$this->arrVariantOptions['attributes'][$strField], true))
				{
					$this->Input->setPost($strField, $this->Input->get($strField));
				}
				else
				{
					$this->Input->setGet($strField, '');
				}
			}
		}
		else
		{
			if (is_array($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']) && count($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']))
			{
				foreach( $GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback'] as $callback )
				{
					$this->import($callback[0]);
					$arrData = $this->{$callback[0]}->{$callback[1]}($strField, $arrData, $this);
				}
			}

			$arrField = $this->prepareForWidget($arrData, $strField);
		}

		// Translate widget
		$arrField['label'] = $this->Isotope->translate($arrField['label']);
		if (is_array($arrField['options']))
		{
			foreach( $arrField['options'] as $k => $v )
			{
				if ($v['label'])
				{
					$arrField['options'][$k]['label'] = $this->Isotope->translate($v['label']);
				}
				elseif (is_array($v))
				{
					foreach( $v as $kk => $vv )
					{
						if ($k['label'])
						{
							$arrField['options'][$k][$kk]['label'] = $this->Isotope->translate($vv['label']);
						}
					}
				}
			}
		}

		$objWidget = new $strClass($arrField);

		$objWidget->storeValues = true;
		$objWidget->tableless = true;
		$objWidget->id .= "_" . ($this->pid ? $this->pid : $this->id);

		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == 'iso_product_'.($this->pid ? $this->pid : $this->id) || $this->Input->get($strField) != '')
		{
			$objWidget->validate();

			if ($objWidget->hasErrors())
			{
				$this->doNotSubmit = true;
			}

			// Store current value
			elseif ($objWidget->submitInput())
			{
				$varValue = $objWidget->value;

				// Convert date formats into timestamps
				if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}

				// Trigger the save_callback
				if (is_array($arrData['save_callback']))
				{
					try
					{
						foreach ($arrData['save_callback'] as $callback)
						{
							$this->import($callback[0]);
							$varValue = $this->$callback[0]->$callback[1]($varValue, $this);
						}
					}
					catch( Exception $e )
					{
						$objWidget->addError($e->getMessage());
						$this->doNotSubmit = true;
					}
				}

				if ($varValue != '')
				{
					$this->arrOptions[$strField] = $varValue;
				}
			}
		}

		if ($objWidget instanceof uploadable)
		{
			$this->hasUpload = true;
		}

		return $objWidget->parse();
	}


	/**
	 * Find advanced price (if enabled)
	 */
	protected function findPrice()
	{
		if (!$this->arrType['prices'])
			return;

		$time = time();

		$objPrice = $this->Database->execute("SELECT price, tax_class
											FROM tl_iso_price_tiers t
											LEFT JOIN tl_iso_prices p ON t.pid=p.id
											WHERE
												min<={$this->quantity_requested}
												AND t.pid=
												(
													SELECT id
													FROM tl_iso_prices
													WHERE
														(config_id={$this->Isotope->Config->id} OR config_id=0)
														AND (" . (count($this->User->groups) ? ('member_group IN (' . implode(',', $this->User->groups) . ') OR') : '') . "member_group=0)
														AND (start='' OR start<$time)
														AND (stop='' OR stop>$time)
														AND pid={$this->id}
													ORDER BY config_id DESC, " . (count($this->User->groups) ? ('member_group='.implode(' DESC, member_group=', $this->User->groups).' DESC') : 'member_group DESC') . ", start DESC, stop DESC
													LIMIT 1
												)
											ORDER BY min DESC LIMIT 1");

		$this->arrData['price'] = $objPrice->price;
		$this->arrData['tax_class'] = $objPrice->tax_class;
	}


	/**
	 * Load data of a product variant if the options match one
	 */
	protected function validateVariant()
	{
		if (!$this->arrType['variants'])
			return;

		$arrOptions = array();

		foreach( $this->arrAttributes as $attribute )
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
			{
				if ($this->Input->post('FORM_SUBMIT') == 'iso_product_'.($this->pid ? $this->pid : $this->id) && in_array($this->Input->post($attribute), (array)$this->arrVariantOptions['attributes'][$attribute], true))
				{
					$arrOptions[$attribute] = $this->Input->post($attribute);
				}
				elseif ($this->Input->post('FORM_SUBMIT') == '' && in_array($this->Input->get($attribute), (array)$this->arrVariantOptions['attributes'][$attribute], true))
				{
					$arrOptions[$attribute] = $this->Input->get($attribute);
				}
			}
		}

		if (count($arrOptions))
		{
			$objVariant = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid=" . ($this->pid ? $this->pid : $this->id) . " AND published='1' AND language='' AND " . implode("=? AND ", array_keys($arrOptions)) . "=?")->execute($arrOptions);

			// Variant already loaded
			if ($objVariant->id == $this->id)
				return;

			// Must match 1 variant, must not match multiple
			if ($objVariant->numRows == 1)
			{
				$this->loadVariantData($objVariant->row(), false);
			}
			else
			{
				$this->doNotSubmit = true;
			}
		}
	}


	public function loadVariantData($arrData, $blnLoadLanguage=true)
	{
		$arrInherit = deserialize($arrData['inherit'], true);

		$this->arrData['id'] = $arrData['id'];
		$this->arrData['pid'] = $arrData['pid'];

		foreach( $this->arrVariantAttributes as $attribute )
		{
			if (in_array($attribute, $arrInherit) || ($this->blnLocked && in_array($attribute, array('sku', 'name', 'price'))))
				continue;

			$this->arrData[$attribute] = $arrData[$attribute];
			unset($this->arrCache[$attribute]);
		}

		if (!$this->blnLocked && in_array('price', $this->arrVariantAttributes))
		{
			$this->findPrice();
			$this->arrData['original_price'] = $this->arrData['price'];
		}

		// Load variant options
		foreach( $this->arrAttributes as $attribute )
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
			{
				$this->arrOptions[$attribute] = $arrData[$attribute];
			}
		}

		// Unset arrDownloads cache
		$this->arrDownloads = null;

		if ($blnLoadLanguage)
		{
			$this->loadLanguage($arrInherit);
		}
	}


	/**
	 * Load the language data for a product/variant if found based on the current page language
	 */
	protected function loadLanguage($arrIgnore=array())
	{
		// This should never happen, but make sure, or we might fetch the master product record.
		if (!strlen($GLOBALS['TL_LANGUAGE']))
			return;

		$objLanguage = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid={$this->id} AND language=?")->limit(1)->execute($GLOBALS['TL_LANGUAGE']);

		if ($objLanguage->numRows)
		{
			$arrAttributes = $this->arrData['pid'] > 0 ? $this->arrVariantAttributes : $this->arrAttributes;

			foreach( $arrAttributes as $attribute )
			{
				if (in_array($attribute, $arrIgnore))
					continue;

				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['multilingual'])
				{
					$this->arrData[$attribute] = $objLanguage->$attribute;
				}
			}
		}
	}
}

