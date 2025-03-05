<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Security;

final class IsotopeCorePermissions
{
    public const USER_CAN_ACCESS_PRODUCTS = 'contao_user.modules.iso_products';

    public const USER_CAN_ACCESS_ORDERS = 'contao_user.modules.iso_orders';

    public const USER_CAN_ACCESS_SETUP = 'contao_user.modules.iso_setup';

    public const USER_CAN_ACCESS_MODULE = 'contao_user.iso_modules';

    public const USER_CAN_ACCESS_PRODUCT_TYPES = 'contao_user.iso_modules.producttypes';

    public const USER_CAN_ACCESS_ATTRIBUTES = 'contao_user.iso_modules.attributes';

    public const USER_CAN_ACCESS_RELATED_CATEGORIES = 'contao_user.iso_modules.related_categories';

    public const USER_CAN_ACCESS_BASEPRICES = 'contao_user.iso_modules.baseprice';

    public const USER_CAN_ACCESS_PAYMENT_METHODS = 'contao_user.iso_modules.payment';

    public const USER_CAN_ACCESS_SHIPPING_METHODS = 'contao_user.iso_modules.shipping';

    public const USER_CAN_ACCESS_TAX_CLASSES = 'contao_user.iso_modules.tax_class';

    public const USER_CAN_ACCESS_TAX_RATES = 'contao_user.iso_modules.tax_rate';

    public const USER_CAN_ACCESS_CONFIGS = 'contao_user.iso_modules.configs';

    public const USER_CAN_ACCESS_ORDERSTATUS = 'contao_user.iso_modules.orderstatus';

    public const USER_CAN_ACCESS_NOTIFICATIONS = 'contao_user.iso_modules.notifications';

    public const USER_CAN_ACCESS_DOCUMENTS = 'contao_user.iso_modules.documents';

    public const USER_CAN_ACCESS_GALLERIES = 'contao_user.iso_modules.gallery';

    public const USER_CAN_ACCESS_LABELS = 'contao_user.iso_modules.labels';

    public const USER_CAN_ACCESS_INTEGRITY = 'contao_user.iso_modules.integrity';

    public const USER_CAN_EDIT_PRODUCT_TYPE = 'contao_user.iso_product_types';

    public const USER_CAN_CREATE_PRODUCT_TYPES = 'contao_user.iso_product_typep.create';

    public const USER_CAN_DELETE_PRODUCT_TYPES = 'contao_user.iso_product_typep.delete';

    public const USER_CAN_EDIT_PAYMENT_METHOD = 'contao_user.iso_payment_modules';

    public const USER_CAN_CREATE_PAYMENT_METHODS = 'contao_user.iso_payment_modulep.create';

    public const USER_CAN_DELETE_PAYMENT_METHODS = 'contao_user.iso_payment_modulep.delete';

    public const USER_CAN_EDIT_SHIPPING_METHOD = 'contao_user.iso_shipping_modules';

    public const USER_CAN_CREATE_SHIPPING_METHODS = 'contao_user.iso_shipping_modulep.create';

    public const USER_CAN_DELETE_SHIPPING_METHODS = 'contao_user.iso_shipping_modulep.delete';

    public const USER_CAN_EDIT_TAX_CLASS = 'contao_user.iso_tax_classes';

    public const USER_CAN_CREATE_TAX_CLASSES = 'contao_user.iso_tax_classp.create';

    public const USER_CAN_DELETE_TAX_CLASSES = 'contao_user.iso_tax_classp.delete';

    public const USER_CAN_EDIT_TAX_RATE = 'contao_user.iso_tax_rates';

    public const USER_CAN_CREATE_TAX_RATES = 'contao_user.iso_tax_ratep.create';

    public const USER_CAN_DELETE_TAX_RATES = 'contao_user.iso_tax_ratep.delete';

    public const USER_CAN_EDIT_CONFIG = 'contao_user.iso_configs';

    public const USER_CAN_CREATE_CONFIGS = 'contao_user.iso_configp.create';

    public const USER_CAN_DELETE_CONFIGS = 'contao_user.iso_configp.delete';

    public const USER_CAN_EDIT_GROUP = 'contao_user.iso_groups';

    public const USER_CAN_CREATE_GROUPS = 'contao_user.iso_groupp.create';

    public const USER_CAN_DELETE_GROUPS = 'contao_user.iso_groupp.delete';

    public const USER_CAN_ACCESS_ORDERS_OF_MEMBER_GROUP = 'contao_user.iso_member_groups';
}
