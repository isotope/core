<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\SaveCollectionTransactionEvent;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(SaveCollectionTransactionEvent::NAME.'.pre')]
class SaveCartMetadataListener
{
    public function __construct(private readonly StoreManager $storeManager)
    {
    }

    public function __invoke(SaveCollectionTransactionEvent $event): void
    {
        $collection = $event->getCollection();

        if (!$collection instanceof Cart) {
            return;
        }

        $model = $event->getCollectionModel();
        $model->billing_address_id = $this->saveAddress($collection->getBillingAddress(), $model);
        $model->shipping_address_id = $this->saveAddress($collection->getShippingAddress(), $model);
        $model->shipping_id = $collection->getShippingMethod()?->getId() ?? 0;
        $model->payment_id = $collection->getPaymentMethod()?->getId() ?? 0;
    }

    private function saveAddress(AddressModel|null $address, ProductCollectionModel $collection): int
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

        if (!$address->ptable || !$address->pid) {
            if ($memberId = $this->storeManager->getCurrentContext()->getMemberId()) {
                $address->ptable = 'tl_member';
                $address->pid = $memberId;
            } else {
                $address->ptable = 'tl_iso_product_collection';
                $address->pid = $collection->id;
            }
        }

        $address->tstamp = time();
        $address->store_id = $collection->store_id;
        $address->save();

        return (int) $address->id;
    }
}
