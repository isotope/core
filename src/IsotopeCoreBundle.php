<?php

declare(strict_types=1);

namespace Isotope\CoreBundle;

use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterAttributePass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterHookListenersPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterModelTypePass;
use Isotope\CoreBundle\DependencyInjection\IsotopeCoreExtension;
use Isotope\CoreBundle\ModelType\DocumentRegistry;
use Isotope\CoreBundle\ModelType\GalleryRegistry;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IsotopeCoreBundle extends Bundle
{
    public function getContainerExtension(): IsotopeCoreExtension
    {
        return new IsotopeCoreExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterHookListenersPass());
        $container->addCompilerPass(new RegisterAttributePass(), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(PaymentRegistry::TAG_NAME, PaymentRegistry::class, ['tl_iso_payment.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(ShippingRegistry::TAG_NAME, ShippingRegistry::class, ['tl_iso_shipping.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(DocumentRegistry::TAG_NAME, DocumentRegistry::class, ['tl_iso_document.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(GalleryRegistry::TAG_NAME, GalleryRegistry::class, ['tl_iso_gallery.type']), priority: 10);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
