<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

namespace Isotope\CoreBundle\EventListener\DataContainer\Module;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

#[AsCallback('tl_module', 'fields.iso_order_conditions.fields.step.options')]
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
