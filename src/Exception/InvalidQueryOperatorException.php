<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Exception;

use Contao\CoreBundle\Exception\BadRequestException;

class InvalidQueryOperatorException extends BadRequestException
{
    public function __construct(
        public readonly string $operator,
        int $code = 0,
        \Throwable|null $previous = null,
    ) {
        $message = sprintf('Invalid product filter operator "%s"', $operator);

        parent::__construct($message, $code, $previous);
    }
}
