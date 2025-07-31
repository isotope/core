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

    #[AsCallback('tl_iso_gallery', 'fields.customTpl.options')]
    public function getGalleryTemplates(): array
    {
        return $this->getOptions('gallery', true);
    }

    #[AsCallback('tl_iso_producttype', 'fields.list_template.options')]
    public function productTypeList(): array
    {
        return $this->getOptions('product/list');
    }

    #[AsCallback('tl_iso_producttype', 'fields.reader_template.options')]
    public function productTypeReader(): array
    {
        return $this->getOptions('product/reader');
    }

    #[AsCallback('tl_module', 'fields.iso_list_layout.options')]
    public function moduleList(): array
    {
        return $this->getOptions('product/list', true);
    }

    #[AsCallback('tl_module', 'fields.iso_reader_layout.options')]
    public function moduleReader(): array
    {
        return $this->getOptions('product/reader', true);
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
