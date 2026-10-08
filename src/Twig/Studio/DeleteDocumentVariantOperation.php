<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Studio;

use Contao\CoreBundle\DependencyInjection\Attribute\AsOperationForTemplateStudioElement;

/**
 * @internal
 */
#[AsOperationForTemplateStudioElement(name: 'delete', priority: -1)]
class DeleteDocumentVariantOperation extends AbstractDeleteVariantOperation
{
    protected function getPrefix(): string
    {
        return 'isotope/document';
    }

    #[\Override]
    protected function getDatabaseReferencesThatShouldBeMigrated(): array
    {
        return [
            'tl_iso_document.customTpl',
        ];
    }
}
