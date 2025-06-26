<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Backend\ShopConfig;

use Contao\Backend;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractTableShopConfig implements ShopConfigInterface
{
    public function getGroup(): string
    {
        return explode('_', $this->getClassName(), 2)[0];
    }

    public function getName(): string
    {
        return explode('_', $this->getClassName(), 2)[1];
    }

    public function getUrl(): string
    {
        // Last argument is forward-compatibility with Contao 5.5+ (do not add request token)
        return Backend::addToUrl('table='.$this->getTables()[0], true, []);
    }

    public function getIcon(): string|null
    {
        return null;
    }

    private function getClassName(): string
    {
        $className = ltrim(strrchr(static::class, '\\'), '\\');

        if (str_ends_with($className, 'Config')) {
            $className = substr($className, 0, -6);
        }

        if (str_ends_with($className, 'Shop')) {
            $className = substr($className, 0, -4);
        }

        return Container::underscore($className);
    }
}
