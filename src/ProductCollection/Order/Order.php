<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Order;

use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;

class Order extends AbstractProductCollection
{
    private const string COLLECTION_TYPE = 'order';

    public static function getType(): string
    {
        return self::COLLECTION_TYPE;
    }
}
