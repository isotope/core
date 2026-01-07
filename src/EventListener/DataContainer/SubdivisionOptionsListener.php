<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Intl\Subdivisions;

#[AsCallback('tl_iso_config', 'fields.subdivision.options')]
#[AsCallback('tl_iso_shipping', 'fields.subdivisions.options')]
#[AsCallback('tl_iso_address', 'fields.subdivision.options')]
class SubdivisionOptionsListener
{
    public function __construct(private readonly Subdivisions $subdivisions)
    {
    }

    public function __invoke(): array
    {
        return $this->subdivisions->all();
    }
}
