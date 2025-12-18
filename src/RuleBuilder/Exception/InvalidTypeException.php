<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Exception;

class InvalidTypeException extends \InvalidArgumentException
{
    public function __construct(string $type, string $class, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct(sprintf('Type "%s" is not supported by %s', $type, $class), $code, $previous);
    }
}
