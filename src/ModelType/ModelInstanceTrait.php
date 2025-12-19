<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\Model;
use Isotope\CoreBundle\ModelType\Exception\InvalidInstanceException;

trait ModelInstanceTrait
{
    private function validateInstanceOf(Model $model, array|string $class): void
    {
        $class = (array) $class;

        if ([] !== array_filter($class, static fn (string $c) => is_a($model, $c))) {
            return;
        }

        throw new InvalidInstanceException($model, $class);
    }
}
