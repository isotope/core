<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Tax;

use Ramsey\Collection\AbstractCollection;

/**
 * @extends AbstractCollection<TaxRate>
 */
class TaxRateCollection extends AbstractCollection
{
    private TaxRate|null $included = null;

    public function getType(): string
    {
        return TaxRate::class;
    }

    public function getIncluded(): TaxRate|null
    {
        return $this->included;
    }

    public function setIncluded(TaxRate|null $included): self
    {
        $this->included = $included;

        return $this;
    }
}
