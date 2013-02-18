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
        'tables'                    => array('tl_iso_products', 'tl_iso_groups', 'tl_iso_product_categories', 'tl_iso_downloads', 'tl_iso_related_products', 'tl_iso_prices', 'tl_iso_price_tiers'),
        'icon'                        => 'system/modules/isotope/assets/store-open.png',
        'javascript'                => 'system/modules/isotope/assets/backend.min.js',
        'generate'                    => array('Isotope\tl_iso_products', 'generateVariants'),
        'quick_edit'                => array('Isotope\tl_iso_products', 'quickEditVariants'),
        'import'                    => array('Isotope\tl_iso_products', 'importAssets'),
    ),
    'iso_orders' => array
    (
        'tables'                    => array('tl_iso_collection', 'tl_iso_collection_product', 'tl_iso_collection_surcharge', 'tl_iso_collection_download'),
        'icon'                        => 'system/modules/isotope/assets/shopping-basket.png',
        'javascript'                => 'system/modules/isotope/assets/backend.min.js',
        'export_emails'             => array('Isotope\tl_iso_collection', 'exportOrderEmails'),
        'print_order'                => array('Isotope\tl_iso_collection', 'printInvoice'),
        'print_invoices'            => array('Isotope\tl_iso_collection', 'printInvoices'),
        'payment'                    => array('Isotope\tl_iso_collection', 'paymentInterface'),
        'shipping'                    => array('Isotope\tl_iso_collection', 'shippingInterface'),
    ),
    'iso_setup' => array
    (
        'callback'                    => 'Isotope\BackendModule\Setup',
        'tables'                    => array(),
        'icon'                        => 'system/modules/isotope/assets/application-monitor.png',
        'javascript'                => 'system/modules/isotope/assets/backend.min.js',
    ),
));

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_iso_addresses';

if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/backend.min.css';
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
            'tables'                    => array('tl_iso_producttypes'),
            'icon'                        => 'system/modules/isotope/assets/drawer.png'
        ),
        'attributes' => array
        (
            'tables'                    => array('tl_iso_attributes'),
            'icon'                        => 'system/modules/isotope/assets/table-insert-column.png',
        ),
        'related_categories' => array
        (
            'tables'                    => array('tl_iso_related_categories'),
            'icon'                        => 'system/modules/isotope/assets/category.png',
        ),
        'baseprice' => array
        (
            'tables'                    => array('tl_iso_baseprice'),
            'icon'                        => 'system/modules/isotope/assets/sort-price-descending.png',
        ),
    ),
    'checkout' => array
    (
        'payment' => array
        (
            'tables'                    => array('tl_iso_payment_modules'),
            'icon'                        => 'system/modules/isotope/assets/money-coin.png',
        ),
        'shipping' => array
        (
                'tables'                => array('tl_iso_shipping_modules','tl_iso_shipping_options'),
                'icon'                    => 'system/modules/isotope/assets/box-label.png',
        ),
        'tax_class' => array
        (
            'tables'                    => array('tl_iso_tax_class'),
            'icon'                        => 'system/modules/isotope/assets/globe.png',
        ),
        'tax_rate' => array
        (
            'tables'                    => array('tl_iso_tax_rate'),
            'icon'                        => 'system/modules/isotope/assets/calculator.png',
        ),
    ),
    'config' => array
    (
        'iso_mail' => array
        (
            'tables'                    => array('tl_iso_mail', 'tl_iso_mail_content'),
            'icon'                        => 'system/modules/isotope/assets/mail-open-document-text.png',
            'importMail'                => array('Isotope\Backend', 'importMail'),
            'exportMail'                => array('Isotope\Backend', 'exportMail'),
        ),
        'configs' => array
        (
            'tables'                    => array('tl_iso_config'),
            'icon'                        => 'system/modules/isotope/assets/construction.png',
        ),
        'orderstatus' => array
        (
            'tables'                    => array('tl_iso_collectiontatus'),
            'icon'                        => 'system/modules/isotope/assets/traffic-light.png',
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
    'iso_productlist'            => 'Isotope\Module\ProductList',
    'iso_productvariantlist'    => 'Isotope\Module\ProductVariantList',
    'iso_productreader'            => 'Isotope\Module\ProductReader',
    'iso_cart'                    => 'Isotope\Module\Cart',
    'iso_checkout'                => 'Isotope\Module\Checkout',
    'iso_productfilter'            => 'Isotope\Module\ProductFilter',
    'iso_cumulativefilter'        => 'Isotope\Module\CumulativeFilter',
    'iso_orderhistory'            => 'Isotope\Module\OrderHistory',
    'iso_orderdetails'            => 'Isotope\Module\OrderDetails',
    'iso_configswitcher'        => 'Isotope\Module\ConfigSwitcher',
    'iso_addressbook'            => 'Isotope\Module\AddressBook',
    'iso_relatedproducts'        => 'Isotope\Module\RelatedProducts',
    'iso_messages'                => 'Isotope\Module\Messages',
);


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager']            = 'Isotope\Widget\MediaManager';
$GLOBALS['BE_FFL']['attributeWizard']        = 'Isotope\Widget\AttributeWizard';
$GLOBALS['BE_FFL']['variantWizard']            = 'Isotope\Widget\VariantWizard';
$GLOBALS['BE_FFL']['inheritCheckbox']        = 'Isotope\Widget\InheritCheckBox';
$GLOBALS['BE_FFL']['fieldWizard']            = 'Isotope\Widget\FieldWizard';
$GLOBALS['BE_FFL']['productTree']            = 'Isotope\Widget\ProductTree';


/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_iso_config'] = 'Isotope\Model\Config';
$GLOBALS['TL_MODELS']['tl_iso_addresses'] = 'Isotope\Model\Address';
$GLOBALS['TL_MODELS']['tl_iso_tax_class'] = 'Isotope\Model\TaxClass';
$GLOBALS['TL_MODELS']['tl_iso_tax_rate'] = 'Isotope\Model\TaxRate';
$GLOBALS['TL_MODELS']['tl_iso_payment_modules'] = 'Isotope\Payment\Payment';
$GLOBALS['TL_MODELS']['tl_iso_shipping_modules'] = 'Isotope\Shipping\Shipping';


/**
 * Product types
 */
$GLOBALS['ISO_PRODUCT'] = array
(
    'standard' => array
    (
        'class'    => 'Isotope\Product\Standard',
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
$GLOBALS['ISO_NUM']["10000.00"]        = array(2, '.', "");
$GLOBALS['ISO_NUM']["10,000.00"]    = array(2, '.', ",");
$GLOBALS['ISO_NUM']["10.000,00"]    = array(2, ',', ".");
$GLOBALS['ISO_NUM']["10'000.00"]    = array(2, '.', "'");


/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['loadDataContainer'][]            = array('Isotope\tl_iso_products', 'loadProductsDCA');
$GLOBALS['TL_HOOKS']['loadDataContainer'][]            = array('Isotope\tl_member', 'limitCountries');
$GLOBALS['TL_HOOKS']['addCustomRegexp'][]            = array('Isotope\Isotope', 'validateRegexp');
$GLOBALS['TL_HOOKS']['getSearchablePages'][]        = array('Isotope\Frontend', 'addProductsToSearchIndex');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][]            = array('Isotope\Frontend', 'replaceIsotopeTags');
$GLOBALS['TL_HOOKS']['generatePage'][]                = array('Isotope\Frontend', 'injectMessages');
$GLOBALS['TL_HOOKS']['executePreActions'][]            = array('Isotope\Widget\ProductTree', 'executePreActions');
$GLOBALS['TL_HOOKS']['executePostActions'][]        = array('Isotope\Widget\ProductTree', 'executePostActions');
$GLOBALS['TL_HOOKS']['translateUrlParameters'][]    = array('Isotope\Frontend', 'translateProductUrls');
$GLOBALS['TL_HOOKS']['getSystemMessages'][]            = array('Isotope\Backend', 'getOrderMessages');
$GLOBALS['TL_HOOKS']['sqlGetFromFile'][]            = array('Isotope\Backend', 'addAttributesToDBUpdate');
$GLOBALS['TL_HOOKS']['getArticle'][]                = array('Isotope\Frontend', 'storeCurrentArticle');
$GLOBALS['TL_HOOKS']['generateBreadcrumb'][]        = array('Isotope\Frontend', 'generateBreadcrumb');
$GLOBALS['ISO_HOOKS']['buttons'][]                    = array('Isotope\Isotope', 'defaultButtons');
$GLOBALS['ISO_HOOKS']['checkoutSurcharge'][]        = array('Isotope\Frontend', 'getShippingAndPaymentSurcharges');

if (TL_MODE == 'FE')
{
    // Do not parse backend templates
    $GLOBALS['TL_HOOKS']['parseTemplate'][]            = array('Isotope\Frontend', 'addNavigationClass');
}


/**
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = array('Isotope\Automator', 'deleteOldCarts');
$GLOBALS['TL_CRON']['daily'][] = array('Isotope\Automator', 'convertCurrencies');


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
        'sql'        => "varchar(255) NOT NULL default ''",
        'useIndex'    => true,
    ),
    'textarea' => array
    (
        'sql'        => "text NULL",
    ),
    'select' => array
    (
        'sql'        => "blob NULL",
    ),
    'radio' => array
    (
        'sql'        => "varchar(255) NOT NULL default ''",
        'useIndex'    => true,
    ),
    'checkbox' => array
    (
        'sql'        => "blob NULL",
    ),
    'conditionalselect' => array
    (
        'sql'        => "blob NULL",
        'callback'    => array(array('Isotope', 'mergeConditionalOptionData')),
    ),
    'mediaManager' => array
    (
        'sql'        => "blob NULL",
    ),
    'fileTree' => array
    (
        'sql'        => "blob NULL",
    ),
    'downloads' => array
    (
        'sql'        => "blob NULL",
        'backend'    => 'fileTree',
    ),
    'upload' => array
    (
        'sql'                => "varchar(255) NOT NULL default ''",
        'backend'            => false,
        'customer_defined'    => true,
    ),
);


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
