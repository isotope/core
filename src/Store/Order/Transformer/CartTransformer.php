<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Transformer;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;

class CartTransformer
{
    public static function transform(Cart $cart, StoreContext $context): array
    {
        return [
            'member_id' => $context->getMemberId(),
            'config_id' => $context->getConfigId(),
            'store_id' => $context->getStoreId(),
            'locale' => $context->getLocale(),
            'source_collection_id' => $cart->getId(),
            'payment_id' => $cart->getPaymentMethod()?->getId() ?? 0,
            'shipping_id' => $cart->getShippingMethod()?->getId() ?? 0,
            'subtotal' => $cart->getSubtotal()->getTotalPrice(),
            'total' => $cart->getTotal()->getTotalPrice(),
            'currency' => $context->getCurrency()->getCode(),
        ];
    }
}
