<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;

class SaveCollectionTransaction extends Transaction
{
    public const string NAME = 'isotope.product_collection.save';

    public function __construct(
        public readonly AbstractProductCollection $collection,
        public readonly ProductCollectionModel $collectionModel,
    ) {
    }

    public function getEventName(): string
    {
        return self::NAME;
    }
}
