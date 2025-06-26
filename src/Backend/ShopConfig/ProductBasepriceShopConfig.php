<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ProductBasepriceShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_baseprice'];
    }

    public static function getDefaultPriority(): int
    {
        return 120;
    }
}
