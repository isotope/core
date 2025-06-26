<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class ProductRelatedCategoriesShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_related_category'];
    }

    public static function getDefaultPriority(): int
    {
        return 130;
    }
}
