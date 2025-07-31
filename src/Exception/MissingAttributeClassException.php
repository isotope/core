<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Exception;

class MissingAttributeClassException extends \UnderflowException
{
    public function __construct(
        public readonly string $name,
        public readonly array $config,
        public readonly array $dca,
        int $code = 0,
        \Throwable|null $previous = null,
    ) {
        parent::__construct('Missing attribute class for field "'.$name.'"', $code, $previous);
    }
}
