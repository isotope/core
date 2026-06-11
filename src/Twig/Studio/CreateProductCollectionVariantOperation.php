<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Studio;

use Contao\CoreBundle\DependencyInjection\Attribute\AsOperationForTemplateStudioElement;

/**
 * @internal
 */
#[AsOperationForTemplateStudioElement]
final class CreateProductCollectionVariantOperation extends AbstractCreateVariantOperation
{
    protected function getPrefix(): string
    {
        return 'isotope/product_collection';
    }
}
