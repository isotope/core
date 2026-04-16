<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;

class SaveItemTransaction extends Transaction
{
    public const string NAME = 'isotope.product_collection_item.save';

    public function __construct(
        public readonly AbstractProductCollection $collection,
        public readonly ProductCollectionModel $collectionModel,
        public readonly LineItem $lineItem,
        public readonly ProductCollectionItemModel $itemModel,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }
}
