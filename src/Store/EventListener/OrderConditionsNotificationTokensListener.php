<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\EventListener;

use Codefog\HasteBundle\StringParser;
use Isotope\CoreBundle\ProductCollection\Extension\OrderConditionsExtension;
use Isotope\CoreBundle\Store\Event\CollectOrderNotificationTokensEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @internal
 */
#[AsEventListener]
class OrderConditionsNotificationTokensListener
{
    public function __construct(private readonly StringParser $stringParser)
    {
    }

    public function __invoke(CollectOrderNotificationTokensEvent $event): void
    {
        $extension = $event->getOrder()->getExtension(OrderConditionsExtension::class);

        if (!$extension) {
            return;
        }

        $tokens = [];
        $this->stringParser->flatten($extension->all(), 'form', $tokens);

        $event->addTokens($tokens);
    }
}
