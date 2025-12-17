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

class PriceCalculator
{
    public function __construct(private readonly TaxCalculator $taxCalculator)
    {
    }

    public function calculate(CoreProduct $product, int $quantity, bool $showRange, StoreContext $context): CalculatedPrice|null
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

        $price = $prices->reduce(static function (Price|null $price, Price $value) use ($quantity, $showRange): Price|null {
            if (!$showRange && $quantity < $value->getQuantity()) {
                return $price;
            }

            if (null === $price) {
                return $value;
            }

            return $value->getPrice() < $price->getPrice() ? $value : $price;
        }, null);

        $amount = $price->getPrice();

        if (1.0 !== $context->getCurrencyFactor()) {
            $amount *= $context->getCurrencyFactor();
        }

        $taxRates = $this->taxCalculator->getTaxRates($price->getTaxId(), $context);
        $netPrice = $this->taxCalculator->calculateNetPrice($amount, $taxRates, $context);
        $taxes = $this->taxCalculator->calculateTaxes($netPrice, $taxRates);

        $amount = match ($context->getPriceDisplay()) {
            PriceDisplay::Gross => $netPrice + $taxes->sum(),
            PriceDisplay::Net => $netPrice,
            PriceDisplay::Fixed => $amount,
        };

        $calculated = new CalculatedPrice(
            $amount,
            $amount * $quantity,
            $taxes,
            $taxRates,
            $quantity,
            $showRange && $prices->hasRange(),
        );

        $calculated->setBasePrice($this->calculateBasePrice($product));

        return $calculated;
    }

    private function calculateBasePrice(CoreProduct $product): BasePrice|null
    {
        $data = StringUtil::deserialize($product->baseprice);

        if (!\is_array($data) || !($data['unit'] ?? null) || !($data['value'] ?? null)) {
            return null;
        }

        $basePrice = BasePriceModel::findByPk((int) $data['unit']);

        if (null === $basePrice) {
            return null;
        }

        return new BasePrice((float) $data['value'], (float) $basePrice->amount, $basePrice->label);
    }
}
