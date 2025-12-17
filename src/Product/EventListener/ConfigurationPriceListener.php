<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\EventListener;

use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Option\Option;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Product\Event\ProductConfigurationEvent;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Ramsey\Collection\Exception\NoSuchElementException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ConfigurationPriceListener
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly TaxCalculator $taxCalculator,
    ) {
    }

    public function __invoke(ProductPriceEvent $event): void
    {
        $productPrice = $event->getPrice();
        $context = $event->getContext();
        $configuration = $event->getConfiguration();

        $names = array_intersect(
            $this->attributeRegistry->getPricedFields(),
            array_keys($configuration),
        );

        if (!$productPrice) {
            return;
        }

        foreach ($names as $name) {
            $attribute = $this->attributeRegistry->get($name);

            if (!$attribute instanceof OptionsAttributeInterface) {
                continue;
            }

            try {
                /** @var Option $option */
                $option = $attribute
                    ->getOptions(new ProductContext($event->getProduct(), $event->getContext()))
                    ->where('getValue', $configuration[$name])
                    ->first()
                ;

                if (!($optionPrice = $option->getPrice())) {
                    continue;
                }

                $amount = $productPrice->unitPrice + $optionPrice->unitPrice;

                $netPrice = $this->taxCalculator->calculateNetPrice($amount, $productPrice->getTaxRates(), $context);
                $taxes = $this->taxCalculator->calculateTaxes($netPrice, $productPrice->getTaxRates());

                $amount = match ($context->getPriceDisplay()) {
                    PriceDisplay::Gross => $netPrice + $taxes->sum(),
                    PriceDisplay::Net => $netPrice,
                    PriceDisplay::Fixed => $amount,
                };

                $productPrice = new CalculatedPrice(
                    $amount,
                    $amount * $productPrice->quantity,
                    $taxes,
                    $productPrice->getTaxRates(),
                    $productPrice->quantity,
                    $productPrice->isRange(),
                );
            } catch (NoSuchElementException) {
                continue;
            }
        }

        $event->setPrice($productPrice);
    }
}
