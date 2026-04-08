<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface as SymfonyContainerInterface;

trait ContainerAwareTrait
{
    protected ContainerInterface $container;

    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @template T
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    protected function get(string $id): object
    {
        return $this->container->get($id, SymfonyContainerInterface::EXCEPTION_ON_INVALID_REFERENCE);
    }
}
