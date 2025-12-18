<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType\Exception;

class MissingFactoryException extends \UnderflowException
{
    public function __construct(string $type, string $class, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct(\sprintf('Missing factory for %s of type "%s"', $class, $type), $code, $previous);
    }
}
