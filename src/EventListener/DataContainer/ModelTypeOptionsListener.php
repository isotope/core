<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\DataContainer;

class ModelTypeOptionsListener
{
    public function __construct(private readonly array $types)
    {
    }

    public function __invoke(DataContainer $dc): array
    {
        return $this->types;
    }
}
