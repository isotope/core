<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @extends Transaction<AbstractProductCollection>
 */
class LoadCollectionTransaction extends Transaction
{
    public const string NAME = 'isotope.product_collection.load';

    public function __construct(
        public readonly AbstractProductCollection $collection,
        public readonly ProductCollectionModel $collectionModel,
        public readonly StoreContext $context,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }
}
