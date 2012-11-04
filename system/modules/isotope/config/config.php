<?php

/**
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009-2012 Isotope eCommerce Workgroup
 *
 * @package    Isotope
 * @link       http://www.isotopeecommerce.com
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 *
 * @author     Andreas Schempp <andreas.schempp@terminal42.ch>
 * @author     Fred Bliss <fred.bliss@intelligentspark.com>
 * @author     Christian de la Haye <service@delahaye.de>
 */


/**
 * Isotope Version
 */
@define('ISO_VERSION', '2.0');
@define('ISO_BUILD', 'dev');


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
		'tables'					=> array('tl_iso_products', 'tl_iso_groups', 'tl_iso_product_categories', 'tl_iso_downloads', 'tl_iso_related_products', 'tl_iso_prices', 'tl_iso_price_tiers'),
		'icon'						=> 'system/modules/isotope/html/store-open.png',
		'javascript'				=> 'system/modules/isotope/html/backend.js',
		'generate'					=> array('tl_iso_products', 'generateVariants'),
		'quick_edit'				=> array('tl_iso_products', 'quickEditVariants'),
		'import'					=> array('tl_iso_products', 'importAssets'),
	),
	'iso_orders' => array
	(
		'tables'					=> array('tl_iso_orders', 'tl_iso_order_items'),
		'icon'						=> 'system/modules/isotope/html/shopping-basket.png',
		'javascript'				=> 'system/modules/isotope/html/backend.js',
		'export_emails'     		=> array('tl_iso_orders', 'exportOrderEmails'),
		'print_order'				=> array('tl_iso_orders', 'printInvoice'),
		'print_invoices'			=> array('tl_iso_orders', 'printInvoices'),
		'payment'					=> array('tl_iso_orders', 'paymentInterface'),
		'shipping'					=> array('tl_iso_orders', 'shippingInterface'),
	),
	'iso_setup' => array
	(
		'callback'					=> 'ModuleIsotopeSetup',
		'tables'					=> array(),
		'icon'						=> 'system/modules/isotope/html/application-monitor.png',
		'javascript'				=> 'system/modules/isotope/html/backend.js',
	),
));

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_iso_addresses';

if (TL_MODE == 'BE')
{
	$GLOBALS['TL_CSS'][] = 'system/modules/isotope/html/backend.css';
}


/**
 * Isotope Modules
 */
$GLOBALS['ISO_MOD'] = array
(
	'product' => array
	(
		'producttypes' => array
		(
			'tables'					=> array('tl_iso_producttypes'),
			'icon'						=> 'system/modules/isotope/html/drawer.png'
		),
		'attributes' => array
		(
			'tables'					=> array('tl_iso_attributes'),
			'icon'						=> 'system/modules/isotope/html/table-insert-column.png',
		),
		'related_categories' => array
		(
			'tables'					=> array('tl_iso_related_categories'),
			'icon'						=> 'system/modules/isotope/html/category.png',
		),
	),
	'checkout' => array
	(
		'payment' => array
		(
			'tables'					=> array('tl_iso_payment_modules'),
			'icon'						=> 'system/modules/isotope/html/money-coin.png',
		),
		'shipping' => array
		(
				'tables'				=> array('tl_iso_shipping_modules','tl_iso_shipping_options'),
				'icon'					=> 'system/modules/isotope/html/box-label.png',
		),
		'tax_class' => array
		(
			'tables'					=> array('tl_iso_tax_class'),
			'icon'						=> 'system/modules/isotope/html/globe.png',
		),
		'tax_rate' => array
		(
			'tables'					=> array('tl_iso_tax_rate'),
			'icon'						=> 'system/modules/isotope/html/calculator.png',
		),
	),
	'config' => array
	(
		'iso_mail' => array
		(
			'tables'					=> array('tl_iso_mail', 'tl_iso_mail_content'),
			'icon'						=> 'system/modules/isotope/html/mail-open-document-text.png',
			'importMail'				=> array('IsotopeBackend', 'importMail'),
			'exportMail'				=> array('IsotopeBackend', 'exportMail'),
		),
		'configs' => array
		(
			'tables'					=> array('tl_iso_config'),
			'icon'						=> 'system/modules/isotope/html/construction.png',
		),
		'baseprice' => array
		(
			'tables'					=> array('tl_iso_baseprice'),
			'icon'						=> 'system/modules/isotope/html/sort-price-descending.png',
		),
		'orderstatus' => array
		(
			'tables'					=> array('tl_iso_orderstatus'),
			'icon'						=> 'system/modules/isotope/html/traffic-light.png',
		),
	)
);

// Enable tables in iso_setup
if ($_GET['do'] == 'iso_setup')
{
	foreach ($GLOBALS['ISO_MOD'] as $strGroup=>$arrModules)
	{
		foreach ($arrModules as $strModule => $arrConfig)
		{
			if (is_array($arrConfig['tables']))
			{
				$GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'] = array_merge($GLOBALS['BE_MOD']['isotope']['iso_setup']['tables'], $arrConfig['tables']);
			}
		}
	}
}


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope'] = array
(
	'iso_productlist'			=> 'ModuleIsotopeProductList',
	'iso_productvariantlist'	=> 'ModuleIsotopeProductVariantList',
	'iso_productreader'			=> 'ModuleIsotopeProductReader',
	'iso_cart'					=> 'ModuleIsotopeCart',
	'iso_checkout'				=> 'ModuleIsotopeCheckout',
	'iso_productfilter'			=> 'ModuleIsotopeProductFilter',
	'iso_cumulativefilter'		=> 'ModuleIsotopeCumulativeFilter',
	'iso_orderhistory'			=> 'ModuleIsotopeOrderHistory',
	'iso_orderdetails'			=> 'ModuleIsotopeOrderDetails',
	'iso_configswitcher'		=> 'ModuleIsotopeConfigSwitcher',
	'iso_addressbook'			=> 'ModuleIsotopeAddressBook',
	'iso_relatedproducts'		=> 'ModuleIsotopeRelatedProducts'
);


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager']			= 'MediaManager';
$GLOBALS['BE_FFL']['attributeWizard']		= 'AttributeWizard';
$GLOBALS['BE_FFL']['variantWizard']			= 'VariantWizard';
$GLOBALS['BE_FFL']['inheritCheckbox']		= 'InheritCheckBox';
$GLOBALS['BE_FFL']['fieldWizard']			= 'FieldWizard';
$GLOBALS['BE_FFL']['productTree']			= 'ProductTree';


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
$GLOBALS['ISO_PAY']['cybersource']				= 'PaymentCybersource';


/**
 * Galleries
 */
$GLOBALS['ISO_GAL']['default']					= 'IsotopeGallery';
$GLOBALS['ISO_GAL']['inline']					= 'InlineGallery';
$GLOBALS['ISO_GAL']['zoom']						= 'ZoomGallery';


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
 * Permissions are access settings for user and groups (fields in tl_user and tl_user_group)
 */
$GLOBALS['TL_PERMISSIONS'][] = 'iso_modules';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_product_types';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_product_typep';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_payment_modules';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_payment_modulep';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_shipping_modules';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_shipping_modulep';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_tax_rates';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_tax_ratep';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_tax_classes';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_tax_classp';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_mails';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_mailp';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_configs';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_configp';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_groups';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_groupp';


/**
 * Allow to delete the cache in maintenance module
 */
$GLOBALS['TL_CACHE'][] = 'tl_iso_productcache';
$GLOBALS['TL_CACHE'][] = 'tl_iso_requestcache';


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
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]			= array('Isotope', 'validateRegexp');
$GLOBALS['TL_HOOKS']['getSearchablePages'][]		= array('IsotopeFrontend', 'addProductsToSearchIndex');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]			= array('IsotopeFrontend', 'replaceIsotopeTags');
$GLOBALS['TL_HOOKS']['generatePage'][]				= array('IsotopeFrontend', 'injectMessages');
$GLOBALS['TL_HOOKS']['executePreActions'][]			= array('ProductTree', 'executePreActions');
$GLOBALS['TL_HOOKS']['executePostActions'][]		= array('ProductTree', 'executePostActions');
$GLOBALS['TL_HOOKS']['translateUrlParameters'][]	= array('IsotopeFrontend', 'translateProductUrls');
$GLOBALS['TL_HOOKS']['getSystemMessages'][]			= array('IsotopeBackend', 'getOrderMessages');
$GLOBALS['TL_HOOKS']['sqlGetFromFile'][]			= array('IsotopeBackend', 'addAttributesToDBUpdate');
$GLOBALS['TL_HOOKS']['getArticle'][]				= array('IsotopeFrontend', 'storeCurrentArticle');
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][]		= array('IsotopeFrontend', 'generateBreadcrumb');
$GLOBALS['ISO_HOOKS']['buttons'][]					= array('Isotope', 'defaultButtons');
$GLOBALS['ISO_HOOKS']['checkoutSurcharge'][]		= array('IsotopeFrontend', 'getShippingAndPaymentSurcharges');

if (TL_MODE == 'FE')
{
	// Do not parse backend templates
	$GLOBALS['TL_HOOKS']['parseTemplate'][]			= array('IsotopeFrontend', 'addNavigationClass');
}


/**
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = array('IsotopeAutomator', 'deleteOldCarts');
$GLOBALS['TL_CRON']['daily'][] = array('IsotopeAutomator', 'convertCurrencies');


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
		array('ModuleIsotopeCheckout', 'getOrderConditionsOnTop'),
		array('ModuleIsotopeCheckout', 'getOrderInfoInterface'),
		array('ModuleIsotopeCheckout', 'getOrderConditionsBeforeProducts'),
		array('ModuleIsotopeCheckout', 'getOrderProductsInterface'),
		array('ModuleIsotopeCheckout', 'getOrderConditionsAfterProducts'),
	)
);

$GLOBALS['ISO_ATTR'] = array
(
	'text' => array
	(
		'sql'		=> "varchar(255) NOT NULL default ''",
		'useIndex'	=> true,
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
		'sql'		=> "varchar(255) NOT NULL default ''",
		'useIndex'	=> true,
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
	'fileTree' => array
	(
		'sql'		=> "blob NULL",
	),
	'downloads' => array
	(
		'sql'		=> "blob NULL",
		'backend'	=> 'fileTree',
	),
	'upload' => array
	(
		'sql'				=> "varchar(255) NOT NULL default ''",
		'backend'			=> false,
		'customer_defined'	=> true,
	),
);


/**
 * URL Keywords for FolderURL extension
 */
$GLOBALS['URL_KEYWORDS'][] = 'product';
$GLOBALS['URL_KEYWORDS'][] = 'step';


/**
 * Auto_item keywords
 */
$GLOBALS['TL_AUTO_ITEM'][] = 'product';
$GLOBALS['TL_AUTO_ITEM'][] = 'step';


/**
 * Default configuration
 */
$GLOBALS['TL_CONFIG']['iso_cartTimeout'] = 2592000;


/**
 * Options
 */
define('ISO_CLASS_NAME', 1);
define('ISO_CLASS_KEY', 2);
define('ISO_CLASS_COUNT', 4);
define('ISO_CLASS_EVENODD', 8);
define('ISO_CLASS_FIRSTLAST', 16);
define('ISO_CLASS_ROW', 32);
define('ISO_CLASS_COL', 64);

