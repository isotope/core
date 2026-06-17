<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;

/**
 * @extends TransactionEvent<void>
 */
class SaveCollectionTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.product_collection.save';

    public function __construct(
        private readonly AbstractProductCollection $collection,
        private readonly ProductCollectionModel $collectionModel,
    ) {
    }

    public function getName(): string
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
}
