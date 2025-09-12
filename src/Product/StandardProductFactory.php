<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\Model;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\Model\Product;

/**
 * @extends ProductFactoryInterface<StandardProduct>
 */
class StandardProductFactory implements ProductFactoryInterface
{
    public function create(Model $model): StandardProduct
    {
        if (!$model instanceof Product) {
            throw new \LogicException(\sprintf('%s should be an instance of %s', $model::class, Product::class));
        }

        $productType = $model->getRelated('type');

        if (!$productType instanceof ProductType) {
            throw new \UnderflowException(\sprintf('ProductType for product ID %s not found', $model->id));
        }

        return new StandardProduct($model, $productType);
    }

    public function createFromId(string|int $id): StandardProduct
    {
        $product = Product::findById($id);

        if (!$product instanceof Product) {
            throw new \UnderflowException('Product with ID "'.$id.'" not found.');
        }

        return $this->create($product);
    }
}
