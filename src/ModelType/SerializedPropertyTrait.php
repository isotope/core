<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Contao\StringUtil;

trait SerializedPropertyTrait
{
    private function unserialize(string|null $value, callable|null $callable = null): array|null
    {
        $values = StringUtil::deserialize($value);

        if (!\is_array($values) || [] === $values) {
            return null;
        }

        if ($callable) {
            return array_map($callable, $values);
        }

        return $values;
    }
}
