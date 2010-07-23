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
	protected $arrType;

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
	 * Downloads for this product
	 */
	protected $arrDownloads = array();

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
	 * Isotope object
	 * @var object
	 */
	protected $Isotope;
	

	/**
	 * Construct the object
	 */
	public function __construct($arrData)
	{
		parent::__construct();
		$this->import('Database');
		$this->import('Isotope');

		$this->arrData = $arrData;		

		$this->arrType = $this->Database->execute("SELECT * FROM tl_iso_producttypes WHERE id=".$this->arrData['type'])->fetchAssoc();
		$this->arrAttributes = deserialize($this->arrType['attributes'], true);
		$this->arrCache['list_template'] = $this->arrType['list_template'];
		$this->arrCache['reader_template'] = $this->arrType['reader_template'];
		$this->arrVariantAttributes = $this->arrType['variants'] ? deserialize($this->arrType['variant_attributes']) : array();

		// Cache downloads for this product
		if ($this->arrType['downloads'])
		{
			$this->arrDownloads = $this->Database->execute("SELECT * FROM tl_iso_downloads WHERE pid=".$this->arrData['id'])->fetchAllAssoc();
		}
		
		// Find lowest price
		if ($this->arrType['variants'] && in_array('price', $this->arrVariantAttributes))
		{
			$objProduct = $this->Database->execute("SELECT MIN(price) AS low_price, MAX(price) AS high_price FROM tl_iso_products WHERE pid={$this->id} AND published='1' AND language=''");

			if ($objProduct->low_price < $objProduct->high_price)
			{
				$this->arrCache['low_price'] = $objProduct->low_price;
			}
		}
				
		$this->arrData['original_price'] = $this->arrData['price'];
				
		$this->loadLanguage();
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
			case 'categories':
				return deserialize($this->arrData[$strKey], true);	
			case 'original_price':
				return $this->Isotope->calculatePrice($this->arrData['original_price'], $this, 'original_price', $this->arrData['tax_class']);
				
			case 'price':
				if ($this->arrType['variants'] && !$this->arrData['vid'] && $this->arrCache['low_price'])
				{
					return $this->Isotope->calculatePrice($this->arrCache['low_price'], $this, 'low_price', $this->arrData['tax_class']);
				}
				
				return $this->Isotope->calculatePrice($this->arrData['price'], $this, 'price', $this->arrData['tax_class']);		
			case 'price_override':
				return ($this->arrData['price_override'] ? $this->arrData['price_override'] : '');

			case 'total_price':
				return ($this->quantity_requested ? $this->quantity_requested : 1) * $this->price;

			case 'hasDownloads':
				return count($this->arrDownloads) ? true : false;
				
			case 'description_meta':
				$strDescription = strlen($this->arrData['description_meta']) ? $this->arrData['description_meta'] : $this->arrData['teaser'];
				$strDescription = strlen($strDescription) ? $strDescription : $this->arrData['description'];
				$strDescription = $this->replaceInsertTags($strDescription);
				$strDescription = str_replace(array("\n", "\r", '"'), array(' ' , '', ''), strip_tags($strDescription));
				
				// shorten description to ~200 chars, respect sentences
				if (strlen($strDescription) > 200 && ($pos = utf8_strpos($strText, '.', $limit)) !== false) 
				{ 
					if ($pos < utf8_strlen($strDescription) - 1) 
					{ 
						$strText = utf8_substr($strDescription, 0, $pos+1); 
					} 
				} 
				
				return $strDescription; 
				break;

			default:
				// Initialize attribute
				if (!isset($this->arrCache[$strKey]))
				{
					if (isset($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]))
					{
						switch( $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['inputType'] )
						{
							case 'mediaManager':
								$strClass = $GLOBALS['ISO_GAL'][(strlen($GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery']) ? $GLOBALS['TL_DCA'][$this->strTable]['fields'][$strKey]['attributes']['gallery'] : $this->Isotope->Config->gallery)];
								
								if (!strlen($strClass) || !$this->classFileExists($strClass))
									$strClass = 'IsotopeGallery';
									
								$varValue = new $strClass($strKey.'_'.$this->id, deserialize($this->arrData[$strKey]));
								$varValue->href_reader = $this->href_reader;
						}
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
					}

					$this->arrCache[$strKey] = $varValue ? $varValue : deserialize($this->arrData[$strKey]);
				}

				return $this->arrCache[$strKey];
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
				$this->arrData['href_reader'] = $this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($varValue)->fetchAssoc(), '/product/' . $this->arrData['alias']);
				break;
				
			case 'reader_jumpTo_Override':
				$this->arrData['href_reader'] = $varValue;
				break;
				
			case 'sku':
			case 'name':
			case 'price':
				$this->arrData[$strKey] = $varValue;
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
	//!@todo: Confirm that files are available, possibly on __wakeup() ?
	public function getDownloads()
	{
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
		
		foreach( $this->arrOptions as $name => $value )
		{
			$arrOptions[] = $this->getProductOptionValues($name, $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$name]['inputType'], $value);
		}
		
		return $arrOptions;
	}
	
	
	/**
	 * Set options data and validate variant
	 */
	public function setOptions(array $arrOptions)
	{
		$this->arrOptions = $arrOptions;
		
		$this->validateVariant();
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
		$this->validateVariant();
		
		$objTemplate = new FrontendTemplate($strTemplate);
		
		$arrProductOptions = array();
		$arrAjaxOptions = array();
		$arrAttributes = $this->getAttributes();
		
		foreach( $arrAttributes as $attribute => $varValue )
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['is_customer_defined'] || $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['add_to_product_variants'])
			{
				$objTemplate->hasOptions = true;
				$arrProductOptions[$attribute] = $this->generateProductOptionWidget($attribute);
				
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['add_to_product_variants'])
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
		
		
		if ($this->Input->post('FORM_SUBMIT') == 'iso_product_'.$this->id && !$this->doNotSubmit)
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
		
		$objTemplate->original_price = $this->formatted_original_price;
		$objTemplate->options = $arrProductOptions;	
		$objTemplate->hasOptions = count($arrProductOptions) ? true : false;
		
		$objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$objTemplate->formId = 'iso_product_'.$this->id;
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->formSubmit = 'iso_product_'.$this->id;
		
		$GLOBALS['TL_MOOTOOLS'][] = "<script type=\"text/javascript\">new IsotopeProduct('" . $objModule->id . "', '" . $this->id . "', ['ctrl_" . implode("_".$this->id."', 'ctrl_", $arrAjaxOptions) . "_".$this->id."'], {language: '" . $GLOBALS['TL_LANGUAGE'] . "'});</script>";
		
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
	
	
	public function applyPriceRules($objProduct)
	{
		if(is_array($GLOBALS['TL_HOOKS']['isoCartPriceRules']) && count($GLOBALS['TL_HOOKS']['isoCartPriceRules']))
		{
			foreach($GLOBALS['TL_HOOKS']['isoCartPriceRules'] as $callback)
			{									
				$this->import($callback[0]);
				
				$arrReturn = $this->$callback[0]->$callback[1]($objProduct);
			}
		}	
		
		return $arrReturn;	
	}
	
	/**
	 * Generate the product data on ajax update
	 */
	public function generateAjax()
	{
		$this->validateVariant();
		
		// Find lowest price	@WHY???   
			/*if ($this->arrType['variants'] && in_array('price', $this->arrVariantAttributes))
		{
			$arrSearch = array();
			foreach( $this->arrOptions as $k => $v )
			{
				if (strlen($v))
				{
					$arrSearch[$k] = $v;
				}
			}
			
		
			$objProduct = $this->Database->prepare("SELECT MIN(price) AS low_price, MAX(price) AS high_price FROM tl_iso_products WHERE pid={$this->id} AND published='1' AND language=''" . (count($arrSearch) ? " AND " . implode("=? AND ", array_keys($arrSearch)) . "=?" : ''))->execute($arrSearch);
			
			
			if ($objProduct->low_price < $objProduct->high_price)
			{
				$this->arrCache['low_price'] = $objProduct->low_price;
			}
			else
			{
				unset($this->arrCache['low_price']);
				$this->arrData['price'] = $objProduct->price;
			}
			
		}*/
		
		$arrOptions = array();
		$arrAttributes = $this->getAttributes();
		
		foreach( $arrAttributes as $attribute => $varValue )
		{
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['add_to_product_variants'])
			{
				$arrOptions[] = array
				(
					'id'		=> ('ctrl_' . $attribute . '_' . $this->id),
					'html'		=> $this->generateProductOptionWidget($attribute, true),
				);
			}
			elseif (in_array($attribute, $this->arrVariantAttributes))
			{
				if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['inputType'] == 'mediaManager')
				{
					$objGallery = $this->$attribute;
					
					foreach( array('large', 'medium', 'thumbnail', 'gallery') as $type )
					{
						$arrOptions[] = array
						(
							'id'		=> ($attribute . '_' . $this->id . '_' . $type . 'size'),
							'html'		=> $objGallery->generateMainImage($type),
						);
					}
					
					$arrOptions[] = array
					(
						'id'		=> ($attribute . '_' . $this->id . '_gallery'),
						'html'		=> $objGallery->generateGallery(),
					);
				}
				else
				{
					$arrOptions[] = array
					(
						'id'		=> ($attribute . '_' . $this->id),
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
		$strBuffer = '';
		
		switch($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['inputType'])
		{
			case 'mediaManager':
				return $this->$attribute;
				break;

			case 'select':
			case 'radio':
			case 'checkbox':
				if($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['use_alternate_source'])
				{																											
					$objData = $this->Database->prepare("SELECT * FROM " . $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['list_source_table'] . " WHERE id=?")
											  ->limit(1)									 
											  ->execute($varValue);
					
					if(!$objData->numRows)
					{										
						$strBuffer = $varValue;
					}
					else
					{
						//!@todo this is not going to work, whats this?
						$strBuffer = array
						(
							'id'	=> $varValue,
							'raw'	=> $objData->fetchAssoc(),
						);
					}
				}
				else
				{
					//check for a related label to go with the value.
					$arrOptions = deserialize($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['option_list']);
					$varValues = deserialize($varValue);
					$arrLabels = array();
					
					if (is_array($arrOptions) && count($arrOptions))
					{
						foreach($arrOptions as $option)
						{
							if(is_array($varValues))
							{
								if(in_array($option['value'], $varValues))
								{
									$arrLabels[] = $option['label'];
								}
							}
							else
							{	
								if($option['value']===$v)
								{
									$arrLabels[] = $option['label'];
								}
							}
						}
					}
					
					if($arrLabels)
					{									
						$strBuffer = join(',', $arrLabels); 
					}
				}
				break;
				
			case 'textarea':
				$strBuffer = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['use_rich_text_editor'] ? $varValue : nl2br($varValue);
				break;
																																		
			default:
				switch( $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['eval']['rgxp'] )
				{
					case 'price':
						if ($attribute == 'price' && $this->arrType['variants'] && !$this->arrData['vid'] && $this->arrCache['low_price'])
						{
							$strBuffer = sprintf($GLOBALS['TL_LANG']['MSC']['priceRangeLabel'], $this->Isotope->formatPriceWithCurrency($varValue));
						}
						else
						{
							$strBuffer = $this->Isotope->formatPriceWithCurrency($varValue);
						}
						break;
						
					default:
						$strBuffer = $varValue;
						break;
				}
				break;
		}
		
		if (in_array($attribute, $this->arrVariantAttributes))
		{
			return '<span id="' . $attribute . '_' . $this->id . '">' . $strBuffer . '</span>';
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
		
		if ($arrData['attributes']['add_to_product_variants'] && is_array($arrData['options']))
		{
			$arrData['eval']['includeBlankOption'] = true;
			$arrSearch = array('pid'=>$this->arrData['id']);
			
			foreach( $this->arrOptions as $name => $value )
			{
				if ($name != $strField && $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$name]['attributes']['add_to_product_variants'] && strlen($value))
				{
					$arrSearch[$name] = $value;
				}
			}
			
			$arrOptions = $this->Database->prepare("SELECT " . $strField . " FROM tl_iso_products WHERE language='' AND published='1' AND " . implode("=? AND ", array_keys($arrSearch)) . "=? GROUP BY " . $strField)->execute($arrSearch)->fetchEach($strField);
			
			foreach( $arrData['options'] as $k => $v )
			{
				if (is_array($v))
				{
					foreach( $v as $kk => $vv )
					{
						if (!in_array($kk, $arrOptions))
						{
							unset($arrData['options'][$k][$kk]);
						}
					}
					
					if (!count($arrData['options'][$k]))
					{
						unset($arrData['options'][$k]);
					}
				}
				else
				{
					if (!in_array($k, $arrOptions))
					{
						unset($arrData['options'][$k]);
					}
				}
			}
		}
		
		if (is_array($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']) && count($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']))
		{
			foreach( $GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback'] as $callback )
			{
				$this->import($callback[0]);
				$arrData = $this->{$callback[0]}->{$callback[1]}($strField, $arrData, $this);
			}
		}
		
		$objWidget = new $strClass($this->prepareForWidget($arrData, $strField));
					
		$objWidget->storeValues = true;
		$objWidget->tableless = true;
		$objWidget->id .= "_" . $this->id;
		
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == 'iso_product_'.$this->id)
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
				
				$this->arrOptions[$strField] = $varValue;
			}
		}
		
		if ($objWidget instanceof uploadable)
		{
			$this->hasUpload = true;
		}
		
		return $objWidget->parse();
	}
	
	
	/**
	 * Parse options for cart/checkout listing
	 */
	//!@todo I dislike the listing approach, we might find a better solution
	protected function getProductOptionValues($strField, $inputType, $varValue)
	{	
		$arrData = $GLOBALS['TL_DCA']['tl_iso_products']['fields'][$strField];
		
		switch($inputType)
		{
			case 'radio':
			case 'checkbox':
			case 'select':
				
				//get the actual labels, not the key reference values.
				$arrOptions = $this->getOptionList($arrData['attributes']);
				
				if(is_array($varValue))
				{
					foreach($varValue as $value)
					{
						foreach($arrOptions as $option)
						{
							if($option['value']==$value)
							{
								$varOptionValues[] = $option['label'];
								break;
							}
						}
					}	
				}
				else
				{
					foreach($arrOptions as $option)
					{
						if($option['value']==$varValue)
						{
							$varOptionValues[] = $option['label'];
							break;
						}
					}
				}
				break;
				
			default:
				//these values are not by reference - they were directly entered.  
				if(is_array($varValue))
				{
				  foreach($varValue as $value)
				  {
					$varOptionValues[] = $value;
				  }
				}
				else
				{
				  if($arrData['eval']['rgxp'])
				  {
				  	switch($arrData['eval']['rgxp'])
					{
						case 'date':
						case 'time':
						case 'datim':
							$varValue = $this->parseDate($GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'].'Format'], $varValue);
				  			break;
						default:
							break;
					}
				  }
				  
				  $varOptionValues[] = $varValue;
				}
				
				break;
		
		}		
		
		$arrValues = array
		(
			'name'		=> $arrData['label'][0],
			'values'	=> $varOptionValues			
		);
		
		return $arrValues;
	}


	protected function getOptionList($arrAttributeData)
	{
		if($arrAttributeData['use_alternate_source']==1)
		{
			if(strlen($arrAttributeData['list_source_table']) > 0 && strlen($arrAttributeData['list_source_field']) > 0)
			{
				//$strForeignKey = $arrAttributeData['list_source_table'] . '.' . $arrAttributeData['list_source_field'];
				$objOptions = $this->Database->execute("SELECT id, " . $arrAttributeData['list_source_field'] . " FROM " . $arrAttributeData['list_source_table']);
				
				if(!$objOptions->numRows)
				{
					return array();
				}
				
				while($objOptions->next())
				{
					$arrValues[] = array
					(
						'value'		=> $objOptions->id,
						'label'		=> $objOptions->$arrAttributeData['list_source_field']
					);
				}
			}
		}
		else
		{
			$arrValues = deserialize($arrAttributeData['option_list']);
		}
		
		return $arrValues;
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
			if ($GLOBALS['TL_DCA']['tl_iso_products']['fields'][$attribute]['attributes']['add_to_product_variants'])
			{
				$this->generateProductOptionWidget($attribute);
				$arrOptions[$attribute] = $this->arrOptions[$attribute];
			}
		}
		
		if (count($arrOptions))
		{
			$objVariant = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid={$this->id} AND published='1' AND language='' AND " . implode("=? AND ", array_keys($arrOptions)) . "=?")->execute($arrOptions);
			
			// Must match 1 variant, must not match multiple
			if ($objVariant->numRows == 1)
			{
				$arrInherit = deserialize($objVariant->inherit, true);

				$this->arrData['vid'] = $objVariant->id;
				
				foreach( $this->arrVariantAttributes as $attribute )
				{
					if (in_array($attribute, $arrInherit))
						continue;
											
					switch($attribute)
					{
						case 'price':
							$arrReturn = $this->applyPriceRules($objVariant);
		
							if(count($arrReturn))
							{
								$this->arrData['original_price'] = $this->arrData[$attribute];
								$this->arrData['price'] = $arrReturn[0];
								$this->arrData['rules'] = $arrReturn[1];
							}
							else
							{
								$this->arrData[$attribute] = $objVariant->$attribute;
							}
							break;
							
						default:
							$this->arrData[$attribute] = $objVariant->$attribute;
							break;
					}
											
					unset($this->arrCache[$attribute]);
				}
				
				$this->loadLanguage($arrInherit);
			}
			else
			{
				$this->doNotSubmit = true;
			}
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
			
		$intId = $this->arrData['id'];
		$arrAttributes = $this->arrAttributes;
		
		if (strlen($this->arrData['vid']))
		{
			$intId = $this->arrData['vid'];
			$arrAttributes = $this->arrVariantAttributes;
		}
		
		$objLanguage = $this->Database->prepare("SELECT * FROM tl_iso_products WHERE pid=? AND language=?")->limit(1)->execute($intId, $GLOBALS['TL_LANGUAGE']);
		
		if ($objLanguage->numRows)
		{
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
