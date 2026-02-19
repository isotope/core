<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Action;

use Symfony\Contracts\Service\ServiceProviderInterface;

class ProductCollectionActionRegistry
{
    public function __construct(private readonly ServiceProviderInterface $actions)
    {
    }

    public function get(string $name): ProductCollectionActionInterface|null
    {
        if (!$this->actions->has($name)) {
            return null;
        }

        return $this->actions->get($name);
    }
}
