<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Contao\StringUtil;
use Isotope\CoreBundle\Model\BasePrice as BasePriceModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Price\Price;
use Isotope\CoreBundle\Product\Price\PriceCollection;
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
            $variantIds = array_unique($prices->column('getVariantId'));
            $prices = new PriceCollection(array_map(static fn (int $variantId) => $prices->where('getVariantId', $variantId)->sort('getQuantity')->first(), $variantIds));
        }

        $price = $prices->reduce(
            static function (Price|null $price, Price $value) use ($quantity, $showRange): Price|null {
                if (!$showRange && $quantity < $value->getQuantity()) {
                    return $price;
                }

                if (null === $price) {
                    return $value;
                }

                return $value->getAmount() < $price->getAmount() ? $value : $price;
            },
            null
        );

        $amount = $price->getAmount();

        if (1.0 !== $context->getCurrencyFactor()) {
            $amount *= $context->getCurrencyFactor();
        }

        $taxRates = $this->taxCalculator->getTaxRates($price->getTaxId(), $context);
        $calculated = $this->calculate($amount, $taxRates, $context, $quantity, $showRange && $prices->hasRange());

        $calculated->setBasePrice($this->calculateBasePrice($product));

        return $calculated;
    }

    public function calculateOption(string $price, CalculatedPrice $base, StoreContext $context): CalculatedPrice
    {
        $amount = $this->amountFromPriceString($price, $base);

        return $this->calculate($amount, $base->getTaxRates(), $context);
    }

    public function sum(CalculatedPrice $basePrice, CalculatedPrice $addedPrice): CalculatedPrice
    {
        $amount = FloatComparator::cast($basePrice->getUnitPrice() + $addedPrice->getUnitPrice());
        $taxRates = $this->taxCalculator->combineTaxRates($basePrice->getTaxRates(), $addedPrice->getTaxRates(), $basePrice->getUnitPrice(), $addedPrice->getUnitPrice());
        $calculatedTaxes = $this->taxCalculator->combineCalculatedTaxes($basePrice->getCalculatedTaxes(), $addedPrice->getCalculatedTaxes());

        return new CalculatedPrice(
            $amount,
            $amount * $basePrice->getQuantity(),
            $calculatedTaxes,
            $taxRates,
            $basePrice->getQuantity(),
        );
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

    private function amountFromPriceString(string $price, CalculatedPrice $base): float
    {
        if (str_ends_with($price, '%')) {
            return $base->getUnitPrice() / 100 * substr($price, 0, -1);
        }

        return (float) $price;
    }

    private function calculate(float $amount, TaxRateCollection $taxRates, StoreContext $context, int $quantity = 1, bool $isRange = false): CalculatedPrice
    {
        $priceDisplay = $context->getPriceDisplay();
        $netPrice = $this->taxCalculator->calculateNetPrice($amount, $taxRates, $priceDisplay);

        if (PriceDisplay::Net === $priceDisplay) {
            $netPrice = $context->getMoneyFormat()->moneyRound($netPrice);
            $amount = $netPrice;
        }

        $taxes = $this->taxCalculator->calculateTaxes($netPrice, $taxRates, $context);

        if (PriceDisplay::Gross === $priceDisplay) {
            $taxTotal = $taxes->sum();
            $amount = $context->getMoneyFormat()->moneyRound($netPrice + $taxTotal);

            // Re-calculate net price and taxes from rounded gross price
            $noIncluded = clone $taxRates;
            $noIncluded->setIncluded(null);
            $netPrice = $this->taxCalculator->calculateNetPrice($amount - $taxTotal, $noIncluded, $priceDisplay);
            $taxes = $this->taxCalculator->calculateTaxes($netPrice, $taxRates, $context);
        }

        return new CalculatedPrice(
            $amount,
            $amount * $quantity,
            $taxes,
            $taxRates,
            $quantity,
            $isRange,
        );
    }
}
