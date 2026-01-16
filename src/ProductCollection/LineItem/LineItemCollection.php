<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\LineItem;

use Isotope\CoreBundle\Util\FloatComparator;
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

    public function sumQuantity(): int
    {
        return $this->reduce(
            static fn (int $total, LineItem $item) => $total + $item->getQuantity(),
            0,
        );
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
