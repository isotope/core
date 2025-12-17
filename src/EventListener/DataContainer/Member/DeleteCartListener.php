<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Member;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\DataContainer;
use Isotope\CoreBundle\Model\ProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Cart\CartStorage;

class DeleteCartListener
{
    public function __construct(private readonly CartStorage $storage)
    {
    }

    #[AsCallback('tl_member', 'config.ondelete')]
    public function onDeleteMember(DataContainer $dc): void
    {
        $this->deleteCarts((int) $dc->id);
    }

    #[AsHook('closeAccount')]
    public function onCloseAccount(int|string $userId): void
    {
        $this->deleteCarts((int) $userId);
    }

    private function deleteCarts(int $memberId): void
    {
        $carts = ProductCollection::findByMember($memberId, Cart::COLLECTION_TYPE);

        if (null !== $carts) {
            foreach ($carts as $cart) {
                $this->storage->delete($cart);
            }
        }
    }
}
