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
 * @author     Fred Bliss <fred@winanscreative.com>
 * @author     Andreas Schempp <andreas@schempp.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


abstract class ModuleIsotopeBase extends Module
{

	/**
	 * URL cache array
	 * @var array
	 */
	private static $arrUrlCache = array();
	
	
	/**
	 * Template
	 * @var string
	 */
	protected $strPriceTemplate = 'stpl_price';
	
	/**
	 * Price String Override Template
	 * @var string
	 */
	protected $strPriceOverrideTemplate = 'stpl_price_override';
	
 
	/**
	 * product options array
	 * @var array
	 */
	protected $arrProductOptionsData = array();
	
	/**
	 * for widgets, helps determine the encoding type for a form
	 * @var boolean
	 */
	protected $hasUpload = false;
	
	/**
	 * for widgets, don't submit if certain validation(s) fail
	 * @var boolean;
	 */
	protected $doNotSubmit = false;
	
	
	public function __construct(Database_Result $objModule, $strColumn='main')
	{
		parent::__construct($objModule, $strColumn);
		
		if (TL_MODE == 'FE')
		{	
			$this->import('Isotope');
			$this->import('IsotopeCart', 'Cart');
			
			if (FE_USER_LOGGED_IN)
			{
				$this->import('FrontendUser', 'User');
			}
			
			// Load isotope javascript class
			$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/isotope/html/isotope.js';
		}
	}
	
		
	
	/**
	 * Generate a button for a given function such as add to cart buttons
	 * @param array
	 * @param string
	 * @return array
	 */
	protected function generateButtons($arrButtonData, $pageId, $strReturnUrl = NULL)
	{
		//$arrButtonTypes = array_keys($arrButtonData);
		
		foreach($arrButtonData as $buttonProperties)
		{									
				if(!strlen($buttonProperties['button_template']))
				{
					throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['missingButtonTemplate'], $buttonProperties['button_template']));
				}else{
					$objTemplate = new FrontendTemplate($buttonProperties['button_template']); //creating one base template object and cloning for specific products
				}
				
				$objTemplate->buttonType = 'link';
				$objTemplate->isAjaxEnabledButton = false;	
				$objTemplate->buttonLabelOrImage = $buttonProperties['button_label'];
				
				//Get list of product ids for the current button
				
				if($buttonProperties['params'])
				{
				
					$arrProductIds = array_keys($buttonProperties['params']);
					
					foreach($arrProductIds as $productId)
					{
								
						//Get the current product Id's params & process
						$objTemplate->buttonId = $buttonProperties['button_id'] . $productId;
						$objTemplate->actionTitle = sprintf($buttonProperties['action_string'], $buttonProperties['params'][$productId]['name']);
						$objTemplate->actionLink = $this->generateActionLinkString($buttonProperties['button_type'], $productId, $buttonProperties['params'][$productId], $pageId);
						$arrButtonHTML[$buttonProperties['button_type']][$productId] = $objTemplate->parse();
						
						
					}
				}
		}	
					
											
			return $arrButtonHTML;
			
			/*		
			Button Model Properties Not yet used - BEGIN

			--------------
			$objTemplate->buttonName = '';						//prefix "button_", NOT USED YET
			$objTemplate->buttonTabIndex = 0;						//tab index (optional)
			$objTemplate->buttonClickEvent = '';					//click event to invoke the button's script.  May be to an AJAX handler or just to a form submit.
			--------------

			Button Model Properties Not yet used - END
		*/	
	}
	
	/**
	 * Generate a link string for various actions such as adding a product to the cart, removing from, or updating.
	 * @param string
	 * @param array
	 * @return string
	 */
	protected function generateActionLinkString($strAction, $intProductId, $arrParams, $pageId)
	{
		$strCacheKeyParams = 'action_' . $strAction . '_';
		$strParams = 'action/' . $strAction . '/';
		
		foreach($arrParams as $k=>$v)
		{
			if(array_key_exists('exclude', $arrParams))
			{
				if(!in_array($k, $arrParams['exclude']))
				{
					$strCacheKeyParams .= $k . '_' . $v . '_';
					$strParams .= $k . '/' . $v . '/';
				}
			}else{
					$strCacheKeyParams .= $k . '_' . $v . '_';
					$strParams .= $k . '/' . $v . '/';
			}
		}

		$strParams .= 'id/' . $intProductId;
		
		$strCacheKey = 'id_' . $intProductId . '_' . $strCacheKeyParams . $arrProduct['tstamp'];


		// Load URL from cache
		if (array_key_exists($strCacheKey, self::$arrUrlCache))
		{			
			return self::$arrUrlCache[$strCacheKey];
		}
		
		
		$strUrl = ampersand($this->Environment->request, ENCODE_AMPERSANDS);
		
		$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
								  ->limit(1)
								  ->execute($pageId);

		if ($objPage->numRows)
		{
				$strUrl = ampersand($this->generateFrontendUrl($objPage->fetchAssoc(), '/' . $strParams));			
		}
				
		self::$arrUrlCache[$strCacheKey] = $strUrl;

		return self::$arrUrlCache[$strCacheKey];
	
	}
	
	
	/**
	 * Generate the required buttons for a products within the current store configuration
	 * @param integer
	 * @return string
	 */
	public function generateButtonsWidget($intStoreConfigurationId, $strFormTemplate, $strButtonTemplate)
	{
		//Get the form template
		$objTemplate = new FrontendTemplate($strFormTemplate);
		
		//Get the button template
		$objButtonTemplate = new FrontendTemplate($strButtonTemplate);
		
		$objButtonTemplate->buttonClickEvent = '.submit(); return false;';
		//$objButtonTemplate->buttonId = '';
		$objButtonTemplate->buttonLabel = '';
		$objButtonTemplate->buttonTabIndex = '';
		
		$objTemplate->action = '';
		$objTemplate->formId = '';
		$objTemplate->method = '';
		$objTemplate->enctype = '';
		$objTemplate->attributes = '';
		$objTemplate->formSubmit  = ''; //(unique form value)
		$objTemplate->hidden  = ''; //(collection of hidden fields)
		$objTemplate->buttons  = ''; //(collection of buttons)
	
		return $strHTML;
	}
	
	
	public function getPageData($id)
	{
		// Get target page
		$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
								  ->limit(1)
								  ->execute($id);
		
		
		if ($objPage->numRows > 0)
		{
				$strUrl = $this->generateFrontendUrl($objPage->fetchAssoc());
			
		}
		
		return $strUrl;
	}
			
	/**
	 * Generate a URL and return it as string
	 * @param object
	 * @param boolean
	 * @return string
	 */
	protected function generateProductUrl($arrProduct, $intJumpTo, $strProductIdKey = 'id', $blnAddArchive=false)
	{
		global $objPage;
		$strCacheKey = $strProductIdKey . '_' . $arrProduct[$strProductIdKey] . '_' . $arrProduct['tstamp'];

		// Load URL from cache
		if (array_key_exists($strCacheKey, self::$arrUrlCache))
		{
			return self::$arrUrlCache[$strCacheKey];
		}

		$strUrl = ampersand($this->Environment->request, ENCODE_AMPERSANDS);

		// Get target page
		$objJump = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
								  ->limit(1)
								  ->execute($intJumpTo);
	
		if ($objJump->numRows > 0)
		{
			$strUrl = ampersand($this->generateFrontendUrl($objJump->fetchAssoc(), '/product/' . $arrProduct['alias']));
		}
		else
		{
			
			$strUrl = ampersand($this->generateFrontendUrl(array('id'=>$objPage->id, 'alias'=>$objPage->alias), '/details/product/' . $arrProduct['alias']));
		}

		self::$arrUrlCache[$strCacheKey] = $strUrl;
			
		return self::$arrUrlCache[$strCacheKey];
	}

	

	/**
	 * Generate a link and return it as string
	 * @param string
	 * @param object
	 * @param boolean
	 * @return string
	 */
	protected function generateProductLink($strLink, $arrProduct, $intJumpTo, $strProductIdKey = 'id', $blnAddArchive=false)
	{

		// Internal link
		return 	$this->generateProductUrl($arrProduct, $intJumpTo, $strProductIdKey, $blnAddArchive);
	}
	
	/**
	 * Sort out protected archives
	 * @param array
	 * @return array
	 */
	protected function sortOutProtected($arrArchives)
	{
		if (BE_USER_LOGGED_IN)
		{
			return $arrArchives;
		}

		$this->import('FrontendUser', 'User');
		$objArchive = $this->Database->execute("SELECT id, protected, groups FROM tl_news_archive WHERE id IN(" . implode(',', $arrArchives) . ")");
		$arrArchives = array();

		while ($objArchive->next())
		{
			if ($objArchive->protected)
			{
				$groups = deserialize($objArchive->groups, true);

				if (!is_array($this->User->groups) || count($this->User->groups) < 1 || !is_array($groups) || count($groups) < 1)
				{
					continue;
				}

				if (count(array_intersect($groups, $this->User->groups)) < 1)
				{
					continue;
				}
			}

			$arrArchives[] = $objArchive->id;
		}

		return $arrArchives;
	}
	

	
	/**
	 * Generate a price string based on an product price, a template, and any pricing rules that apply.
	 *
	 * @param integer
	 * @return string (formatted html)
	 *
	 */
	protected function generatePriceStringOverride($strPriceOverrideTemplate, $varValue)
	{
			
		$objTemplate = new FrontendTemplate($strPriceOverrideTemplate);
				
		$objTemplate->price = $varValue;
		
		
		//$objPriceTemplate->priceNote = $this->getPriceNote($intProductId); - Additional note to appear below the price itself, perhaps indicating what price includes?
				
		return $objTemplate->parse();
		
	}
	
	
	protected function generatePrice($fltPrice, $strTemplate='stpl_price')
	{
		$objTemplate = new FrontendTemplate($strTemplate);
		
		$objTemplate->price = $this->Isotope->formatPriceWithCurrency($fltPrice, null, true);
		
		return $objTemplate->parse();
	}
	
	
	/**
	 * Get the final price including related price rules
	 *
	 */
	protected function getFinalPrice($intProductId)
	{
		$objProductPrice = $this->Database->prepare("SELECT price FROM tl_product_data WHERE id=?")
										  ->limit(1)
										  ->execute($intProductId);
		
		if($objProductPrice->numRows < 1)
		{
			return '';
		}
		
		return $objProductPrice->price;
	}
	
	/**
	 * Get any messages relevant to a particular product
	 *
	 * @param integer
	 * @param string
	 * return array
	 */
	protected function getProductMessages($intProductId)
	{
		return array();
	
	}
	
	
	protected function formatProductData($arrProductData)
	{
		global $objPage;
		
 		foreach($arrProductData as $row)
		{	
			$intTotalPrice = $row['price'] * $row['quantity_requested'];
			
			$row['id'] = $row['product_id'];	//needed to ensure all product links work for now.
			
			
			$arrImages = deserialize($row['main_image'], true);
	
			$arrFormattedProductData[] = array
			(
				'id'				=> $row['product_id'],
				'image'				=> (is_array($arrImages[0]) ? $this->getImage('isotope/' . substr($arrImages[0]['src'], 0, 1) . '/' . $arrImages[0]['src'], $this->Isotope->Store->gallery_image_width, $this->Isotope->Store->gallery_image_height) : ""),
				'name'				=> $row['name'],
				'link'				=> ($this->iso_reader_jumpTo ? $this->generateProductLink($row['alias'], $row, $this->iso_reader_jumpTo, 'id') : $row['link']),
				'price'				=> $this->generatePrice($row['price'], $this->strPriceTemplate),
				'total_price'		=> $this->generatePrice($intTotalPrice, 'stpl_total_price'),
				'quantity'			=> $row['quantity_requested'],
				'option_values'		=> $row['product_options'],
				'cart_item_id'		=> $row['cart_item_id'],
				'remove_link'		=> $this->generateActionLinkString('remove_from_cart', $row['cart_item_id'], array('quantity'=>0, 'source_cart_id'=>$row['source_cart_id']), $objPage->id),
				'remove_link_title' => sprintf($GLOBALS['TL_LANG']['MSC']['removeProductLinkTitle'], $row['name'])
			
			);
		}
		
		return $arrFormattedProductData;
	}
	
	
	protected function getRootAssetImportPath($intStoreId)
	{
		$objPath = $this->Database->prepare("SELECT root_asset_import_path FROM tl_store WHERE id=?")
										  ->limit(1)
										  ->execute($intStoreId);
		
		if($objPath->numRows < 1)
		{
			return '';
		}
		
		$strFilePath = $objPath->root_asset_import_path;
	
	
		return $strFilePath;
		
	}
	
	
	/**
	 * Generate a Teaser text that terminates at the end of the closest sentence to the teaser length value.
	 *
	 * @param mixed
	 * @param object
	 * @return string
	 */
	protected function generateTeaser($varValue, $intLength=0)
	{
		if($intLength == 0 || empty($intLength))
		{
			$intLength = $GLOBALS['TL_LANG']['MSC']['teaserLength'];
		}
		
		$string = substr($varValue, 0, $intLength);
		
		
		if(!strpos($string, "."))
		{
			//Get the position of the first period after the first X number of characters
			$intFirstPeriod = strpos($varValue, ".", $intLength);
			
			$intFirstPeriod++;
			
			$string = substr($varValue, 0, $intFirstPeriod);
		}
			
		$char = strtolower(strlen($string));
								
		while ($char > 0)
		{
			if ($string{$char} == ".")
			{
				break;
			}else{
			
				$char = $char - 1;
			}
		}
		
		$char++;
		$string = substr($string, 0, $char); 
		
		return $string;
	}
	
	
	/**
	 * Clean a query string of any invalid characters to prevent XSS attacks, etc.
	 *
	 * @param string
	 * @return string
	 */
	protected function sanitizeQueryString($strValue) 
	{  
    	return ereg_replace("[^A-Za-z0-9-]", "", $strValue);  
	}  
	
	protected function verifyFilter($key, $value)
	{
		
		$objSampleData = $this->Database->prepare("SELECT " . $key . " FROM tl_product_data WHERE " . $key . " IS NOT NULL")
										->limit(1)
										->execute();
				
		if(!is_int($objSampleData->$key) && !is_string($value))
		{
			return false;
		}elseif(!is_int((int)$value)){
			return false;
		}
				
		return true;
	}
	
	public function getFilterData($intAttributeId, $strAttributeType)
	{
		//global $objPage;
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($intAttributeId);
		
		
		if($objAttributeData->numRows < 1)
		{
			return '';
		}
		
		if($objAttributeData->use_alternate_source==1)
		{
			$objLinkData = $this->Database->prepare("SELECT id, " . $objAttributeData->list_source_field . " FROM " . $objAttributeData->list_source_table)
										  ->execute();
			
			if($objLinkData->numRows < 1)
			{
				return array();
			}
			
			$arrLinkValues = $objLinkData->fetchAllAssoc();
			
			$filter_name = $objAttributeData->list_source_field;
						
			foreach($arrLinkValues as $value)
			{
				$arrLinkData[] = array
				(
					'value'		=> $value[$objAttributeData->id],
					'label'		=> $value[$objAttributeData->list_source_field]
				);
			
			}
			
		}else{
		
			$this->import('ProductCatalog');
			
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			$filter_name = strtolower($this->ProductCatalog->mysqlStandardize($objAttributeData->name));
			
			foreach($arrLinkValues as $value)
			{
				$arrLinkData[] = array
				(
					'value'		=> $value['value'],
					'label'		=> $value['label']
				);
			
			}
		}
		
		return $arrLinkData;
			
	}
	
	/**
	 *	getFilterListData - Grab a list of values and labels for a filter by attribute Id and by a list of eligible values.  If the array is empty, grab all values. 
	 *	@param integer
	 *  @param array
	 *  @return array
	 */
	protected function getFilterListData($intAttributeId, $arrValues = array())
	{
		if(sizeof($arrValues) < 1)
		{
			$blnGrabAll = true;
		}
				
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field, field_name FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($intAttributeId);
									  
		if($objAttributeData->numRows < 1)
		{
			return array();
		}
		$arrListData[] = array
		(
			'value'		=> NULL,
			'label'		=> $GLOBALS['TL_LANG']['MSC']['selectItemPrompt']
		);
		
		if($objAttributeData->use_alternate_source==1)
		{

			$objLinkData = $this->Database->prepare("SELECT id, " . $objAttributeData->list_source_field . " FROM " . $objAttributeData->list_source_table)
										  ->execute();
			
			if($objLinkData->numRows < 1)
			{
				return array();
			}
			
			$arrLinkValues = $objLinkData->fetchAllAssoc();
						
			$filter_name = $objAttributeData->list_source_field;
									
			if($blnGrabAll)
			{
				foreach($arrLinkValues as $value)
				{
					$arrListData[] = array
					(
						'value'		=> $value[$objAttributeData->id],
						'label'		=> $value[$objAttributeData->list_source_field]
					);
				}
				
			}else{
				
							
				
				foreach($arrLinkValues as $value)
				{
					if(in_array($value['id'], $arrValues))
					{
						$arrListData[] = array
						(
							'value'		=> $value['id'],
							'label'		=> $value[$objAttributeData->list_source_field]
						);
					}			
				}
				
			}
				
		}else{
					
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			if($blnGrabAll)
			{
				foreach($arrLinkValues as $value)
				{
				
					$arrListData[] = array
					(			
						'value'		=> $value['value'],
						'label'		=> $value['label']
					);

				}
									
			}else{
				
				foreach($arrLinkValues as $value)
				{
					
					if(in_array($value['value'], $arrValues))
					{
						$arrListData[] = array
						(			
							'value'		=> $value['value'],
							'label'		=> $value['label']
						);
					
					}
	
				}
							
			}
			
			
		}
		
		usort($arrListData, array($this, "sortArrayAsc"));
		
	
		return $arrListData;
	
	
	}
	
	protected function sortArrayAsc($a, $b)
	{
		return strcasecmp($a['label'], $b['label']);
	}
	
	
	protected function getCookieTimeWindow($intStoreId)
	{
		$objCookieTimeWindow = $this->Database->prepare("SELECT cookie_duration FROM tl_store WHERE id=?")	
											  ->limit(1)
											  ->execute($intStoreId);
		
		if($objCookieTimeWindow->numRows < 1)
		{
			return 0;
		}
				
		return $objCookieTimeWindow->cookie_duration;
	}
	
	

	/**
	 * determine the form's action method.
	 * @access protected
	 * @param string $strKey
	 * @return string
	 */
	protected function getRequestData($strKey)
	{
		return strlen($this->Input->post($strKey)) ? $this->Input->post($strKey) : $this->Input->get($strKey);
	}
	
	/** 
	 * Return a widget object based on a product attribute's properties
	 * @access public
	 * @param string $strField
	 * @param array $arrData
	 * @param boolean $blnUseTable
	 * @return string
	 */
	public function generateProductOptionWidget($strField, $arrData = array(), $strFormId, $arrOptionFields, $blnUseTable = false)
	{
		
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
										
			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))// || !$arrData['eval']['isoEditable'])
			{
				
				return false;	
			}
		
	
			$objWidget = new $strClass($this->prepareForWidget($arrData, $strField));
			
			$objWidget->storeValues = true;
			
			sizeof($arrData['options']) ? $objWidget->options = $arrData['options'] : $objWidget->options = NULL;
			//$_SESSION['FORM_DATA'][$strField] = $objWidget->value;
				
			// Validate input
			if ($this->Input->post('FORM_SUBMIT') == $strFormId)
			{
				
				$objWidget->validate();
				$varValue = $objWidget->value;
				$objWidget->value = NULL;
				
				// Convert date formats into timestamps
				if (strlen($varValue) && in_array($arrData['eval']['rgxp'], array('date', 'time', 'datim')))
				{
					$objDate = new Date($varValue, $GLOBALS['TL_CONFIG'][$arrData['eval']['rgxp'] . 'Format']);
					$varValue = $objDate->tstamp;
				}
	
				if ($objWidget->hasErrors())
				{
					$this->doNotSubmit = true;
					$_SESSION['FORM_DATA'][$strField] = $varValue;
				}
	
				// Store current value
				elseif ($objWidget->submitInput() && !$this->doNotSubmit)
				{
					//Store this options value to the productOptionsData array which is then serialized and stored for the given product that is being added to the cart.
					
					//Has to collect this data differently - product variant data relies upon actual values specified for the given product ID, where as simple options
					//only rely upon predefined option lists and what ones were actually selected.
					switch($strField)
					{					
						case 'product_variants':
							$this->arrProductOptionsData = $this->getSubproductValues($varValue, $arrOptionFields);	//field is implied							
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
					
			//$_SESSION['FORM_DATA'][$strField] = $varValue;
			
			//$varSave = is_array($varValue) ? serialize($varValue) : $varValue;
					
			$temp .= $objWidget->parse() . '<br />';
					
		return $temp;
	}
	
	
	
	private function getProductOptionValues($strField, $inputType, $varValue)
	{	
		
		$arrAttributeData = $this->getProductAttributeData($strField); //1 will eventually be irrelevant but for now just going with it...
		
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
	
	/**
	 * Get the maximum file size that is allowed for file uploads
	 * @return string
	 */
	protected function getMaxFileSize()
	{
		return $GLOBALS['TL_CONFIG']['maxFileSize'];
		//$this->Template->maxFileSize = $GLOBALS['TL_CONFIG']['maxFileSize'];

		/*$objMaxSize = $this->Database->prepare("SELECT MAX(maxlength) AS maxlength FROM tl_form_field WHERE pid=? AND type=? AND maxlength>?")
									 ->execute($this->id, 'upload', 0);

		if ($objMaxSize->maxlength > 0)
		{
			$this->Template->maxFileSize = $objMaxSize->maxlength;
		}*/
	}
	
	/**
	 * Validate product option widgets
	 * @param array $arrOptions
	 * @return void
	 */
	protected function validateOptionValues($arrOptions, $currFormId, $blnProductVariants = false, $intPid = 0)
	{
		if(sizeof($arrOptions) < 1)
		{
			return;
		}
		
		if(!$blnProductVariants)
		{
			foreach($arrOptions as $option)
			{
				$arrAttributeData = $this->getProductAttributeData($option);
				
				if($arrAttributeData['is_customer_defined'])
				{
					$arrOptionFields[] = $k;
							
					$arrData = $this->getDCATemplate($arrAttributeData);	//Grab the skeleton DCA info for widget generation
																	
					$this->generateProductOptionWidget($option, $arrData, $currFormId);
	
				}
																
			}	
		}
		else
		{
			if($intPid>0)
			{
				//Create a special widget that combins all option value combos that are enabled.
				$arrData = array
				(
					'name'			=> 'subproducts',
					'description'	=> &$GLOBALS['TL_LANG']['tl_product_data']['product_options'],
					'inputType'		=> 'select',					
					'options'		=> $this->getSubproductOptionValues($intPid, $arrOptions),
					'eval'			=> array()
				);
				
				//$arrData = $this->getDCATemplate($arrAttributeData);	//Grab the skeleton DCA info for widget generation
	
				$product['options'][] = array
				(
					'name'			=> $k,
					'description'	=> $arrAttributeData['description'],									
					'html'			=> $this->generateProductOptionWidget('product_variants', $arrData, $currFormId, $arrOptions)
				);	
			}
		}	
	}
	
	protected function getDCATemplate($arrAttributeData)
	{
		$arrData['label'] 	= $arrAttributeData['description'];
		$arrData['prompt'] 	= $arrAttributeData['name'];
		$arrData['eval']['mandatory'] = $arrAttributeData['is_required'] ? true : false;

		switch($arrAttributeData['type'])
		{
			case 'text':
				$arrData['inputType'] = 'textCollection';
				$arrData['eval']['collectionsize'] = $arrAttributeData['text_collection_rows'];
				$arrData['eval']['prompt'] = $arrAttributeData['name'];
				$arrData['eval']['maxlength'] = 255;
				break;
			/*case 'select':
				$arrGroups = $this->getSelectList($arrAttributeData);
				$arrData['inputType'] 	= 'select';
				$arrData['default'] 	= '';
				$arrData['options']     = $arrAttributeData['option_list'];
				//$arrData['reference']   = $arrGroups['label'];
				var_dump($arrData['options']);
				break;				*/
			
			case 'options':
			case 'select':
			case 'checkbox':
				$arrAttributeData['type']=='options' ? $strType = 'radio' : $strType = $arrAttributeData['type'];
				
				//$arrOptions = $this->getOptionList($arrAttributeData);	//TODO - needs to be replaced to load option values from enabled subproducts.
				
				//START HERE - either grab from products themselves or else from variant_data serialized values... this would be quicker, but reliable?
				$arrOptions = $this->getSubproductOptionValues($arrAttributeData['name'], array());
					
				$arrData['inputType'] 	= $strType;
				$arrData['default'] 	= '';
				$arrData['options']     = $arrOptions;
				
				if($arrAttributeData['type']=='checkbox') $arrData['eval']['prompt'] = $arrAttributeData['name'];
				//$arrData['reference']   = $arrOptions;
	
				break;
			default:
				break;		
		
		}
		
		return $arrData;
	
	}

	protected function getSubproductOptionValues($intPid, $arrOptionList)
	{
		if (!is_array($arrOptionList) || !count($arrOptionList))
			return array();
			
		$strOptionValues = join(',', $arrOptionList);
		
		$objData = $this->Database->prepare("SELECT id, " . $strOptionValues . ", price FROM tl_product_data WHERE pid=?")
								  ->execute($intPid);
		
		if($objData->numRows < 1)
		{
			return false;
		}
		
		$arrOptionValues = $objData->fetchAllAssoc();
				
		foreach($arrOptionValues as $option)
		{
			$arrValues = array();
			
			foreach($arrOptionList as $optionName)
			{
				$arrValues[] = $option[$optionName];
			}
			
			$strOptionValue = join(',', $arrValues) . ' - ' . $this->Isotope->formatPriceWithCurrency($option['price']);
			
			$arrOptions[] = array
			(
				'value'	=>		$option['id'] . '_' . $intPid,
				'label' => 		$strOptionValue
			);
		}
		
		return $arrOptions;
	}
	
	/*
	 * Get the option value data for cart item elaboration
	 * @param variant $varValue
	 * @param array $arrOptionFields
	 * @return array
	 */
	protected function getSubproductValues($varValue, $arrOptionFields)
	{
			
		$strOptionValues = join(',', $arrOptionFields);
		
		$arrValue = explode('_', $varValue);
		
		//values are stored as <id>_<pid> format, e.g. 1_3
		$intPid = $arrValue[1];
		$intId = $arrValue[0];
		
		//get the selected variant values;
		$objData = $this->Database->prepare("SELECT " . $strOptionValues . " FROM tl_product_data WHERE pid=? AND id=?")
								  ->execute($intPid, $intId);
		
		if($objData->numRows < 1)
		{
			return false;
		}
		
		$arrOptionValues = $objData->fetchAllAssoc();
				
		foreach($arrOptionValues as $row)
		{
			foreach($row as $k=>$v)
			{
			
				$arrAttributeData = $this->getProductAttributeData($k);
					
				$arrOptionData[] = array
				(
					'name'		=> $arrAttributeData['name'],
					'values'	=> array($v)		
				);
			}			
		}
		
		return $arrOptionData;
	
	}
	
	protected function getOptionList($arrAttributeData)
	{
		if($arrAttributeData['use_alternate_source']==1)
		{
			
			if(strlen($arrAttributeData['list_source_table']) > 0 && strlen($arrAttributeData['list_source_field']) > 0)
			{
				$strForeignKey = $arrAttributeData['list_source_table'] . '.' . $arrAttributeData['list_source_field'];
			
			}
		}else{
			$arrValues = deserialize($arrAttributeData['option_list']);
		}
	
		return $arrValues;
	}
	
	/**
	 * Get attribute data and do something with it based on the properties of the attribute.
	 *
	 * @param string
	 * @param integer
	 * @return array
	 *
	 */
	protected function getProductAttributeData($strFieldName)
	{		
		
		$objAttributeData = $this->Database->prepare("SELECT * FROM tl_product_attributes WHERE field_name=?")
										   ->limit(1)
										   ->execute($strFieldName);

		if($objAttributeData->numRows < 1)
		{
			
			return array();
		}
		
		return $objAttributeData->fetchAssoc();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Shortcut for a single product by ID
	 */
	protected function getProduct($intId)
	{
		$arrProducts = $this->getProducts(array($intId));
		
		return array_shift($arrProducts);
	}
	
	/**
	 * Shortcut for a single product by alias (from url?)
	 */
	protected function getProductByAlias($strAlias)
	{
		$objProduct = $this->Database->prepare("SELECT id FROM tl_product_data WHERE alias=?")->limit(1)->execute($strAlias);
		
		if (!$objProduct->numRows)
			return false;
			
		$arrProducts = $this->getProducts(array($objProduct->id));
			
		return array_shift($arrProducts);
	}
	
	/**
	 * Retrieve product data.
	 *
	 * - Deserialize all data
	 * - Empty attribtues which are not enabled on the product type
	 */
	protected function getProducts($arrIds)
	{
		if (!is_array($arrIds) || !count($arrIds))
			return array();
			
		// Product type attributes cache
		$arrAttributes = array();
		
		$arrProducts = array();
		$objProducts = $this->Database->execute("SELECT * FROM tl_product_data WHERE id IN (" . implode(',', $arrIds) . ")");
		
		while( $objProducts->next() )
		{
			if (!isset($arrAttributes[$objProducts->type]))
			{
				$objType = $this->Database->prepare("SELECT * FROM tl_product_types WHERE id=?")->limit(1)->execute($objProducts->type);
				
				$attributeIds = deserialize($objType->attributes);
				
				// Skip this product, it does not have any attributes
				if (!is_array($attributeIds) || !count($attributeIds))
					continue;
					
				$arrAttributes[$objType->id] = $this->Database->execute("SELECT * FROM tl_product_attributes WHERE id IN (" . implode(',', $attributeIds) . ") AND disabled=''")->fetchAllAssoc();
			}
			
			$arrProduct = array
			(
				'raw'			=> $objProducts->row(),
				'href_reader'	=> $this->generateFrontendUrl($this->Database->prepare("SELECT * FROM tl_page WHERE id=?")->execute($this->iso_reader_jumpTo)->fetchAssoc(), '/product/' . $objProducts->alias),
			);
			
			foreach( $arrAttributes[$objProducts->type] as $attribute )
			{
				switch( $attribute['type'] )
				{
					case 'media':
						$varValue = array();
						$arrImages = deserialize($objProducts->{$attribute['field_name']});
						
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
										
	/*
										$varValue[] = array_merge($file, array
										(
											'is_image'		=> true,
											'large'			=> $this->getImage($strFile, $this->Isotope->Store->large_image_width, $this->Isotope->Store->large_image_height),
											'medium'		=> $this->getImage($strFile, $this->Isotope->Store->medium_image_width, $this->Isotope->Store->medium_image_height),
											'thumb'			=> $this->getImage($strFile, $this->Isotope->Store->thumbnail_image_width, $this->Isotope->Store->thumbnail_image_height),
											'gallery'		=> $this->getImage($strFile, $this->Isotope->Store->gallery_image_width, $this->Isotope->Store->gallery_image_height),
											'large_size'	=> sprintf(' width="%s" height="%s"', $this->Isotope->Store->large_image_width, $this->Isotope->Store->large_image_height),
											'medium_size'	=> sprintf(' width="%s" height="%s"', $this->Isotope->Store->medium_image_width, $this->Isotope->Store->medium_image_height),
											'thumb_size'	=> sprintf(' width="%s" height="%s"', $this->Isotope->Store->thumbnail_image_width, $this->Isotope->Store->thumbnail_image_height),
											'gallery_size'	=> sprintf(' width="%s" height="%s"', $this->Isotope->Store->gallery_image_width, $this->Isotope->Store->gallery_image_height),
										));
	*/
									}
								}
							}
						}
						break;
						
					default:
						switch( $attribute['field_name'] )
						{
							case $this->Isotope->Store->priceField:
							case $this->Isotope->Store->priceOverrideField:
								$varValue = $this->Isotope->calculatePrice($objProducts->{$attribute['field_name']});
								break;
						
							default:
								$varValue = deserialize($objProducts->{$attribute['field_name']});
								break;
						}
						break;
				}
				
				$arrProduct[$attribute['field_name']] = $attribute;
				$arrProduct[$attribute['field_name']]['value'] = $varValue;
			}
			
			$arrProducts[] = $arrProduct;
		}
		
		return $arrProducts;
	}
	
	
	/**
	 * Generate a product for the template
	 */
	protected function generateProduct($arrProduct, $strTemplate)
	{
		$objTemplate = new FrontendTemplate($strTemplate);
		
		$arrOptionFields = array();
		$arrProductOptions = array();
		
		foreach( $arrProduct as $field => $attribute )
		{
			switch( $field )
			{
				case 'raw':
				case 'href_reader':
					$objTemplate->$field = $attribute;
					break;
					
				case 'main_image':
					if (is_array($attribute['value']) && count($attribute['value']))
					{
						$arrImages = $attribute['value'];
						$objTemplate->hasImage = true;
						$objTemplate->mainImage = array_shift($arrImages);
						
						if (count($arrImages))
						{
							$objTemplate->hasGallery = true;
							$objTemplate->gallery = $arrImages;
						}
					}
					break;
					
				default:
					$blnIsMergedOptionSet = true;
					
					if($attribute['is_customer_defined'])
					{
						//does it have a value?
						if($attribute['value'])
						{
							$arrOptionFields[] = $field;
						}															
						
						if(!$blnIsMergedOptionSet)
						{
							$arrData = $this->getDCATemplate($attribute);	//Grab the skeleton DCA info for widget generation

							$arrProductOptions[] = array
							(
								'name'			=> $field,
								'description'	=> $attribute['description'],									
								'html'			=> $this->generateProductOptionWidget('field', $arrData, $this->strFormId)
							);										
						}
					}
					else
					{
						switch($attribute['type'])
						{
							case 'select':
							case 'radio':
							case 'checkbox':
								//check for a related label to go with the value.
								$arrOptions = deserialize($attribute['option_list']);
								$varValues = deserialize($attribute['value']);
								$arrLabels = array();
								
								if($attribute['is_visible_on_front'])
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
										$objTemplate->$field = join(',', $arrLabels); 
									}
									
								}
								break;
								
							case 'longtext':
								$objTemplate->$field = $attribute['use_rich_text_editor'] ? $attribute['value'] : nl2br($attribute['value']);
								break;
																																		
							default:
								if($attribute['is_visible_on_front'])
								{
									//just direct render
									$objTemplate->$field = $attribute['value'];
								}
								break;
						}
					}
					break;
			}
		}
		
		$objTemplate->price = ($arrProduct['use_price_override'] && $arrProduct['use_price_override']['value']) ? $this->Isotope->formatPriceWithCurrency($arrProduct[$this->Isotope->Store->priceOverrideField]['value']) : $this->Isotope->formatPriceWithCurrency($arrProduct[$this->Isotope->Store->priceField]['value']);
		
		return $objTemplate->parse();
	}
}

