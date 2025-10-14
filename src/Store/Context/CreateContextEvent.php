<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Isotope\Model\Config;
use Symfony\Contracts\EventDispatcher\Event;

class CreateContextEvent extends Event
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
