<?php

declare(strict_types=1);

namespace Isotope\Picker;

use Contao\CoreBundle\Picker\AbstractTablePickerProvider;

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
