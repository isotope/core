<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

class ModuleToElementMigration extends AbstractModuleToElementMigration
{
    protected function getMapping(): array
    {
        return [
            'iso_productlist' => 'iso_productlist',
            'iso_productreader' => 'iso_productreader',
            'iso_cart' => 'iso_cart',
            'iso_configswitcher' => 'iso_configswitcher',
            'iso_orderhistory' => 'iso_orderhistory',
            'iso_orderdetails' => 'iso_orderdetails',
            'iso_productfilter' => 'iso_productfilter',
            'iso_categoryfilter' => 'iso_categoryfilter',
            'iso_relatedproducts' => 'iso_relatedproducts',
            'iso_checkout' => 'iso_checkout',
        ];
    }
}
