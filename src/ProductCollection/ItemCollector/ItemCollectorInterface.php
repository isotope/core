<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemCollector;

use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;

interface ItemCollectorInterface
{
    public function process(AbstractProductCollection $collection, AbstractProductCollection $original, StoreContext $context): void;
}
