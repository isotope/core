<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\EventDispatcher\TransactionDispatcher;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\ProductCollection\Event\DeleteCollectionTransaction;
use Isotope\CoreBundle\ProductCollection\Event\LoadCollectionTransaction;
use Isotope\CoreBundle\ProductCollection\Event\LoadItemTransaction;
use Isotope\CoreBundle\ProductCollection\Event\SaveCollectionTransaction;
use Isotope\CoreBundle\ProductCollection\Event\SaveItemTransaction;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;

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
    public function load(AbstractProductCollection $collection, ProductCollectionModel $collectionModel, StoreContext $context): AbstractProductCollection
    {
        $collection->setId((int) $collectionModel->id);

        return $this->transactionDispatcher->dispatch(
            new LoadCollectionTransaction($collection, $collectionModel, $context),
            function (LoadCollectionTransaction $transaction) {
                $this->loadLineItems($transaction->getCollection(), $transaction->getCollectionModel(), $transaction->getStoreContext());

                return $transaction->getCollection();
            },
        );
    }

    public function save(AbstractProductCollection $collection, StoreContext $context): void
    {
        $model = ProductCollectionModel::findByIdentifier($collection->getIdentifier(), $collection->getType(), $context->getStoreId());

        if (!$model) {
            $model = new ProductCollectionModel();
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
            function (SaveCollectionTransaction $transaction) {
                $transaction->getCollectionModel()->save();
            },
        );

        $this->saveLineItems($collection, $model);
    }

    public function delete(ProductCollectionModel $model): bool
    {
        if (!$model->id) {
            return false;
        }

        $success = $this->transactionDispatcher->dispatch(new DeleteCollectionTransaction((int) $model->id, $model), function (DeleteCollectionTransaction $transaction) {
            return $transaction->getCollectionModel()->delete() > 0;
        });

        if ($success) {
            $this->connection->executeStatement(
                'DELETE FROM tl_iso_product_collection_item WHERE pid=?',
                [$model->id],
            );

            $this->connection->executeStatement(
                "DELETE FROM tl_iso_address WHERE ptable='tl_iso_product_collection' AND pid=?",
                [$model->id],
            );
        }

        return $success;
    }

    protected function loadLineItems(AbstractProductCollection $collection, ProductCollectionModel $collectionModel, StoreContext $context): void
    {
        $models = ProductCollectionItemModel::findByCollection($collectionModel->id)?->getModels() ?? [];
        $subtotals = [];
        $total = null;

        foreach ($models as $itemModel) {
            $item = new LineItem(
                $itemModel->type,
                $itemModel->identifier,
                (int) $itemModel->quantity,
            );

            $item->setId($itemModel->id);
            $item->setSku($itemModel->sku);
            $item->setLabel($itemModel->label);
            $item->setReferencePage($itemModel->referencePage ?: null);
            $item->setLevel((int) $itemModel->level);

            if ($itemModel->price_data) {
                $item->setPrice(CalculatedPrice::createFromJson($itemModel->price_data));
            }

            if (\is_array($configuration = StringUtil::deserialize($itemModel->configuration))) {
                $item->setConfiguration($configuration);
            }

            $this->transactionDispatcher->dispatch(
                new LoadItemTransaction($collection, $item, $itemModel, $context),
                function (LoadItemTransaction $transaction) use (&$total, &$subtotals) {
                    $lineItem = $transaction->getLineItem();

                    switch ($lineItem->getType()) {
                        case LineItem::TYPE_TOTAL:
                            $total = $lineItem->getPrice();
                            break;

                        case LineItem::TYPE_SUBTOTAL:
                            $subtotals[$lineItem->getLevel()] = $lineItem->getPrice();
                            break;

                        default:
                            $transaction->getCollection()->getLineItems()->add($lineItem);
                            break;
                    }
                },
            );
        }

        $collection->setSubtotals($subtotals);
        $collection->setTotal($total);
    }

    protected function saveLineItems(AbstractProductCollection $collection, ProductCollectionModel $collectionModel): void
    {
        $itemIds = [0];

        foreach ($collection->getLineItems() as $lineItem) {
            if ($lineItem->isDynamic()) {
                continue;
            }

            $itemModel = ProductCollectionItemModel::findOneByCollection($collectionModel->id, $lineItem->getIdentifier(), $lineItem->getConfiguration());

            if (!$itemModel) {
                $itemModel = new ProductCollectionItemModel();
            }

            $itemModel->pid = $collectionModel->id;
            $itemModel->tstamp = time();
            $itemModel->type = $lineItem->getType();
            $itemModel->identifier = $lineItem->getIdentifier();
            $itemModel->configuration = $lineItem->getConfiguration();
            $itemModel->quantity = $lineItem->getQuantity();
            $itemModel->label = $lineItem->getLabel();
            $itemModel->sku = $lineItem->getSku();
            $itemModel->referencePage = $lineItem->getReferencePageId();

            if ($product = $lineItem->getProduct()) {
                //$itemModel->product_id = $product->getId();
                //$itemModel->type = $product->getType();
                $itemModel->label = $product->getName();
                $itemModel->sku = $product->getSku();
                // TODO: save price etc.
            }

            $this->transactionDispatcher->dispatch(
                new SaveItemTransaction($collection, $collectionModel, $lineItem, $itemModel),
                function (SaveItemTransaction $transaction) {
                    $transaction->getItemModel()->save();
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
