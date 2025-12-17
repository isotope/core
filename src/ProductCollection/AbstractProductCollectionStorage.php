<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Doctrine\DBAL\Connection;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @template T of AbstractProductCollection
 */
abstract class AbstractProductCollectionStorage
{
    public function __construct(
        protected readonly Connection $connection,
    ) {
    }

    /**
     * @return AbstractProductCollection<T>|null
     */
    abstract public function load(StoreContext $context): AbstractProductCollection|null;

    /**
     * @param AbstractProductCollection $collection
     */
    abstract public function save(AbstractProductCollection $collection, StoreContext $context): void;

    public function delete(ProductCollection $collection): bool
    {
        $pid = $collection->id;
        $affectedRows = $collection->delete();

        if (!$pid || !$affectedRows) {
            return false;
        }

        $this->connection->executeStatement('
            DELETE FROM tl_iso_product_collection_download
            WHERE pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid=?)
        ', [$pid]);

        $this->connection->executeStatement(
            "DELETE FROM tl_iso_product_collection_item WHERE pid=?",
            [$pid],
        );

        $this->connection->executeStatement(
            'DELETE FROM tl_iso_product_collection_surcharge WHERE pid=?',
            [$pid]
        );

        $this->connection->executeStatement(
            "DELETE FROM tl_iso_address WHERE ptable='tl_iso_product_collection' AND pid=?",
            [$pid],
        );

        return true;
    }
}
