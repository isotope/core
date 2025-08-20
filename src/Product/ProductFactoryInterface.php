<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\ModelType\ModelTypeFactoryInterface;
use Isotope\Model\Product;

/**
 * @template TType of ProductInterface
 *
 * @extends ModelTypeFactoryInterface<TType, Product>
 */
interface ProductFactoryInterface extends ModelTypeFactoryInterface
{
    public function createFromId(string|int $id): CollectibleInterface;
}
