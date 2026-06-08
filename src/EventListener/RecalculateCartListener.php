<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener]
class RecalculateCartListener
{
    public function __construct(private readonly CartManager $cartManager)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $context = $event->getRequest()->attributes->get(StoreManager::CONTEXT_ATTRIBUTE);

        if (
            $context instanceof StoreContext
            && ($cart = $context->getCart())
            && $cart->isModified()
        ) {
            $this->cartManager->recalculate($cart, $context);
        }
    }
}
