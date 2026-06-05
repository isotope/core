<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEvent;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;

class LoadItemTransactionEvent extends TransactionEvent
{
    public const string NAME = 'isotope.product_collection_item.load';

    public function __construct(
        private readonly AbstractProductCollection $collection,
        private readonly LineItem $lineItem,
        private readonly ProductCollectionItemModel $itemModel,
        private readonly StoreContext $context,
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

    public function getLineItem(): LineItem
    {
        return $this->lineItem;
    }

    public function getItemModel(): ProductCollectionItemModel
    {
        return $this->itemModel;
    }

    public function getStoreContext(): StoreContext
    {
        return $this->context;
    }
}
