<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Exception;

use Isotope\CoreBundle\Exception\InvalidArgumentException;

class InvalidOperatorException extends InvalidArgumentException
{
    public function __construct(string $operator, string $class, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct(\sprintf('Operator "%s" is not supported by %s', $operator, $class), $code, $previous);
    }
}
