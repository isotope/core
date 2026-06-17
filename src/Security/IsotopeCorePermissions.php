<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Security;

final class IsotopeCorePermissions
{
    public const string USER_CAN_ACCESS_PRODUCTS = 'contao_user.modules.iso_products';

    public const string USER_CAN_ACCESS_ORDERS = 'contao_user.modules.iso_orders';

    public const string USER_CAN_ACCESS_SETUP = 'contao_user.modules.iso_setup';

    public const string USER_CAN_ACCESS_MODULE = 'contao_user.iso_modules';

    public const string USER_CAN_ACCESS_PRODUCTTYPES = 'contao_user.iso_modules.producttypes';

    public const string USER_CAN_ACCESS_ATTRIBUTES = 'contao_user.iso_modules.attributes';

    public const string USER_CAN_ACCESS_RELATED_CATEGORIES = 'contao_user.iso_modules.related_categories';

    public const string USER_CAN_ACCESS_BASEPRICES = 'contao_user.iso_modules.baseprice';

    public const string USER_CAN_ACCESS_PAYMENTS = 'contao_user.iso_modules.payment';

    public const string USER_CAN_ACCESS_SHIPPINGS = 'contao_user.iso_modules.shipping';

    public const string USER_CAN_ACCESS_TAX_CLASSES = 'contao_user.iso_modules.tax_class';

    public const string USER_CAN_ACCESS_TAX_RATES = 'contao_user.iso_modules.tax_rate';

    public const string USER_CAN_ACCESS_CONFIGS = 'contao_user.iso_modules.configs';

    public const string USER_CAN_ACCESS_ORDERSTATUS = 'contao_user.iso_modules.orderstatus';

    public const string USER_CAN_ACCESS_GALLERIES = 'contao_user.iso_modules.gallery';

    public const string USER_CAN_EDIT_PRODUCTTYPE = 'contao_user.iso_producttypes';

    public const string USER_CAN_EDIT_PAYMENT = 'contao_user.iso_payments';

    public const string USER_CAN_EDIT_SHIPPING = 'contao_user.iso_shippings';

    public const string USER_CAN_EDIT_TAX_CLASS = 'contao_user.iso_tax_classes';

    public const string USER_CAN_EDIT_TAX_RATE = 'contao_user.iso_tax_rates';

    public const string USER_CAN_EDIT_CONFIG = 'contao_user.iso_configs';

    public const string USER_CAN_ACCESS_ORDERS_OF_MEMBER_GROUP = 'contao_user.iso_member_groups';
}
