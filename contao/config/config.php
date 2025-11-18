<?php

use Contao\ArrayUtil;
use Contao\System;
use Isotope\Automator;
use Isotope\Backend;
use Isotope\Backend\Member\Callback as MemberCallback;
use Isotope\Backend\ProductCollection\Callback;
use Isotope\Backend\ProductType\Help;
use Isotope\Backend\SubtableVersion;
use Isotope\CheckoutStep\BillingAddress;
use Isotope\CheckoutStep\OrderInfo;
use Isotope\CheckoutStep\OrderProducts;
use Isotope\CheckoutStep\PaymentMethod;
use Isotope\CheckoutStep\ShippingAddress;
use Isotope\CheckoutStep\ShippingMethod;
use Isotope\CompatibilityHelper;
use Isotope\CoreBundle\Model\Attribute;
use Isotope\CoreBundle\Model\BasePrice;
use Isotope\CoreBundle\Model\Document;
use Isotope\CoreBundle\Model\Gallery;
use Isotope\CoreBundle\Model\OrderStatus;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\Model\ProductCategory;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Model\RelatedCategory;
use Isotope\CoreBundle\Model\RelatedProduct;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\Widget\InheritCheckBox;
use Isotope\CoreBundle\Widget\MediaManager;
use Isotope\EventListener\ChangeLanguageListener;
use Isotope\EventListener\PostCheckoutUploads;
use Isotope\Frontend;
use Isotope\Model\Address;
use Isotope\Model\AttributeOption;
use Isotope\Model\Config;
use Isotope\Model\Download;
use Isotope\Model\Product;
use Isotope\Model\Product\Standard as StandardProduct;
use Isotope\Model\ProductCollection;
use Isotope\Model\ProductCollection\Cart as CartCollection;
use Isotope\Model\ProductCollection\Order;
use Isotope\Model\ProductCollectionDownload;
use Isotope\Model\ProductCollectionItem;
use Isotope\Model\ProductCollectionLog;
use Isotope\Model\ProductCollectionSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Payment as PaymentSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Shipping as ShippingSurcharge;
use Isotope\Model\ProductCollectionSurcharge\Tax;
use Isotope\Model\TaxClass;
use Isotope\Model\TaxRate;
use Isotope\Module\AddressBook;
use Isotope\Module\Cart;
use Isotope\Module\CartAddress;
use Isotope\Module\Checkout;
use Isotope\Module\ConfigSwitcher;
use Isotope\Module\Messages;
use Isotope\Module\OrderHistory;
use Isotope\Module\ShippingCalculator;
use Isotope\VatNoValidator\EuViesValidator;
use Symfony\Component\HttpFoundation\Request;

/*
 * Backend modules
 */
if (!is_array($GLOBALS['BE_MOD']['isotope'] ?? null)) {
    ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 1, ['isotope' => []]);
}

ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['isotope'], 0, [
    'iso_products' => [
        'tables' => ['tl_iso_product', 'tl_iso_product_category', 'tl_iso_download', 'tl_iso_related_product', 'tl_iso_attribute_option'],
    ],
    'iso_orders' => [
        'tables' => ['tl_iso_product_collection', 'tl_iso_product_collection_item', 'tl_iso_product_collection_surcharge', 'tl_iso_product_collection_download', 'tl_iso_address'],
        'print_document' => [Callback::class, 'printDocument'],
    ],
    'iso_setup' => [],
]);

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_iso_address';

/*
 * Frontend modules
 */
$GLOBALS['FE_MOD']['isotope'] = [
    'iso_cart' => Cart::class,
    'iso_checkout' => Checkout::class,
    'iso_orderhistory' => OrderHistory::class,
    'iso_configswitcher' => ConfigSwitcher::class,
    'iso_addressbook' => AddressBook::class,
    'iso_messages' => Messages::class,
    'iso_shipping_calculator' => ShippingCalculator::class,
    'iso_cart_address' => CartAddress::class,
];

/*
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager'] = MediaManager::class;
$GLOBALS['BE_FFL']['inheritCheckbox'] = InheritCheckBox::class;

/*
 * Products
 */
Product::registerModelType('standard', StandardProduct::class);

/*
 * Product collections
 */
ProductCollection::registerModelType('cart', CartCollection::class);
ProductCollection::registerModelType('order', Order::class);

/*
 * Product collection surcharge
 */
ProductCollectionSurcharge::registerModelType('payment', PaymentSurcharge::class);
ProductCollectionSurcharge::registerModelType('shipping', ShippingSurcharge::class);
ProductCollectionSurcharge::registerModelType('tax', Tax::class);

/*
 * Notification Center notification types
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'] = ['recipient_email', 'form_*', 'billing_address_email', 'shipping_address_email'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['attachment_tokens'] = ['form_*', 'document'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'] = [
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
];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_subject'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_html'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_replyTo'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_sender_name'] = ['recipient_email', 'admin_email', 'admin_name', 'config_*'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_sender_address'] = ['recipient_email', 'admin_email', 'config_*'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_recipient_cc'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_recipient_bcc'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['recipients'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['file_name'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['file_content'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_order_status_change']['email_text'];

$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['recipients'] = ['admin_email', 'address_email', 'member_email'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'] = ['admin_email', 'address_*', 'address_old_*', 'member_*', 'config_*'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_text'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_sender_name'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_sender_address'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_replyTo'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['file_name'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['file_content'] = &$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['isotope']['iso_memberaddress_change']['email_subject'];

/*
 * Models
 */
$GLOBALS['TL_MODELS'][Address::getTable()] = Address::class;
$GLOBALS['TL_MODELS'][Attribute::getTable()] = Attribute::class;
$GLOBALS['TL_MODELS'][AttributeOption::getTable()] = AttributeOption::class;
$GLOBALS['TL_MODELS'][BasePrice::getTable()] = BasePrice::class;
$GLOBALS['TL_MODELS'][Config::getTable()] = Config::class;
$GLOBALS['TL_MODELS'][Document::getTable()] = Document::class;
$GLOBALS['TL_MODELS'][Download::getTable()] = Download::class;
$GLOBALS['TL_MODELS'][Gallery::getTable()] = Gallery::class;
$GLOBALS['TL_MODELS'][OrderStatus::getTable()] = OrderStatus::class;
$GLOBALS['TL_MODELS'][Payment::getTable()] = Payment::class;
$GLOBALS['TL_MODELS'][Product::getTable()] = Product::class;
$GLOBALS['TL_MODELS'][ProductCategory::getTable()] = ProductCategory::class;
$GLOBALS['TL_MODELS'][ProductCollection::getTable()] = ProductCollection::class;
$GLOBALS['TL_MODELS'][ProductCollectionDownload::getTable()] = ProductCollectionDownload::class;
$GLOBALS['TL_MODELS'][ProductCollectionItem::getTable()] = ProductCollectionItem::class;
$GLOBALS['TL_MODELS'][ProductCollectionLog::getTable()] = ProductCollectionLog::class;
$GLOBALS['TL_MODELS'][ProductCollectionSurcharge::getTable()] = ProductCollectionSurcharge::class;
$GLOBALS['TL_MODELS'][ProductType::getTable()] = ProductType::class;
$GLOBALS['TL_MODELS'][RelatedCategory::getTable()] = RelatedCategory::class;
$GLOBALS['TL_MODELS'][RelatedProduct::getTable()] = RelatedProduct::class;
$GLOBALS['TL_MODELS'][Shipping::getTable()] = Shipping::class;
$GLOBALS['TL_MODELS'][TaxClass::getTable()] = TaxClass::class;
$GLOBALS['TL_MODELS'][TaxRate::getTable()] = TaxRate::class;

/*
 * Checkout steps
 */
$GLOBALS['ISO_CHECKOUTSTEP'] = [
    'address' => [BillingAddress::class, ShippingAddress::class],
    'shipping' => [ShippingMethod::class],
    'payment' => [PaymentMethod::class],
    'review' => [OrderInfo::class, OrderProducts::class],
];

/*
 * Vat No Validation
 */
$GLOBALS['ISO_VAT']['eu_vies'] = EuViesValidator::class;

/*
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
$GLOBALS['TL_PERMISSIONS'][] = 'iso_member_groups';

/*
 * Number formatting
 */
$GLOBALS['ISO_NUM']['10000.00'] = [2, '.', ''];
$GLOBALS['ISO_NUM']['10,000.00'] = [2, '.', ','];
$GLOBALS['ISO_NUM']['10.000,00'] = [2, ',', '.'];
$GLOBALS['ISO_NUM']["10'000.00"] = [2, '.', "'"];

/*
 * Hooks
 */
if (Contao\Config::getInstance()->isComplete()) {
    $GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [Frontend::class, 'replaceIsotopeTags'];
    $GLOBALS['TL_HOOKS']['modifyFrontendPage'][] = [Frontend::class, 'injectScripts'];

    $GLOBALS['ISO_HOOKS']['findSurchargesForCollection'][] = [Frontend::class, 'findShippingAndPaymentSurcharges'];
    $GLOBALS['ISO_HOOKS']['postCheckout'][] = ['Isotope\Analytics', 'trackOrder'];
    $GLOBALS['ISO_HOOKS']['postCheckout'][] = [PostCheckoutUploads::class, 'onPostCheckout'];
    $GLOBALS['ISO_HOOKS']['calculatePrice'][] = [Frontend::class, 'addOptionsPrice'];

    // changelanguage
    $GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = [ChangeLanguageListener::class, '__invoke'];

    // Set module and module id for payment and/or shipping modules
    if (System::getContainer()->get('contao.routing.scope_matcher')->isFrontendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
        // Only limit countries in FE
        $GLOBALS['TL_HOOKS']['loadDataContainer'][] = [MemberCallback::class, 'limitCountries'];
    }

    if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''))) {
        // Type agent help is only needed in back end
        $GLOBALS['TL_HOOKS']['loadDataContainer'][] = [Backend::class, 'loadTypeAgentHelp'];
        $GLOBALS['TL_HOOKS']['loadLanguageFile'][] = [Help::class, 'initializeWizard'];

        $GLOBALS['TL_HOOKS']['parseTemplate'][] = [SubtableVersion::class, 'removeFromWelcomeScreen'];
    }
}

/*
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = [Automator::class, 'deleteOldCarts'];
$GLOBALS['TL_CRON']['daily'][] = [Automator::class, 'deleteOldOrders'];
