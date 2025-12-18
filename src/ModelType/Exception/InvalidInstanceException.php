<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType\Exception;

class InvalidInstanceException extends \LogicException
{
    public function __construct(object $instance, string|array $class, int $code = 0, \Throwable|null $previous = null)
    {
        $class = (array) $class;

        parent::__construct(\sprintf('%s is not an instance of %s', $instance::class, implode(' or', $class)), $code, $previous);
    }
}
