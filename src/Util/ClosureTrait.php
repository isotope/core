<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Util;

/**
 * Trait to evaluate closures to retrieve a property value.
 */
trait ClosureTrait
{
    private function resolveClosure(mixed &$property): void
    {
        if ($property instanceof \Closure) {
            $property = $property($this);
        }
    }
}
