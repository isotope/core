<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Model\ProductCollectionModel;

/**
 * @extends Transaction<bool>
 */
final class DeleteCollectionTransaction extends Transaction
{
    public const string NAME = 'isotope.product_collection.delete';

    public function __construct(
        private readonly ProductCollectionModel $collectionModel,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }

    public function getCollectionModel(): ProductCollectionModel
    {
        return $this->collectionModel;
    }
}
