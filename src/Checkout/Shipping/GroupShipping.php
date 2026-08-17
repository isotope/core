<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\Exception\UnexpectedValueException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Twig\TemplateMarkup;

#[AsShipping]
class GroupShipping extends AbstractShipping
{
    /**
     * @var array<ShippingInterface>
     */
    private readonly array $inner;

    /**
     * @param array<ShippingInterface> $methods,
     */
    public function __construct(
        int $id,
        StoreContext $context,
        array $methods,
        private readonly GroupCalculation $groupCalculation,
        private readonly int $taxClassId,
        private readonly bool $inherit,
    ) {
        parent::__construct($id, $context);

        $inner = [];

        foreach ($methods as $shipping) {
            if (!$shipping instanceof ShippingInterface) {
                throw new UnexpectedValueException(\sprintf('%s is not an instance of %s', $shipping::class, ShippingInterface::class));
            }

            $inner[$shipping->getId()] = $shipping;
        }

        $this->inner = $inner;
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            PriceCalculator::class,
        ];
    }

    #[\Override]
    public function isAvailable(): bool
    {
        if (!parent::isAvailable()) {
            return false;
        }

        // Group is available if at least one shipping method is available.
        foreach ($this->inner as $shipping) {
            if ($shipping->isAvailable()) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        if ($this->inherit) {
            foreach ($this->inner as $shipping) {
                if ($shipping->isAvailable()) {
                    return $shipping->getLabel();
                }
            }
        }

        return parent::getLabel();
    }

    #[\Override]
    public function getNote(): string
    {
        if ($this->inherit) {
            foreach ($this->inner as $shipping) {
                if ($shipping->isAvailable()) {
                    return $shipping->getNote();
                }
            }
        }

        return parent::getNote();
    }

    #[\Override]
    public function getTrackingUrl(): string|null
    {
        if ($this->inherit) {
            foreach ($this->inner as $shipping) {
                if ($shipping->isAvailable()) {
                    return $shipping->getTrackingUrl();
                }
            }
        }

        return parent::getTrackingUrl();
    }

    public function hasError(): bool
    {
        return false;
    }

    public function review(): TemplateMarkup|null
    {
        return null;
    }

    #[\Override]
    public function getPriceDefinition(): PriceDefinition|null
    {
        if ([] === $this->inner) {
            return null;
        }

        switch ($this->groupCalculation) {
            default:
            case GroupCalculation::first:
                return array_first($this->getPrices(false))[0];

            case GroupCalculation::lowest:
                $return = null;

                foreach ($this->getPrices() as $price) {
                    if (null === $return || $price[1]->getUnitPrice() < $return[1]->getUnitPrice()) {
                        $return = $price[0];
                    }
                }

                return $return;

            case GroupCalculation::highest:
                $return = null;

                foreach ($this->getPrices() as $price) {
                    if (null === $return || $price[1]->getUnitPrice() > $return[1]->getUnitPrice()) {
                        $return = $price[0];
                    }
                }

                return $return;

            case GroupCalculation::sum:
                $total = null;

                foreach ($this->getPrices() as $price) {
                    $total += $price[1]->getUnitPrice();
                }

                return new PriceDefinition($total, $this->taxClassId);
        }
    }

    /**
     * @return array<array{0: PriceDefinition, 1: CalculatedPrice}>
     */
    private function getPrices(bool $calculate = true): array
    {
        $calculator = $this->get(PriceCalculator::class);
        $subtotal = null;

        if (
            $calculate
            && !($subtotal = $this->getStoreContext()->getCart()?->getSubtotal($this->getLevel() - 1))
        ) {
            return [];
        }

        $prices = [];

        foreach ($this->inner as $method) {
            if ($method->isAvailable() && ($definition = $method->getPriceDefinition())) {
                $prices[] = [
                    $definition,
                    $calculate ? $calculator->calculatePriceDefinition($definition, $subtotal, $this->context) : null,
                ];
            }
        }

        return $prices;
    }
}
