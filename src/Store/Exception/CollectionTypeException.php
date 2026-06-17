<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Exception;

use Isotope\CoreBundle\Exception\LogicException;
use Isotope\CoreBundle\Model\ProductCollectionModel;

class CollectionTypeException extends LogicException
{
    public function __construct(string $expectedType, ProductCollectionModel|null $collection, int $code = 0, \Throwable|null $previous = null)
    {
        if (!$collection) {
            parent::__construct(\sprintf('Collection of type "%s" not found.', $expectedType), $code, $previous);
        } else {
            parent::__construct(\sprintf('Expected collection of type "%s", got type "%s".', $expectedType, $collection->type), $code, $previous);
        }
    }
}
