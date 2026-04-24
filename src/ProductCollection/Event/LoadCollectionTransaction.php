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
        private readonly AbstractProductCollection $collection,
        private readonly ProductCollectionModel $collectionModel,
        private readonly StoreContext $context,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }

    public function getCollection(): AbstractProductCollection
    {
        return $this->collection;
    }

    public function getCollectionModel(): ProductCollectionModel
    {
        return $this->collectionModel;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->context;
    }
}
