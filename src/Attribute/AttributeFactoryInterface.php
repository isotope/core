<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

interface AttributeFactoryInterface
{
    public function create(string $fieldName, array $config, array $dca): AttributeInterface|null;
}
