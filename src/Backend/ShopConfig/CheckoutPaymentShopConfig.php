<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

/**
 * @internal
 */
final class CheckoutPaymentShopConfig extends AbstractCoreShopConfig
{
    public function getTables(): array
    {
        return ['tl_iso_payment'];
    }

    public static function getDefaultPriority(): int
    {
        return 100;
    }
}
