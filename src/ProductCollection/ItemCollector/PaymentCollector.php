<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemCollector;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('isotope.item_collector')]
class PaymentCollector implements ItemCollectorInterface
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
    ) {
    }

    public function process(Cart $cart, Cart $original, StoreContext $context): void
    {
        $payment = $original->getPaymentMethod();

        if (!$payment || !$payment->isAvailable($context)) {
            return;
        }

        $priceDefinition = $payment->getPriceDefinition();

        if (null === $priceDefinition) {
            return;
        }

        $price = $this->priceCalculator->calculatePriceDefinition($priceDefinition, $original->getSubtotal($payment->getLevel() - 1), $context);

        $item = new LineItem(LineItem::TYPE_PAYMENT, 'tl_iso_payment.'.$payment->getId());
        $item->setLabel($payment->getLabel());
        $item->setLevel($payment->getLevel());
        $item->setDynamic(true);
        $item->setPrice($price);
        $cart->add($item);
    }
}
