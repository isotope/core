<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\Model\TaxClass;
use Isotope\Model\TaxRate as TaxRateModel;

class TaxCalculator
{
    public function __construct(private readonly RuleMatcher $ruleMatcher)
    {
    }

    public function getTaxRates(int|null $taxClassId, StoreContext $context): TaxRateCollection
    {
        if (!$taxClassId || !($taxClass = TaxClass::findById($taxClassId))) {
            return new TaxRateCollection();
        }

        $taxCollection = new TaxRateCollection();

        if ($taxClass->includes && ($taxRate = TaxRateModel::findById($taxClass->includes))) {
            $tax = new TaxRate(0, (float) $taxRate->rate);
            $taxCollection->setIncluded($tax);

            if (!$taxRate->rule || $this->ruleMatcher->isApplicable($taxRate->rule, $context)) {
                $taxCollection->add($tax);
            }
        }

        if ($taxClass->rates && ($taxRates = TaxRateModel::findMultipleByIds(explode(',', $taxClass->rates)))) {
            foreach ($taxRates as $taxRate) {
                if (!$taxRate->rule || $this->ruleMatcher->isApplicable($taxRate->rule, $context)) {
                    $taxCollection->add(new TaxRate((int) $taxRate->id, (float) $taxRate->rate));
                }
            }
        }

        return $taxCollection;
    }

    public function calculateNetPrice(float $price, TaxRateCollection $taxRates, StoreContext $context): float
    {
        if ($taxRates->getIncluded()) {
            $price -= $this->calculateTaxFromGrossPrice($price, $taxRates->getIncluded())->getTax();
        }

        if (PriceDisplay::Fixed === $context->getPriceDisplay()) {
            foreach ($taxRates as $taxRate) {
                if ($taxRates->getIncluded() !== $taxRate) {
                    $price -= $this->calculateTaxFromGrossPrice($price, $taxRate)->getTax();
                }
            }
        }

        return $price;
    }

    public function calculateTaxes(float $netPrice, TaxRateCollection $taxRates): CalculatedTaxCollection
    {
        $taxes = [];

        foreach ($taxRates as $taxRate) {
            $taxes[] = $this->calculateTaxFromNetPrice($netPrice, $taxRate);
        }

        return new CalculatedTaxCollection($taxes);
    }

    private function calculateTaxFromNetPrice(float $net, TaxRate $tax): CalculatedTax
    {
        // calculate percentage value of net price
        $net = $net / 100 * $tax->getPercentage();

        $calculatedTax = $net * $tax->getTaxRate() / 100;

        return new CalculatedTax($tax->getId(), $calculatedTax, $tax->getTaxRate(), $net);
    }

    private function calculateTaxFromGrossPrice(float $gross, TaxRate $tax): CalculatedTax
    {
        // calculate percentage value of gross price
        $gross = $gross / 100 * $tax->getPercentage();

        $calculatedTax = $gross / ((100 + $tax->getTaxRate()) / 100) * $tax->getTaxRate() / 100;

        return new CalculatedTax($tax->getId(), $calculatedTax, $tax->getTaxRate(), $gross);
    }
}
