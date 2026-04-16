<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Exception;

use Isotope\CoreBundle\Exception\LogicException;

class EmptyCartException extends LogicException
{
    public function __construct(string $message = '', int $code = 0, \Throwable|null $previous = null)
    {
        parent::__construct($message ?: 'The cart is empty.', $code, $previous);
    }
}
