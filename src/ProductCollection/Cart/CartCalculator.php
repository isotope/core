<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\ProductCollection\ItemCollector\ItemCollectorInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class CartCalculator
{
    /**
     * @param iterable<ItemCollectorInterface> $collectors
     */
    public function __construct(
        #[AutowireIterator('isotope.item_collector')] private readonly iterable $collectors,
        private readonly CartFactory $cartFactory,
    ) {
    }

    public function calculate(Cart $cart, StoreContext $context): Cart
    {
        $originalHash = $this->hash($cart);

        for ($i = 0; $i < 10; $i++) {
            $toCalculate = $this->cartFactory->create($context);
            $toCalculate->setBillingAddress($cart->getBillingAddress());
            $toCalculate->setShippingAddress($cart->getShippingAddress());
            $toCalculate->setShippingMethod($cart->getShippingMethod());
            $toCalculate->setPaymentMethod($cart->getPaymentMethod());

            foreach ($this->collectors as $collector) {
                $collector->process($toCalculate, $cart, $context);
            }

            $calculatedHash = $this->hash($toCalculate);

            if ($calculatedHash === $originalHash) {
                break;
            }

            $originalHash = $calculatedHash;
            $cart = $toCalculate;
        }

        $toCalculate->setSubtotals($this->calculateSubtotals($toCalculate, $context));

        // TODO: actually calculate the cart total
        $toCalculate->setTotal(new CalculatedPrice($toCalculate->getSubtotal(), $toCalculate->getSubtotal(), new CalculatedTaxCollection(), new TaxRateCollection()));

        return $toCalculate;
    }

    private function hash(Cart $cart): string
    {
        $data = [
            'price' => $cart->getSubtotal(),
            'shipping' => $cart->getShippingMethod()?->getId(),
            'payment' => $cart->getPaymentMethod()?->getId(),
            'items' => [],
        ];

        foreach ($cart->getLineItems() as $item) {
            $data['items'][] = $item->getHashData();
        }

        return \hash('sha256', \json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function calculateSubtotals(Cart $cart): array
    {
        $totals = [];

        foreach ($cart->getLineItems() as $item) {
            if ($price = $item->getPrice()) {
                if (!isset($totals[$item->getLevel()])) {
                    $totals[$item->getLevel()] = 0;
                }

                $totals[$item->getLevel()] += $price->totalPrice;
            }
        }

        return $totals;
    }
}
