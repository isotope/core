<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Twig\Finder\FinderFactory;
use Contao\DataContainer;

#[AsCallback('tl_iso_gallery', 'fields.customTpl.options')]
class TemplateOptionsListener
{
    public function __construct(private readonly FinderFactory $finderFactory)
    {
    }

    public function __invoke(DataContainer $dc): array
    {
        $type = substr($dc->table, strrpos($dc->table, '_') + 1);

        return $this->finderFactory
            ->create()
            ->identifier('isotope/'.$type)
            ->extension('html.twig')
            ->withVariants(true)
            ->asTemplateOptions()
        ;
    }
}
