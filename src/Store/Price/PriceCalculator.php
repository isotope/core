<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Contao\StringUtil;
use Isotope\CoreBundle\Model\BasePriceModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Util\FloatComparator;

class PriceCalculator
{
    public function __construct(private readonly TaxCalculator $taxCalculator)
    {
    }

    public function calculateProduct(CoreProduct $product, int $quantity, bool $showRange, StoreContext $context): CalculatedPrice|null
    {
        $prices = $product->getPrices();

        if (!$prices) {
            return null;
        }

        $type = $product->getProductType();

        // Filter for one price per product variant with lowest quantity
        if ($showRange && $type->hasAdvancedPrices() && !$type->showPriceTiers() && $type->hasVariantPrices()) {
            $variantIds = array_unique($prices->column('getReference'));
            $prices = new PriceDefinitionCollection(array_map(static fn (int $variantId) => $prices->where('getReference', (string) $variantId)->sort('getQuantity')->first(), $variantIds));
        }

        $price = $prices->reduce(
            static function (PriceDefinition|null $price, PriceDefinition $value) use ($quantity, $showRange): PriceDefinition|null {
                if (!$showRange && $quantity < $value->getQuantity()) {
                    return $price;
                }

                if (null === $price) {
                    return $value;
                }

                return $value->getAmount() < $price->getAmount() ? $value : $price;
            },
            null,
        );

        if (null === $price) {
            return null;
        }

        $calculated = $this
            ->calculateProductUnitPrice($price, $quantity, $context)
            ->setRange($showRange && $prices->hasRange())
            ->setBasePrice($this->calculateBasePrice($product))
        ;

        if (null !== ($listAmount = $price->getListAmount())) {
            $listPrice = $this->calculateProductUnitPrice($price->withAmount($listAmount), $quantity, $context);
            $calculated->setListPrice($listPrice->getUnitPrice());
        }

        return $calculated;
    }

    public function calculatePriceDefinition(PriceDefinition $priceDefinition, CalculatedPrice $base, StoreContext $context): CalculatedPrice
    {
        if (PriceDefinition::SPLIT_TAX === $priceDefinition->getTaxClassId()) {
            $taxRates = $base->getTaxRates();
            $includedTaxRates = $base->getIncludedTaxRates();
        } elseif ($priceDefinition->getTaxClassId() > 0) {
            [$taxRates, $includedTaxRates] = $this->taxCalculator->getTaxRates($priceDefinition->getTaxClassId(), $context);
        } else {
            $taxRates = new TaxRateCollection();
            $includedTaxRates = new TaxRateCollection();
        }

        if ($priceDefinition->isPercentage()) {
            $amount = $base->getUnitPrice();

            if (PriceDisplay::Net !== $context->getPriceDisplay()) {
                $amount -= $base->getCalculatedTaxes()->sum();
            }

            $amount = $amount / 100 * $priceDefinition->getAmount();

            $price = $this->calculate(
                $amount,
                $taxRates,
                new TaxRateCollection(),
                $context,
                $priceDefinition->getQuantity(),
                PriceDisplay::Net === $context->getPriceDisplay() ? PriceDisplay::Net : PriceDisplay::Gross,
            );
        } else {
            $amount = $priceDefinition->getAmount();

            if (1.0 !== $context->getCurrencyFactor()) {
                $amount *= $context->getCurrencyFactor();
            }

            $price = $this->calculate($amount, $taxRates, $includedTaxRates, $context, $priceDefinition->getQuantity());
        }

        return $price->setUnitLabel($priceDefinition->getUnitLabel());
    }

    public function sum(CalculatedPrice $basePrice, CalculatedPrice $addedPrice, PriceDisplay $priceDisplay): CalculatedPrice
    {
        $amount = FloatComparator::cast($basePrice->getUnitPrice() + $addedPrice->getUnitPrice());

        $baseUnitPrice = $basePrice->getUnitPrice();
        $addedUnitPrice = $addedPrice->getUnitPrice();

        if (PriceDisplay::Net !== $priceDisplay) {
            $baseUnitPrice = $basePrice->getUnitPrice() - $basePrice->getCalculatedTaxes()->sum();
            $addedUnitPrice = $addedPrice->getUnitPrice() - $addedPrice->getCalculatedTaxes()->sum();
        }

        $taxRates = $this->taxCalculator->combineTaxRates($basePrice->getTaxRates(), $addedPrice->getTaxRates(), $baseUnitPrice, $addedUnitPrice);
        $includedTaxRates = $this->taxCalculator->combineTaxRates($basePrice->getIncludedTaxRates(), $addedPrice->getIncludedTaxRates(), $baseUnitPrice, $addedUnitPrice);
        $calculatedTaxes = $this->taxCalculator->combineCalculatedTaxes($basePrice->getCalculatedTaxes(), $addedPrice->getCalculatedTaxes());

        return new CalculatedPrice(
            $amount,
            $amount * $basePrice->getQuantity(),
            $calculatedTaxes,
            $taxRates,
            $includedTaxRates,
            $basePrice->getQuantity(),
        );
    }

    /**
     * Recalculate taxes for a unit price in the store's display currency and tax basis.
     */
    public function recalculateUnitPrice(CalculatedPrice $price, float $amount, StoreContext $context): CalculatedPrice
    {
        $isNet = PriceDisplay::Net === $context->getPriceDisplay();

        return $this->calculate(
            $amount,
            $price->getTaxRates(),
            $isNet ? new TaxRateCollection() : $price->getIncludedTaxRates(),
            $context,
            $price->getQuantity(),
            $isNet ? PriceDisplay::Net : PriceDisplay::Fixed,
        )->setUnitLabel($price->getUnitLabel());
    }

    private function calculateBasePrice(CoreProduct $product): BasePrice|null
    {
        $data = StringUtil::deserialize($product->baseprice);

        if (!\is_array($data) || !($data['unit'] ?? null) || !($data['value'] ?? null)) {
            return null;
        }

        $basePrice = BasePriceModel::findById((int) $data['unit']);

        if (null === $basePrice) {
            return null;
        }

        return new BasePrice((float) $data['value'], (float) $basePrice->amount, $basePrice->label);
    }

    private function calculateProductUnitPrice(PriceDefinition $price, int $quantity, StoreContext $context): CalculatedPrice
    {
        [$taxRates, $includedTaxRates] = $this->taxCalculator->getTaxRates($price->getTaxClassId(), $context);

        return $this->calculate($price->getAmount() * $context->getCurrencyFactor(), $taxRates, $includedTaxRates, $context, $quantity);
    }

    private function calculate(float $amount, TaxRateCollection $taxRates, TaxRateCollection $includedTaxRates, StoreContext $context, int $quantity = 1, PriceDisplay|null $priceDisplay = null): CalculatedPrice
    {
        $priceDisplay ??= $context->getPriceDisplay();

        if (PriceDisplay::Fixed === $priceDisplay) {
            $amount = $context->getMoneyFormat()->moneyRound($amount);
        }

        $netPrice = $this->taxCalculator->calculateNetPrice($amount, PriceDisplay::Fixed === $priceDisplay ? $taxRates : $includedTaxRates);

        if (PriceDisplay::Net === $priceDisplay) {
            $netPrice = $context->getMoneyFormat()->moneyRound($netPrice);
            $amount = $netPrice;
        }

        $calculatedTaxes = $this->taxCalculator->calculateTaxes($netPrice, $taxRates, $context->getTaxCalculation(), $context->getMoneyFormat());

        // Re-calculate net price and taxes from rounded gross price
        if (PriceDisplay::Gross === $priceDisplay) {
            $taxTotal = $calculatedTaxes->sum();
            $amount = $context->getMoneyFormat()->moneyRound($netPrice + $taxTotal);

            $netPrice = $this->taxCalculator->calculateNetPrice($amount, $taxRates);
            $calculatedTaxes = $this->taxCalculator->calculateTaxes($netPrice, $taxRates, $context->getTaxCalculation(), $context->getMoneyFormat());
        }

        return new CalculatedPrice($amount, $amount * $quantity, $calculatedTaxes, $taxRates, $includedTaxRates, $quantity);
    }
}
