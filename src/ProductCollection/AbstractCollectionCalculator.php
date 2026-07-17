<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Isotope\CoreBundle\ProductCollection\ItemCollector\ItemCollectorInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;

/**
 * @template T of AbstractProductCollection
 */
abstract class AbstractCollectionCalculator
{
    /**
     * @param iterable<ItemCollectorInterface> $collectors
     */
    public function __construct(
        private readonly iterable $collectors,
        private readonly PriceCalculator $priceCalculator,
        private readonly ProductCollectionHasher $collectionHasher,
    ) {
    }

    /**
     * @param T $collection
     * @return T
     */
    public function calculate(AbstractProductCollection $collection, StoreContext $context): AbstractProductCollection
    {
        $originalHash = $this->collectionHasher->hash($collection);

        for ($i = 0; $i < 10; ++$i) {
            $toCalculate = $this->createNewInstance($collection, $context);

            foreach ($this->collectors as $collector) {
                $collector->process($toCalculate, $collection, $context);
            }

            $this->finalize($toCalculate, $context);

            $calculatedHash = $this->collectionHasher->hash($toCalculate);

            if ($calculatedHash === $originalHash) {
                break;
            }

            $originalHash = $calculatedHash;
            $collection = $toCalculate;

            $context->set($collection::class, $collection);
        }

        return $toCalculate;
    }

    /**
     * @param T $collection
     * @return T
     */
    abstract protected function createNewInstance(AbstractProductCollection $collection, StoreContext $context): AbstractProductCollection;

    protected function finalize(AbstractProductCollection $collection, StoreContext $context): void
    {
        $collection->setLineItems($collection->getLineItems()->sort('getLevel'));
        $collection->setSubtotals($this->calculateSubtotals($collection->getLineItems(), $context->getPriceDisplay()));

        $total = clone $collection->getSubtotal();

        if (PriceDisplay::Net === $context->getPriceDisplay()) {
            // Always round tax here because we display it to the client
            $tax = array_reduce($total->getCalculatedTaxes()->toArray(), static fn (float $total, CalculatedTax $tax) => $total + $context->getMoneyFormat()->mathRound($tax->getAmount()), 0);
            $amount = $context->getMoneyFormat()->moneyRound($total->getUnitPrice() + $tax);

            $total = new CalculatedPrice($amount, $amount, $total->getCalculatedTaxes(), $total->getTaxRates(), $total->getIncludedTaxRates());
        }

        $collection->setTotal($total);
        $this->updateTaxNumbers($collection);
    }

    protected function calculateSubtotals(LineItemCollection $lineItems, PriceDisplay $priceDisplay): array
    {
        $levels = [];
        $subtotal = new CalculatedPrice(0, 0);

        foreach ($lineItems->byLevel() as $level => $items) {
            foreach ($items as $item) {
                if ($price = $item->getPrice()) {
                    $subtotal = $this->priceCalculator->sum($subtotal, $price->reduce(), $priceDisplay);
                }
            }

            $levels[$level] = $subtotal;
        }

        if ([] === $levels) {
            $levels[0] = new CalculatedPrice(0, 0);
        }

        return $levels;
    }

    protected function updateTaxNumbers(AbstractProductCollection $collection): void
    {
        $numbers = [];

        foreach ($collection->getLineItems() as $lineItem) {
            foreach ($lineItem->getPrice()->getCalculatedTaxes() as $tax) {
                $number = $numbers[$tax->getId()] ?? \count($numbers) + 1;
                $numbers[$tax->getId()] = $number;
                $tax->setNumber($number);
            }
        }

        foreach ($collection->getSubtotals() as $subtotal) {
            foreach ($subtotal->getCalculatedTaxes() as $tax) {
                $number = $numbers[$tax->getId()] ?? \count($numbers) + 1;
                $numbers[$tax->getId()] = $number;
                $tax->setNumber($number);
            }
        }

        foreach ($collection->getTotal()->getCalculatedTaxes() as $tax) {
            $number = $numbers[$tax->getId()] ?? \count($numbers) + 1;
            $numbers[$tax->getId()] = $number;
            $tax->setNumber($number);
        }
    }
}
