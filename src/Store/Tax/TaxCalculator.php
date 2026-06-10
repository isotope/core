<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Contao\CoreBundle\Framework\ContaoFramework;
use Isotope\CoreBundle\Model\TaxClassModel;
use Isotope\CoreBundle\Model\TaxRateModel;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Isotope\CoreBundle\Util\FloatComparator;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaxCalculator
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly RuleMatcher $ruleMatcher,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @internal
     * @todo probably move this to PriceCalculator
     *
     * @return array{0: TaxRateCollection, 1: TaxRateCollection}
     */
    public function getTaxRates(int|null $taxClassId, StoreContext $context): array
    {
        $taxClassAdapter = $this->framework->getAdapter(TaxClassModel::class);
        $taxRateAdapter = $this->framework->getAdapter(TaxRateModel::class);

        $result = [new TaxRateCollection(), new TaxRateCollection()];

        if (!$taxClassId || !($taxClass = $taxClassAdapter->findById($taxClassId))) {
            return $result;
        }

        if ($taxClass->includes && ($taxRate = $taxRateAdapter->findById($taxClass->includes))) {
            $tax = new TaxRate(
                (string) $taxRate->id,
                (float) $taxRate->rate,
                100,
                $this->trans(PriceDisplay::Net === $context->getPriceDisplay() ? ($taxRate->label ?: null) : ($taxClass->label ?: $taxRate->label ?: null)),
            );

            $result[1]->add($tax);

            if (!$taxRate->rule || $this->ruleMatcher->isApplicable($taxRate->rule, $context)) {
                $result[0]->add($tax);
            }
        }

        if ($taxClass->rates && ($taxRates = $taxRateAdapter->findMultipleByIds(explode(',', $taxClass->rates)))) {
            foreach ($taxRates as $taxRate) {
                if (!$taxRate->rule || $this->ruleMatcher->isApplicable($taxRate->rule, $context)) {
                    $result[0]->add(new TaxRate(
                        (string) $taxRate->id,
                        (float) $taxRate->rate,
                        100,
                        $this->trans($taxRate->label ?: null),
                    ));
                }
            }
        }

        return $result;
    }

    public function calculateNetPrice(float $price, TaxRateCollection $taxRates): float
    {
        $tax = 0;

        foreach ($taxRates as $taxRate) {
            $percentage = $taxRate->getPercentage() / 100;
            $taxFactor = $taxRate->getTaxRate() / 100;

            $tax += $taxFactor * $percentage;
        }

        return $price / (1 + $tax);
    }

    public function calculateTaxes(float $netPrice, TaxRateCollection $taxRates, TaxCalculation $taxCalculation, MoneyFormat $moneyFormat): CalculatedTaxCollection
    {
        $taxes = [];

        foreach ($taxRates as $taxRate) {
            $taxes[] = $this->calculateTaxFromNetPrice($netPrice, $taxRate, $taxCalculation, $moneyFormat);
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
        $calculatedTaxes = new CalculatedTaxCollection();

        /** @var CalculatedTax $tax */
        foreach ([...$taxes1->toArray(), ...$taxes2->toArray()] as $tax) {
            /** @var CalculatedTax $newTax */
            if ($newTax = $calculatedTaxes->get($tax->getId())) {
                $newTax->increment($tax);
            } else {
                $taxClone = clone $tax;
                $calculatedTaxes->put($taxClone->getId(), $taxClone);
            }
        }

        return $calculatedTaxes;
    }

    private function calculateTaxFromNetPrice(float $net, TaxRate $tax, TaxCalculation $taxCalculation, MoneyFormat $moneyFormat): CalculatedTax
    {
        // calculate percentage value of net price
        $net = $net / 100 * $tax->getPercentage();

        $taxAmount = $net * $tax->getTaxRate() / 100;

        if (TaxCalculation::Horizontal === $taxCalculation) {
            $taxAmount = $moneyFormat->mathRound($taxAmount);
        }

        $calculatedTax = new CalculatedTax($tax->getId(), $taxAmount, $tax->getTaxRate(), $net);
        $calculatedTax->setLabel($tax->getLabel());

        return $calculatedTax;
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

    private function trans(string|null $message): string|null
    {
        if (!$message) {
            return $message;
        }

        return $this->translator->trans($message, [], 'isotope');
    }
}
