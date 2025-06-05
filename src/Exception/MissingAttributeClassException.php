<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Exception;

use JetBrains\PhpStorm\Pure;

class MissingAttributeClassException extends \UnderflowException
{
    #[Pure] public function __construct(
        public readonly string $name,
        public readonly array $config,
        public readonly array $dca,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct('Missing attribute class for field "'.$name.'"', $code, $previous);
    }
}
