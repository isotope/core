<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\LineItem;

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
}
