<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class MergeCollectionEvent extends Event
{
    public function __construct(
        public readonly AbstractProductCollection $sourceCollection,
        public readonly AbstractProductCollection $targetCollection,
        public readonly StoreContext $context,
    ) {
    }
}
