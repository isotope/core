<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Content;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Isotope\CoreBundle\Product\Action\ProductActionRegistry;

#[AsCallback('tl_content', 'fields.iso_buttons.options')]
class ProductActionOptionsListener
{
    public function __construct(private readonly ProductActionRegistry $productActionRegistry)
    {
    }

    public function __invoke(): array
    {
        return $this->productActionRegistry->getNames();
    }
}
