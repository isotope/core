<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Event;

use Isotope\CoreBundle\EventDispatcher\Transaction;
use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @extends Transaction<AbstractProductCollection>
 */
class MergeGuestCartEvent extends Event
{
    public function __construct(
        public readonly AbstractProductCollection $cart,
        public readonly AbstractProductCollection $guestCart,
        public readonly StoreContext $context,
    ) {
    }
}
