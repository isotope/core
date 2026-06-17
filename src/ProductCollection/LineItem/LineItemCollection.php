<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\LineItem;

use Isotope\CoreBundle\Store\Weight\Scale;
use Isotope\CoreBundle\Store\Weight\Weight;
use Isotope\CoreBundle\Store\Weight\WeightUnit;
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

    public function clone(): self
    {
        return $this->map(static fn (LineItem $old) => (clone $old)->setId(0));
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
                for ($i = 0; $i < $item->getQuantity(); ++$i) {
                    $scale->add($weight);
                }
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
                $levels[$item->getLevel()] = new self();
            }

            $levels[$item->getLevel()]->add($item);
        }

        return $levels;
    }

    #[\Override]
    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->data));
    }
}
