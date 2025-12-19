<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Contao\StringUtil;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Util\PostalCodeUtil;
use Isotope\Model\Address;
use Isotope\Model\TaxClass;
use Isotope\Model\TaxRate as TaxRateModel;

class TaxCalculator
{
    public function getTaxRates(int|null $taxClassId, StoreContext $context): TaxRateCollection
    {
        if (!$taxClassId || !($taxClass = TaxClass::findById($taxClassId))) {
            return new TaxRateCollection();
        }

        $taxCollection = new TaxRateCollection();

        if ($taxClass->includes && ($taxRate = TaxRateModel::findById($taxClass->includes))) {
            $tax = new TaxRate(0, (float) $taxRate->rate);
            $taxCollection->setIncluded($tax);

            if ($this->taxRateApplicable($taxRate, $context)) {
                $taxCollection->add($tax);
            }
        }

        if ($taxClass->rates && ($taxRates = TaxRateModel::findMultipleByIds(explode(',', $taxClass->rates)))) {
            foreach ($taxRates as $taxRate) {
                if ($this->taxRateApplicable($taxRate, $context)) {
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

    private function taxRateApplicable(TaxRateModel $taxRate, StoreContext $context): bool
    {
        // Tax rate is limited to another store config
        if ($taxRate->config > 0 && (int) $taxRate->config !== $context->getConfigId()) {
            dd('config');
            return false;
        }

        // Tax rate is protected, check member groups
        if ($taxRate->protected) {
            $groups = StringUtil::deserialize($taxRate->groups);

            if (!\is_array($groups) || [] === array_intersect($context->getMemberGroups(), array_map(intval(...), $groups))) {
                dd('protected');
                return false;
            }
        }

        $addresses = array_filter(explode(',', $taxRate->address));

        if ([] === $addresses) {
            return true;
        }

        if (
            \in_array('billing', $addresses, true)
            && ($address = $context->getCart()->getBillingAddress())
            && $this->taxRateApplicableToAddress($taxRate, $address)
        ) {
            return true;
        }

        if (
            \in_array('shipping', $addresses, true)
            && ($address = $context->getCart()->getShippingAddress())
            && $this->taxRateApplicableToAddress($taxRate, $address)
        ) {
            return true;
        }

        return false;
    }

    private function taxRateApplicableToAddress(TaxRateModel $taxRate, Address $address): bool
    {
        if ($taxRate->countries && !\in_array($address->country, explode(',', $taxRate->countries), true)) {
            dd('countries');
            return false;
        }

        if ($taxRate->countries && $taxRate->subdivisions && !\in_array($address->subdivision, explode(',', $taxRate->subdivisions), true)) {
            dd('subdivisions');
            return false;
        }

        // Check if address has a valid postal code
        if (!empty($taxRate->postalCodes)) {
            $arrCodes = PostalCodeUtil::parseRange($taxRate->postalCodes);

            if (!\in_array($address->postal, $arrCodes)) {
                dd('postal code');
                return false;
            }
        }

        // TODO: validate customer VAT number
        if ($taxRate->exemptOnValidVAT) {
            //$validators = StringUtil::deserialize(Isotope::getConfig()->vatNoValidators);
            //if (!empty($validators) && \is_array($validators)) {
            //    foreach ($validators as $type) {
            //
            //        /** @var IsotopeVatNoValidator $service */
            //        $service = new $GLOBALS['ISO_VAT'][$type]();
            //
            //        if ($service->exemptTax($address, $this)) {
            //            continue 2;
            //        }
            //    }
            //}
        }

        return true;
    }
}
