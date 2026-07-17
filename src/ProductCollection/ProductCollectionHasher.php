<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Isotope\CoreBundle\ProductCollection\Event\HashCollectionEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProductCollectionHasher
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function hash(AbstractProductCollection $collection): string
    {
        $data = [
            'items' => [],
            'total' => $collection->getTotal()?->getTotalPrice() ?? 0.0,
        ];

        foreach ($collection->getLineItems() as $item) {
            $data['items'][] = $item->getHashData();
        }

        $event = new HashCollectionEvent($collection, $data);
        $this->eventDispatcher->dispatch($event);

        return hash('xxh3', json_encode($event->getData(), JSON_THROW_ON_ERROR));
    }
}
