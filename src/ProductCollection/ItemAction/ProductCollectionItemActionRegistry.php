<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\ItemAction;

use Symfony\Contracts\Service\ServiceProviderInterface;

class ProductCollectionItemActionRegistry
{
    public const TAG_NAME = 'isotope.product_collection_item_action';

    public function __construct(private readonly ServiceProviderInterface $actions)
    {
    }

    public function get(string $name): ProductCollectionItemActionInterface|null
    {
        if (!$this->actions->has($name)) {
            return null;
        }

        return $this->actions->get($name);
    }
}
