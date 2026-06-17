<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionItemAction;
use Symfony\Contracts\Service\ServiceProviderInterface;

#[AsProductCollectionItemAction]
class ProductCollectionItemActionRegistry
{
    /**
     * @param ServiceProviderInterface<ProductCollectionItemActionInterface> $actions
     */
    public function __construct(private readonly ServiceProviderInterface $actions)
    {
    }

    public function get(string $type): ProductCollectionItemActionInterface|null
    {
        if (!$this->actions->has($type)) {
            return null;
        }

        return $this->actions->get($type);
    }
}
