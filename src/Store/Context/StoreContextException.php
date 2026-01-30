<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Context;

use Isotope\CoreBundle\Exception\RuntimeException;

class StoreContextException extends RuntimeException
{
    public static function noRootPage(): self
    {
        return new self('No root page found.');
    }

    public static function noConfig(): self
    {
        return new self('No shop config selected in the root page.');
    }

    public static function noRequestAvailable(): self
    {
        return new self('No request available.');
    }
}
