<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

use Symfony\Component\DependencyInjection\Container;

trait ClassTypeTrait
{
    private function removeSuffix(string $className, array $suffixes): string
    {
        $className = ltrim(strrchr($className, '\\'), '\\');

        foreach ($suffixes as $suffix) {
            if (str_ends_with($className, $suffix)) {
                $className = substr($className, 0, -strlen($suffix));
                break;
            }
        }

        return Container::underscore($className);
    }
}
