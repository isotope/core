<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\Model;
use Isotope\CoreBundle\Product\CollectibleInterface;
use Isotope\CoreBundle\Product\ProductFactoryInterface;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\Model\Product;
use Isotope\Model\ProductCollectionItem;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<CollectibleInterface, Product>
 */
#[Autoconfigure(public: true)]
class ProductRegistry extends AbstractModelTypeRegistry
{
    public const TAG_NAME = 'isotope.product';

    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, ProductInterface::class);
    }

    protected function create(Model $model): CollectibleInterface
    {
        $instance = match (true) {
            $model instanceof ProductCollectionItem => $this->createFromIdentifier($model->identifier),
            $model instanceof Product => $this->getFactory($model->getType()->class)->create($model),
            default => throw new \RuntimeException($model::class.' must be an instance of '.Product::class.' or '.ProductCollectionItem::class),
        };

        return $instance;
    }

    private function createFromIdentifier(string $identifier): CollectibleInterface
    {
        [$type, $id] = explode('.', $identifier, 2);

        return $this->getFactory($type)->createFromId($id);
    }

    private function getFactory(string $type): ProductFactoryInterface
    {
        try {
            return $this->factories->get($type);
        } catch (ServiceNotFoundException $e) {
            throw new \RuntimeException(\sprintf('Missing factory for product type "%s"', $type), previous: $e);
        }
    }
}
