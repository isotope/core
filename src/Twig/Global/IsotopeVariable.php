<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Global;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\HttpFoundation\RequestStack;

class IsotopeVariable
{
    public function __construct(
        private readonly StoreManager $storeManager,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getContext(): StoreContext
    {
        return $this->storeManager->getCurrentContext();
    }

    public function getCart(): Cart|null
    {
        return $this->storeManager->getCurrentContext()->getCart();
    }

    public function getProduct(): CoreProduct|null
    {
        $product = $this->requestStack->getCurrentRequest()?->attributes->get('_content');

        return $product instanceof CoreProduct ? $product : null;
    }
}
