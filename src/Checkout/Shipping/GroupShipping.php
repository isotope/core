<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\Exception\UnderflowException;
use Isotope\CoreBundle\Exception\UnexpectedValueException;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\Interfaces\IsotopeOrderableCollection;

#[AsShipping]
class GroupShipping extends AbstractShipping
{
    /**
     * @var array<ShippingInterface>
     */
    private readonly array $inner;

    /**
     * @var array<int, array<ShippingInterface>>
     */
    private array $cache = [];

    /**
     * @param array<ShippingInterface> $methods,
     */
    public function __construct(
        int $id,
        StoreContext $context,
        array $methods,
        private readonly GroupCalculation $groupCalculation,
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

    public function isAvailable(StoreContext $context): bool
    {
        if (!parent::isAvailable($context)) {
            return false;
        }

        // Group is available if at least one shipping method is available.
        foreach ($this->inner as $shipping) {
            if ($shipping->isAvailable($context)) {
                return true;
            }
        }

        return false;
    }

    public function getLabel(): string
    {
        if ($this->inherit) {
            try {
                return $this->getFirst($collection)->getLabel($collection);
            } catch (\UnderflowException) {
            }
        }

        return parent::getLabel($collection);
    }

    public function getNote(): string
    {
        if ($this->inherit) {
            try {
                return $this->getFirst($collection)->getNote($collection);
            } catch (\UnderflowException) {
            }
        }

        return parent::getNote($collection);
    }

    public function getPrice(IsotopeOrderableCollection $collection): float|null
    {
        if ([] === $this->inner) {
            return null;
        }

        switch ($this->groupCalculation) {
            default:
            case GroupCalculation::first:
                return $this->getFirst($collection)->getPrice($collection);

            case GroupCalculation::lowest:
                $return = null;

                foreach ($this->getAvailable($collection) as $method) {
                    $price = $method->getPrice($collection);
                    if (null !== $price && (null === $return || $price < $return)) {
                        $return = $price;
                    }
                }

                return $return;

            case GroupCalculation::highest:
                $return = null;

                foreach ($this->getAvailable($collection) as $method) {
                    $price = $method->getPrice($collection);
                    if (null !== $price && (null === $return || $price > $return)) {
                        $return = $price;
                    }
                }

                return $return;

            case GroupCalculation::sum:
                $total = null;

                foreach ($this->getAvailable($collection) as $method) {
                    if (null !== ($price = $method->getPrice($collection))) {
                        if (null === $total) {
                            $total = 0;
                        }

                        $total += $price;
                    }
                }

                return $total;
        }
    }

    /**
     * @return array<ShippingInterface>
     */
    private function getAvailable(IsotopeOrderableCollection $collection): array
    {
        if (isset($this->cache[$collection->getId()])) {
            return $this->cache[$collection->getId()];
        }

        $methods = array_filter(
            $this->inner,
            static fn (ShippingInterface $shipping) => $shipping->isAvailable($collection),
        );

        $this->cache[$collection->getId()] = $methods;

        return $methods;
    }

    private function getFirst(IsotopeOrderableCollection $collection): ShippingInterface
    {
        $methods = $this->getAvailable($collection);
        $method = reset($methods) ?: null;

        if (null === $method) {
            throw new UnderflowException('No shipping method available in group ID '.$this->getId());
        }

        return $method;
    }
}
