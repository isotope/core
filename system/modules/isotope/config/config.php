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


// Default settings, can be overwritten in localconfig.php
$GLOBALS['TL_CONFIG']['isotope_root'] = TL_ROOT . '/isotope';
$GLOBALS['TL_CONFIG']['isotope_upload_path'] = 'isotope';


// Rules workaround
$GLOBALS['ISO_RULES']['excludeAttributeSets'] = array(); //array(6, 3);


/**
 * Backend modules
 */
if (!is_array($GLOBALS['BE_MOD']['store']))
{
	array_insert($GLOBALS['BE_MOD'], 1, array('store' => array()));
}
 
array_insert($GLOBALS['BE_MOD']['store'], 0, array
(
	'product_manager' => array
	(
		'tables'					=> array('tl_product_data', 'tl_product_downloads'),
		'icon'						=> 'system/modules/isotope/html/icon_pm.gif',
		'stylesheet'				=> 'system/modules/isotope/html/backend.css',
		'import'					=> array('tl_product_data', 'importAssets'),
	),
	'product_type_manager' => array
	(
		'tables'					=> array('tl_product_types'),
		'icon'						=> 'system/modules/isotope/html/cog_edit.png'
	),
	'attribute_manager' => array
	(
		'tables'					=> array('tl_product_attributes'),
		'icon'						=> 'system/modules/isotope/html/icon_pa.gif',
	),
	'orders' => array
	(
		'tables'					=> array('tl_iso_orders','tl_iso_order_items'),
		'authorize_process_payment'	=> array('IsotopePOS', 'getPOSInterface'),
		'print_order'				=> array('IsotopePOS','printInvoice'),
		'icon'						=> 'system/modules/isotope/html/icon-orders.gif',
	),
	'shipping' => array
	(
			'tables'				=> array('tl_shipping_modules', 'tl_shipping_options'),
			'icon'					=> 'system/modules/isotope/html/icon-shipping.gif',
	),
	'payment' => array
	(
		'tables'					=> array('tl_payment_modules', 'tl_payment_options'),
		'icon'						=> 'system/modules/isotope/html/icon-payment.png',
	),
	'tax_class' => array
	(
		'tables'					=> array('tl_tax_class'),
		'icon'						=> 'system/modules/isotope/html/icon-taxclass.gif',
	),
	'tax_rate' => array
	(
		'tables'					=> array('tl_tax_rate'),
		'icon'						=> 'system/modules/isotope/html/icon-taxrate.gif',
	),
	'iso_mail' => array
	(
		'tables'					=> array('tl_iso_mail', 'tl_iso_mail_content'),
		'icon'						=> 'system/modules/isotope/html/icon_mail.png',
	),
	'store_configuration' => array
	(
		'tables'					=> array('tl_store'),
		'icon'						=> 'system/modules/isotope/html/icon_iso.gif',
	),
));

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_address_book';


/**
 * Hooks
 */
//$GLOBALS['TL_HOOKS']['createNewUser'][]				= array('IsotopeCallbacks','copyAddressBookEntry');
//$GLOBALS['TL_HOOKS']['createNewUser'][]				= array('IsotopeCallbacks','autoActivateNewMember');
$GLOBALS['TL_HOOKS']['createNewUser'][]				= array('Isotope','createNewUser');
$GLOBALS['TL_HOOKS']['getMappingAttributes'][]		= array('ProductCatalog','generateMappingAttributeList');
//$GLOBALS['TL_HOOKS']['mappingAttributesCallback'][]	= array('products_attribute_set' => array('ProductCatalog','batchUpdateCAPAggregate'));
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]			= array('IsotopeFrontend', 'replaceIsotopeTags');
//$GLOBALS['TL_HOOKS']['postLogin'][] = array('IsotopeCallbacks','memberLogin');

//$GLOBALS['TL_HOOKS']['getMappingAttributes'][]	= array('getProductMapping' => array('ProductCatalog','generateMappingAttributeList'));
$GLOBALS['TL_HOOKS']['executePostActions'][] = array('ProductOptionWizard', 'executePostActions');

//$GLOBALS['TL_HOOKS']['executePostActions'][] = array('AutoComplete', 'executePostActions');


$GLOBALS['TL_HOOKS']['isoCheckoutSurcharge'][] = array('IsotopeCart', 'getShippingSurcharge');
$GLOBALS['TL_HOOKS']['isoCheckoutSurcharge'][] = array('IsotopeCart', 'getPaymentSurcharge');



/**
 * Backend widgets
 */
$GLOBALS['BE_FFL']['mediaManager'] = 'MediaManager';
$GLOBALS['BE_FFL']['variantsWizard'] = 'VariantsWizard';
$GLOBALS['BE_FFL']['attributeWizard'] = 'AttributeWizard';

//$GLOBALS['BE_FFL']['productOptionsWizard'] = 'ProductOptionsWizard';

/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope'] = array
(
	'isoFilterModule'			=> 'ModuleFilters',
	'isoProductLister'			=> 'ModuleProductLister',
	'isoProductReader'			=> 'ModuleProductReader',
	'isoShoppingCart'			=> 'ModuleShoppingCart',
	'isoCheckout'				=> 'ModuleIsotopeCheckout',
	'isoGiftRegistryManager'	=> 'ModuleGiftRegistry',
	'isoGiftRegistrySearch'		=> 'ModuleGiftRegistrySearch',
	'isoGiftRegistryResults'	=> 'ModuleGiftRegistryResults',
	'isoGiftRegistryReader'		=> 'ModuleGiftRegistryReader',
	'isoAddressBook'			=> 'ModuleAddressBook',
	'isoOrderHistory'			=> 'ModuleOrderHistory',
	'isoOrderDetails'			=> 'ModuleOrderDetails',
	'isoStoreSwitcher'			=> 'ModuleStoreSwitcher',
);


/** 
 * Frontend Form Fields
 */
$GLOBALS['TL_FFL']['textCollection'] = 'FormTextCollectionField';


/**
 * Shipping modules
 */
$GLOBALS['ISO_SHIP']['order_total']	 = 'ShippingOrderTotal';
$GLOBALS['ISO_SHIP']['flat']		 = 'ShippingFlat';


/**
 * Payment modules
 */
$GLOBALS['ISO_PAY']['cash']						= 'PaymentCash';
$GLOBALS['ISO_PAY']['paypal']					= 'PaymentPaypal';
$GLOBALS['ISO_PAY']['postfinance']				= 'PaymentPostfinance';
$GLOBALS['ISO_PAY']['authorizedotnet']			= 'PaymentAuthorizeDotNet';
$GLOBALS['ISO_PAY']['cc_types']['visa']			= 'Visa';
$GLOBALS['ISO_PAY']['cc_types']['mastercard']	= 'Mastercard';
$GLOBALS['ISO_PAY']['cc_types']['amex']			= 'American Express';
$GLOBALS['ISO_PAY']['cc_types']['discover']		= 'Discover';


/** 
 * Order module additional operations
 */
$GLOBALS['ISO_ORDERS']['operations'][] = 'IsotopePOS';


/** 
 * Miscellaneous Isotope-specific settings
 */
$GLOBALS['ISO_MSC']['tl_product_data']['groups_ordering'] = array
('general_legend','pricing_legend','inventory_legend','shipping_legend','tax_legend','options_legend','media_legend','publish_legend');


/**
 * Handle Collections
 */
/*$GLOBALS['FE_MOD']['isoLister']['TPL_COLL']['listing'] = array(
	'sortByOptions'			 => array
	(
		'url'				 => '',
		'label' 			 => ''
	),
	'products'				 => array
	(
		'thumbnail_image'	 => '',
		'name'		 => '',
		'link'		 => '',
		'teaser'	 => '',
		'price_string'		 => '',
	),
	'buttons'			 	 => array
	(
		'button_class'		 => '',
		'button_object'		 => ''
	),
	'labelPagerSectionTitle' => $GLOBALS['TL_LANG']['MSC']['labelPagerSectionTitle'],
	'labelSortBy'			 => $GLOBALS['TL_LANG']['MSC']['labelSortBy'],
	'pagination'			 => ''
);

ß
$GLOBALS['FE_MOD']['isoLister']['TPL_COLL']['generic_listing'] = array(
	'value'					=> '',
	'label'					=> ''
);*/


/**
 * Used in store configuration to enable/disable certain store-wide features
 * @todo what do we need that for?
 */
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoShoppingCart';
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoCheckout';
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoGiftRegistry';
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoAddressBook';

//$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoProductComments';
//$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoCartWishlist';

//$GLOBALS['ISO_PAYMENT_MODULE']['GATEWAY_TYPES'][] = 'authorize';

//$GLOBALS['ISO_PAYMENT_MODULE']['GATEWAY_TYPES'][] = 'paypal_pro';

	
/**
 * Various
 */
$GLOBALS['TL_CTE']['links']['attributeLinkRepeater'] = 'ContentAttributeLinkRepeater';
  
$GLOBALS['ISO_PLUGINS']['jwMediaPlayer']['mediaRSSPlaylist'] = 'media_rss';

$GLOBALS['TL_LANG']['MSC']['isotope_function_group'] = 'Isotope Ecommerce Functions';

//$GLOBALS['ISO_ACTIVE_CUSTOM_PRODUCT_BUTTONS'][] = array('add_to_gift_registry');



/** 
 * Permissions are access settings for user and groups (fields in tl_user and tl_user_group)
 */
$GLOBALS['TL_PERMISSIONS'][] = 'iso_stores';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_product_types';


/**
 * Number formatting
 */
$GLOBALS['ISO_NUM']["10000.00"]		= array(2, '.', "");
$GLOBALS['ISO_NUM']["10,000.00"]	= array(2, '.', ",");
$GLOBALS['ISO_NUM']["10.000,00"]	= array(2, ',', ".");
$GLOBALS['ISO_NUM']["10'000.00"]	= array(2, '.', "'");



$GLOBALS['ISO_CHECKOUT_STEPS'] = array
(
	'address' => array
	(
		array('ModuleIsotopeCheckout', 'getBillingAddressInterface'),
		array('ModuleIsotopeCheckout', 'getShippingAddressInterface'),
	),
	'shipping' => array
	(
		array('ModuleIsotopeCheckout', 'getShippingModulesInterface'),
	),
	'payment' => array
	(
		array('ModuleIsotopeCheckout', 'getPaymentModulesInterface'),
	),
	'review' => array
	(
		array('ModuleIsotopeCheckout', 'getOrderReviewInterface'),
		array('ModuleIsotopeCheckout', 'getOrderConditionsInterface'),
	),
);



/**
 * These fields are default fields required to get the store and default templates working
 * We must improve behavious because currently we cannot rename a field (eg. to german) or it would break!
 */
$GLOBALS['ISO_ATTR'] = array
(
	// Shipping Class		
	array
	( 
		'pid'						=> 0,
		'tstamp'					=> time(),
		'type'						=> 'decimal',
		'field_name'				=> 'shipping_class',
		'legend'					=> 'shipping_legend',
		'name'						=> 'Shipping Class',
		'description'				=> 'Left blank, the default shipping option will be applied.',
		'attr_use_mode'				=> 'fixed',
		'is_customer_defined'		=> 0,
		'is_visible_on_front'		=> 0,
		'is_required'				=> 0,
		'is_filterable'				=> 0,
		'is_searchable'				=> 0,
		'is_used_for_price_rules'	=> 1,
		'is_multiple_select'		=> 0,
		'use_rich_text_editor'		=> 0,
		'is_user_defined'			=> 0,
		'is_listing_field'			=> 0,
		'delete_locked'				=> 1
	),
	
	// Featured Item		
	array
	( 
		'pid'						=> 0,
		'tstamp'					=> time(),
		'type'						=> 'checkbox',
		'field_name'				=> 'featured_product',
		'legend'					=> 'general_legend',
		'name'						=> 'Is a featured product?',
		'description'				=> '',
		'attr_use_mode'				=> 'fixed',
		'is_customer_defined'		=> 0,
		'is_visible_on_front'		=> 0,
		'is_required'				=> 0,
		'is_filterable'				=> 0,
		'is_searchable'				=> 0,
		'is_used_for_price_rules'	=> 0,
		'is_multiple_select'		=> 0,
		'use_rich_text_editor'		=> 0,
		'is_user_defined'			=> 0,
		'is_listing_field'			=> 0,
		'delete_locked'				=> 1
	),
);

