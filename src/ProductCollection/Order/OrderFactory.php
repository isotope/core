<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Order;

use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;
use Isotope\CoreBundle\Util\StringUtil;

class OrderFactory
{
    public function __construct(
        private readonly ProductCollectionStorage $storage,
        private readonly StoreContextFactory $contextFactory,
    ) {
    }

    public function loadFromId(int $id): Order|null
    {
        $collection = ProductCollectionModel::findById($id);

        if (!$collection || Order::getType() !== $collection->type) {
            return null;
        }

        return $this->load($collection);
    }

    public function loadFromIdentifier(string $identifier): Order|null
    {
        $collection = ProductCollectionModel::findByIdentifier($identifier, Order::getType());

        if (!$collection) {
            return null;
        }

        return $this->load($collection);
    }

    public function load(ProductCollectionModel $collection): Order
    {
        $context = $this->contextFactory->create(
            $collection->store_id,
            Config::findById($collection->config_id),
            $collection->language ?? $GLOBALS['TL_LANGUAGE'], // TODO: a collection must always have a language!
            $collection->uniqid ?? StringUtil::generateRandomId(), // TODO: a collection must always have an ID!
            $collection->member ?: null,
        );

        return $this->storage->load(new Order($context->getIdentifier(), $context), $collection, $context);
    }
}
