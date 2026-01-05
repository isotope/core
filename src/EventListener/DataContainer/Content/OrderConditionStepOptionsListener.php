<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

#[AsCallback('tl_content', 'fields.iso_order_conditions.fields.step.options')]
class OrderConditionStepOptionsListener extends Backend
{
    public function __invoke(): array
    {
        $options = [];

        foreach ($GLOBALS['ISO_CHECKOUTSTEP'] as $group => $steps) {
            $groupLabel = $GLOBALS['TL_LANG']['MSC']['checkout_'.$group];

            foreach ($steps as $step) {
                $options[$groupLabel][$step] = $GLOBALS['TL_LANG']['CHECKOUT'][$step] ?? substr($step, strrpos($step, '\\') + 1);
            }
        }

        return $options;
    }
}
