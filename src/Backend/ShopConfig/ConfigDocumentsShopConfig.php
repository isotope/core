<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ConfigDocumentsShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_document'];
    }

    public static function getDefaultPriority(): int
    {
        return 20;
    }
}
