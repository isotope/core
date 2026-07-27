<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait ContainerAwareTrait
{
    protected ContainerInterface $container;

    #[Required]
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $id
     *
     * @return T
     */
    protected function get(string $id): object
    {
        return $this->container->get($id);
    }
}
