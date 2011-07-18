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
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]			= 'iso_enableLimit';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][]			= 'iso_emptyMessage';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productlist']			= '{title_legend},name,headline,type;{display_legend},perPage,iso_cols;{config_legend},iso_category_scope,iso_filterModules,iso_jump_first,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_reader_jumpTo,iso_addProductJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},iso_includeMessages,iso_use_quantity,iso_emptyMessage,iso_list_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productvariantlist']	= '{title_legend},name,headline,type;{display_legend},perPage,iso_cols;{config_legend},iso_category_scope,iso_filterModules,iso_jump_first,iso_listingSortField,iso_listingSortDirection;{redirect_legend},iso_reader_jumpTo,iso_addProductJumpTo;{reference_legend:hide},defineRoot;{template_legend:hide},iso_includeMessages,iso_use_quantity,iso_emptyMessage,iso_list_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productreader']		= '{title_legend},name,headline,type;{config_legend},iso_use_quantity;{redirect_legend},iso_addProductJumpTo;{template_legend:hide},iso_includeMessages,iso_reader_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_cart']					= '{title_legend},name,headline,type;{redirect_legend},iso_cart_jumpTo,iso_checkout_jumpTo;{template_legend},iso_includeMessages,iso_cart_layout;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkout']				= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutmember']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo,iso_login_jumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutguest']		= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_checkoutboth']			= '{title_legend},name,headline,type;{config_legend},iso_checkout_method,iso_payment_modules,iso_shipping_modules,iso_order_conditions,iso_addToAddressbook;{redirect_legend},iso_forward_review,orderCompleteJumpTo;{template_legend},iso_includeMessages,iso_login_jumpTo,iso_mail_customer,iso_mail_admin,iso_sales_email,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderhistory']			= '{title_legend},name,headline,type;{config_legend},iso_config_ids;{redirect_legend},jumpTo;{template_legend},iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_orderdetails']			= '{title_legend},name,headline,type;{redirect_legend},jumpTo;{template_legend},iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_configswitcher']		= '{title_legend},name,headline,type;{config_legend},iso_config_ids;{template_legend},iso_includeMessages;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_productfilter']		= '{title_legend},name,headline,type;{config_legend},iso_category_scope,iso_enableLimit,iso_filterFields,iso_searchFields,iso_sortingFields;{template_legend},iso_includeMessages,iso_filterTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_addressbook']			= '{title_legend},name,headline,type;{template_legend},iso_includeMessages,memberTpl,tableless;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['iso_relatedproducts']		= '{title_legend},name,headline,type;{config_legend},iso_related_categories,perPage,iso_cols;{redirect_legend},iso_reader_jumpTo,iso_addProductJumpTo;{template_legend:hide},iso_includeMessages,iso_use_quantity,iso_emptyMessage,iso_list_layout,iso_buttons;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


/**
 * Subpalettes
 */
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_enableLimit']		= 'iso_perPage';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['iso_emptyMessage']		= 'iso_noProducts';


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

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_reader_layout'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_reader_layout'],
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope', 'getReaderTemplates'),
	'eval'						=> array('includeBlankOption'=>true),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cart_layout'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_cart_layout'],
	'default'					=> 'iso_reader_product_single',
	'exclude'					=> true,
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope', 'getCartTemplates'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterTpl'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_filterTpl'],
	'exclude'					=> true,
	'default'					=> 'iso_filter_default',
	'inputType'					=> 'select',
	'options_callback'			=> array('tl_module_isotope', 'getFilterTemplates'),
	'eval'						=> array('mandatory'=>true, 'tl_class'=>'w50'),
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

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_cols'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_cols'],
	'default'				  => 1,
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>1, 'rgxp'=>'digit', 'tl_class'=>'w50'),
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

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_emptyMessage'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_emptyMessage'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_noProducts'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_noProducts'],
	'exclude'                 => true,
	'inputType'               => 'text',
	'eval'                    => array('maxlength'=>255, 'tl_class'=>'clr long')
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

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterModules'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_filterModules'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'options_callback'		  => array('tl_module_isotope', 'getFilterModules'),
	'eval'					  => array('multiple'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_filterFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_filterFields'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'options_callback'		  => array('tl_module_isotope', 'getFilterFields'),
	'eval'					  => array('multiple'=>true, 'tl_class'=>'clr'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_sortingFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_sortingFields'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'options_callback'		  => array('tl_module_isotope', 'getSortingFields'),
	'eval'					  => array('multiple'=>true),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_searchFields'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_searchFields'],
	'exclude'                 => true,
	'inputType'               => 'checkboxWizard',
	'options_callback'		  => array('tl_module_isotope', 'getSearchFields'),
	'eval'					  => array('multiple'=>true),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_enableLimit'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_enableLimit'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('submitOnChange'=>true, 'tl_class'=>'clr w50 m12'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_perPage'] = array
(
	'label'						=> &$GLOBALS['TL_LANG']['tl_module']['iso_perPage'],
	'default'					=> '8,12,32,64',
	'exclude'					=> true,
	'inputType'					=> 'text',
	'eval'						=> array('mandatory'=>true, 'maxlength'=>64, 'rgxp'=>'extnd', 'tl_class'=>'w50'),
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
	'options_callback'		  => array('tl_module_isotope', 'getSortingFields'),
	'eval'					  => array('includeBlankOption'=>true, 'tl_class'=>'clr w50'),
);

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_listingSortDirection'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_listingSortDirection'],
	'exclude'                 => true,
	'default'				  => 'DESC',
	'inputType'               => 'select',
	'options'				  => array('DESC','ASC'),
	'reference'				  => &$GLOBALS['TL_LANG']['tl_module']['sortingDirection'],
	'eval'					  => array('tl_class'=>'w50'),
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

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_includeMessages'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_includeMessages'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'eval'					  => array('tl_class'=>'w50', 'doNotCopy'=>true)
);

class tl_module_isotope extends Backend
{

	public function __construct()
	{
		parent::__construct();
		$this->loadDataContainer('tl_iso_products');
		$this->loadLanguageFile('tl_iso_products');
	}


	/**
	 * getFilterFields function.
	 *
	 * @access public
	 * @return array
	 */
	public function getFilterFields()
	{
		$arrAttributes = array();

		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['attributes']['fe_filter'])
			{
				$arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrAttributes;
	}


	public function getSortingFields()
	{
		$arrAttributes = array();

		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['attributes']['fe_sorting'])
			{
				$arrAttributes[$field] = strlen($arrData['label'][0]) ? $arrData['label'][0] : $field;
			}
		}

		return $arrAttributes;
	}


	public function getSearchFields()
	{
		$arrAttributes = array();

		foreach( $GLOBALS['TL_DCA']['tl_iso_products']['fields'] as $field => $arrData )
		{
			if ($arrData['attributes']['fe_search'])
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

		if (isset($GLOBALS['ISO_HOOKS']['buttons']) && is_array($GLOBALS['ISO_HOOKS']['buttons']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['buttons'] as $callback)
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


	/**
	 * Return array of tl_module records where type='iso_productfilter'
	 * @param	object
	 * @return	array
	 */
	public function getFilterModules(DataContainer $dc)
	{
		$arrModules = array();
		$objModules = $this->Database->execute("SELECT * FROM tl_module WHERE type='iso_productfilter'");

		while( $objModules->next() )
		{
			$arrModules[$objModules->id] = $objModules->name;
		}

		return $arrModules;
	}
}

