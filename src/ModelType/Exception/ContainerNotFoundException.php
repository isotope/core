<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class ContainerNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public function __construct(string $class, int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($class.' requires a service container but the registry does not have one.', $code, $previous);
    }
}
