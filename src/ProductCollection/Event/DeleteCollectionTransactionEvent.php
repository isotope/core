<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;
use Isotope\CoreBundle\Model\ProductCollectionModel;

/**
 * @extends TransactionEvent<bool>
 */
final class DeleteCollectionTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.product_collection.delete';

    public function __construct(
        private readonly int $collectionId,
        private readonly ProductCollectionModel $collectionModel,
    ) {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getCollectionId(): int
    {
        return $this->collectionId;
    }

    public function getCollectionModel(): ProductCollectionModel
    {
        return $this->collectionModel;
    }
}
