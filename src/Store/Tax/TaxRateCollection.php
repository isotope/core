<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<TaxRate>
 */
class TaxRateCollection extends AbstractCollection implements \JsonSerializable
{
    public function getType(): string
    {
        return TaxRate::class;
    }

    public function jsonSerialize(): array
    {
        return array_map(static fn (TaxRate $tax) => $tax->jsonSerialize(), $this->data);
    }
}
