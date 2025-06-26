<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class CheckoutTaxClassShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_tax_class'];
    }

    public static function getDefaultPriority(): int
    {
        return 80;
    }
}
