<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\Twig\Studio\CreateDocumentVariantOperation;
use Isotope\CoreBundle\Twig\Studio\CreateProductCollectionVariantOperation;
use Isotope\CoreBundle\Twig\Studio\CreateProductVariantOperation;
use Isotope\CoreBundle\Twig\Studio\DeleteDocumentVariantOperation;
use Isotope\CoreBundle\Twig\Studio\DeleteProductVariantOperation;
use Isotope\CoreBundle\Twig\Studio\RenameDocumentVariantOperation;
use Isotope\CoreBundle\Twig\Studio\RenameProductCollectionVariantOperation;
use Isotope\CoreBundle\Twig\Studio\RenameProductVariantOperation;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class DisabledTemplateStudioPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('contao.twig.studio.template_skeleton_factory')) {
            return;
        }

        $container->removeDefinition(CreateProductVariantOperation::class);
        $container->removeDefinition(CreateProductCollectionVariantOperation::class);
        $container->removeDefinition(CreateDocumentVariantOperation::class);

        $container->removeDefinition(RenameProductVariantOperation::class);
        $container->removeDefinition(RenameProductCollectionVariantOperation::class);
        $container->removeDefinition(RenameDocumentVariantOperation::class);

        $container->removeDefinition(DeleteProductVariantOperation::class);
        $container->removeDefinition(DeleteDocumentVariantOperation::class);
    }
}
