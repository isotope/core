<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Member;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\DataContainer;
use Isotope\Model\ProductCollection\Cart;

class DeleteCartListener
{
    public function __construct()
    {
    }

    #[AsCallback('tl_member', 'config.ondelete')]
    public function onDeleteMember(DataContainer $dc): void
    {
        $this->deleteCarts($dc->id);
    }

    #[AsHook('closeAccount')]
    public function onCloseAccount(int|string $userId): void
    {
        $this->deleteCarts((int) $userId);
    }

    private function deleteCarts(int $memberId): void
    {
        $carts = Cart::findBy('member', $memberId);

        if (null !== $carts) {
            foreach ($carts as $cart) {
                $cart->delete();
            }
        }
    }
}
