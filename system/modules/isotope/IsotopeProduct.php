<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 * Copyright (C) 2005 Leo Feyer
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at http://www.gnu.org/licenses/.
 *
 * PHP version 5
 * @copyright  Winans Creative 2009
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


class IsotopeProduct extends Controller
{

	/**
	 * Name of the current table
	 * @var string
	 */
	protected $strTable = 'tl_product_data';
	
	/**
	 * Data array
	 * @var array
	 */
	protected $arrData = array();

	/**
	 * Attributes assigned to this product
	 * @var array
	 */
	protected $arrAttributes = array();

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
	 * product options array
	 * @var array
	 */
	//!@todo this seems not to be in use... it is only filled, never used.
	protected $arrProductOptionsData = array();
		
	/**
	 * for widgets, helps determine the encoding type for a form
	 * @todo this seems not to be in use... it is only filled, never used.
	 * @var boolean
	 */
	protected $hasUpload = false;
	
	/**
	 * for widgets, don't submit if certain validation(s) fail
	 * @var boolean
	 */
	protected $doNotSubmit = false;


	/**
	 * Construct the object
	 */
	public function __construct($arrData)
	{
		parent::__construct();
		$this->import('Database');
		$this->import('Isotope');

		$this->arrData = $arrData;		

		$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")->execute($this->arrData['type']);
		$this->arrAttributes = deserialize($objType->attributes, true);
		$this->arrCache['list_template'] = $objType->list_template;
		$this->arrCache['reader_template'] = $objType->reader_template;

		// Cache downloads for this product
		if ($objType->downloads)
		{
			$this->arrDownloads = $this->Database->prepare("SELECT * FROM tl_product_downloads WHERE pid=?")->execute($this->arrData['id'])->fetchAllAssoc();
		}

		if ($this->hasVariants)
		{
			$objProduct = $this->Database->prepare("SELECT MIN(" . $this->Isotope->Store->priceField . ") AS low_price, MAX(" . $this->Isotope->Store->priceField . ") AS high_price FROM tl_product_data WHERE pid=?")
										 ->execute($this->id);

			$this->low_price = $this->Isotope->calculatePrice($objProduct->low_price, $this->arrData['tax_class']);
			$this->high_price = $this->Isotope->calculatePrice($objProduct->high_price, $this->arrData['tax_class']);
		}
		else
		{
			$this->low_price = $this->Isotope->calculatePrice($this->arrData[$this->Isotope->Store->priceField], $this->arrData['tax_class']);
			$this->high_price = $this->Isotope->calculatePrice($this->arrData[$this->Isotope->Store->priceField], $this->arrData['tax_class']);
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

			case 'price':
				return $this->Isotope->calculatePrice($this->arrData[$this->Isotope->Store->priceField], $this->arrData['tax_class']);

			case 'price_override':
				return ($this->arrData[$this->Isotope->Store->priceOverrideField] ? $this->arrData[$this->Isotope->Store->priceOverrideField] : '');

			case 'total_price':
				return ($this->quantity_requested ? $this->quantity_requested : 1) * $this->price;

			case 'low_price':
			case 'high_price':
				return $this->Isotope->calculatePrice($this->arrData[$strKey], $this->arrData['tax_class']);

			case 'hasDownloads':
				return count($this->arrDownloads) ? true : false;

			case 'hasVariants':
				return $this->getFirstChild($this->id) ? true : false;
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
								$varValue = array();
								$arrImages = deserialize($this->arrData[$strKey]);

								if(is_array($arrImages) && count($arrImages))
								{
									foreach( $arrImages as $k => $file )
									{
										$strFile = 'isotope/' . substr($file['src'], 0, 1) . '/' . $file['src'];

										if (is_file(TL_ROOT . '/' . $strFile))
										{
											$objFile = new File($strFile);

											if ($objFile->isGdImage)
											{
												$file['is_image'] = true;

												foreach( array('large', 'medium', 'thumbnail', 'gallery') as $size )
												{
													$strImage = $this->getImage($strFile, $this->Isotope->Store->{$size . '_image_width'}, $this->Isotope->Store->{$size . '_image_height'});
													$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);

													$file[$size] = $strImage;

													if (is_array($arrSize) && strlen($arrSize[3]))
													{
														$file[$size . '_size'] = $arrSize[3];
													}
												}

												$varValue[] = $file;
											}
										}
									}
								}
								break;
						}
					}

					switch( $strKey )
					{
						case 'formatted_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->price);
							break;
							
						case 'formatted_low_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->low_price);
							break;
							
						case 'formatted_high_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->high_price);
							break;
							
						case 'formatted_total_price':
							$varValue = $this->Isotope->formatPriceWithCurrency($this->total_price);
							break;

						case 'images':
							// No image available, add default image
							if (!count($varValue) && is_file(TL_ROOT . '/' . $this->Isotope->Store->missing_image_placeholder))
							{
								foreach( array('large', 'medium', 'thumbnail', 'gallery') as $size )
								{
									$strImage = $this->getImage($this->Isotope->Store->missing_image_placeholder, $this->Isotope->Store->{$size . '_image_width'}, $this->Isotope->Store->{$size . '_image_height'});
									$arrSize = @getimagesize(TL_ROOT . '/' . $strImage);

									$file[$size] = $strImage;

									if (is_array($arrSize) && strlen($arrSize[3]))
									{
										$file[$size . '_size'] = $arrSize[3];
									}
								}

								$varValue[] = $file;
							}
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
			case 'low_price':
			case 'high_price':
				$this->arrData[$strKey] = $varValue;
				break;

			case 'price':
				$this->arrData[$this->Isotope->Store->priceField] = $varValue;
				break;

			case 'price_override':
				$this->arrData[$this->Isotope->Store->overridePriceField] = $varValue;
				break;

			default:
				$this->arrCache[$strKey] = $varValue;
		}

	}


	/**
	 * Destroy unnessessary data when serializing
	 */
	public function __sleep()
	{
		//clean up product object - remove non-essential data to reduce table size.
		unset($this->arrData['description'], $this->arrData['teaser']);

		return array('arrAttributes', 'arrDownloads', 'arrData', 'arrOptions');
	}


	/**
	 * Make sure required data is available
	 */
	public function __wakeup()
	{
		$this->import('Config');
		$this->import('Input');
		$this->import('Environment');
		$this->import('Session');
		$this->import('Database');
		$this->import('Isotope');
	}


	//!@todo this is really a bad function name!
	public function getFirstChild($intId)
	{
		$objChild = $this->Database->prepare("SELECT id FROM tl_product_data WHERE pid=?")->execute($intId);

		return (!$objChild->numRows ? false : true);
	}
	
	
	/**
	 * Return the current record as associative array
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


	public function getOptions()
	{
		return $this->arrOptions;
	}


	/**
	 * Return all attributes for this product
	 */
	public function getAttributes()
	{
		$arrData = array();

		foreach( $this->arrAttributes as $attribute )
		{
			$arrData[$attribute] = $this->$attribute;
		}

		return $arrData;
	}


	/**
	 * Set subproduct attributes for product variants
	 */
	public function setVariant($intId, $strVariantFields)
	{
		$strPriceField = $this->Isotope->Store->priceField;

		$objVariant = $this->Database->prepare("SELECT id, sku, weight, " . $strPriceField . ", " . $strVariantFields . ", images FROM " . $this->strTable . " WHERE id=?")->limit(1)->execute($intId);

		if(!$objVariant->numRows)
			return;

		$arrValues = $objVariant->fetchAssoc();

		foreach($arrValues as $k=>$v)
		{
			switch($k)
			{
				case $strPriceField:
					$this->arrData[$k] = $this->Isotope->calculatePrice($v, $this->arrData['tax_class']);
					break;
				case 'images':
					if(!$v)
						break;
				default:
					$this->arrData[$k] = $v;
			}
		}
	}
	
	
	/**
	 * Generate a product template
	 */
	public function generate($strTemplate, &$objModule, $intParentProductId = 0)
	{
		$objTemplate = new FrontendTemplate($strTemplate);

//		$objTemplate->setData($arrData);
		
		$arrEnabledOptions = array();
		$arrVariantOptionFields = array();
		$arrProductOptions = array();
		$arrAttributes = $this->getAttributes();
	
		foreach( $arrAttributes as $attribute => $varValue )
		{
			switch( $attribute )
			{
				case 'images':
					if (is_array($varValue) && count($varValue))
					{
						$objTemplate->hasImage = true;
						
						//$objTemplate->mainImage = array_shift($varValue);
						$objTemplate->mainImage = $varValue[0];
						
						//if (count($varValue))
						//{
						$objTemplate->hasGallery = true;
						$objTemplate->gallery = $varValue;
						//}
					}
					break;
					
				default:
					if ($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['is_customer_defined'])
					{
						$objTemplate->hasOptions = true;
						
						if($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['add_to_product_variants'])
						{					
							$blnIsMergedOptionSet = true;
							$arrVariantOptionFields[] = $attribute;	
						}
						else
						{
							$arrAttributeData = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes'];
							$arrEnabledOptions[] = $attribute;	
							$arrProductOptions[$attribute] = $this->generateProductOptionWidget($attribute, $GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]);
						}
					}
					else
					{						
						switch($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['type'])
						{
							case 'select':
							case 'radio':
							case 'checkbox':
								
								if($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['use_alternate_source'])
								{																											
									$objData = $this->Database->prepare("SELECT * FROM " . $GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['list_source_table'] . " WHERE id=?")
															  ->limit(1)									 
															  ->execute($varValue);
									
									if(!$objData->numRows)
									{										
										$objTemplate->$attribute = $varValue;
									}
									else
									{
										$objTemplate->$attribute = array
										(
											'id'	=> $varValue,
											'raw'	=> $objData->fetchAssoc(),
										);
									}
								}
								else
								{
									//check for a related label to go with the value.
									$arrOptions = deserialize($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['option_list']);
									$varValues = deserialize($varValue);
									$arrLabels = array();
									
									if($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['is_visible_on_front'])
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
										
										if($arrLabels)
										{									
											$objTemplate->$attribute = join(',', $arrLabels); 
										}
										
									}
								}
								break;
								
							case 'textarea':
								$objTemplate->$attribute = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['use_rich_text_editor'] ? $varValue : nl2br($varValue);
								break;
																																		
							default:
								if(!isset($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['is_visible_on_front']) || $GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['is_visible_on_front'])
								{
									//just direct render
									$objTemplate->$attribute = $varValue;
								}
								break;
						}
					}
					break;
			}
		}

		if($blnIsMergedOptionSet && count($arrVariantOptionFields))
		{
 			$objTemplate->hasVariants = true;
 			
			// Create a special widget that combines all option value combos that are enabled.
			$arrData = array
			(
	            'name'			=> 'subproducts',
	            'description'	=> &$GLOBALS['TL_LANG']['tl_product_data']['product_options'],
	            'inputType'		=> 'select',          
	            'options'		=> $this->getSubproductOptionValues(($intParentProductId ? $intParentProductId : $this->id), $arrVariantOptionFields),
	            'eval'			=> array('mandatory'=>true)
			);
       
			$arrAttributeData = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$k]['attributes'];

			$strHtml = $this->generateProductOptionWidget('product_variants', $arrData, $arrVariantOptionFields);
	
			if(strlen($strHtml) && $arrData['options'])
			{
				$arrVariantWidget = array
				(
					'name'      => $k,
					'description'  => $GLOBALS['TL_LANG']['MSC']['labelProductVariants'],                  
					'html'		=> $strHtml 
				);
			}
			else
			{
				$objTemplate->hasVariants = false;
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
		
		$objTemplate->price = $this->formatted_price;
		$objTemplate->low_price = $this->formatted_low_price;
		$objTemplate->high_price = $this->formatted_high_price;
		$objTemplate->priceRangeLabel = $GLOBALS['TL_LANG']['MSC']['priceRangeLabel'];
		$objTemplate->options = $arrProductOptions;	
		$objTemplate->hasOptions = (count($arrProductOptions) || count($arrVariantWidget) ? true : false);
		$objTemplate->variantList = implode(',', $arrVariantOptionFields);
		$objTemplate->variant_widget = $arrVariantWidget;
				
		$objTemplate->optionList = implode(',', $arrEnabledOptions);
		
		$objTemplate->enctype = $this->hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
		$objTemplate->formId = 'iso_product_'.$this->id;
		$objTemplate->action = ampersand($this->Environment->request, true);
		$objTemplate->formSubmit = 'iso_product_'.$this->id;
		
		return $objTemplate->parse();
	}
	
	
	/** 
	 * Return a widget object based on a product attribute's properties.
	 *
	 * @access protected
	 * @param string $strField
	 * @param array $arrData
	 * @param boolean $blnUseTable
	 * @return string
	 */
	protected function generateProductOptionWidget($strField, $arrData, $arrOptionFields = array(), $blnUseTable = false)
	{
		$hideVariants = false;
		
		$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
									
		// Continue if the class is not defined
		if (!$this->classFileExists($strClass))
		{
			return '';
		}

		$arrData['eval']['required'] = $arrData['eval']['mandatory'] ? true : false;
		
		//$GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel']));
		
		$objWidget = new $strClass($this->prepareForWidget($arrData, $strField));
		
		if (is_array($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']) && count($GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback']))
		{
			foreach( $GLOBALS['ISO_ATTR'][$arrData['attributes']['type']]['callback'] as $callback )
			{
				$this->import($callback[0]);
				$arrData = $this->{$callback[0]}->{$callback[1]}($arrData, $arrData['attributes'], $objWidget, $this);
			}
		}
					
		$objWidget->storeValues = true;
		$objWidget->tableless = true;
		$objWidget->name .= "[" . $this->id . "]";
		$objWidget->id .= "_" . $this->id;
		
		// Validate input
		if ($this->Input->post('FORM_SUBMIT') == 'iso_product_'.$this->id)
		{
			$GLOBALS['TL_LANG']['ERR']['mandatory'] = $GLOBALS['TL_LANG']['ERR']['mandatoryOption'];
			
			$objWidget->validate();
			$varValue = $objWidget->value;
			
			// Convert date formats into timestamps
			if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
			{
				$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
				$varValue = $objDate->tstamp;
			}

			if ($objWidget->hasErrors())
			{
				$this->doNotSubmit = true;					
			}

			// Store current value
			elseif ($objWidget->submitInput())
			{
				$this->arrOptions[$strField] = $varValue;
				
				//Store this options value to the productOptionsData array which is then serialized and stored for the given product that is being added to the cart.
				
				//Has to collect this data differently - product variant data relies upon actual values specified for the given product ID, where as simple options
				//only rely upon predefined option lists and what ones were actually selected.
				switch($strField)
				{					
					case 'product_variants':
						if(count($arrOptionFields))
						{
							$this->arrProductOptionsData = $this->getSubproductValues($varValue, $arrOptionFields);	//field is implied							
						}
						
						if(!count($this->arrProductOptionsData))
						{
							$hideVariants = true;
						}
						break;
									
					default:
						$this->arrProductOptionsData[] = $this->getProductOptionValues($strField, $arrData['inputType'], $varValue); 
						break;
				}			
			}
		}
		
		if ($objWidget instanceof uploadable)
		{
			$this->hasUpload = true;
		}
		
		if (!$hideVariants)
		{
			$temp .= $objWidget->parse() . '<br />';
			return $temp;
		}
	}
	
	
	protected function getSubproductOptionValues($intPid, $arrOptionList)
	{
		if (!is_array($arrOptionList) || !count($arrOptionList))
			return array();
			
		$strOptionValues = join(',', $arrOptionList);

		$objData = $this->Database->prepare("SELECT id, " . $strOptionValues . ", price FROM tl_product_data WHERE pid=? AND published='1'")
								  ->execute($intPid);
		
		if($objData->numRows < 1)
		{
			return false;
		}
		
		$arrOptionValues = $objData->fetchAllAssoc();

		//include blank option, manual label override
		$arrOptions[''] = $GLOBALS['TL_LANG']['MSC']['emptySelectOptionLabel'];

		foreach($arrOptionValues as $option)
		{
			$arrValues = array();
			
			foreach($arrOptionList as $optionName)
			{
				$arrValues[] = $option[$optionName];
			}
			
			$strOptionValue = join(',', $arrValues) . ' - ' . $this->Isotope->formatPriceWithCurrency($option['price']);
			
			$arrOptions[$option['id']] = $strOptionValue;
		}
		
		return $arrOptions;
	}


	/*
	 * Get the option value data for cart item elaboration.
	 *
	 * @param variant $varValue
	 * @param array $arrOptionFields
	 * @return array
	 */
	protected function getSubproductValues($varValue, $arrOptionFields)
	{
		$strOptionValues = join(',', $arrOptionFields);
						
		//get the selected variant values;
		$objData = $this->Database->prepare("SELECT " . $strOptionValues . " FROM tl_product_data WHERE id=? AND published='1'")
								  ->execute($varValue);
		
		if($objData->numRows < 1)
		{
			return false;
		}
		
		$arrOptionValues = $objData->fetchAllAssoc();
				
		foreach($arrOptionValues as $row)
		{
			foreach($row as $k=>$v)
			{
				$arrAttributeData = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$k]['attributes'];
					
				$arrOptionData[] = array
				(
					'name'		=> $arrAttributeData['name'],
					'values'	=> array($v)		
				);
			}			
		}
		
		return $arrOptionData;
	}
	
	
	protected function getProductOptionValues($strField, $inputType, $varValue)
	{	
		$arrAttributeData = $GLOBALS['TL_DCA']['tl_product_data']['fields'][$strField]['attributes']; //1 will eventually be irrelevant but for now just going with it...
		
		switch($inputType)
		{
			case 'radio':
			case 'checkbox':
			case 'select':
				
				//get the actual labels, not the key reference values.
				$arrOptions = $this->getOptionList($arrAttributeData);
				
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
					$varOptionValues[] = $varValue;
				}
				
				break;
		
		}		
		
		$arrValues = array
		(
			'name'		=> $arrAttributeData['name'],
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

}

