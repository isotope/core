<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class CheckoutShippingShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_shipping'];
    }

    public static function getDefaultPriority(): int
    {
        return 90;
    }
}
