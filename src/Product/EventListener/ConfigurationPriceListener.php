<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\EventListener;

use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class ConfigurationPriceListener
{
    public function __construct(
        private readonly AttributeRegistry $attributeRegistry,
        private readonly PriceCalculator $priceCalculator,
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

            $values = array_map(strval(...), (array) $configuration[$name]);

            foreach ($attribute->getOptions(new ProductContext($event->getProduct(), $context)) as $option) {
                if (
                    $option->isGroup()
                    || !($optionPrice = $option->getPrice())
                    || !\in_array($option->getValue(), $values, true)
                ) {
                    continue;
                }

                $productPrice = $this->priceCalculator
                    ->sum($productPrice, $optionPrice, $context->getPriceDisplay())
                    ->setBasePrice($productPrice->getBasePrice())
                    ->setRange($productPrice->isRange())
                ;
            }
        }

        $event->setPrice($productPrice);
    }
}
