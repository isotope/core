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
 * Isotope Version
 */
@define('ISO_VERSION', '0.2');
@define('ISO_BUILD', '5');


/**
 * Backend modules
 */
if (!is_array($GLOBALS['BE_MOD']['isotope']))
{
	array_insert($GLOBALS['BE_MOD'], 1, array('isotope' => array()));
}
 
array_insert($GLOBALS['BE_MOD']['isotope'], 0, array
(
	'iso_products' => array
	(
		'tables'					=> array('tl_iso_products', 'tl_iso_product_categories', 'tl_iso_downloads', 'tl_iso_related_products'),
		'icon'						=> 'system/modules/isotope/html/icon-products.gif',
		'stylesheet'				=> 'system/modules/isotope/html/backend.css',
		'javascript'				=> 'system/modules/isotope/html/backend.js',
		'generate'					=> array('tl_iso_products', 'generateVariants'),
		'quick_edit'				=> array('tl_iso_products', 'quickEditVariants'),
		'import'					=> array('tl_iso_products', 'importAssets'),
	),
	'iso_orders' => array
	(
		'tables'					=> array('tl_iso_orders', 'tl_iso_order_items'),
		'icon'						=> 'system/modules/isotope/html/icon-orders.gif',
		'stylesheet'				=> 'system/modules/isotope/html/backend.css',
		'javascript'				=> 'system/modules/isotope/html/backend.js',
		'export_emails'     		=> array('tl_iso_orders', 'exportOrderEmails'),
		'print_order'				=> array('tl_iso_orders', 'printInvoice'),
		'print_invoices'			=> array('tl_iso_orders', 'printInvoices'),
		'payment'					=> array('tl_iso_orders', 'paymentInterface'),
		'shipping'					=> array('tl_iso_orders', 'shippingInterface'),
	),/*
	'iso_statistics' => array
	(
		'callback'					=> 'ModuleIsotopeStatistics',
		'icon'						=> 'system/modules/isotope/html/icon-statistics.gif',
	),*/
	'iso_setup' => array
	(
		'callback'					=> 'ModuleIsotopeSetup',
		'tables'					=> array('tl_iso_config', 'tl_iso_shipping_modules', 'tl_iso_shipping_options', 'tl_iso_payment_modules', 'tl_payment_options', 'tl_iso_tax_class', 'tl_iso_tax_rate', 'tl_iso_producttypes', 'tl_iso_attributes', 'tl_iso_related_categories', 'tl_iso_mail', 'tl_iso_mail_content'),
		'icon'						=> 'system/modules/isotope/html/icon-isotope.png',
		'stylesheet'				=> 'system/modules/isotope/html/backend.css',
	),
));

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_iso_addresses';

// Callback is only used for overview screen
if ($_GET['do'] == 'iso_setup' && strlen($_GET['table']))
{
	unset($GLOBALS['BE_MOD']['isotope']['iso_setup']['callback']);
}

// Isotope Modules
$GLOBALS['ISO_MOD'] = array
(
	'product' => array
	(
		'producttypes' => array
		(
			'tables'					=> array('tl_iso_producttypes'),
			'icon'						=> 'system/modules/isotope/html/icon-types.gif'
		),
		'attributes' => array
		(
			'tables'					=> array('tl_iso_attributes'),
			'icon'						=> 'system/modules/isotope/html/icon-attributes.gif',
		),
		'related_categories' => array
		(
			'tables'					=> array('tl_iso_related_categories'),
			'icon'						=> 'system/modules/isotope/html/icon-related.png',
		),
	),
	'checkout' => array
	(
		'payment' => array
		(
			'tables'					=> array('tl_iso_payment_modules', 'tl_payment_options'),
			'icon'						=> 'system/modules/isotope/html/icon-payment.png',
		),
		'shipping' => array
		(
				'tables'				=> array('tl_iso_shipping_modules','tl_iso_shipping_options'),
				'icon'					=> 'system/modules/isotope/html/icon-shipping.gif',
		),
		'tax_class' => array
		(
			'tables'					=> array('tl_iso_tax_class'),
			'icon'						=> 'system/modules/isotope/html/icon-taxclass.gif',
		),
		'tax_rate' => array
		(
			'tables'					=> array('tl_iso_tax_rate'),
			'icon'						=> 'system/modules/isotope/html/icon-taxrate.gif',
		),
	),
	'config' => array
	(
		'iso_mail' => array
		(
			'tables'					=> array('tl_iso_mail', 'tl_iso_mail_content'),
			'icon'						=> 'system/modules/isotope/html/icon-mail.gif',
		),
		'configs' => array
		(
			'tables'					=> array('tl_iso_config'),
			'icon'						=> 'system/modules/isotope/html/icon-isotope.png',
		),
	)
);


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope'] = array
(
	'iso_productlist'			=> 'ModuleIsotopeProductList',
	'iso_productreader'			=> 'ModuleIsotopeProductReader',
	'iso_cart'					=> 'ModuleIsotopeCart',
	'iso_checkout'				=> 'ModuleIsotopeCheckout',
	'iso_productfilter'			=> 'ModuleIsotopeProductFilter',
	'iso_orderhistory'			=> 'ModuleIsotopeOrderHistory',
	'iso_orderdetails'			=> 'ModuleIsotopeOrderDetails',
	'iso_configswitcher'		=> 'ModuleIsotopeConfigSwitcher',
	'iso_addressbook'			=> 'ModuleIsotopeAddressBook',
	'iso_relatedproducts'		=> 'ModuleIsotopeRelatedProducts',
);


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager']			= 'MediaManager';
$GLOBALS['BE_FFL']['attributeWizard']		= 'AttributeWizard';
$GLOBALS['BE_FFL']['surchargeWizard']		= 'SurchargeWizard';
$GLOBALS['BE_FFL']['variantWizard']			= 'VariantWizard';
$GLOBALS['BE_FFL']['inheritCheckbox']		= 'InheritCheckBox';
$GLOBALS['BE_FFL']['imageWatermarkWizard']	= 'ImageWatermarkWizard';
$GLOBALS['BE_FFL']['timePeriod']			= 'TimePeriod'; // This widget belongs to the core, but if extension "calendar" is disable it wont be available without this


/**
 * Shipping modules
 */
$GLOBALS['ISO_SHIP']['flat']		 = 'ShippingFlat';
$GLOBALS['ISO_SHIP']['order_total']	 = 'ShippingOrderTotal';
$GLOBALS['ISO_SHIP']['weight_total'] = 'ShippingWeightTotal';
$GLOBALS['ISO_SHIP']['ups']			 = 'ShippingUPS';
$GLOBALS['ISO_SHIP']['usps']		 = 'ShippingUSPS';


/**
 * Payment modules
 */
$GLOBALS['ISO_PAY']['cash']						= 'PaymentCash';
$GLOBALS['ISO_PAY']['paypal']					= 'PaymentPaypal';
$GLOBALS['ISO_PAY']['paypalpro']				= 'PaymentPaypalPro';
$GLOBALS['ISO_PAY']['paypalpayflowpro']			= 'PaymentPaypalPayflowPro';
$GLOBALS['ISO_PAY']['postfinance']				= 'PaymentPostfinance';
$GLOBALS['ISO_PAY']['authorizedotnet']			= 'PaymentAuthorizeDotNet';
$GLOBALS['ISO_PAY']['epay_window']				= 'PaymentEPay';
$GLOBALS['ISO_PAY']['epay_form']				= 'PaymentEPayForm';
$GLOBALS['ISO_PAY']['cybersource']				= 'PaymentCybersource';


/**
 * Galleries
 */
$GLOBALS['ISO_GAL']['default']					= 'IsotopeGallery';


/**
 * Product types
 */
$GLOBALS['ISO_PRODUCT'] = array
(
	'regular' => array
	(
		'class'	=> 'IsotopeProduct',
	),
);


/** 
 * Per page defaults
 */
$GLOBALS['ISO_PERPAGE'] = array(8,12,32,64);


/** 
 * Order Statuses
 */
$GLOBALS['ISO_ORDER'] = array('pending', 'processing', 'complete', 'on_hold', 'cancelled');

	
/** 
 * Permissions are access settings for user and groups (fields in tl_user and tl_user_group)
 */
$GLOBALS['TL_PERMISSIONS'][] = 'iso_configs';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_product_types';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_modules';


/**
 * Number formatting
 */
$GLOBALS['ISO_NUM']["10000.00"]		= array(2, '.', "");
$GLOBALS['ISO_NUM']["10,000.00"]	= array(2, '.', ",");
$GLOBALS['ISO_NUM']["10.000,00"]	= array(2, ',', ".");
$GLOBALS['ISO_NUM']["10'000.00"]	= array(2, '.', "'");


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][]			= array('Isotope', 'loadProductsDataContainer');
$GLOBALS['TL_HOOKS']['isoButtons'][]				= array('Isotope', 'defaultButtons');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]			= array('Isotope', 'validateRegexp');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]			= array('IsotopeFrontend', 'replaceIsotopeTags');
//$GLOBALS['TL_HOOKS']['googleTracking'][] 			= array('ModuleIsotopeCheckout', 'googleTracking');


/**
 * Checkout surcharge calculation callbacks
 */
$GLOBALS['TL_HOOKS']['isoCheckoutSurcharge'][] = array('IsotopeCart', 'getShippingSurcharge');
$GLOBALS['TL_HOOKS']['isoCheckoutSurcharge'][] = array('IsotopeCart', 'getPaymentSurcharge');


/**
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = array('IsotopeAutomator', 'deleteOldCarts');
$GLOBALS['TL_CRON']['daily'][] = array('IsotopeAutomator', 'deleteFailedOrders');


/**
 * Step callbacks for checkout module
 */
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
		array('ModuleIsotopeCheckout', 'getOrderConditionsInterface')
	),
);

$GLOBALS['ISO_ATTR'] = array
(
	'text' => array
	(
		'sql'		=> "varchar(255) NOT NULL default ''",
	),
	'textarea' => array
	(
		'sql'		=> "text NULL",
	),
	'select' => array
	(
		'sql'		=> "blob NULL",
	),
	'radio' => array
	(
		'sql'		=> "blob NULL",
	),
	'checkbox' => array
	(
		'sql'		=> "blob NULL",
	),
	'conditionalselect' => array
	(
		'sql'		=> "blob NULL",
		'callback'	=> array(array('Isotope', 'mergeConditionalOptionData')),
	),
	'mediaManager' => array
	(
		'sql'		=> "blob NULL",
	),
);


/**
 * URL Keywords for FolderURL extension
 */
$GLOBALS['URL_KEYWORDS'][] = 'product';
$GLOBALS['URL_KEYWORDS'][] = 'step';


/**
 * Default configuration
 */
$GLOBALS['TL_CONFIG']['iso_cartTimeout'] = 2592000;

