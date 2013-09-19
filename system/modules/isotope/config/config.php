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
@define('ISO_BUILD', 'dev');
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
        'tables'                    => array('tl_iso_products', 'tl_iso_groups', 'tl_iso_product_categories', 'tl_iso_downloads', 'tl_iso_related_products', 'tl_iso_prices', 'tl_iso_price_tiers'),
        'icon'                        => 'system/modules/isotope/assets/store-open.png',
        'javascript'                => 'system/modules/isotope/assets/backend.min.js',
        'generate'                    => array('Isotope\tl_iso_products', 'generateVariants'),
        'import'                    => array('Isotope\tl_iso_products', 'importAssets'),
    ),
    'iso_orders' => array
    (
        'tables'                    => array('tl_iso_product_collection', 'tl_iso_product_collection_item', 'tl_iso_product_collection_surcharge', 'tl_iso_product_collection_download', 'tl_iso_addresses'),
        'icon'                        => 'system/modules/isotope/assets/shopping-basket.png',
        'javascript'                => 'system/modules/isotope/assets/backend.min.js',
        'print_document'                => array('Isotope\tl_iso_product_collection', 'printDocument'),
        'payment'                    => array('Isotope\tl_iso_product_collection', 'paymentInterface'),
        'shipping'                    => array('Isotope\tl_iso_product_collection', 'shippingInterface'),
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
            'tables'            => array('tl_iso_producttypes'),
            'icon'              => 'system/modules/isotope/assets/setup-producttypes.png'
        ),
        'attributes' => array
        (
            'tables'            => array('tl_iso_attributes'),
            'icon'              => 'system/modules/isotope/assets/setup-attributes.png',
        ),
        'related_categories' => array
        (
            'tables'            => array('tl_iso_related_categories'),
            'icon'              => 'system/modules/isotope/assets/setup-related_categories.png',
        ),
        'gallery' => array
        (
            'tables'            => array('tl_iso_gallery'),
            'icon'              => 'system/modules/isotope/assets/setup-gallery.png',
        ),
        'baseprice' => array
        (
            'tables'            => array('tl_iso_baseprice'),
            'icon'              => 'system/modules/isotope/assets/setup-baseprice.png',
        ),
    ),
    'checkout' => array
    (
        'payment' => array
        (
            'tables'            => array('tl_iso_payment_modules'),
            'icon'              => 'system/modules/isotope/assets/setup-payment.png',
        ),
        'shipping' => array
        (
                'tables'        => array('tl_iso_shipping_modules','tl_iso_shipping_options'),
                'icon'          => 'system/modules/isotope/assets/setup-shipping.png',
        ),
        'tax_class' => array
        (
            'tables'            => array('tl_iso_tax_class'),
            'icon'              => 'system/modules/isotope/assets/setup-tax_class.png',
        ),
        'tax_rate' => array
        (
            'tables'            => array('tl_iso_tax_rate'),
            'icon'              => 'system/modules/isotope/assets/setup-tax_rate.png',
        ),
    ),
    'config' => array
    (
        'documents' => array
        (
            'tables'            => array('tl_iso_document'),
            'icon'              => 'system/modules/isotope/assets/setup-documents.png'
        ),
        'labels' => array
        (
            'tables'            => array('tl_iso_labels'),
            'icon'              => 'system/modules/isotope/assets/setup-labels.png'
        ),
        'iso_mail' => array
        (
            'tables'            => array('tl_iso_mail', 'tl_iso_mail_content'),
            'icon'              => 'system/modules/isotope/assets/setup-mail.png',
            'importMail'        => array('Isotope\Backend', 'importMail'),
            'exportMail'        => array('Isotope\Backend', 'exportMail'),
        ),
        'configs' => array
        (
            'tables'            => array('tl_iso_config'),
            'icon'              => 'system/modules/isotope/assets/setup-config.png',
        ),
        'orderstatus' => array
        (
            'tables'            => array('tl_iso_orderstatus'),
            'icon'              => 'system/modules/isotope/assets/setup-orderstatus.png',
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
$GLOBALS['BE_FFL']['fieldWizard']            = 'Isotope\Widget\FieldWizard';
$GLOBALS['BE_FFL']['productTree']            = 'Isotope\Widget\ProductTree';
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
\Isotope\Model\Payment::registerModelType('saferpay', 'Isotope\Model\Payment\Saferpay');
\Isotope\Model\Payment::registerModelType('sparkasse', 'Isotope\Model\Payment\Sparkasse');
\Isotope\Model\Payment::registerModelType('sofortueberweisung', 'Isotope\Model\Payment\Sofortueberweisung');

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
\Isotope\Model\Gallery::registerModelType('zoom', 'Isotope\Model\Gallery\Zoom');

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
\Isotope\Model\Attribute::registerModelType('conditionalselect', 'Isotope\Model\Attribute\ConditionalSelectMenu');
\Isotope\Model\Attribute::registerModelType('mediaManager', 'Isotope\Model\Attribute\MediaManager');
\Isotope\Model\Attribute::registerModelType('fileTree', 'Isotope\Model\Attribute\FileTree');
\Isotope\Model\Attribute::registerModelType('downloads', 'Isotope\Model\Attribute\Downloads');
\Isotope\Model\Attribute::registerModelType('upload', 'Isotope\Model\Attribute\Upload');


/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_iso_config']                          = 'Isotope\Model\Config';
$GLOBALS['TL_MODELS']['tl_iso_addresses']                       = 'Isotope\Model\Address';
$GLOBALS['TL_MODELS']['tl_iso_baseprice']                       = 'Isotope\Model\BasePrice';
$GLOBALS['TL_MODELS']['tl_iso_document']                        = 'Isotope\Model\Document';
$GLOBALS['TL_MODELS']['tl_iso_downloads']                       = 'Isotope\Model\Download';
$GLOBALS['TL_MODELS']['tl_iso_groups']                          = 'Isotope\Model\Group';
$GLOBALS['TL_MODELS']['tl_iso_labels']                          = 'Isotope\Model\Label';
$GLOBALS['TL_MODELS']['tl_iso_orderstatus']                     = 'Isotope\Model\OrderStatus';
$GLOBALS['TL_MODELS']['tl_iso_prices']                          = 'Isotope\Model\ProductPrice';
$GLOBALS['TL_MODELS']['tl_iso_product_collection']              = 'Isotope\Model\ProductCollection';
$GLOBALS['TL_MODELS']['tl_iso_product_collection_item']         = 'Isotope\Model\ProductCollectionItem';
$GLOBALS['TL_MODELS']['tl_iso_product_collection_surcharge']    = 'Isotope\Model\ProductCollectionSurcharge';
$GLOBALS['TL_MODELS']['tl_iso_product_collection_download']     = 'Isotope\Model\ProductCollectionDownload';
$GLOBALS['TL_MODELS']['tl_iso_productcache']                    = 'Isotope\Model\ProductCache';
$GLOBALS['TL_MODELS']['tl_iso_products']                        = 'Isotope\Model\Product';
$GLOBALS['TL_MODELS']['tl_iso_producttypes']                    = 'Isotope\Model\ProductType';
$GLOBALS['TL_MODELS']['tl_iso_requestcache']                    = 'Isotope\Model\RequestCache';
$GLOBALS['TL_MODELS']['tl_iso_tax_class']                       = 'Isotope\Model\TaxClass';
$GLOBALS['TL_MODELS']['tl_iso_tax_rate']                        = 'Isotope\Model\TaxRate';
$GLOBALS['TL_MODELS']['tl_iso_payment_modules']                 = 'Isotope\Model\Payment';
$GLOBALS['TL_MODELS']['tl_iso_shipping_modules']                = 'Isotope\Model\Shipping';

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
$GLOBALS['TL_PERMISSIONS'][] = 'iso_mails';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_mailp';
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
include(TL_ROOT . '/system/modules/isotope/config/hooks.php');


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
