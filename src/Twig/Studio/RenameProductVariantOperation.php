<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Isotope\CoreBundle\Twig\Studio;

use Contao\CoreBundle\DependencyInjection\Attribute\AsOperationForTemplateStudioElement;

/**
 * @internal
 */
#[AsOperationForTemplateStudioElement]
final class RenameProductVariantOperation extends AbstractRenameVariantOperation
{
    protected function getPrefix(): string
    {
        return 'isotope/product';
    }

    protected function getDatabaseReferencesThatShouldBeMigrated(): array
    {
        return [
            'tl_content.iso_list_layout',
            'tl_content.iso_reader_layout',
            'tl_iso_producttype.list_template',
            'tl_iso_producttype.reader_template',
        ];
    }
}
