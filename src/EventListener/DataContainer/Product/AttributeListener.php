<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Isotope\CoreBundle\Attribute\AttributeRegistry;

/**
 * Loads attributes into the tl_iso_product table.
 */
#[AsHook('loadDataContainer')]
class AttributeListener
{
    public function __construct(private readonly AttributeRegistry $attributeRegistry)
    {
    }

    public function __invoke(string $table): void
    {
        if ('tl_iso_product' !== $table) {
            return;
        }

        $this->attributeRegistry->load();
    }
}
