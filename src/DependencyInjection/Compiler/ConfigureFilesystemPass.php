<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Contao\CoreBundle\DependencyInjection\Filesystem\FilesystemConfiguration;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ConfigureFilesystemPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $config = new FilesystemConfiguration($container);
        $config
            ->mountLocalAdapter('var/isotope/documents', 'isotope/documents')
            ->addVirtualFilesystem('isotope_documents', 'isotope/documents')
        ;
    }
}
