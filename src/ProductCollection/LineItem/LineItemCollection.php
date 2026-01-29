<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\LineItem;

use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\Util\FloatComparator;
use Isotope\CoreBundle\Weight\Scale;
use Isotope\CoreBundle\Weight\Weight;
use Isotope\CoreBundle\Weight\WeightUnit;
use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\CollectionInterface;

/**
 * @extends AbstractCollection<LineItem>
 */
class LineItemCollection extends AbstractCollection
{
    public function getType(): string
    {
        return LineItem::class;
    }

    /**
     * @return LineItemCollection
     */
    public function whereType(string $type): CollectionInterface
    {
        return $this->where('getType', $type);
    }

    public function products(): self
    {
        return $this->where('getType', LineItem::TYPE_PRODUCT);
    }

    public function sumQuantity(): int
    {
        return $this->reduce(
            static fn (int $total, LineItem $item) => $total + $item->getQuantity(),
            0,
        );
    }

    public function calculateWeight(WeightUnit $unit): Weight
    {
        $scale = new Scale();

        foreach ($this as $item) {
            if ($weight = $item->getWeight()) {
                $scale->add($weight);
            }
        }

        return $scale->amountIn($unit);
    }

    /**
     * @return array<int, LineItemCollection>
     */
    public function byLevel(): array
    {
        $levels = [];

        foreach ($this as $item) {
            if (!isset($levels[$item->getLevel()])) {
                $levels[$item->getLevel()] = new LineItemCollection();
            }

            $levels[$item->getLevel()]->add($item);
        }

        return $levels;
    }

    public function getUnitPriceAmount(): float
    {
        $prices = $this->map(fn (LineItem $item) => $item->getPrice()?->getUnitPrice() ?? 0.0);

        return FloatComparator::cast(array_sum($prices->toArray()));
    }

    public function getTotalPriceAmount(): float
    {
        $prices = $this->map(fn (LineItem $item) => $item->getPrice()?->getTotalPrice() ?? 0.0);

        return FloatComparator::cast(array_sum($prices->toArray()));
    }
}
