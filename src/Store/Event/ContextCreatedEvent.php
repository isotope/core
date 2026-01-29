<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Event;

use Isotope\CoreBundle\Model\Config;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Contracts\EventDispatcher\Event;

class ContextCreatedEvent extends Event
{
    public function __construct(
        private readonly StoreContext $context,
        private readonly Config $config,
    ) {
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }
}
