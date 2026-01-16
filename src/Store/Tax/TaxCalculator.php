<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Isotope\CoreBundle\Util\FloatComparator;
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
            $tax = new TaxRate(
                (string) $taxRate->id,
                (float) $taxRate->rate,
                100,
                PriceDisplay::Net === $context->getPriceDisplay() ? ($taxRate->label ?: null) : ($taxClass->label ?: $taxRate->label ?: null),
            );

            $taxCollection->setIncluded($tax);

            if (!$taxRate->rule || $this->ruleMatcher->isApplicable($taxRate->rule, $context)) {
                $taxCollection->add($tax);
            }
        }

        if ($taxClass->rates && ($taxRates = TaxRateModel::findMultipleByIds(explode(',', $taxClass->rates)))) {
            foreach ($taxRates as $taxRate) {
                if (!$taxRate->rule || $this->ruleMatcher->isApplicable($taxRate->rule, $context)) {
                    $taxCollection->add(new TaxRate(
                        (string) $taxRate->id,
                        (float) $taxRate->rate,
                        100,
                        $taxRate->label ?: null,
                    ));
                }
            }
        }

        return $taxCollection;
    }

    public function calculateNetPrice(float $price, TaxRateCollection $taxRates, PriceDisplay $priceDisplay): float
    {
        if (PriceDisplay::Fixed === $priceDisplay) {
            $tax = 0;

            foreach ($taxRates as $taxRate) {
                $tax += $this->calculateTaxFromGrossPrice($price, $taxRate)->getTax();
            }

            return $price - $tax;
        }

        if ($taxRates->getIncluded()) {
            $price -= $this->calculateTaxFromGrossPrice($price, $taxRates->getIncluded())->getTax();
        }

        return $price;
    }

    public function calculateTaxes(float $netPrice, TaxRateCollection $taxRates, StoreContext $context): CalculatedTaxCollection
    {
        $taxes = [];

        foreach ($taxRates as $taxRate) {
            $taxes[] = $this->calculateTaxFromNetPrice($netPrice, $taxRate, $context);
        }

        return new CalculatedTaxCollection($taxes);
    }

    public function combineTaxRates(TaxRateCollection $taxes1, TaxRateCollection $taxes2, float $price1, float $price2): TaxRateCollection
    {
        if (0.0 === $price1 && 0.0 === $price2) {
            return new TaxRateCollection();
        }

        if (0.0 === $price1) {
            return $taxes2;
        }

        if (0.0 === $price2) {
            return $taxes1;
        }

        $total = FloatComparator::cast($price1 + $price2);
        $p1 = FloatComparator::cast(100 / $total * $price1);
        $p2 = FloatComparator::cast(100 / $total * $price2);

        $taxes = $this->taxRatesWithPercentage($taxes2, $p2);

        foreach ($this->taxRatesWithPercentage($taxes1, $p1) as $taxRate) {
            if (isset($taxes[$taxRate->getId()])) {
                $current = $taxes[$taxRate->getId()];
                $taxes[$taxRate->getId()] = new TaxRate($taxRate->getId(), $taxRate->getTaxRate(), $current->getPercentage() + $taxRate->getPercentage(), $taxRate->getLabel());
            } else {
                $taxes[$taxRate->getId()] = $taxRate;
            }
        }

        return new TaxRateCollection($taxes);
    }

    public function combineCalculatedTaxes(CalculatedTaxCollection $taxes1, CalculatedTaxCollection $taxes2): CalculatedTaxCollection
    {
        $number = 1;
        $calculatedTaxes = new CalculatedTaxCollection();

        foreach ([$taxes1, $taxes2] as $taxes) {
            foreach ($taxes as $tax) {
                /** @var CalculatedTax $newTax */
                if ($newTax = $calculatedTaxes->get($tax->getId())) {
                    $tax->setNumber($newTax->getNumber());
                    $newTax->increment($tax);
                } else {
                    $taxClone = clone $tax;
                    $taxClone->setNumber($number++);
                    $tax->setNumber($taxClone->getNumber());
                    $calculatedTaxes->put($taxClone->getId(), $taxClone);
                }
            }
        }

        return $calculatedTaxes;
    }

    private function calculateTaxFromNetPrice(float $net, TaxRate $tax, StoreContext $context): CalculatedTax
    {
        // calculate percentage value of net price
        $net = $net / 100 * $tax->getPercentage();

        $taxAmount = $net * $tax->getTaxRate() / 100;

        if (TaxCalculation::Horizontal === $context->getTaxCalculation()) {
            $taxAmount = $context->getMoneyFormat()->mathRound($taxAmount);
        }

        $calculatedTax = new CalculatedTax($tax->getId(), $taxAmount, $tax->getTaxRate(), $net);
        $calculatedTax->setLabel($tax->getLabel());

        return $calculatedTax;
    }

    private function calculateTaxFromGrossPrice(float $gross, TaxRate $tax): CalculatedTax
    {
        // calculate percentage value of gross price
        $gross = $gross / 100 * $tax->getPercentage();

        $calculatedTax = $gross / ((100 + $tax->getTaxRate()) / 100) * $tax->getTaxRate() / 100;

        return new CalculatedTax($tax->getId(), $calculatedTax, $tax->getTaxRate(), $gross);
    }

    /**
     * @return array<TaxRate>
     */
    private function taxRatesWithPercentage(TaxRateCollection $current, float $percentage): array
    {
        $taxes = [];

        foreach ($current as $tax) {
            $taxes[$tax->getId()] = new TaxRate($tax->getId(), $tax->getTaxRate(), $tax->getPercentage() / 100 * $percentage, $tax->getLabel());
        }

        return $taxes;
    }
}
