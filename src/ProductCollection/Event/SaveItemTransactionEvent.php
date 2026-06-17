<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;

/**
 * @extends TransactionEvent<void>
 */
class SaveItemTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.product_collection_item.save';

    public function __construct(
        private readonly AbstractProductCollection $collection,
        private readonly ProductCollectionModel $collectionModel,
        private readonly LineItem $lineItem,
        private readonly ProductCollectionItemModel $itemModel,
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

    public function getLineItem(): LineItem
    {
        return $this->lineItem;
    }

    public function getItemModel(): ProductCollectionItemModel
    {
        return $this->itemModel;
    }
}
