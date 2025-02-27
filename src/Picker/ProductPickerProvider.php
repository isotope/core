<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Picker;

use Contao\CoreBundle\DependencyInjection\Attribute\AsPickerProvider;
use Contao\CoreBundle\Picker\AbstractTablePickerProvider;

#[AsPickerProvider]
class ProductPickerProvider extends AbstractTablePickerProvider
{
    public function getName(): string
    {
        return 'productPicker';
    }

    protected function getDataContainer(): string
    {
        return 'ProductData';
    }
}
