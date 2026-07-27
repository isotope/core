<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Symfony\Contracts\Service\ServiceProviderInterface;

class ProductActionRegistry
{
    /**
     * @param ServiceProviderInterface<ProductActionInterface> $actions
     */
    public function __construct(private readonly ServiceProviderInterface $actions)
    {
    }

    public function get(string $type): ProductActionInterface|null
    {
        if (!$this->actions->has($type)) {
            return null;
        }

        return $this->actions->get($type);
    }

    /**
     * @return list<string>
     */
    public function getNames(): array
    {
        return array_keys($this->actions->getProvidedServices());
    }
}
