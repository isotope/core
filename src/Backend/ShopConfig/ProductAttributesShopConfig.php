<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ProductAttributesShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_attribute', 'tl_iso_attribute_option'];
    }

    public static function getDefaultPriority(): int
    {
        return 140;
    }
}
