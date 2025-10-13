<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\StoreManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsEventListener]
class ContextCookieListener
{
    public function __construct(
        #[Autowire(param: 'isotope.cart_timeout')] private readonly int|string $cartTimeout,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $context = $event->getRequest()->attributes->get(StoreManager::CONTEXT_ATTRIBUTE);

        if (!$context instanceof StoreContext) {
            return;
        }

        $response = $event->getResponse();

        if (null !== $context->getMemberId()) {
            $response->headers->clearCookie(StoreManager::CONTEXT_COOKIE);
        } else {
            $response->headers->setCookie(new Cookie(StoreManager::CONTEXT_COOKIE, $context->getIdentifier(), $this->cartTimeout));
        }
    }
}
