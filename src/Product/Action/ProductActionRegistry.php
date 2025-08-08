<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Action;

use Symfony\Contracts\Service\ServiceProviderInterface;

class ProductActionRegistry
{
    public const TAG_NAME = 'isotope.product_action';

    public function __construct(private readonly ServiceProviderInterface $actions)
    {
    }

    public function get(string $name): ProductActionInterface|null
    {
        if (!$this->actions->has($name)) {
            return null;
        }

        return $this->actions->get($name);
    }

    /**
     * @return list<string>
     */
    public function getNames(): array
    {
        return array_keys($this->actions->getProvidedServices());
    }
}
