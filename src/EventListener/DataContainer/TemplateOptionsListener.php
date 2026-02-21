<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Twig\Finder\FinderFactory;

class TemplateOptionsListener
{
    public function __construct(private readonly FinderFactory $finderFactory)
    {
    }

    #[AsCallback('tl_iso_producttype', 'fields.list_template.options')]
    #[AsCallback('tl_iso_producttype', 'fields.reader_template.options')]
    #[AsCallback('tl_content', 'fields.iso_list_layout.options')]
    #[AsCallback('tl_content', 'fields.iso_reader_layout.options')]
    public function getProductTemplates(): array
    {
        return $this->getOptions('product', true);
    }

    private function getOptions(string $type, bool $variantsExclusive = false): array
    {
        return $this->finderFactory
            ->create()
            ->identifier('isotope/'.$type)
            ->extension('html.twig')
            ->withVariants($variantsExclusive)
            ->asTemplateOptions()
        ;
    }
}
