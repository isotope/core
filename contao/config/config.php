<?php

use Contao\ArrayUtil;
use Isotope\Automator;
use Isotope\Backend\ProductCollection\Callback;
use Isotope\CheckoutStep\BillingAddress;
use Isotope\CheckoutStep\OrderInfo;
use Isotope\CheckoutStep\OrderProducts;
use Isotope\CheckoutStep\PaymentMethod;
use Isotope\CheckoutStep\ShippingAddress;
use Isotope\CheckoutStep\ShippingMethod;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\AttributeModel;
use Isotope\CoreBundle\Model\AttributeOptionModel;
use Isotope\CoreBundle\Model\BasePriceModel;
use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Model\DocumentModel;
use Isotope\CoreBundle\Model\GalleryModel;
use Isotope\CoreBundle\Model\OrderStatusModel;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\Model\ProductCategoryModel;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\Model\ProductCollectionLogModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\Model\ProductModel;
use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Model\RelatedCategoryModel;
use Isotope\CoreBundle\Model\RelatedProductModel;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\Model\RuleModel;
use Isotope\CoreBundle\Model\ShippingModel;
use Isotope\CoreBundle\Model\TaxClassModel;
use Isotope\CoreBundle\Model\TaxRateModel;
use Isotope\CoreBundle\Widget\InheritCheckBox;
use Isotope\CoreBundle\Widget\MediaManager;

/*
 * Backend modules
 */
if (!is_array($GLOBALS['BE_MOD']['isotope'] ?? null)) {
    $GLOBALS['BE_MOD']['isotope'] = [];
}

ArrayUtil::arrayInsert($GLOBALS['BE_MOD']['isotope'], 0, [
    'iso_products' => [
        'tables' => ['tl_iso_product', 'tl_iso_product_category', 'tl_iso_related_product', 'tl_iso_attribute_option'],
    ],
    'iso_orders' => [
        'tables' => ['tl_iso_product_collection', 'tl_iso_product_collection_item', 'tl_iso_address'],
        'class' => 'iso_orders',
        'print_document' => [Callback::class, 'printDocument'],
    ],
    'iso_rules' => [
        'tables' => ['tl_iso_rule', 'tl_iso_rule_condition'],
    ],
    'iso_setup' => [],
]);

$GLOBALS['BE_MOD']['accounts']['member']['tables'][] = 'tl_iso_address';

/*
 * Backend form fields
 */
$GLOBALS['BE_FFL']['mediaManager'] = MediaManager::class;
$GLOBALS['BE_FFL']['inheritCheckbox'] = InheritCheckBox::class;

/*
 * Models
 */
$GLOBALS['TL_MODELS'][AddressModel::getTable()] = AddressModel::class;
$GLOBALS['TL_MODELS'][AttributeModel::getTable()] = AttributeModel::class;
$GLOBALS['TL_MODELS'][AttributeOptionModel::getTable()] = AttributeOptionModel::class;
$GLOBALS['TL_MODELS'][BasePriceModel::getTable()] = BasePriceModel::class;
$GLOBALS['TL_MODELS'][ConfigModel::getTable()] = ConfigModel::class;
$GLOBALS['TL_MODELS'][DocumentModel::getTable()] = DocumentModel::class;
$GLOBALS['TL_MODELS'][GalleryModel::getTable()] = GalleryModel::class;
$GLOBALS['TL_MODELS'][OrderStatusModel::getTable()] = OrderStatusModel::class;
$GLOBALS['TL_MODELS'][PaymentModel::getTable()] = PaymentModel::class;
$GLOBALS['TL_MODELS'][ProductModel::getTable()] = ProductModel::class;
$GLOBALS['TL_MODELS'][ProductCategoryModel::getTable()] = ProductCategoryModel::class;
$GLOBALS['TL_MODELS'][ProductCollectionModel::getTable()] = ProductCollectionModel::class;
$GLOBALS['TL_MODELS'][ProductCollectionItemModel::getTable()] = ProductCollectionItemModel::class;
$GLOBALS['TL_MODELS'][ProductCollectionLogModel::getTable()] = ProductCollectionLogModel::class;
$GLOBALS['TL_MODELS'][ProductTypeModel::getTable()] = ProductTypeModel::class;
$GLOBALS['TL_MODELS'][RelatedCategoryModel::getTable()] = RelatedCategoryModel::class;
$GLOBALS['TL_MODELS'][RelatedProductModel::getTable()] = RelatedProductModel::class;
$GLOBALS['TL_MODELS'][ShippingModel::getTable()] = ShippingModel::class;
$GLOBALS['TL_MODELS'][TaxClassModel::getTable()] = TaxClassModel::class;
$GLOBALS['TL_MODELS'][TaxRateModel::getTable()] = TaxRateModel::class;
$GLOBALS['TL_MODELS'][RuleModel::getTable()] = RuleModel::class;
$GLOBALS['TL_MODELS'][RuleConditionModel::getTable()] = RuleConditionModel::class;

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
 * Permissions are access settings for user and groups (fields in tl_user and tl_user_group)
 */
$GLOBALS['TL_PERMISSIONS'][] = 'iso_modules';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_producttypes';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_payments';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_shippings';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_tax_rates';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_tax_classes';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_configs';
$GLOBALS['TL_PERMISSIONS'][] = 'iso_member_groups';

/*
 * Number formatting
 */
$GLOBALS['ISO_NUM']['10000.00'] = [2, '.', ''];
$GLOBALS['ISO_NUM']['10,000.00'] = [2, '.', ','];
$GLOBALS['ISO_NUM']['10.000,00'] = [2, ',', '.'];
$GLOBALS['ISO_NUM']["10'000.00"] = [2, '.', "'"];

/*
 * Cron Jobs
 */
$GLOBALS['TL_CRON']['daily'][] = [Automator::class, 'deleteOldCarts'];
$GLOBALS['TL_CRON']['daily'][] = [Automator::class, 'deleteOldOrders'];
