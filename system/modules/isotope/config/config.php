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
 * @author     Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 */


/**
 * Isotope constants
 */
@define('ISO_VERSION', '2.0');
@define('ISO_BUILD', 'beta1');
@define('ISO_DEBUG', (bool) $GLOBALS['TL_CONFIG']['debugMode']);


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
        'tables'            => array(\Isotope\Model\Product::getTable(), \Isotope\Model\Group::getTable(), \Isotope\Model\ProductCategory::getTable(), \Isotope\Model\Download::getTable(), \Isotope\Model\RelatedProduct::getTable(), \Isotope\Model\ProductPrice::getTable(), 'tl_iso_product_pricetier'),
        'icon'              => 'system/modules/isotope/assets/images/store-open.png',
        'javascript'        => 'system/modules/isotope/assets/js/backend'.(ISO_DEBUG ? '' : '.min').'.js',
        'generate'          => array('Isotope\Backend\Product\VariantGenerator', 'generate'),
        'import'            => array('Isotope\Backend\Product\AssetImport', 'generate'),
    ),
    'iso_orders' => array
    (
        'tables'            => array(\Isotope\Model\ProductCollection::getTable(), \Isotope\Model\ProductCollectionItem::getTable(), \Isotope\Model\ProductCollectionSurcharge::getTable(), \Isotope\Model\ProductCollectionDownload::getTable(), \Isotope\Model\Address::getTable()),
        'icon'              => 'system/modules/isotope/assets/images/shopping-basket.png',
        'javascript'        => 'system/modules/isotope/assets/js/backend'.(ISO_DEBUG ? '' : '.min').'.js',
        'print_document'    => array('Isotope\Backend\ProductCollection\Callback', 'printDocument'),
        'payment'           => array('Isotope\Backend\ProductCollection\Callback', 'paymentInterface'),
        'shipping'          => array('Isotope\Backend\ProductCollection\Callback', 'shippingInterface'),
    ),
    'iso_setup' => array
    (
        'callback'          => 'Isotope\BackendModule\Setup',
        'tables'            => array(),
        'icon'              => 'system/modules/isotope/assets/images/application-monitor.png',
        'javascript'        => 'system/modules/isotope/assets/js/backend'.(ISO_DEBUG ? '' : '.min').'.js',
    ),
));

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = \Isotope\Model\Address::getTable();

if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/css/backend'.(ISO_DEBUG ? '' : '.min').'.css';
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
            'tables'            => array(\Isotope\Model\ProductType::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-producttypes.png'
        ),
        'attributes' => array
        (
            'tables'            => array(\Isotope\Model\Attribute::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-attributes.png',
        ),
        'related_categories' => array
        (
            'tables'            => array(\Isotope\Model\RelatedCategory::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-related_categories.png',
        ),
        'gallery' => array
        (
            'tables'            => array(\Isotope\Model\Gallery::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-gallery.png',
        ),
        'baseprice' => array
        (
            'tables'            => array(\Isotope\Model\BasePrice::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-baseprice.png',
        ),
    ),
    'checkout' => array
    (
        'payment' => array
        (
            'tables'            => array(\Isotope\Model\Payment::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-payment.png',
        ),
        'shipping' => array
        (
                'tables'        => array(\Isotope\Model\Shipping::getTable()),
                'icon'          => 'system/modules/isotope/assets/images/setup-shipping.png',
        ),
        'tax_class' => array
        (
            'tables'            => array(\Isotope\Model\TaxClass::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-tax_class.png',
        ),
        'tax_rate' => array
        (
            'tables'            => array(\Isotope\Model\TaxRate::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-tax_rate.png',
        ),
    ),
    'config' => array
    (
        'configs' => array
        (
            'tables'            => array(\Isotope\Model\Config::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-config.png',
        ),
        'orderstatus' => array
        (
            'tables'            => array(\Isotope\Model\OrderStatus::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-orderstatus.png',
        ),
        'notifications' => array
        (
            'icon'              => 'system/modules/isotope/assets/images/setup-notifications.png',
            'redirect'          => 'contao/main.php?do=nc_notifications',
        ),
        'documents' => array
        (
            'tables'            => array(\Isotope\Model\Document::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-documents.png'
        ),
        'labels' => array
        (
            'tables'            => array(\Isotope\Model\Label::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-labels.png'
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
    'iso_productlist'           => 'Isotope\Module\ProductList',
    'iso_productvariantlist'    => 'Isotope\Module\ProductVariantList',
    'iso_productreader'         => 'Isotope\Module\ProductReader',
    'iso_cart'                  => 'Isotope\Module\Cart',
    'iso_checkout'              => 'Isotope\Module\Checkout',
    'iso_productfilter'         => 'Isotope\Module\ProductFilter',
    'iso_cumulativefilter'      => 'Isotope\Module\CumulativeFilter',
    'iso_orderhistory'          => 'Isotope\Module\OrderHistory',
    'iso_orderdetails'          => 'Isotope\Module\OrderDetails',
    'iso_configswitcher'        => 'Isotope\Module\ConfigSwitcher',
    'iso_addressbook'           => 'Isotope\Module\AddressBook',
    'iso_relatedproducts'       => 'Isotope\Module\RelatedProducts',
    'iso_messages'              => 'Isotope\Module\Messages',
);


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager']           = 'Isotope\Widget\MediaManager';
$GLOBALS['BE_FFL']['inheritCheckbox']        = 'Isotope\Widget\InheritCheckBox';
$GLOBALS['BE_FFL']['productGroupSelector']   = 'Isotope\Widget\ProductGroupSelector';

/**
 * Payment methods
 */
\Isotope\Model\Payment::registerModelType('cash', 'Isotope\Model\Payment\Cash');
\Isotope\Model\Payment::registerModelType('datatrans', 'Isotope\Model\Payment\Datatrans');
\Isotope\Model\Payment::registerModelType('expercash', 'Isotope\Model\Payment\Expercash');
\Isotope\Model\Payment::registerModelType('payone', 'Isotope\Model\Payment\Payone');
\Isotope\Model\Payment::registerModelType('paypal', 'Isotope\Model\Payment\Paypal');
\Isotope\Model\Payment::registerModelType('postfinance', 'Isotope\Model\Payment\Postfinance');
\Isotope\Model\Payment::registerModelType('viveum', 'Isotope\Model\Payment\Viveum');
\Isotope\Model\Payment::registerModelType('saferpay', 'Isotope\Model\Payment\Saferpay');
\Isotope\Model\Payment::registerModelType('sparkasse', 'Isotope\Model\Payment\Sparkasse');
\Isotope\Model\Payment::registerModelType('sofortueberweisung', 'Isotope\Model\Payment\Sofortueberweisung');
\Isotope\Model\Payment::registerModelType('worldpay', 'Isotope\Model\Payment\Worldpay');

/**
 * Shipping methods
 */
\Isotope\Model\Shipping::registerModelType('flat', 'Isotope\Model\Shipping\Flat');

/**
 * Documents
 */
\Isotope\Model\Document::registerModelType('standard', 'Isotope\Model\Document\Standard');

/**
 * Galleries
 */
\Isotope\Model\Gallery::registerModelType('standard', 'Isotope\Model\Gallery\Standard');
\Isotope\Model\Gallery::registerModelType('inline', 'Isotope\Model\Gallery\Inline');

/**
 * Products
 */
\Isotope\Model\Product::registerModelType('standard', 'Isotope\Model\Product\Standard');

/**
 * Product collections
 */
\Isotope\Model\ProductCollection::registerModelType('cart', 'Isotope\Model\ProductCollection\Cart');
\Isotope\Model\ProductCollection::registerModelType('order', 'Isotope\Model\ProductCollection\Order');

/**
 * Product collection surcharge
 */
\Isotope\Model\ProductCollectionSurcharge::registerModelType('payment', 'Isotope\Model\ProductCollectionSurcharge\Payment');
\Isotope\Model\ProductCollectionSurcharge::registerModelType('shipping', 'Isotope\Model\ProductCollectionSurcharge\Shipping');
\Isotope\Model\ProductCollectionSurcharge::registerModelType('tax', 'Isotope\Model\ProductCollectionSurcharge\Tax');

/**
 * Attributes
 */
\Isotope\Model\Attribute::registerModelType('text', 'Isotope\Model\Attribute\TextField');
\Isotope\Model\Attribute::registerModelType('textarea', 'Isotope\Model\Attribute\TextArea');
\Isotope\Model\Attribute::registerModelType('select', 'Isotope\Model\Attribute\SelectMenu');
\Isotope\Model\Attribute::registerModelType('radio', 'Isotope\Model\Attribute\RadioButton');
\Isotope\Model\Attribute::registerModelType('checkbox', 'Isotope\Model\Attribute\CheckboxMenu');
\Isotope\Model\Attribute::registerModelType('mediaManager', 'Isotope\Model\Attribute\MediaManager');
\Isotope\Model\Attribute::registerModelType('fileTree', 'Isotope\Model\Attribute\FileTree');
\Isotope\Model\Attribute::registerModelType('downloads', 'Isotope\Model\Attribute\Downloads');
\Isotope\Model\Attribute::registerModelType('upload', 'Isotope\Model\Attribute\Upload');

/**
 * Notification Center notification types
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'] = array('recipient_email');
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['attachment_tokens'] = array('form_*', 'document');
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'] = array(
    'uniqid',
    'status_id',
    'recipient_email',
    'order_id',
    'order_status',
    'order_status_new',
    'order_items',
    'order_products',
    'order_subtotal',
    'order_total',
    'document_number',
    'cart_html',
    'cart_text',
    'document',
    'billing_*', // All the billing address model fields
    'billing_address', // Billing address as HTML
    'billing_address_text', // Billing address as text
    'shipping_*', // All the shipping address model fields
    'shipping_address', // Shipping address as HTML
    'shipping_address_text', // Shipping address as text
    'form_*', // All the order condition form fields
    'payment_id', // Payment method ID
    'payment_label', // Payment method label
    'payment_note', // Payment method note
    'payment_note_text', // Payment method note without HTML tags
    'shipping_id', // Shipping method ID
    'shipping_label', // Shipping method label
    'shipping_note', // Shipping method note
    'shipping_note_text', // Shipping method note without HTML tags
);
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_subject'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_html'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];


/**
 * Models
 */
$GLOBALS['TL_MODELS'][\Isotope\Model\Config::getTable()]                        = 'Isotope\Model\Config';
$GLOBALS['TL_MODELS'][\Isotope\Model\Address::getTable()]                       = 'Isotope\Model\Address';
$GLOBALS['TL_MODELS'][\Isotope\Model\BasePrice::getTable()]                     = 'Isotope\Model\BasePrice';
$GLOBALS['TL_MODELS'][\Isotope\Model\Document::getTable()]                      = 'Isotope\Model\Document';
$GLOBALS['TL_MODELS'][\Isotope\Model\Download::getTable()]                      = 'Isotope\Model\Download';
$GLOBALS['TL_MODELS'][\Isotope\Model\Group::getTable()]                         = 'Isotope\Model\Group';
$GLOBALS['TL_MODELS'][\Isotope\Model\Label::getTable()]                         = 'Isotope\Model\Label';
$GLOBALS['TL_MODELS'][\Isotope\Model\OrderStatus::getTable()]                   = 'Isotope\Model\OrderStatus';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductPrice::getTable()]                  = 'Isotope\Model\ProductPrice';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCategory::getTable()]               = 'Isotope\Model\ProductCategory';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollection::getTable()]             = 'Isotope\Model\ProductCollection';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollectionItem::getTable()]         = 'Isotope\Model\ProductCollectionItem';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollectionSurcharge::getTable()]    = 'Isotope\Model\ProductCollectionSurcharge';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollectionDownload::getTable()]     = 'Isotope\Model\ProductCollectionDownload';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCache::getTable()]                  = 'Isotope\Model\ProductCache';
$GLOBALS['TL_MODELS'][\Isotope\Model\Product::getTable()]                       = 'Isotope\Model\Product';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductType::getTable()]                   = 'Isotope\Model\ProductType';
$GLOBALS['TL_MODELS'][\Isotope\Model\RelatedCategory::getTable()]               = 'Isotope\Model\RelatedCategory';
$GLOBALS['TL_MODELS'][\Isotope\Model\RelatedProduct::getTable()]                = 'Isotope\Model\RelatedProduct';
$GLOBALS['TL_MODELS'][\Isotope\Model\RequestCache::getTable()]                  = 'Isotope\Model\RequestCache';
$GLOBALS['TL_MODELS'][\Isotope\Model\TaxClass::getTable()]                      = 'Isotope\Model\TaxClass';
$GLOBALS['TL_MODELS'][\Isotope\Model\TaxRate::getTable()]                       = 'Isotope\Model\TaxRate';
$GLOBALS['TL_MODELS'][\Isotope\Model\Payment::getTable()]                       = 'Isotope\Model\Payment';
$GLOBALS['TL_MODELS'][\Isotope\Model\Shipping::getTable()]                      = 'Isotope\Model\Shipping';

/**
 * Checkout steps
 */
$GLOBALS['ISO_CHECKOUTSTEP'] = array
(
    'address'   => array('\Isotope\CheckoutStep\BillingAddress', '\Isotope\CheckoutStep\ShippingAddress'),
    'shipping'  => array('\Isotope\CheckoutStep\ShippingMethod'),
    'payment'   => array('\Isotope\CheckoutStep\PaymentMethod'),
    'review'    => array('\Isotope\CheckoutStep\OrderConditionsOnTop', '\Isotope\CheckoutStep\OrderInfo', '\Isotope\CheckoutStep\OrderConditionsBeforeProducts', '\Isotope\CheckoutStep\OrderProducts', '\Isotope\CheckoutStep\OrderConditionsAfterProducts'),
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
$GLOBALS['TL_PERMISSIONS'][] = 'iso_configs';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_configp';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_groups';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_groupp';


/**
 * Allow to delete the cache in maintenance module
 */
$GLOBALS['TL_PURGE']['tables']['iso_productcache'] = array(
    'callback'  => array('\Isotope\Backend', 'truncateProductCache'),
    'affected'  => array('tl_iso_productcache'),
);

$GLOBALS['TL_PURGE']['tables']['iso_requestcache'] = array(
    'callback'  => array('\Isotope\Backend', 'truncateRequestCache'),
    'affected'  => array('tl_iso_requestcache'),
);


/**
 * Number formatting
 */
$GLOBALS['ISO_NUM']["10000.00"]     = array(2, '.', "");
$GLOBALS['ISO_NUM']["10,000.00"]    = array(2, '.', ",");
$GLOBALS['ISO_NUM']["10.000,00"]    = array(2, ',', ".");
$GLOBALS['ISO_NUM']["10'000.00"]    = array(2, '.', "'");


/**
 * Hooks
 */
if (\Config::getInstance()->isComplete()) {
    include(TL_ROOT . '/system/modules/isotope/config/hooks.php');
}


/**
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = array('Isotope\Automator', 'deleteOldCarts');
$GLOBALS['TL_CRON']['daily'][] = array('Isotope\Automator', 'convertCurrencies');


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
