<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;

use Isotope\CoreBundle\ProductCollection\ItemCollector\ItemCollectorInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;
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
        private readonly PriceCalculator $priceCalculator,
    ) {
    }

    public function calculate(Cart $cart, StoreContext $context, \Closure|null $setter = null): Cart
    {
        $originalHash = $this->hash($cart);

        for ($i = 0; $i < 10; ++$i) {
            $toCalculate = $this->prepare($cart, $context);

            foreach ($this->collectors as $collector) {
                $collector->process($toCalculate, $cart, $context);
            }

            $this->finalize($toCalculate, $context);

            $calculatedHash = $this->hash($toCalculate);

            if ($calculatedHash === $originalHash) {
                break;
            }

            $originalHash = $calculatedHash;
            $cart = $toCalculate;

            if ($setter) {
                $setter($cart);
            }
        }

        return $toCalculate;
    }

    private function prepare(Cart $cart, StoreContext $context): Cart
    {
        $toCalculate = $this->cartFactory->create($context);

        $toCalculate->setBillingAddress($cart->getBillingAddress());
        $toCalculate->setShippingAddress($cart->getShippingAddress());
        $toCalculate->setShippingMethod($cart->getShippingMethod());
        $toCalculate->setPaymentMethod($cart->getPaymentMethod());

        return $toCalculate;
    }

    private function finalize(Cart $cart, StoreContext $context): void
    {
        $cart->setLineItems($cart->getLineItems()->sort('getLevel'));
        $cart->setSubtotals($this->calculateSubtotals($cart));

        $total = clone $cart->getSubtotal();

        if (PriceDisplay::Net === $context->getPriceDisplay()) {
            // Always round tax here because we display it to the client
            $tax = array_reduce($total->getCalculatedTaxes()->toArray(), static fn (float $total, CalculatedTax $tax) => $total + $context->getMoneyFormat()->mathRound($tax->getAmount()), 0);
            $amount = $context->getMoneyFormat()->moneyRound($total->getUnitPrice() + $tax);

            $total = new CalculatedPrice($amount, $amount, $total->getCalculatedTaxes(), $total->getTaxRates());
        }

        $cart->setTotal($total);
    }

    private function hash(Cart $cart): string
    {
        $data = [
            'price' => $cart->getSubtotal()->getTotalPrice(),
            'shipping' => $cart->getShippingMethod()?->getId(),
            'payment' => $cart->getPaymentMethod()?->getId(),
            'items' => [],
        ];

        foreach ($cart->getLineItems() as $item) {
            $data['items'][] = $item->getHashData();
        }

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function calculateSubtotals(Cart $cart): array
    {
        $levels = [];
        $subtotal = new CalculatedPrice(0, 0, new CalculatedTaxCollection(), new TaxRateCollection());

        foreach ($cart->getLineItems()->byLevel() as $level => $items) {
            foreach ($items as $item) {
                if ($price = $item->getPrice()) {
                    $subtotal = $this->priceCalculator->sum($subtotal, $price->reduce());
                }
            }

            $levels[$level] = $subtotal;
        }

        return $levels;
    }
}
