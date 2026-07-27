<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\DataCollector;

use Isotope\CoreBundle\Store\Context\StoreContext;

interface DataCollectorInterface
{
    public function collect(ProductDtoCollection $collection, StoreContext $context): void;
}
