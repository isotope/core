<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Studio;

use Contao\CoreBundle\DependencyInjection\Attribute\AsOperationForTemplateStudioElement;
use Contao\CoreBundle\Twig\Studio\Operation\OperationContext;

/**
 * @internal
 */
#[AsOperationForTemplateStudioElement]
final class CreateDocumentVariantOperation extends AbstractCreateVariantOperation
{
    protected function getPrefix(): string
    {
        return 'isotope/document';
    }
}
