<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Transformer;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;

class CartTransformer
{
    public static function transform(Cart $cart, StoreContext $context): array
    {
        $data = [
            'member_id' => $context->getMemberId(),
            'config_id' => $context->getConfigId(),
            'store_id' => $context->getStoreId(),
            'locale' => $context->getLocale(),

            'payment_id' => $cart->getPaymentMethod()?->getId() ?? 0,
            'payment_data' => null,

            'shipping_id' => $cart->getShippingMethod()?->getId() ?? 0,
            'shipping_data' => null,

            'subtotal' => $cart->getSubtotal()->getTotalPrice(),
            'total' => $cart->getTotal()->getTotalPrice(),
            'currency' => $context->getCurrency()->getCode(),
        ];

        return $data;
    }
}
