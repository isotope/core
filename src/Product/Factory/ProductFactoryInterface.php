<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Factory;

use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Store\Context\StoreContext;

interface ProductFactoryInterface
{
    public function create(ProductDto $dto, StoreContext $context): Product;
}
