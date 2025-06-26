<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ConfigConfigsShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_config'];
    }

    public static function getDefaultPriority(): int
    {
        return 50;
    }
}
