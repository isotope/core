<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Order;

use Isotope\CoreBundle\Model\ConfigModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;
use Isotope\CoreBundle\Store\Exception\ConfigNotFoundException;
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
        if (!($config = ConfigModel::findById($collection->config_id))) {
            throw ConfigNotFoundException::cannotRestoreOrder((int) $collection->config_id, (int) $collection->id);
        }

        $context = $this->contextFactory->create(
            $collection->store_id,
            $config,
            $collection->language,
            $collection->uniqid,
            $collection->member ?: null,
        );

        if ($collection->locked) {
            $context->setTimestamp(\DateTimeImmutable::createFromFormat('U', (string) $collection->locked));
        }

        return $this->storage->load(new Order($context->getIdentifier(), $context), $collection, $context);
    }
}
