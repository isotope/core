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
			
			// Make sure field data is available
			if (!is_array($GLOBALS['TL_DCA']['tl_product_data']['fields']))
			{
				$this->loadDataContainer('tl_product_data');
				$this->loadLanguageFile('tl_product_data');
			}
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
			
			/*		
			Button Model Properties Not yet used - BEGIN

			--------------
			$objTemplate->buttonName = '';						//prefix "button_", NOT USED YET
			$objTemplate->buttonTabIndex = 0;						//tab index (optional)
			$objTemplate->buttonClickEvent = '';					//click event to invoke the button's script.  May be to an AJAX handler or just to a form submit.
			--------------

			Button Model Properties Not yet used - END
		*/	
			return $arrButtonHTML;

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
			$intLength = 300;
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
		if(count($arrValues) < 1)
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
	public function generateProductOptionWidget($strField, $arrData = array(), $strFormId, $arrOptionFields = array(), $blnUseTable = false)
	{
		
			$strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];
										
			// Continue if the class is not defined
			if (!$this->classFileExists($strClass))// || !$arrData['eval']['isoEditable'])
			{
				
				return false;	
			}
		
	
			$objWidget = new $strClass($this->prepareForWidget($arrData, $strField));
			
			$objWidget->storeValues = true;
			
			count($arrData['options']) ? $objWidget->options = $arrData['options'] : $objWidget->options = NULL;
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
							if(count($arrOptionFields))
							{
								$this->arrProductOptionsData = $this->getSubproductValues($varValue, $arrOptionFields);	//field is implied							
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
		if(count($arrOptions) < 1)
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
				//$arrOptions = $this->getSubproductOptionValues($arrAttributeData['name'], array());
				$arrOptionList = deserialize($arrAttributeData['option_list']);
				
				foreach($arrOptionList as $k=>$v)
				{
					$arrOptions[$k] = $v;
				}
					
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
				'value'	=>		$option['id'],
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
						
		//get the selected variant values;
		$objData = $this->Database->prepare("SELECT " . $strOptionValues . " FROM tl_product_data WHERE id=?")
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
		$objProduct = new IsotopeProduct();
		
		if (!$objProduct->findBy('id', $intId))
			return null;
			
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo;
			
		return $objProduct;
	}
	
	/**
	 * Shortcut for a single product by alias (from url?)
	 */
	protected function getProductByAlias($strAlias)
	{
		$objProduct = new IsotopeProduct();
		
		if (!$objProduct->findBy('alias', $strAlias))
			return null;
			
		$objProduct->reader_jumpTo = $this->iso_reader_jumpTo;
			
		return $objProduct;
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
		
		$arrProducts = array();
		
		foreach( $arrIds as $intId )
		{
			$objProduct = new IsotopeProduct();
		
			if (!$objProduct->findBy('id', $intId))
				continue;
				
			$objProduct->reader_jumpTo = $this->iso_reader_jumpTo;
				
			$arrProducts[] = $objProduct;
		}
		
		return $arrProducts;
	}
	
	
	/**
	 * Generate a product template
	 */
	public function generateProduct($objProduct, $strTemplate, $arrData=array())
	{
		$objTemplate = new FrontendTemplate($strTemplate);

		$objTemplate->setData($arrData);
		
		$arrEnabledOptions = array();
		$arrVariantOptionFields = array();
		$arrProductOptions = array();
		$arrAttributes = $objProduct->getAttributes();
		
		foreach( $arrAttributes as $attribute => $varValue )
		{
			switch( $attribute )
			{
				case 'images':
					if (is_array($varValue) && count($varValue))
					{
						$objTemplate->hasImage = true;
						$objTemplate->mainImage = array_shift($varValue);
						
						if (count($varValue))
						{
							$objTemplate->hasGallery = true;
							$objTemplate->gallery = $varValue;
						}
					}
					break;
					
				default:
										
					if($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['is_customer_defined'])
					{						
						
						$objTemplate->hasOptions = true;
						
						
						if($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['add_to_product_variants'])
						{					
							$blnIsMergedOptionSet = true;
							$arrVariantOptionFields[] = $attribute;	
						}
						else
						{
							$arrAttributeData = $this->getProductAttributeData($attribute);
							
							$arrEnabledOptions[] = $attribute;	
																	
							$arrData = $this->getDCATemplate($arrAttributeData);	//Grab the skeleton DCA info for widget generation

							$arrProductOptions[] = array
							(
								'name'			=> $attribute,
								'description'	=> $GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['description'],									
								'html'			=> $this->generateProductOptionWidget($attribute, $arrData, '')
							);										
						}
					}
					else
					{
						switch($GLOBALS['TL_DCA']['tl_product_data']['fields'][$attribute]['attributes']['type'])
						{
							case 'select':
							case 'radio':
							case 'checkbox':
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
								break;
								
							case 'longtext':
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
			//Create a special widget that combins all option value combos that are enabled.
			$arrData = array
			(
	            'name'      => 'subproducts',
	            'description'  => &$GLOBALS['TL_LANG']['tl_product_data']['product_options'],
	            'inputType'    => 'select',          
	            'options'    => $this->getSubproductOptionValues($objProduct->id, $arrVariantOptionFields),
	            'eval'      => array()
	        );
          
          //$arrData = $this->getDCATemplate($arrAttributeData);  //Grab the skeleton DCA info for widget generation
		  $arrAttributeData = $this->getProductAttributeData($k);

          $arrVariantWidget = array
          (
            'name'      => $k,
            'description'  => $GLOBALS['TL_LANG']['MSC']['labelProductVariants'],                  
            'html'      => $this->generateProductOptionWidget('product_variants', $arrData, '', $arrVariantOptionFields)
          ); 
           
        }
		
		
		
		$objTemplate->raw = $objProduct->getData();
		$objTemplate->href_reader = $objProduct->href_reader;
		
		$objTemplate->label_detail = $GLOBALS['TL_LANG']['MSC']['detailLabel'];
		
		$objTemplate->price = $objProduct->formatted_price;
		$objTemplate->options = $arrProductOptions;	
		$objTemplate->variantList = implode(',', $arrVariantOptionFields);
		$objTemplate->variant_widget = $arrVariantWidget;
				
		$objTemplate->optionList = implode(',', $arrEnabledOptions);
		
		return $objTemplate->parse();
	}
}

