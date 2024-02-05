<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

use Contao\ArrayUtil;
use Isotope\CompatibilityHelper;

/**
 * Backend modules
 */
if (!\is_array($GLOBALS['BE_MOD']['isotope'] ?? null))
{
    ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 1, array('isotope' => array()));
}

ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['isotope'], 0, array
(
    'iso_products' => array
    (
        'tables'            => array(\Isotope\Model\Product::getTable(), \Isotope\Model\Group::getTable(), \Isotope\Model\ProductCategory::getTable(), \Isotope\Model\Download::getTable(), \Isotope\Model\RelatedProduct::getTable(), \Isotope\Model\ProductPrice::getTable(), 'tl_iso_product_pricetier', \Isotope\Model\AttributeOption::getTable()),
        'javascript'        => 'system/modules/isotope/assets/js/backend.js',
        'generate'          => array('Isotope\Backend\Product\VariantGenerator', 'generate'),
        'import'            => array('Isotope\Backend\Product\AssetImport', 'generate'),
        'fallback'          => array('Isotope\Backend\Product\Fallback', 'setFromUrl'),
    ),
    'iso_orders' => array
    (
        'tables'            => array(\Isotope\Model\ProductCollection::getTable(), \Isotope\Model\ProductCollectionItem::getTable(), \Isotope\Model\ProductCollectionSurcharge::getTable(), \Isotope\Model\ProductCollectionDownload::getTable(), \Isotope\Model\Address::getTable()),
        'javascript'        => 'system/modules/isotope/assets/js/backend.js',
        'print_document'    => array('Isotope\Backend\ProductCollection\Callback', 'printDocument'),
        'payment'           => array('Isotope\Backend\ProductCollection\Callback', 'paymentInterface'),
        'shipping'          => array('Isotope\Backend\ProductCollection\Callback', 'shippingInterface'),
    ),
    'iso_setup' => array
    (
        'callback'          => 'Isotope\BackendModule\Setup',
        'tables'            => array(\Isotope\Model\Payment::getTable(), \Isotope\Model\Shipping::getTable(), \Isotope\Model\OrderStatus::getTable()),
        'javascript'        => 'system/modules/isotope/assets/js/backend.js',
    ),
));

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = \Isotope\Model\Address::getTable();

if (CompatibilityHelper::isBackend()) {
    $GLOBALS['TL_CSS'][] = 'system/modules/isotope/assets/css/backend.css|static';
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
            'tables'            => array(\Isotope\Model\Attribute::getTable(), \Isotope\Model\AttributeOption::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-attributes.png',
        ),
        'related_categories' => array
        (
            'tables'            => array(\Isotope\Model\RelatedCategory::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-related_categories.png',
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
        'gallery' => array
        (
            'tables'            => array(\Isotope\Model\Gallery::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-gallery.png',
        ),
    ),
    'miscellaneous:hide' => array
    (
        'labels' => array
        (
            'tables'            => array(\Isotope\Model\Label::getTable()),
            'icon'              => 'system/modules/isotope/assets/images/setup-labels.png'
        ),
        'integrity' => array
        (
            'callback'          => 'Isotope\BackendModule\Integrity',
            'icon'              => 'system/modules/isotope/assets/images/setup-integrity.png'
        ),
    )
);


/**
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope'] = array
(
    'iso_productlist'               => 'Isotope\Module\ProductList',
    'iso_productvariantlist'        => 'Isotope\Module\ProductVariantList',
    'iso_productreader'             => 'Isotope\Module\ProductReader',
    'iso_favorites'                 => 'Isotope\Module\Favorites',
    'iso_cart'                      => 'Isotope\Module\Cart',
    'iso_checkout'                  => 'Isotope\Module\Checkout',
    'iso_productfilter'             => 'Isotope\Module\ProductFilter',
    'iso_cumulativefilter'          => 'Isotope\Module\CumulativeFilter',
    'iso_categoryfilter'            => 'Isotope\Module\CategoryFilter',
    'iso_rangefilter'               => 'Isotope\Module\RangeFilter',
    'iso_orderhistory'              => 'Isotope\Module\OrderHistory',
    'iso_orderdetails'              => 'Isotope\Module\OrderDetails',
    'iso_wishlistmanager'           => 'Isotope\Module\WishlistManager',
    'iso_wishlistviewer'            => 'Isotope\Module\WishlistViewer',
    'iso_wishlistdetails'           => 'Isotope\Module\WishlistDetails',
    'iso_configswitcher'            => 'Isotope\Module\ConfigSwitcher',
    'iso_addressbook'               => 'Isotope\Module\AddressBook',
    'iso_relatedproducts'           => 'Isotope\Module\RelatedProducts',
    'iso_messages'                  => 'Isotope\Module\Messages',
    'iso_shipping_calculator'       => 'Isotope\Module\ShippingCalculator',
    'iso_cart_address'              => 'Isotope\Module\CartAddress',
    'iso_trustedshops'              => 'Isotope\Module\Trustedshops',
);


/**
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager']           = 'Isotope\Widget\MediaManager';
$GLOBALS['BE_FFL']['inheritCheckbox']        = 'Isotope\Widget\InheritCheckBox';

/**
 * Payment methods
 */
\Isotope\Model\Payment::registerModelType('cash', 'Isotope\Model\Payment\Cash');
\Isotope\Model\Payment::registerModelType('concardis', 'Isotope\Model\Payment\Concardis');
\Isotope\Model\Payment::registerModelType('datatrans', 'Isotope\Model\Payment\Datatrans');
\Isotope\Model\Payment::registerModelType('epay', 'Isotope\Model\Payment\EPay');
\Isotope\Model\Payment::registerModelType('expercash', 'Isotope\Model\Payment\Expercash');
\Isotope\Model\Payment::registerModelType('innopay', 'Isotope\Model\Payment\Innopay');
\Isotope\Model\Payment::registerModelType('paybyway', 'Isotope\Model\Payment\Paybyway');
\Isotope\Model\Payment::registerModelType('payone', 'Isotope\Model\Payment\Payone');
\Isotope\Model\Payment::registerModelType('paypal', 'Isotope\Model\Payment\Paypal');
\Isotope\Model\Payment::registerModelType('paypal_plus', 'Isotope\Model\Payment\PaypalPlus');
\Isotope\Model\Payment::registerModelType('paypal_checkout', 'Isotope\Model\Payment\PaypalCheckout');
\Isotope\Model\Payment::registerModelType('postfinance', 'Isotope\Model\Payment\Postfinance');
\Isotope\Model\Payment::registerModelType('quickpay', 'Isotope\Model\Payment\QuickPay');
\Isotope\Model\Payment::registerModelType('saferpay', 'Isotope\Model\Payment\Saferpay');
\Isotope\Model\Payment::registerModelType('billpay_saferpay', 'Isotope\Model\Payment\BillpayWithSaferpay');
\Isotope\Model\Payment::registerModelType('sparkasse', 'Isotope\Model\Payment\Sparkasse');
\Isotope\Model\Payment::registerModelType('sofortueberweisung', 'Isotope\Model\Payment\Sofortueberweisung');
\Isotope\Model\Payment::registerModelType('viveum', 'Isotope\Model\Payment\Viveum');
\Isotope\Model\Payment::registerModelType('worldpay', 'Isotope\Model\Payment\Worldpay');
\Isotope\Model\Payment::registerModelType('opp', 'Isotope\Model\Payment\OpenPaymentPlatform');

if (class_exists('Mpay24\Mpay24')) {
    \Isotope\Model\Payment::registerModelType('mpay24', 'Isotope\Model\Payment\Mpay24');
}

if (class_exists('Terminal42\SwissbillingApi\Client')) {
    \Isotope\Model\Payment::registerModelType('swissbilling', 'Isotope\Model\Payment\Swissbilling');
}

/**
 * Shipping methods
 */
\Isotope\Model\Shipping::registerModelType('flat', 'Isotope\Model\Shipping\Flat');
\Isotope\Model\Shipping::registerModelType('group', 'Isotope\Model\Shipping\Group');
\Isotope\Model\Shipping::registerModelType('product_price', 'Isotope\Model\Shipping\ProductPrice');

if (class_exists('Petschko\DHL\BusinessShipment')) {
    \Isotope\Model\Shipping::registerModelType('dhl_business', 'Isotope\Model\Shipping\DHLBusiness');
}

/**
 * Documents
 */
\Isotope\Model\Document::registerModelType('standard', 'Isotope\Model\Document\Standard');

/**
 * Galleries
 */
\Isotope\Model\Gallery::registerModelType('standard', 'Isotope\Model\Gallery\Standard');
\Isotope\Model\Gallery::registerModelType('inline', 'Isotope\Model\Gallery\Inline');
\Isotope\Model\Gallery::registerModelType('elevatezoom', 'Isotope\Model\Gallery\ElevateZoom');

/**
 * Products
 */
\Isotope\Model\Product::registerModelType('standard', 'Isotope\Model\Product\Standard');

/**
 * Product collections
 */
\Isotope\Model\ProductCollection::registerModelType('cart', 'Isotope\Model\ProductCollection\Cart');
\Isotope\Model\ProductCollection::registerModelType('order', 'Isotope\Model\ProductCollection\Order');
\Isotope\Model\ProductCollection::registerModelType('favorites', 'Isotope\Model\ProductCollection\Favorites');
\Isotope\Model\ProductCollection::registerModelType('wishlist', 'Isotope\Model\ProductCollection\Wishlist');

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
\Isotope\Model\Attribute::registerModelType('pageTree', 'Isotope\Model\Attribute\PageTree');
\Isotope\Model\Attribute::registerModelType('downloads', 'Isotope\Model\Attribute\Downloads');
\Isotope\Model\Attribute::registerModelType('upload', 'Isotope\Model\Attribute\Upload');
\Isotope\Model\Attribute::registerModelType('media', 'Isotope\Model\Attribute\Media');
\Isotope\Model\Attribute::registerModelType('quantitySurcharge', 'Isotope\Model\Attribute\QuantitySurcharge');

if (\Composer\InstalledVersions::isInstalled('terminal42/contao-fineuploader')) {
    \Isotope\Model\Attribute::registerModelType('fineUploader', 'Isotope\Model\Attribute\FineUploader');
}

/**
 * Product actions
 */
\Isotope\Frontend\ProductAction\Registry::add(new \Isotope\Frontend\ProductAction\UpdateAction());
\Isotope\Frontend\ProductAction\Registry::add(new \Isotope\Frontend\ProductAction\CartAction());
\Isotope\Frontend\ProductAction\Registry::add(new \Isotope\Frontend\ProductAction\FavoriteAction());
\Isotope\Frontend\ProductAction\Registry::add(new \Isotope\Frontend\ProductAction\WishlistAction());

/**
 * Notification Center notification types
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'] = array('recipient_email', 'form_*', 'billing_address_email', 'shipping_address_email');
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['attachment_tokens'] = array('form_*', 'document');
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'] = array(
    'uniqid',
    'order_status',
    'order_status_old',
    'order_status_id',
    'order_status_id_old',
    'order_status_tracking_numbers',
    'order_status_notes',
    'recipient_email',
    'order_id',
    'order_items',
    'order_products',
    'order_subtotal',
    'order_total',
    'document_number',
    'cart_html',
    'cart_text',
    'document',
    'bank_name',
    'bank_account',
    'bank_code',
    'tax_number',
    'collection_*', // All the collection fields
    'billing_address', // Formatted billing address
    'billing_address_*', // All the billing address model fields
    'shipping_address', // Formatted shipping address
    'shipping_address_*', // All the shipping address model fields
    'form_*', // All the order condition form fields
    'payment_id', // Payment method ID
    'payment_label', // Payment method label
    'payment_note', // Payment method note
    'payment_*',
    'shipping_id', // Shipping method ID
    'shipping_label', // Shipping method label
    'shipping_note', // Shipping method note
    'shipping_*',
    'config_*', // Store configuration model fields
    'member_*', // Member model fields
);
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_subject'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_html'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_replyTo'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_recipient_cc'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_recipient_bcc'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['file_name'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['file_content'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['recipients'] = array('admin_email', 'address_email', 'member_email');
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'] = array('admin_email', 'address_*', 'address_old_*', 'member_*', 'config_*');
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_text'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_sender_name'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_sender_address'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_replyTo'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['file_name'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['file_content'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];

/**
 * Models
 */
$GLOBALS['TL_MODELS'][\Isotope\Model\Address::getTable()]                       = 'Isotope\Model\Address';
$GLOBALS['TL_MODELS'][\Isotope\Model\Attribute::getTable()]                     = 'Isotope\Model\Attribute';
$GLOBALS['TL_MODELS'][\Isotope\Model\AttributeOption::getTable()]               = 'Isotope\Model\AttributeOption';
$GLOBALS['TL_MODELS'][\Isotope\Model\BasePrice::getTable()]                     = 'Isotope\Model\BasePrice';
$GLOBALS['TL_MODELS'][\Isotope\Model\Config::getTable()]                        = 'Isotope\Model\Config';
$GLOBALS['TL_MODELS'][\Isotope\Model\Document::getTable()]                      = 'Isotope\Model\Document';
$GLOBALS['TL_MODELS'][\Isotope\Model\Download::getTable()]                      = 'Isotope\Model\Download';
$GLOBALS['TL_MODELS'][\Isotope\Model\Gallery::getTable()]                       = 'Isotope\Model\Gallery';
$GLOBALS['TL_MODELS'][\Isotope\Model\Group::getTable()]                         = 'Isotope\Model\Group';
$GLOBALS['TL_MODELS'][\Isotope\Model\Label::getTable()]                         = 'Isotope\Model\Label';
$GLOBALS['TL_MODELS'][\Isotope\Model\OrderStatus::getTable()]                   = 'Isotope\Model\OrderStatus';
$GLOBALS['TL_MODELS'][\Isotope\Model\Payment::getTable()]                       = 'Isotope\Model\Payment';
$GLOBALS['TL_MODELS'][\Isotope\Model\Product::getTable()]                       = 'Isotope\Model\Product';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCategory::getTable()]               = 'Isotope\Model\ProductCategory';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollection::getTable()]             = 'Isotope\Model\ProductCollection';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollectionDownload::getTable()]     = 'Isotope\Model\ProductCollectionDownload';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollectionItem::getTable()]         = 'Isotope\Model\ProductCollectionItem';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollectionLog::getTable()]          = 'Isotope\Model\ProductCollectionLog';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCollectionSurcharge::getTable()]    = 'Isotope\Model\ProductCollectionSurcharge';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductPrice::getTable()]                  = 'Isotope\Model\ProductPrice';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductCache::getTable()]                  = 'Isotope\Model\ProductCache';
$GLOBALS['TL_MODELS'][\Isotope\Model\ProductType::getTable()]                   = 'Isotope\Model\ProductType';
$GLOBALS['TL_MODELS'][\Isotope\Model\RelatedCategory::getTable()]               = 'Isotope\Model\RelatedCategory';
$GLOBALS['TL_MODELS'][\Isotope\Model\RelatedProduct::getTable()]                = 'Isotope\Model\RelatedProduct';
$GLOBALS['TL_MODELS'][\Isotope\Model\RequestCache::getTable()]                  = 'Isotope\Model\RequestCache';
$GLOBALS['TL_MODELS'][\Isotope\Model\Shipping::getTable()]                      = 'Isotope\Model\Shipping';
$GLOBALS['TL_MODELS'][\Isotope\Model\TaxClass::getTable()]                      = 'Isotope\Model\TaxClass';
$GLOBALS['TL_MODELS'][\Isotope\Model\TaxRate::getTable()]                       = 'Isotope\Model\TaxRate';

/**
 * Checkout steps
 */
$GLOBALS['ISO_CHECKOUTSTEP'] = array
(
    'address'   => array('Isotope\CheckoutStep\BillingAddress', 'Isotope\CheckoutStep\ShippingAddress'),
    'shipping'  => array('Isotope\CheckoutStep\ShippingMethod'),
    'payment'   => array('Isotope\CheckoutStep\PaymentMethod'),
    'review'    => array('Isotope\CheckoutStep\OrderInfo', 'Isotope\CheckoutStep\OrderProducts'),
);

/**
 * Integrity checks
 */
$GLOBALS['ISO_INTEGRITY'] = array
(
    '\Isotope\IntegrityCheck\PriceTable',
    '\Isotope\IntegrityCheck\VariantPrices',
    '\Isotope\IntegrityCheck\VariantOrphans',
    '\Isotope\IntegrityCheck\AttributeOptionOrphans',
    '\Isotope\IntegrityCheck\MultilingualAttributes',
    '\Isotope\IntegrityCheck\UnusedRules',
);

/**
 * Vat No Validation
 */
$GLOBALS['ISO_VAT']['eu_vies'] = '\Isotope\VatNoValidator\EuViesValidator';

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
$GLOBALS['TL_PERMISSIONS'][] = 'iso_member_groups';


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
if (\Contao\Config::getInstance()->isComplete()) {
    $GLOBALS['TL_HOOKS']['loadDataContainer'][]             = array('Isotope\Backend\Product\DcaManager', 'initialize');
    $GLOBALS['TL_HOOKS']['getAttributesFromDca'][]          = array('Isotope\Backend\Product\DcaManager', 'addOptionsFromAttribute');
    $GLOBALS['TL_HOOKS']['addCustomRegexp'][]               = array('Isotope\Isotope', 'validateRegexp');
    $GLOBALS['TL_HOOKS']['getPageIdFromUrl'][]              = array('Isotope\Frontend', 'loadReaderPageFromUrl');
    $GLOBALS['TL_HOOKS']['getPageLayout'][]                 = array('Isotope\Frontend', 'overrideReaderPage');
    $GLOBALS['TL_HOOKS']['getArticles'][]                   = array('Isotope\Frontend', 'overrideArticles');
    $GLOBALS['TL_HOOKS']['getSearchablePages'][]            = array('Isotope\Frontend', 'addProductsToSearchIndex');
    $GLOBALS['TL_HOOKS']['replaceInsertTags'][]             = array('Isotope\Frontend', 'replaceIsotopeTags');
    $GLOBALS['TL_HOOKS']['modifyFrontendPage'][]            = array('Isotope\Frontend', 'injectScripts');
    $GLOBALS['TL_HOOKS']['executePreActions'][]             = array('Isotope\Backend', 'executePreActions');
    $GLOBALS['TL_HOOKS']['executePostActions'][]            = array('Isotope\Backend', 'executePostActions');
    $GLOBALS['TL_HOOKS']['getSystemMessages'][]             = array('Isotope\Backend', 'getOrderMessages');
    $GLOBALS['TL_HOOKS']['getArticle'][]                    = array('Isotope\Frontend', 'storeCurrentArticle');
    $GLOBALS['TL_HOOKS']['generateBreadcrumb'][]            = array('Isotope\Frontend', 'addProductToBreadcrumb');

    $GLOBALS['ISO_HOOKS']['buttons'][]                      = array('Isotope\Isotope', 'defaultButtons');
    $GLOBALS['ISO_HOOKS']['findSurchargesForCollection'][]  = array('Isotope\Frontend', 'findShippingAndPaymentSurcharges');
    $GLOBALS['ISO_HOOKS']['postCheckout'][]                 = array('Isotope\Analytics', 'trackOrder');
    $GLOBALS['ISO_HOOKS']['postCheckout'][]                 = array('Isotope\EventListener\PostCheckoutUploads', 'onPostCheckout');
    $GLOBALS['ISO_HOOKS']['calculatePrice'][]               = array('Isotope\Frontend', 'addOptionsPrice');
    $GLOBALS['ISO_HOOKS']['calculatePrice'][]               = array('Isotope\EventListener\CalculatePrice\QuantitySurchagePriceListener', '__invoke');
    $GLOBALS['ISO_HOOKS']['orderConditions'][]              = array('Isotope\Model\Payment\BillpayWithSaferpay', 'addOrderCondition');
    $GLOBALS['ISO_HOOKS']['generateDocumentTemplate'][]     = array('Isotope\Model\Payment\BillpayWithSaferpay', 'addToDocumentTemplate');
    $GLOBALS['ISO_HOOKS']['initializePostsale'][]           = array('Isotope\Frontend', 'setPostsaleModuleSettings');

    // changelanguage
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][]      = array('Isotope\EventListener\ChangeLanguageListener', '__invoke');

    // Set module and module id for payment and/or shipping modules
    if (CompatibilityHelper::isFrontend()) {
        // Only limit countries in FE
        $GLOBALS['TL_HOOKS']['loadDataContainer'][]         = array('Isotope\Backend\Member\Callback', 'limitCountries');
    }

    if (CompatibilityHelper::isBackend()) {
        // Type agent help is only needed in back end
        $GLOBALS['TL_HOOKS']['loadDataContainer'][]         = array('Isotope\Backend', 'loadTypeAgentHelp');
        $GLOBALS['TL_HOOKS']['loadLanguageFile'][]          = array('Isotope\Backend\ProductType\Help', 'initializeWizard');

        // Adjust the product groups manager
        $GLOBALS['TL_HOOKS']['parseTemplate'][]             = array('Isotope\Backend', 'adjustGroupsManager');
        $GLOBALS['TL_HOOKS']['parseTemplate'][]             = array('Isotope\Backend\SubtableVersion', 'removeFromWelcomeScreen');

        // Enable the module tables in setup
        $GLOBALS['TL_HOOKS']['initializeSystem'][]          = array('Isotope\BackendModule\InitializeListener', 'enableModuleTablesInSetup');
    }

    if (class_exists('Petschko\DHL\BusinessShipment')) {
        $GLOBALS['ISO_HOOKS']['postCheckout'][] = array('Isotope\EventListener\DHLBusinessCheckoutListener', 'onPostCheckout');
    }
}


/**
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = array('Isotope\Automator', 'deleteOldCarts');
$GLOBALS['TL_CRON']['daily'][] = array('Isotope\Automator', 'deleteOldOrders');
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
$GLOBALS['TL_CONFIG']['iso_orderTimeout'] = 604800;
