<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\EventDispatcher\TransactionDispatcher;
use Isotope\CoreBundle\Exception\InvalidArgumentException;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\Model\ProductCollectionItem;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\LoadCollectionTransaction;
use Isotope\CoreBundle\ProductCollection\Event\LoadItemTransaction;
use Isotope\CoreBundle\ProductCollection\Event\DeleteCollectionTransaction;
use Isotope\CoreBundle\ProductCollection\Event\SaveCollectionTransaction;
use Isotope\CoreBundle\ProductCollection\Event\SaveItemTransaction;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;

class ProductCollectionStorage
{
    public function __construct(
        protected readonly Connection $connection,
        protected readonly TransactionDispatcher $transactionDispatcher,
    ) {
    }

    /**
     * @template TType of AbstractProductCollection
     *
     * @param AbstractProductCollection<TType> $collection
     *
     * @return AbstractProductCollection<TType>
     */
    public function load(AbstractProductCollection $collection, ProductCollection $collectionModel, StoreContext $context): AbstractProductCollection
    {
        $collection->setId((int) $collectionModel->id);

        return $this->transactionDispatcher->dispatch(
            new LoadCollectionTransaction($collection, $collectionModel, $context),
            function (AbstractProductCollection $collection, ProductCollection $collectionModel, StoreContext $context) {
                $this->loadLineItems($collection, $collectionModel, $context);

                return $collection;
            },
        );
    }

    public function save(AbstractProductCollection $collection, StoreContext $context): void
    {
        // TODO: check if collection is locked

        $model = ProductCollection::findByIdentifier($collection->getIdentifier(), $collection->getType(), $context->getStoreId());

        if (!$model) {
            $model = new ProductCollection();
            $model->uniqid = $collection->getIdentifier();
            $model->type = $collection->getType();
            $model->store_id = $context->getStoreId();
        }

        $model->tstamp = time();
        $model->config_id = $context->getConfigId();
        $model->language = $context->getLocale();
        $model->currency = $context->getCurrency()->getCode();
        $model->member = (int) $context->getMemberId();

        // TODO: save price etc.

        $this->transactionDispatcher->dispatch(
            new SaveCollectionTransaction($collection, $model),
            function (AbstractProductCollection $collection, ProductCollection $collectionModel) {
                $collectionModel->save();
            },
        );

        $this->saveLineItems($collection, $model);
    }

    public function delete(ProductCollection $model): bool
    {
        return $this->transactionDispatcher->dispatch(new DeleteCollectionTransaction($model), function (ProductCollection $collectionModel) {
            $pid = $collectionModel->id;
            $affectedRows = $collectionModel->delete();

            if (!$pid || !$affectedRows) {
                return false;
            }

            $this->connection->executeStatement('
                DELETE FROM tl_iso_product_collection_download
                WHERE pid IN (SELECT id FROM tl_iso_product_collection_item WHERE pid=?)
            ', [$pid]);

            $this->connection->executeStatement(
                'DELETE FROM tl_iso_product_collection_item WHERE pid=?',
                [$pid],
            );

            $this->connection->executeStatement(
                "DELETE FROM tl_iso_address WHERE ptable='tl_iso_product_collection' AND pid=?",
                [$pid],
            );

            return true;
        });
    }

    protected function loadLineItems(AbstractProductCollection $collection, ProductCollection $collectionModel, StoreContext $context): void
    {
        $models = ProductCollectionItem::findByCollection($collectionModel->id)?->getModels() ?? [];

        foreach ($models as $itemModel) {
            $item = new LineItem(
                $itemModel->type,
                $itemModel->identifier,
                (int) $itemModel->quantity,
            );

            $item->setId($itemModel->id);
            $item->setSku($itemModel->sku);
            $item->setLabel($itemModel->label);
            $item->setLevel((int) $itemModel->level);

            if (\is_array($configuration = StringUtil::deserialize($itemModel->configuration))) {
                $item->setConfiguration($configuration);
            }

            $this->transactionDispatcher->dispatch(
                new LoadItemTransaction($collection, $item, $itemModel, $context),
                function (AbstractProductCollection $collection, LineItem $lineItem, ProductCollectionItem $itemModel, StoreContext $context) {
                    $collection->add($lineItem);
                },
            );
        }
    }

    protected function saveLineItems(AbstractProductCollection $collection, ProductCollection $collectionModel): void
    {
        $itemIds = [0];

        foreach ($collection->getLineItems() as $lineItem) {
            if ($lineItem->isDynamic()) {
                continue;
            }

            $itemModel = ProductCollectionItem::findOneByCollection($collectionModel->id, $lineItem->getIdentifier(), $lineItem->getConfiguration());

            if (!$itemModel) {
                $itemModel = new ProductCollectionItem();
            }

            $itemModel->pid = $collectionModel->id;
            $itemModel->tstamp = time();
            $itemModel->type = $lineItem->getType();
            $itemModel->identifier = $lineItem->getIdentifier();
            $itemModel->configuration = $lineItem->getConfiguration();
            $itemModel->quantity = $lineItem->getQuantity();
            $itemModel->label = $lineItem->getLabel();
            $itemModel->sku = $lineItem->getSku();

            if ($product = $lineItem->getProduct()) {
                //$itemModel->product_id = $product->getId();
                //$itemModel->type = $product->getType();
                $itemModel->label = $product->getName();
                $itemModel->sku = $product->getSku();
                // TODO: save price etc.
            }

            $this->transactionDispatcher->dispatch(
                new SaveItemTransaction($collection, $collectionModel, $lineItem, $itemModel),
                function (AbstractProductCollection $collection, ProductCollection $collectionModel, LineItem $lineItem, ProductCollectionItem $itemModel) {
                    $itemModel->save();
                },
            );

            $itemIds[] = $itemModel->id;
        }

        $this->connection->executeStatement(
            'DELETE FROM tl_iso_product_collection_item WHERE pid=? AND id NOT IN (?)',
            [$collectionModel->id, $itemIds],
            [Types::INTEGER, ArrayParameterType::INTEGER],
        );
    }
}
