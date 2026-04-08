<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Page;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Symfony\Component\Asset\Packages;

#[AsHook('getPageStatusIcon')]
class StatusIconListener
{
    public function __construct(private readonly Packages $packages)
    {
    }

    public function __invoke(object $row, string $icon): string
    {
        if ('iso_products' !== $row->type) {
            return $icon;
        }

        return $this->packages->getUrl('images/page'.substr($icon, 7), 'isotope');
    }
}
