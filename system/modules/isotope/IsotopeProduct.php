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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class IsotopeProduct
 * 
 * Provide methods to handle Isotope products.
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
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
	 * @var array
	 */
	protected $arrVariantOptions = array('current'=>array());

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
	public function __construct($arrData, $arrOptions=null, $blnLocked=false)
	{
		parent::__construct();
		$this->import('Database');
		$this->import('Isotope');

		if (FE_USER_LOGGED_IN)
		{
			$this->import('FrontendUser', 'User');
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

		if (!$this->arrData['type'])
		{
			return;
		}

		$this->formSubmit = 'iso_product_' . $this->arrData['id'];
		$this->arrType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id=".(int)$this->arrData['type'])->fetchAssoc();
		$this->arrAttributes = deserialize($this->arrType['attributes'], true);
		$this->arrVariantAttributes = $this->arrType['variants'] ? deserialize($this->arrType['variant_attributes']) : array();
		$this->arrCache['list_template'] = $this->arrType['list_template'];
		$this->arrCache['reader_template'] = $this->arrType['reader_template'];
		$this->arrOptions = is_array($arrOptions) ? $arrOptions : array();

		// Allow to customize attributes
		if (isset($GLOBALS['ISO_HOOKS']['productAttributes']) && is_array($GLOBALS['ISO_HOOKS']['productAttributes']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['productAttributes'] as $callback)
			{
				$this->import($callback[0]);
				$this->$callback[0]->$callback[1]($this->arrAttributes, $this->arrVariantAttributes, $this);
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

		if (!$this->blnLocked)
		{
			if ($this->arrType['variants'])
			{
				// Find all possible variant options
				$objVariant = clone $this;
				$objVariants = $this->Database->execute(IsotopeProduct::getSelectStatement() . " WHERE p1.pid={$this->arrData['id']} AND p1.language='' AND p1.published='1'");

				while ($objVariants->next())
				{
					$objVariant->loadVariantData($objVariants->row(), false);

					if ($objVariant->available)
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

				// Find lowest price
				if (in_array('price', $this->arrVariantAttributes))
				{
					if ($this->arrType['prices'] && count($this->arrVariantOptions['ids']))
					{
						// Add "price_tiers" to variant attributes, so the field is updated through ajax
						$this->arrVariantAttributes[] = 'price_tiers';
						$time = time();

						$objProduct = $this->Database->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price
																FROM tl_iso_price_tiers
																WHERE pid IN
																(
																	SELECT id
																	FROM
																	(
																		SELECT p1.id, p1.pid FROM tl_iso_prices p1 LEFT JOIN tl_iso_products p2 ON p1.pid=p2.id
																		WHERE
																			p1.pid IN (" . implode(',', $this->arrVariantOptions['ids']) . ")
																			AND p1.config_id IN (" . (int) $this->Isotope->Config->id . ",0)
																			AND p1.member_group IN(" . ((FE_USER_LOGGED_IN && count($this->User->groups)) ? (implode(',', $this->User->groups).',') : '') . "0)
																			AND (p1.start='' OR p1.start<$time)
																			AND (p1.stop='' OR p1.stop>$time)
																		ORDER BY p1.config_id DESC, " . ((FE_USER_LOGGED_IN && count($this->User->groups)) ? ('p1.member_group=' . implode(' DESC, p1.member_group=', $this->User->groups) . ' DESC') : 'p1.member_group DESC') . ", p1.start DESC, p1.stop DESC
																	) AS p
																	GROUP BY pid
																)
																GROUP BY min ORDER BY min ASC LIMIT 1");
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
				}
			}

			if (in_array('price', $this->arrAttributes))
			{
				// Add "price_tiers" to attributes, so the field is available in the template
				$this->arrAttributes[] = 'price_tiers';

				$this->findPrice();
				$this->arrData['original_price'] = $this->arrData['price'];
			}
		}

		if ($arrData['pid'] > 0)
		{
			$this->loadVariantData($arrData);
		}

		if ($this->blnLocked)
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
				return (array) $this->arrVariantOptions['ids'];
				break;

			case 'formSubmit':
				return $this->formSubmit;

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

			case 'tax_free_price':
				return $this->blnLocked ? $this->arrData['price'] : $this->Isotope->calculatePrice($this->arrData['price'], $this, 'price');

			case 'tax_free_total_price':
				return $this->quantity_requested * $this->tax_free_price;

			case 'quantity_requested':
				return ($this->arrCache[$strKey] ? $this->arrCache[$strKey] : 1);

			case 'available':
				if ($this->blnLocked)
				{
					return true;
				}

				if (!BE_USER_LOGGED_IN && (!$this->arrData['published'] || ($this->arrData['start'] > 0 && $this->arrData['start'] > time()) || ($this->arrData['stop'] > 0 && $this->arrData['stop'] < time())))
				{
					return false;
				}

				// Check if "advanced price" is available
				if ($this->arrType['prices'] && (($this->pid > 0 && in_array('price', $this->arrVariantAttributes)) || in_array('price', $this->arrAttributes)) && $this->arrData['price'] === null)
				{
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
					if ($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['inputType'] == 'mediaManager')
					{
						$strClass = $GLOBALS['ISO_GAL'][(strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery'] : $this->Isotope->Config->gallery)];

						if (!strlen($strClass) || !$this->classFileExists($strClass))
						{
							$strClass = 'IsotopeGallery';
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

				if (!$this->blnLocked)
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
	 * Return all downloads for this product
	 * @todo Confirm that files are available
	 * @return array
	 */
	public function getDownloads()
	{
		if (!$this->arrType['downloads'])
		{
			return array();
		}

		// Cache downloads for this product
		if (!is_array($this->arrDownloads))
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
			$arrOptions[] = array
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
	 * Return all attributes for this product as array
	 * @return array
	 */
	public function getAttributes()
	{
		$arrData = array();
		$arrAttributes = array_unique(array_merge($this->arrAttributes, $this->arrVariantAttributes));

		foreach ($arrAttributes as $attribute)
		{
			$arrData[$attribute] = $this->$attribute;
		}

		return $arrData;
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

		$this->formSubmit = (($objModule instanceof ContentElement) ? 'cte' : 'fmd') . $objModule->id . '_product_' . ($this->pid ? $this->pid : $this->id);
		$this->validateVariant();

		$objTemplate = new IsotopeTemplate($strTemplate);
		$arrProductOptions = array();
		$arrAjaxOptions = array();
		$arrAttributes = $this->getAttributes();

		foreach ($arrAttributes as $attribute => $varValue)
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['customer_defined'] || $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
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

		$arrButtons = array();

		// Buttons
		if (isset($GLOBALS['ISO_HOOKS']['buttons']) && is_array($GLOBALS['ISO_HOOKS']['buttons']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}

			$arrButtons = array_intersect_key($arrButtons, array_flip(deserialize($objModule->iso_buttons, true)));
		}

		if ($this->Input->post('FORM_SUBMIT') == $this->formSubmit && !$this->doNotSubmit)
		{
			foreach ($arrButtons as $button => $data)
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
		$objTemplate->quantity_requested = $this->quantity_requested;
		$objTemplate->raw = $this->arrData;
		$objTemplate->raw_options = $this->arrOptions;
		$objTemplate->href_reader = $this->href_reader;
		$objTemplate->label_detail = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
		$objTemplate->options = $arrProductOptions;
		$objTemplate->hasOptions = count($arrProductOptions) ? true : false;
		$objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$objTemplate->formId = $this->formSubmit;
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->formSubmit = $this->formSubmit;

		list(,$startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();
		$GLOBALS['TL_MOOTOOLS'][] = $startScript."\nnew {$this->ajaxClass}('{$objModule->id}', '" . ($this->pid ? $this->pid : $this->id) . "', '{$this->formSubmit}', ['ctrl_" . implode("_".$this->formSubmit."', 'ctrl_", $arrAjaxOptions) . "_".$this->formSubmit."'], {language: '{$GLOBALS['TL_LANGUAGE']}', page: {$objPage->id}});\n".$endScript;

		// HOOK for altering product data before output
		if (isset($GLOBALS['ISO_HOOKS']['generateProduct']) && is_array($GLOBALS['ISO_HOOKS']['generateProduct']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateProduct'] as $callback)
			{
				$this->import($callback[0]);
				$objTemplate = $this->$callback[0]->$callback[1]($objTemplate, $this);
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
		$this->formSubmit = (($objModule instanceof ContentElement) ? 'cte' : 'fmd') . $objModule->id . '_product_' . ($this->pid ? $this->pid : $this->id);
		$this->validateVariant();

		$arrOptions = array();
		$arrAttributes = $this->getAttributes();

		foreach ($arrAttributes as $attribute => $varValue)
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
			{
				$arrOptions[] = array
				(
					'id' => ('ctrl_' . $attribute . '_' . $this->formSubmit),
					'html' => $this->generateProductOptionWidget($attribute, true),
				);
			}
			elseif (in_array($attribute, $this->arrVariantAttributes))
			{
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['inputType'] == 'mediaManager')
				{
					$objGallery = $this->$attribute;

					foreach ((array) $this->Isotope->Config->imageSizes as $size)
					{
						$arrOptions[] = array
						(
							'id' => ($this->formSubmit . '_' . $attribute . '_' . $size['name'] . 'size'),
							'html' => $objGallery->generateMainImage($size['name']),
						);
					}

					$arrOptions[] = array
					(
						'id' => ($this->formSubmit . '_' . $attribute . '_gallery'),
						'html' => $objGallery->generateGallery(),
					);
				}
				else
				{
					$arrOptions[] = array
					(
						'id' => ($this->formSubmit . '_' . $attribute),
						'html' => $this->generateAttribute($attribute, $varValue),
					);
				}
			}
		}

		// HOOK for altering product data before output
		if (isset($GLOBALS['ISO_HOOKS']['generateAjaxProduct']) && is_array($GLOBALS['ISO_HOOKS']['generateAjaxProduct']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateAjaxProduct'] as $callback)
			{
				$this->import($callback[0]);
				$arrOptions = $this->$callback[0]->$callback[1]($arrOptions, $this);
			}
		}

		return $arrOptions;
	}


	/**
	 * Generate an atrtibute and return it as HTML string
	 * @param string
	 * @param mixed
	 * @param string
	 */
	protected function generateAttribute($attribute, $varValue)
	{
		$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute];

		// Return the IsotopeGallery object
		if ($arrData['inputType'] == 'mediaManager')
		{
			return $this->$attribute;
		}

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

				if ($this->original_price > 0 && $varValue != $this->original_price)
				{
					$strBuffer = '<div class="original_price"><strike>' . $this->formatted_original_price . '</strike></div><div class="price">' . $strBuffer . '</div>';
				}
			}
		}

		// Generate download attributes
		elseif ($arrData['inputType'] == 'downloads')
		{
			$this->import('IsotopeFrontend');
			$strBuffer = $this->IsotopeFrontend->generateDownloadAttribute($attribute, $arrData, $varValue);
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

		// Allow for custom attribute types to modify their output.
		if (isset($GLOBALS['ISO_HOOKS']['generateAttribute']) && is_array($GLOBALS['ISO_HOOKS']['generateAttribute']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateAttribute'] as $callback)
			{
				$this->import($callback[0]);
				$strBuffer = $this->$callback[0]->$callback[1]($attribute, $varValue, $strBuffer, $this);
			}
		}

		// Apply <div> ID to variant attributes so we can replace it with javascript/ajax
		if ($this->arrType['variants'] && in_array($attribute, $this->arrVariantAttributes))
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
	 * @return object
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

			if ($this->Input->get($strField) != '' && $this->Input->post('FORM_SUBMIT') != $this->formSubmit)
			{
				if (in_array($this->Input->get($strField), (array)$this->arrVariantOptions['attributes'][$strField], true))
				{
					$arrField['value'] = $this->Input->get($strField);
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
			if (is_array($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback']) && count($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback']))
			{
				foreach ($GLOBALS['ISO_ATTR'][$arrData['inputType']]['callback'] as $callback)
				{
					$this->import($callback[0]);
					$arrData = $this->{$callback[0]}->{$callback[1]}($strField, $arrData, $this);
				}
			}

			$arrField = $this->prepareForWidget($arrData, $strField);
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

		$objWidget = new $strClass($arrField);

		$objWidget->storeValues = true;
		$objWidget->tableless = true;
		$objWidget->id .= "_" . $this->formSubmit;

		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == $this->formSubmit)
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
					catch (Exception $e)
					{
						$objWidget->addError($e->getMessage());
						$this->doNotSubmit = true;
					}
				}

				if ($varValue != '')
				{
					$this->arrOptions[$strField] = $varValue;
					$this->arrVariantOptions['current'][$strField] = $varValue;
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
		{
			return;
		}

		$time = time();

		$objPrices = $this->Database->execute("SELECT min, price, tax_class
												FROM tl_iso_price_tiers t
												LEFT JOIN tl_iso_prices p ON t.pid=p.id
												WHERE
													t.pid=
													(
														SELECT id
														FROM tl_iso_prices
														WHERE
															config_id IN (". (int) $this->Isotope->Config->id . ",0)
															AND member_group IN(" . ((FE_USER_LOGGED_IN && count($this->User->groups)) ? (implode(',', $this->User->groups) . ',') : '') . "0)
															AND (start='' OR start<$time)
															AND (stop='' OR stop>$time)
															AND pid={$this->id}
														ORDER BY config_id DESC, " . ((FE_USER_LOGGED_IN && count($this->User->groups)) ? ('member_group=' . implode(' DESC, member_group=', $this->User->groups) . ' DESC') : 'member_group DESC') . ", start DESC, stop DESC
														LIMIT 1
													)
												ORDER BY min DESC");

		$this->arrData['price'] = 0;
		$this->arrCache['price_tiers'] = $objPrices->fetchAllAssoc();

		foreach ($this->arrCache['price_tiers'] as $price)
		{
			if ($price['min'] <= $this->quantity_requested)
			{
				$this->arrData['price'] = $price['price'];
				$this->arrData['tax_class'] = $price['tax_class'];
				break;
			}
		}
	}


	/**
	 * Load data of a product variant if the options match one
	 */
	protected function validateVariant()
	{
		if (!$this->arrType['variants'])
		{
			return;
		}

		$arrOptions = array();

		foreach ($this->arrAttributes as $attribute)
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['variant_option'])
			{
				if ($this->Input->post('FORM_SUBMIT') == $this->formSubmit && in_array($this->Input->post($attribute), (array)$this->arrVariantOptions['attributes'][$attribute], true))
				{
					$arrOptions[$attribute] = $this->Input->post($attribute);
				}
				elseif ($this->Input->post('FORM_SUBMIT') == '' && in_array($this->Input->get($attribute), (array)$this->arrVariantOptions['attributes'][$attribute], true))
				{
					$arrOptions[$attribute] = $this->Input->get($attribute);
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
			if (in_array($attribute, $arrInherit) || ($this->blnLocked && in_array($attribute, array('sku', 'name', 'price'))))
			{
				continue;
			}

			$this->arrData[$attribute] = $arrData[$attribute];
			unset($this->arrCache[$attribute]);
		}

		if (!$this->blnLocked && in_array('price', $this->arrVariantAttributes))
		{
			$this->findPrice();
			$this->arrData['original_price'] = $this->arrData['price'];
		}

		// Load variant options
		$this->arrOptions = array_merge($this->arrOptions, array_intersect_key($arrData, array_flip(array_intersect($this->arrAttributes, $GLOBALS['ISO_CONFIG']['variant_options']))));

		// Unset arrDownloads cache
		$this->arrDownloads = null;
	}


	/**
	 * Return select statement to load product data including multilingual fields
	 * @return string
	 */
	public static function getSelectStatement()
	{
		static $strSelect = '';

		if ($strSelect == '')
		{
			global $objPage;
			$arrSelect = array("'".$GLOBALS['TL_LANGUAGE']."' AS language");

			foreach ($GLOBALS['ISO_CONFIG']['multilingual'] as $attribute)
			{
				$arrSelect[] = "IFNULL(p2.$attribute, p1.$attribute) AS {$attribute}";
			}

			$strSelect = "
SELECT p1.*,
	" . implode(', ', $arrSelect) . ",
	t.class AS product_class,
	c.sorting
FROM tl_iso_products p1
INNER JOIN tl_iso_producttypes t ON t.id=p1.type
LEFT OUTER JOIN tl_iso_products p2 ON p1.id=p2.pid AND p2.language='" . $GLOBALS['TL_LANGUAGE'] . "'
LEFT OUTER JOIN tl_iso_product_categories c ON p1.id=c.pid AND c.page_id=" . (int) $objPage->id;
		}

		return $strSelect;
	}
}

