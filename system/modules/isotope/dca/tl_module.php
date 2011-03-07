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


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]			= 'iso_checkout_method';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist']			= '{title_legend},name,headline,type;{display_legend},perPage,iso_cols;{config_legend},iso_use_quantity,iso_category_scope,iso_jump_first,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_reader_jumpTo,iso_addProductJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},iso_noProducts,iso_forceNoProducts,iso_list_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productvariantlist']	= '{title_legend},name,headline,type;{display_legend},perPage,iso_cols;{config_legend},iso_use_quantity,iso_category_scope,iso_jump_first,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_reader_jumpTo,iso_addProductJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},iso_noProducts,iso_forceNoProducts,iso_list_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productreader']		= '{title_legend},name,headline,type;{config_legend},iso_use_quantity;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},iso_reader_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart']					= '{title_legend},name,headline,type;{redirect_legend},iso_cart_jumpTo,iso_checkout_jumpTo;{template_legend},iso_cart_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkout']				= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutmember']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_login_jumpTo;{template_legend},iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutguest']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutboth']			= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_login_jumpTo,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderhistory']			= '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderdetails']			= '{title_legend},name,headline,type;{redirect_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_configswitcher']		= '{title_legend},name,headline,type;{config_legend},iso_config_ids;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productfilter']		= '{title_legend},name,headline,type;{config_legend},iso_listingModule,iso_enableLimit,iso_enableSearch,iso_filterFields,iso_orderByFields,iso_searchFields;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_addressbook']			= '{title_legend},name,headline,type;{template_legend},memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_relatedproducts']		= '{title_legend},name,headline,type;{config_legend},iso_related_categories,perPage;{template_legend:hide},iso_list_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_list_layout'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_list_layout'],
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope', 'getListTemplates'),
	'eval'						=> array('includeBlankOption'=>true, 'tl_class'=>'w50'),
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
	'eval'					  => array()
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

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_reader_layout'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout'],
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope', 'getReaderTemplates'),
	'eval'						=> array('includeBlankOption'=>true),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_reader_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_reader_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_login_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_login_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio', 'mandatory'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addProductJumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_addProductJumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cart_layout'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_cart_layout'],
	'default'					=> 'iso_reader_product_single',
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope', 'getCartTemplates'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cols'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_cols'],
	'default'				  => 1,
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>1, 'rgxp'=>'digit', 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['listing_filters'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['listing_filters'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('multiple'=>true,'tl_class'=>'m12 clr'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_id'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_config_id'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'foreignKey'			  => 'tl_iso_config.name',
	'eval'					  => array('includeBlankOption'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_config_ids'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_config_ids'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'foreignKey'			  => 'tl_iso_config.name',
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
	'eval'                    => array('mandatory'=>true, 'fieldType'=>'radio')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_forward_review'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_forward_review'],
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
	'foreignKey'			  => 'tl_form.title',
	'eval'                    => array('includeBlankOption'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_addToAddressbook'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_addToAddressbook'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noProducts'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_noProducts'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_forceNoProducts'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_forceNoProducts'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50 m12'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_category_scope'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope'],
	'exclude'                 => true,
	'inputType'               => 'radio',
	'default'				  => 'current_category',
	'options'				  => array('global', 'current_and_first_child','current_and_all_children', 'current_category', 'parent', 'product'),
	'reference'				  => &$GLOBALS['TL_LANG']['tl_module']['iso_category_scope_ref'],
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'eval'					  => array('multiple'=>true, 'tl_class'=>'clr'),
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
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filter_layout'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_filter_layout'],
	'default'					=> 'iso_reader_product_single',
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope', 'getFilterTemplates'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingModule'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_listingModule'],
	'exclude'                 => true,
	'inputType'               => 'radio',
	'options_callback'		  => array('tl_module_isotope', 'getListingModules')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_enableSearch'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_enableSearch'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cart_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_cart_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_checkout_jumpTo'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_checkout_jumpTo'],
	'exclude'                 => true,
	'inputType'               => 'pageTree',
	'explanation'             => 'jumpTo',
	'eval'                    => array('fieldType'=>'radio', 'helpwizard'=>true)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingSortField'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortField'],
	'exclude'                 => true,
	'inputType'               => 'select',
	'eval'					  => array('includeBlankOption'=>true),
	'options_callback'		  => array('tl_module_isotope','getSortableAttributes')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingSortDirection'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'],
	'exclude'                 => true,
	'default'				  => 'DESC',
	'inputType'               => 'select',
	'options'				  => array('DESC','ASC'),
	'reference'				  => &$GLOBALS['TL_LANG']['tl_module']['sortingDirection']
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_buttons'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_buttons'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'default'				  => array('add_to_cart'),
	'options_callback'		  => array('tl_module_isotope', 'getButtons'),
	'eval'					  => array('multiple'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_related_categories'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_related_categories'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'foreignKey'			  => 'tl_iso_related_categories.name',
	'eval'					  => array('mandatory'=>true, 'multiple'=>true, 'tl_class'=>'clr'),
);


class tl_module_isotope extends Backend
{

	public function getSortableAttributes()
	{
		if (!is_array($GLOBALS['TL_DCA']['tl_iso_products']))
		{
			$this->loadDataContainer('tl_iso_products');
			$this->loadLanguageFile('tl_iso_products');
		}

		$arrAttributes = array();

		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['attributes']['is_order_by_enabled'])
			{
				$arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrAttributes;
	}


	/**
	 * getFilterFields function.
	 *
	 * @access public
	 * @return array
	 */
	public function getFilterFields()
	{
		if (!is_array($GLOBALS['TL_DCA']['tl_iso_products']))
		{
			$this->loadDataContainer('tl_iso_products');
			$this->loadLanguageFile('tl_iso_products');
		}

		$arrAttributes = array();

		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['attributes']['is_filterable'])
			{
				$arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrAttributes;
	}


	//!@todo does almost the same as getSortableAttributes, why do we need both?
	public function getSortByFields()
	{
		if (!is_array($GLOBALS['TL_DCA']['tl_iso_products']))
		{
			$this->loadDataContainer('tl_iso_products');
			$this->loadLanguageFile('tl_iso_products');
		}

		$arrAttributes = array();

		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['attributes']['is_order_by_enabled'])
			{
				$arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrAttributes;
	}


	public function getSearchFields()
	{
		if (!is_array($GLOBALS['TL_DCA']['tl_iso_products']))
		{
			$this->loadDataContainer('tl_iso_products');
			$this->loadLanguageFile('tl_iso_products');
		}

		$arrAttributes = array();

		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['attributes']['is_searchable'])
			{
				$arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrAttributes;
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
		$objPaymentModules = $this->Database->execute("SELECT * FROM tl_iso_payment_modules");

		while( $objPaymentModules->next() )
		{
			$arrPaymentModules[$objPaymentModules->id] = $objPaymentModules->name;
		}

		return $arrPaymentModules;
	}


	/**
	 * getShippingModules function.
	 *
	 * @access public
	 * @return array
	 */
	public function getShippingModules()
	{
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT * FROM tl_iso_shipping_modules WHERE enabled=1");

		while( $objModules->next() )
		{
			$arrModules[$objModules->id] = $objModules->name;
		}

		return $arrModules;
	}


	/**
	 * Returns a list of listing
	 *
	 * @access public
	 * @return array
	 */
	public function getListingModules()
	{
		$arrListingModules = array();
		$objListingModules = $this->Database->execute("SELECT id, name FROM tl_module WHERE type='iso_productlist'");

		while( $objListingModules->next() )
		{
			$arrListingModules[$objListingModules->id] = $objListingModules->name;
		}

		return $arrListingModules;
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


	public function getButtons()
	{
		$arrOptions = array();
		$arrButtons = array();

		if (isset($GLOBALS['TL_HOOKS']['isoButtons']) && is_array($GLOBALS['TL_HOOKS']['isoButtons']))
		{
			foreach ($GLOBALS['TL_HOOKS']['isoButtons'] as $callback)
			{
				$this->import($callback[0]);
				$arrButtons = $this->$callback[0]->$callback[1]($arrButtons);
			}
		}

		foreach( $arrButtons as $button => $data )
		{
			$arrOptions[$button] = $data['label'];
		}

		return $arrOptions;
	}


	/**
	 * Return list templates as array
	 * @param object
	 * @return array
	 */
	public function getListTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('iso_list_', $intPid);
	}


	/**
	 * Return reader templates as array
	 * @param object
	 * @return array
	 */
	public function getReaderTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('iso_reader_', $intPid);
	}


	/**
	 * Return cart templates as array
	 * @param object
	 * @return array
	 */
	public function getCartTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('iso_cart_', $intPid);
	}


	/**
	 * Return filter templates as array
	 * @param object
	 * @return array
	 */
	public function getFilterTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('iso_filter_', $intPid);
	}
}

