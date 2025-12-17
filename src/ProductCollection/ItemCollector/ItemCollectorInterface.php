<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemCollector;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;

interface ItemCollectorInterface
{
    public function process(Cart $cart, Cart $original, StoreContext $context): void;
}
