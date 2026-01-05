<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

trait DcaLabelTrait
{
    private function getDcaLabel(string $name, array $config): string
    {
        if (!isset($config['label'][0])) {
            return $name;
        }

        return sprintf(
            '%s <span class="label-info">[%s]</span>',
            $config['label'][0],
            $name,
        );
    }
}
