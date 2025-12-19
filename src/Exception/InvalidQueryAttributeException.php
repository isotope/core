<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Exception;

use Contao\CoreBundle\Exception\BadRequestException;

class InvalidQueryAttributeException extends BadRequestException
{
    public function __construct(
        public readonly string $attribute,
        int $code = 0,
        \Throwable|null $previous = null,
    ) {
        $message = \sprintf('Invalid product attribute "%s"', $attribute);

        parent::__construct($message, $code, $previous);
    }
}
