<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ProductProducttypesShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_producttype'];
    }

    public static function getDefaultPriority(): int
    {
        return 150;
    }
}
