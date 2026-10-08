<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig\Studio;

use Contao\CoreBundle\DependencyInjection\Attribute\AsOperationForTemplateStudioElement;

/**
 * @internal
 */
#[AsOperationForTemplateStudioElement]
final class RenameDocumentVariantOperation extends AbstractRenameVariantOperation
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
