<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ConfigOrderstatusShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_orderstatus'];
    }

    public static function getDefaultPriority(): int
    {
        return 40;
    }
}
