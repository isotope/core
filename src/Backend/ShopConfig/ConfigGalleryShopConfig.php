<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ConfigGalleryShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_gallery'];
    }

    public static function getDefaultPriority(): int
    {
        return 10;
    }
}
