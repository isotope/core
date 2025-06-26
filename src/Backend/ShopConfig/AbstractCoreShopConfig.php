<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

use Symfony\Component\Asset\Packages;

/**
 * @internal
 */
abstract class AbstractCoreShopConfig extends AbstractTableShopConfig
{
    public function __construct(private readonly Packages $assets)
    {
    }

    public function getIcon(): string|null
    {
        return $this->assets->getUrl('images/setup-'.$this->getName().'.png', 'isotope_core');
    }
}
