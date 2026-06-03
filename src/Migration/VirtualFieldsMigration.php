<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Migration;

/**
 * @internal
 */
class VirtualFieldsMigration extends AbstractVirtualFieldsMigration
{
    protected function getMapping(): array
    {
        return [
            'tl_iso_payment' => ['paypal_account'],
        ];
    }
}
