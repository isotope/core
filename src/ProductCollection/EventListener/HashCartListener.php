<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\HashCollectionEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class HashCartListener
{
    public function __invoke(HashCollectionEvent $event): void
    {
        $collection = $event->getCollection();

        if (!$collection instanceof Cart) {
            return;
        }

        $event->add('shipping', $collection->getShippingMethod()?->getId());
        $event->add('payment', $collection->getPaymentMethod()?->getId());
    }
}
