<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\EventListener;

use Contao\FrontendUser;
use Isotope\CoreBundle\Model\Address;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\SaveCollectionTransaction;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(SaveCollectionTransaction::NAME.'.pre')]
class SaveCartMetadataListener
{
    public function __construct(private readonly StoreManager $storeManager)
    {
    }

    public function __invoke(SaveCollectionTransaction $event): void
    {
        $collection = $event->collection;

        if (!$collection instanceof Cart) {
            return;
        }

        $event->collectionModel->billing_address_id = $this->saveAddress($collection->getBillingAddress(), $event->collectionModel);
        $event->collectionModel->shipping_address_id = $this->saveAddress($collection->getShippingAddress(), $event->collectionModel);
        $event->collectionModel->shipping_id = $collection->getShippingMethod()?->getId() ?? 0;
        $event->collectionModel->payment_id = $collection->getPaymentMethod()?->getId() ?? 0;
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

        if ($memberId = $this->storeManager->getCurrentContext()->getMemberId()) {
            $address->ptable = 'tl_member';
            $address->pid = $memberId;
        } else {
            $address->ptable = 'tl_iso_product_collection';
            $address->pid = $collection->id;
        }

        $address->tstamp = time();
        $address->store_id = $collection->store_id;
        $address->save();

        return (int) $address->id;
    }
}
