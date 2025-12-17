<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Exception;

use JetBrains\PhpStorm\Pure;

class MissingFactoryException extends \UnderflowException
{
    public function __construct(string $type, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct(sprintf('Missing product factory for type "%s"', $type), $code, $previous);
    }
}
