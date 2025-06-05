<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\AttributeOption;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;

/**
 * Disable "isDefault" for option groups.
 */
#[AsCallback('tl_iso_attribute_option', 'config.onbeforesubmit')]
class GroupNotDefaultListener
{
    public function __invoke(array $values, DataContainer $dc): array
    {
        $record = array_merge($dc->getCurrentRecord(), $values);

        if ('group' === $record['type'] && $record['isDefault']) {
            $values['isDefault'] = '';
        }

        return $values;
    }
}
