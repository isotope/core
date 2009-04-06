<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * TYPOlight webCMS
 *
 * The TYPOlight webCMS is an accessible web content management system that 
 * specializes in accessibility and generates W3C-compliant HTML code. It 
 * provides a wide range of functionality to develop professional websites 
 * including a built-in search engine, form generator, file and user manager, 
 * CSS engine, multi-language support and many more. For more information and 
 * additional TYPOlight applications like the TYPOlight MVC Framework please 
 * visit the project website http://www.typolight.org.
 *
 * This is the isotope configuration file.
 *
 * PHP version 5
 * @copyright  Winans Creative / Fred Bliss 2009
 * @author     Fred Bliss
 * @package    Isotope 
 * @license    GPL 
 * @filesource
 */


/**
 * Back-end module
 */

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_address_book';


$GLOBALS['BE_MOD']['store']['store_configuration'] = array
(
	'tables'		=> array('tl_store'),
	'icon'			=> 'system/modules/isotope/html/iso_icon.gif'
);

$GLOBALS['BE_MOD']['store']['products_and_attributes'] = array
(
	'tables'		=> array('tl_product_attribute_sets','tl_product_attributes','tl_product_data'),
	'icon'			=> 'system/modules/isotope/html/pa_icon.gif'
);

$GLOBALS['BE_MOD']['store']['shipping'] = array
(
		'tables'     => array('tl_shipping_modules', 'tl_shipping_rates'),
		'icon'       => 'system/modules/isotope/html/icon-shipping.gif'
);


$GLOBALS['BE_MOD']['store']['orders'] = array
(
		'tables'     => array('tl_iso_orders'),
		'authorize_process_payment' => array('IsotopePOS', 'getPOSInterface'),
		'print_order'	=> array('IsotopePOS','printInvoice'),
		'icon'       => 'system/modules/isotope/html/icon-orders.gif'
);

$GLOBALS['BE_MOD']['store']['taxes'] = array
(
	'tables'		=> array('tl_tax_class','tl_tax_rate'),
	'icon'			=> 'system/modules/isotope/html/icon-taxes.gif'
);

$GLOBALS['TL_HOOKS']['createNewUser'][] = array('IsotopeCallbacks','copyAddressBookEntry');
$GLOBALS['TL_HOOKS']['createNewUser'][] = array('IsotopeCallbacks','autoActivateNewMember');

/*
$GLOBALS['BE_MOD']['store']['payment_modules'] = array
(
	'tables'		=> array('tl_payment_modules'),
	'icon'			=> 'system/modules/isotope/html/pm_icon.gif'
);

$GLOBALS['BE_MOD']['store']['shipping_modules'] = array
(
	'tables'		=> array('tl_shipping_modules'),
	'icon'			=> 'system/modules/isotope/html/sm_icon.gif'
);
*/

$GLOBALS['BE_MOD']['store']['isotopeMedia'] = array
(
	'tables' 		=> array('tl_media')
);

/*$GLOBALS['BE_MOD']['store']['isotope_media_manager'] = array
(
	'tables'		=> array('tl_media'),
	'icon'			=> 'html/icon_mm.gif',
	'stylesheet'	=> 'html/stylesheet.css'	
);*/

/**
 * Used in store configuration to enable/disable certain store-wide features
 */
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoShoppingCart';
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoCheckout';
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoGiftRegistry';
$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoAddressBook';

//$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoProductComments';
//$GLOBALS['ISOTOPE_FE_MODULES'][] = 'isoCartWishlist';

//$GLOBALS['ISO_PAYMENT_MODULE']['GATEWAY_TYPES'][] = 'authorize';

//$GLOBALS['ISO_PAYMENT_MODULE']['GATEWAY_TYPES'][] = 'paypal_pro';

$GLOBALS['BE_FFL']['mediaManager'] = 'MediaManager';


$GLOBALS['FE_MOD']['eCommerce'] = array(
	'isoFilterModule'			=> 'ModuleFilters',
	'isoProductLister'			=> 'ModuleProductLister',
	'isoProductReader'			=> 'ModuleProductReader',
	'isoShoppingCart'			=> 'ModuleShoppingCart',
	'isoCheckout'				=> 'ModuleIsotopeCheckout',
	'isoGiftRegistryManager'	=> 'ModuleGiftRegistry',
	'isoGiftRegistrySearch'		=> 'ModuleGiftRegistrySearch',
	'isoGiftRegistryResults'	=> 'ModuleGiftRegistryResults',
	'isoGiftRegistryReader'		=> 'ModuleGiftRegistryReader',
	'isoAddressBook'			=> 'ModuleAddressBook'
);

/**
 * Handle Collections
 */
/*$GLOBALS['FE_MOD']['isoLister']['TPL_COLL']['product_listing'] = array(
	'sortByOptions'			 => array
	(
		'url'				 => '',
		'label' 			 => ''
	),
	'products'				 => array
	(
		'thumbnail_image'	 => '',
		'product_name'		 => '',
		'product_link'		 => '',
		'product_teaser'	 => '',
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
 * HOOK Permissions
 */
$GLOBALS['TL_CTE']['links']['attributeLinkRepeater'] = 'ContentAttributeLinkRepeater';
  
$GLOBALS['ISO_PLUGINS']['jwMediaPlayer']['mediaRSSPlaylist'] = 'media_rss';

$GLOBALS['TL_HOOKS']['saveProduct'][] = array('MediaManagement','generateMediaPlayerRSSPlaylist');

//$GLOBALS['TL_HOOKS']['getMappingAttributes'][] = array('getProductMapping' => array('ProductCatalog','generateMappingAttributeList'));
$GLOBALS['TL_HOOKS']['getMappingAttributes'][] = array('ProductCatalog','generateMappingAttributeList');

$GLOBALS['TL_HOOKS']['mappingAttributesCallback'][] = array('products_attribute_set' => array('ProductCatalog','batchUpdateCAPAggregate'));

$GLOBALS['TL_LANG']['MSC']['isotope_function_group'] = 'Isotope Ecommerce Functions';

$GLOBALS['DATAMANAGER_PREUPDATE_FUNCTION'][$GLOBALS['TL_LANG']['MSC']['isotope_function_group']][] = array('getStringChunk' => array('ProductCatalog','generateTeaser'));
$GLOBALS['DATAMANAGER_PREUPDATE_FUNCTION'][$GLOBALS['TL_LANG']['MSC']['isotope_function_group']][] = array('createSKU' => array('ProductCatalog','generateSKU'));
$GLOBALS['DATAMANAGER_PREUPDATE_FUNCTION'][$GLOBALS['TL_LANG']['MSC']['isotope_function_group']][] = array('productImageImport' => array('MediaManager','productImageImport'));
$GLOBALS['DATAMANAGER_PREUPDATE_FUNCTION'][$GLOBALS['TL_LANG']['MSC']['isotope_function_group']][] = array('convertToPrice' => array('DataManager','convertToDecimal'));
$GLOBALS['DATAMANAGER_PREUPDATE_FUNCTION'][$GLOBALS['TL_LANG']['MSC']['isotope_function_group']][] = array('convertToWeight' => array('DataManager','convertToDecimal'));

//$GLOBALS['ISO_ACTIVE_CUSTOM_PRODUCT_BUTTONS'][] = array('add_to_gift_registry');

//Product Name
$GLOBALS['ISO_ATTR'][] = array
( 
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'text',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 1,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Name',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1
);
	
//Product SKU			
$GLOBALS['ISO_ATTR'][] = array
( 
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'text',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 1,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product SKU',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1
);
		
// Product Weight	
$GLOBALS['ISO_ATTR'][] = array
(  
	'pid' => $dc->id,  
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'decimal',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 0,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Weight',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1
);
		

//Quantity in stock	
$GLOBALS['ISO_ATTR'][] = array
( 
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'integer',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 1,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Quantity',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1
);
		
// Product Alias	
$GLOBALS['ISO_ATTR'][] = array
( 
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'shorttext',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 1,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Alias',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1,
	'rgxp' => 'extnd',
	'save_callback' => 'ProductCatalog.generateAlias,MediaManagement.createProductAssetFolders'
);

// Product Visibility	
$GLOBALS['ISO_ATTR'][] = array
( 
	
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'checkbox',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 0,
	'is_required' => 0,
	'is_filterable' => 0,
	'is_searchable' => 0,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Visibility',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 0,
	'delete_locked' => 1
);
		
// Product Teaser		
$GLOBALS['ISO_ATTR'][] = array
( 
	
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'longtext',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 0,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Teaser',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1
);
		
	
// Product Description		
$GLOBALS['ISO_ATTR'][] = array
( 
	
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'longtext',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 1,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Description',
	'use_rich_text_editor' => 1,
	'is_user_defined' => 0,
	'is_listing_field' => 0,
	'delete_locked' => 1,
	'save_callback' => 'ProductCatalog.generateTeaser'
);
		
// Product Price		
$GLOBALS['ISO_ATTR'][] = array
( 
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'decimal',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 1,
	'is_filterable' => 0,
	'is_searchable' => 0,
	'is_used_for_price_rules' => 1,
	'is_multiple_select' => 0,
	'name' => 'Product Price',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1
);
	
//Product Price Override
$GLOBALS['ISO_ATTR'][] = array
( 
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'text',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_required' => 0,
	'is_filterable' => 0,
	'is_searchable' => 1,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Price Override',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 1,
	'delete_locked' => 1
);

// Use Product Price Override	
$GLOBALS['ISO_ATTR'][] = array
( 
	
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'checkbox',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 0,
	'is_required' => 0,
	'is_filterable' => 0,
	'is_searchable' => 0,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Use Product Price Override',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 0,
	'delete_locked' => 1
);
	
	
// Product Media
$GLOBALS['ISO_ATTR'][] = array
( 
	'pid' => $dc->id, 
	'tstamp' => time(),
	'sorting' => $intSorting,
	'type' => 'media',
	'attr_use_mode' => 'fixed',
	'is_customer_defined' => 0,
	'is_visible_on_front' => 1,
	'is_hidden_on_backend' => 1,
	'is_required' => 0,
	'is_filterable' => 0,
	'is_searchable' => 0,
	'is_used_for_price_rules' => 0,
	'is_multiple_select' => 0,
	'name' => 'Product Media',
	'use_rich_text_editor' => 0,
	'is_user_defined' => 0,
	'is_listing_field' => 0,
	'delete_locked' => 1,
	//'save_callback' => 'MediaManagement.thumbnailImages',
	'show_files' => 0
);



?>