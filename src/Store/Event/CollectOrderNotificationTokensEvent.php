<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\ProductCollection\Order\Order;
use Symfony\Contracts\EventDispatcher\Event;
use Terminal42\NotificationCenterBundle\Config\NotificationConfig;

class CollectOrderNotificationTokensEvent extends Event
{
    public function __construct(
        private readonly Order $order,
        private readonly NotificationConfig $notificationConfig,
        private array $tokens = [],
    ) {
    }

    public function getNotificationConfig(): NotificationConfig
    {
        return $this->notificationConfig;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setTokens(array $tokens): self
    {
        $this->tokens = $tokens;
        return $this;
    }

    public function addTokens(array $tokens): self
    {
        $this->tokens = array_replace($this->tokens, $tokens);

        return $this;
    }

    public function addToken(string $key, string $value): self
    {
        $this->tokens[$key] = $value;

        return $this;
    }
}
