<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Attribute;

interface TypeAttributeInterface
{
    public function getType(string $className): string;
}
