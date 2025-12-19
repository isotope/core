<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Product\DataCollector\DataCollectorInterface;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DataCollector\ProductDtoCollection;
use Isotope\CoreBundle\Product\Exception\MissingFactoryException;
use Isotope\CoreBundle\Product\Factory\ProductFactoryInterface;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Contracts\Service\ServiceProviderInterface;

#[Autoconfigure(public: true)]
class ProductBuilder
{
    /**
     * @param array<DataCollectorInterface>                     $dataCollectors
     * @param ServiceProviderInterface<ProductFactoryInterface> $factories
     */
    public function __construct(
        private readonly ServiceProviderInterface $factories,
        #[AutowireIterator('isotope.product_data_collector')] private readonly iterable $dataCollectors,
    ) {
    }

    public function build(ProductDtoCollection $dtoCollection, StoreContext $context): ProductCollection
    {
        foreach ($this->dataCollectors as $dataCollector) {
            $dataCollector->collect($dtoCollection, $context);
        }

        $products = new ProductCollection();

        foreach ($dtoCollection as $dto) {
            if (null === $dto->getType()) {
                // Product not loaded, skip it
                continue;
            }

            if (!$this->factories->has($dto->getType())) {
                throw new MissingFactoryException($dto->getType());
            }

            $product = $this->factories->get($dto->getType())->create($dto, $context);

            $products->add($product);
        }

        return $products;
    }

    public function buildOne(ProductDto $dto, StoreContext $context): Product
    {
        return $this->build(new ProductDtoCollection([$dto]), $context)->first();
    }
}
