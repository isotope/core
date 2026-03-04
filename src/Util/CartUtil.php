<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Util;

use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;

class CartUtil
{
    public static function requiresPayment(Cart $cart): bool
    {
        return $cart->getTotal()?->getTotalPrice() > 0;
    }

    public static function requiresShipping(Cart $cart): bool
    {
        foreach ($cart->getLineItems()->products() as $item) {
            /** @var ShippingExtension|null $shipping */
            $shipping = $item->getProduct()?->getExtension(ShippingExtension::class);

            if (!$shipping?->isExemptFromShipping()) {
                return true;
            }
        }

        return false;
    }

    public static function requiresShippingAddress(Cart $cart): bool
    {
        if (!self::requiresShipping($cart)) {
            return false;
        }

        foreach ($cart->getLineItems()->products() as $item) {
            /** @var ShippingExtension|null $shipping */
            $shipping = $item->getProduct()?->getExtension(ShippingExtension::class);

            if ($shipping?->isPickupOnly()) {
                return false;
            }
        }

        return true;
    }
}
