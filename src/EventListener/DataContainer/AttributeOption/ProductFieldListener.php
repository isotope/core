<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\AttributeOption;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\Input;

/**
 * Store the attribute field name for product options.
 */
#[AsCallback('tl_iso_attribute_option', 'config.onbeforesubmit')]
class ProductFieldListener
{
    public function __invoke(array $values, DataContainer $dc): array
    {
        if ('iso_products' === Input::get('do')) {
            $values['field_name'] = Input::get('field');
        }

        return $values;
    }
}
