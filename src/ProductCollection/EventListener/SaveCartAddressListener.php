<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\SaveCollectionTransaction;
use Isotope\Model\Address;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(SaveCollectionTransaction::NAME.'.pre')]
class SaveCartAddressListener
{
    public function __invoke(SaveCollectionTransaction $event): void
    {
        $collection = $event->collection;

        if (!$collection instanceof Cart) {
            return;
        }

        $event->collectionModel->billing_address_id = $this->saveAddress($collection->getBillingAddress(), $event->collectionModel);
        $event->collectionModel->shipping_address_id = $this->saveAddress($collection->getShippingAddress(), $event->collectionModel);
    }

    private function saveAddress(Address|null $address, ProductCollection $collection): int
    {
        if (null === $address) {
            return 0;
        }

        if ($address->id) {
            $address->save();

            return (int) $address->id;
        }

        if (!$collection->id) {
            $collection->save();
        }

        $address->tstamp = time();
        $address->ptable = 'tl_iso_product_collection';
        $address->pid = $collection->id;
        $address->store_id = $collection->store_id;
        $address->save();

        return (int) $address->id;
    }
}
