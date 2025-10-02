<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

interface ContainerAwareInterface extends ServiceSubscriberInterface
{
    /**
     * Sets the container for subscribed services.
     */
    public function setContainer(ContainerInterface $container): void;
}
