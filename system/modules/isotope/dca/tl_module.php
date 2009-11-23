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
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */
 
 
/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]			= 'iso_checkout_method';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoProductLister']			= '{title_legend},name,headline,type;{display_legend},perPage,columns,iso_list_format,iso_show_teaser;{config_legend},iso_category_scope,iso_jump_first,new_products_time_window,featured_products,listing_filters;{redirect_legend},iso_reader_jumpTo;{template_legend:hide},iso_list_layout;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoProductReader']			= '{title_legend},name,headline,type;{config_legend},iso_use_quantity;{template_legend:hide},iso_reader_layout;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoShoppingCart']			= '{title_legend},name,headline,type;iso_cart_layout,iso_forward_cart;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoAddressBook']			= '{title_legend},name,headline,type;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoGiftRegistryManager']	= '{title_legend},name,headline,type;iso_registry_layout;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoGiftRegistrySearch']	= '{title_legend},name,headline,type;jumpTo;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoGiftRegistryResults']	= '{title_legend},name,headline,type;jumpTo;iso_registry_results;perPage;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoGiftRegistryReader']	= '{title_legend},name,headline,type;iso_registry_reader;guests,protected;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoCheckoutmember']			= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_checkout_login,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},orderCompleteJumpTo;{template_legend},iso_checkout_layout,iso_mail_customer,iso_mail_admin,iso_sales_email;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoCheckoutguest']			= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},orderCompleteJumpTo;{template_legend},iso_checkout_layout,iso_mail_customer,iso_mail_admin,iso_sales_email;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoCheckoutboth']			= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_checkout_login,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},orderCompleteJumpTo;{template_legend},iso_checkout_layout,iso_mail_customer,iso_mail_admin,iso_sales_email;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoOrderHistory']			= '{title_legend},name,headline,type;{config_legend},store_ids;{redirect_legend},jumpTo;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoOrderDetails']			= '{title_legend},name,headline,type;{redirect_legend},jumpTo;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoStoreSwitcher']			= '{title_legend},name,headline,type;{config_legend},store_ids;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['isoFilters']				= '{title_legend},name,headline,type;{config_legend},iso_enableLimit,iso_filterFields,iso_orderByFields,iso_searchFields;{template_legend:hide},iso_filter_layout;{protected_legend:hide},guests,protected;{expert_legend:hide},align,space,cssID';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'],
	'default'                 => 'iso_list_productlist',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_list_')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_jump_first'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_jump_first'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_use_quantity'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_use_quantity'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_method'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method'],
	'exclude'                 => true,
	'inputType'               => 'radio',
	'default'				  => 'member',
	'options'				  => array('member', 'guest', 'both'),
	'reference'				  => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_method_ref'],
	'eval'					  => array('mandatory'=>true, 'submitOnChange'=>true),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_login'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_login'],
	'exclude'                 => true,
	'inputType'               => 'radio',
	'options_callback'        => array('tl_module_isotope', 'getLoginModuleList'),
	'eval'                    => array('mandatory'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_reader_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout'],
	'default'                 => 'iso_reader_default',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_reader_')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_reader_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_reader_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cart_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_cart_layout'],
	'default'                 => 'iso_reader_product_single',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_cart_')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_registry_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_registry_layout'],
	'default'                 => 'iso_registry_manage',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_registry_manage')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_registry_reader'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_registry_reader'],
	'default'                 => 'iso_registry_full',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_registry_full')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_registry_results'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_registry_results_lister'],
	'default'                 => 'iso_registry_searchlister',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_registry_search')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_layout'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_layout'],
	'default'                 => 'iso_reader_product_single',
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'                 => $this->getTemplateGroup('iso_mod_checkout_')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['new_products_time_window'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['new_products_time_window'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('rgxp'=>'digit', 'mandatory'=>false, 'maxlength'=>255,'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['columns'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['columns'],
	'default'				  => 3,
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('rgxp'=>'digit', 'mandatory'=>false, 'maxlength'=>255,'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['featured_products'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['featured_products'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('multiple'=>false, 'tl_class'=>'w50 m12')
);


$GLOBALS['TL_DCA']['tl_module']['fields']['listing_filters'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['listing_filters'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('multiple'=>true,'tl_class'=>'m12 clr'),
	'options_callback'		  => array('tl_module_isotope','getFilters')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['store_id'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['store_id'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_store.store_configuration_name',
	'eval'					  => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['store_ids'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['store_ids'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'foreignKey'			  => 'tl_store.store_configuration_name',
	'eval'					  => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_payment_modules'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_payment_modules'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('multiple'=>true),
	'options_callback'		  => array('tl_module_isotope','getPaymentModules')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_shipping_modules'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_shipping_modules'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('multiple'=>true),
	'options_callback'		  => array('tl_module_isotope','getShippingModules')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['orderCompleteJumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['orderCompleteJumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_forward_cart'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_forward_cart'],
	'exclude'                 => true,
	'inputType'               => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_mail_customer'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_mail_customer'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'              => 'tl_iso_mail.name',
	'eval'					  => array('includeBlankOption'=>true, 'mandatory'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_mail_admin'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_mail_admin'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'              => 'tl_iso_mail.name',
	'eval'					  => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_sales_email'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_sales_email'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'rgxp'=>'email', 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_order_conditions'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_order_conditions'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options_callback'        => array('tl_module_isotope', 'getArticleAlias'),
	'eval'                    => array('includeBlankOption'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_show_teaser'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_show_teaser'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50 m12')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_format'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_list_format'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'options'        		  => array('grid', 'list'),
	'eval'					  => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_category_scope'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope'],
	'exclude'                 => true,
	'inputType'               => 'radio',
	'default'				  => 'parent_and_children',
	'options'				  => array('global', 'parent_and_first_child','parent_and_all_children', 'current_category'),
	'reference'				  => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref'],
);


$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'eval'					  => array('multiple'=>true),
	'options_callback'		  => array('tl_module_isotope','getFilterFields')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_orderByFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_orderByFields'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'eval'					  => array('multiple'=>true),
	'options_callback'		  => array('tl_module_isotope','getSortByFields')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_searchFields'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'eval'					  => array('multiple'=>true),
	'options_callback'		  => array('tl_module_isotope','getSearchFields')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_enableLimit'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit'],
	'exclude'                 => true,
	'inputType'               => 'checkbox'
);

/**
 * tl_module_isotope class.
 * 
 * @extends Backend
 */
class tl_module_isotope extends Backend
{
	/**
	 * getFilters function.
	 * 
	 * @todo Returns an error string but should be an array
	 *
	 * @access public
	 * @return array
	 */
	public function getFilterFields()
	{
		
		
		$objFilters = $this->Database->prepare("SELECT id, name FROM tl_product_attributes WHERE is_filterable=?")
									 ->execute(1);
									 
		if($objFilters->numRows < 1)
		{
			return $GLOBALS['TL_LANG']['MSC']['noResult'];
		}
		
		$arrFilters = $objFilters->fetchAllAssoc();
		
		foreach($arrFilters as $filter)
		{
						
			$arrOptionGroups[$filter['id']] = $filter['name'];
		
		}
		
		return $arrOptionGroups;
	}
	
	/**
	 * getFilters function.
	 * 
	 * @todo Returns an error string but should be an array
	 *
	 * @access public
	 * @return array
	 */
	public function getSortByFields()
	{
		
		
		$objFilters = $this->Database->prepare("SELECT id, name FROM tl_product_attributes WHERE is_order_by_enabled=?")
									 ->execute(1);
									 
		if($objFilters->numRows < 1)
		{
			return $GLOBALS['TL_LANG']['MSC']['noResult'];
		}
		
		$arrFilters = $objFilters->fetchAllAssoc();
		
		foreach($arrFilters as $filter)
		{
						
			$arrOptionGroups[$filter['id']] = $filter['name'];
		
		}
		
		return $arrOptionGroups;
	}
	
	/**
	 * getFilters function.
	 * 
	 * @todo Returns an error string but should be an array
	 *
	 * @access public
	 * @return array
	 */
	public function getSearchFields()
	{
		
		
		$objFilters = $this->Database->prepare("SELECT id, name FROM tl_product_attributes WHERE is_searchable=? AND type='text'")
									 ->execute(1);
									 
		if($objFilters->numRows < 1)
		{
			return $GLOBALS['TL_LANG']['MSC']['noResult'];
		}
		
		$arrFilters = $objFilters->fetchAllAssoc();
		
		foreach($arrFilters as $filter)
		{
						
			$arrOptionGroups[$filter['id']] = $filter['name'];
		
		}
		
		return $arrOptionGroups;
	}
	
	
	/**
	 * refineFilterData function.
	 * 
	 * @access public
	 * @param mixed $varValue
	 * @param object DataContainer $dc
	 * @return string
	 */
	public function refineFilterData($varValue, DataContainer $dc)
	{
		$arrValues = deserialize($varValue);
		
		//Get attribute basic data
		foreach($arrValues as $value)
		{
			$objAttributeData = $this->Database->prepare("SELECT field_name FROM tl_product_attributes WHERE id=?")
										   		->limit(1)
										   		->execute($value);
		
			if($objAttributeData->numRows < 1)
			{
				return '';	
			}
		
			$objAttributeData->first();
		
			$strAttributeFieldName = $objAttributeData->field_name;
					
			$arrFilterValues = $this->getFilterValues($value);
						
			$objOptionValuesInUse = $this->Database->prepare("SELECT DISTINCT " . $strAttributeFieldName . " FROM tl_product_data")
									 		   ->execute();
								
			if($objOptionValuesInUse->numRows < 1)
			{
				return '';
			} 
		
			$arrOptionValuesInUse = $objOptionValuesInUse->fetchEach($strAttributeFieldName);

			$i = 0;

			$intCurrSorting = 128;
			
						
			foreach($arrFilterValues as $listValue)
			{
				
				if(!in_array($listValue['value'], $arrOptionValuesInUse))
				{
					unset($arrFilterValues[$i]);
				}else{		
					$arrPages = $this->getAssociatedPagesByListValue($listValue, $strAttributeFieldName);
						
					$arrSet[] = array
					(
						'id'			=> "''",
						'pid'			=> $value,
						'sorting' 		=> $intCurrSorting,
						'tstamp'		=> time(),
						'value'			=> "'" . mysql_escape_string($listValue['value']) . "'",
						'label'			=> "'" . mysql_escape_string($listValue['label']) . "'",
						'pages'			=> "'" . serialize($arrPages) . "'"		
					);
				}
				
				$i++;
				$intCurrSorting+=128;
			
			}
			
					
			//Reset the current attribute's list cache, if any
			$this->Database->prepare("DELETE FROM tl_list_cache WHERE pid=?")->execute($value);
		
			//Break apart the standard SET array into multiple row insert sql statement	
			foreach($arrSet as $currSet)
			{
				$arrInsertRows[] = implode(",", $currSet);
			}
			
			$strInsertRows = join("),(", $arrInsertRows);
			
			$strInsertRows = "(" . $strInsertRows . ")";
			
			//Add the list values to the list cache.
			//echo $strInsertRows;
			
			$this->Database->prepare("INSERT INTO tl_list_cache (id, pid, sorting, tstamp, value, label, pages) VALUES " . $strInsertRows)->execute();
			
		}

		return $varValue;
	}
	
	
	/**
	 * getAssociatedPagesByListValue function.
	 * 
	 * @access private
	 * @param integer $intListValue
	 * @param string $strStoreTable
	 * @param string $strAttributeFieldName
	 * @return array
	 */
	private function getAssociatedPagesByListValue($intListValue, $strAttributeFieldName)
	{
		$objPages = $this->Database->prepare("SELECT pages FROM tl_product_data WHERE " . $strAttributeFieldName . "=?")
								   ->execute($intListValue);
								   
		if($objPages->numRows < 1)
		{
			return array();
		}
	
		$arrRawSerializedPages = $objPages->fetchEach('pages');
				
		$arrRawPagesCurr = array();
		$arrRawPages = array();
				
		foreach($arrRawSerializedPages as $pageCollection)
		{
			$arrRawPagesCurr = deserialize($pageCollection);
			
			foreach($arrRawPagesCurr as $pageVal)
			{
				$arrRawPages[] = $pageVal;
			}
			
			$arrRawPagesCurr = array();
		}
		
				
		$arrPages = array_unique($arrRawPages);
			
		return $arrPages;
	}
	
	
	/**
	 * getFilterValues function.
	 * 
	 * @todo Returns an error string but should be an array
	 *
	 * @access private
	 * @param integer $intAttributeID
	 * @return array
	 */
	private function getFilterValues($intAttributeID)
	{
		$objAttributeData = $this->Database->prepare("SELECT name, option_list, use_alternate_source, list_source_table, list_source_field, field_name FROM tl_product_attributes WHERE id=? AND is_filterable='1' AND (type='select' OR type='checkbox')")
									  ->limit(1)
									  ->execute($intAttributeID);
		
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
				$arrValues[] = array
				(
					'value'		=> $value[$objAttributeData->id],
					'label'		=> $value[$objAttributeData->list_source_field]
				);
			
			}
		}
		else
		{
			$arrLinkValues = deserialize($objAttributeData->option_list);
			
			$filter_name = $objAttributeData->field_name;
			
			foreach($arrLinkValues as $value)
			{
				$arrValues[] = array
				(
					'value'		=> $value['value'],
					'label'		=> $value['label']
				);
			
			}
		}
		
		return $arrValues;
	}
	
	
	/**
	 * Returns a list of enabled payment modules.
	 *
	 * @access public
	 * @return array
	 */
	public function getPaymentModules()
	{
		$arrPaymentModules = array();
		$objPaymentModules = $this->Database->execute("SELECT * FROM tl_payment_modules");
		
		while( $objPaymentModules->next() )
		{
			$arrPaymentModules[$objPaymentModules->id] = $objPaymentModules->name;
		}
		
		return $arrPaymentModules;
	}
	
	
	/**
	 * getShippingModules function.
	 * 
	 * @todo Returns an error string but should be an array
	 *
	 * @access public
	 * @return array
	 */
	public function getShippingModules()
	{
		$return = array();
		
		
		$objShippingModules = $this->Database->prepare("SELECT * FROM tl_shipping_modules WHERE enabled=?")
											->execute('1');
		
		if($objShippingModules->numRows < 1)
		{
			return '<i>' .  $GLOBALS['TL_LANG']['MSC']['noShippingModules'] . '</i>';
		}	
		
		$arrShippingModules = $objShippingModules->fetchAllAssoc();
				
		foreach($arrShippingModules as $module)
		{
			
			$return[$module['id']] = $module['name'];
		}	
	
		return $return;
	}
	
	
	/**
	 * Get all articles and return them as array
	 * @param object
	 * @return array
	 */
	public function getArticleAlias(DataContainer $dc)
	{
		$arrAlias = array();
		$this->loadLanguageFile('tl_article');

		$objAlias = $this->Database->execute("SELECT id, title, inColumn, (SELECT title FROM tl_page WHERE tl_page.id=tl_article.pid) AS parent FROM tl_article ORDER BY parent, sorting");

		while ($objAlias->next())
		{
			$arrAlias[$objAlias->parent][$objAlias->id] = $objAlias->id . ' - ' . $objAlias->title . ' (' . (strlen($GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn]) ? $GLOBALS['TL_LANG']['tl_article'][$objAlias->inColumn] : $objAlias->inColumn) . ')';
		}

		return $arrAlias;
	}


	/**
	 * getLoginModuleList function.
	 * 
	 * @access public
	 * @return array
	 */
	public function getLoginModuleList()
	{
		$arrModules = array();

		$objModules = $this->Database->execute("SELECT id, name FROM tl_module WHERE type='login'");
										  
		while( $objModules->next() )
		{
			$arrModules[$objModules->id] = $objModules->name;
		}

		return $arrModules;
	}
}

