<?php

declare(strict_types=1);

namespace Isotope\CoreBundle;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsDocument;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductAction;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionItemAction;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\DependencyInjection\Compiler\FilterModulesPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterActionPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterAttributePass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterHookListenersPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterModelTypePass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterProductFactoryPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterProductFilterPass;
use Isotope\CoreBundle\DependencyInjection\IsotopeCoreExtension;
use Isotope\CoreBundle\ModelType\DocumentRegistry;
use Isotope\CoreBundle\ModelType\GalleryRegistry;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\Product\Action\ProductActionRegistry;
use Isotope\CoreBundle\ProductCollection\Action\ProductCollectionActionRegistry;
use Isotope\CoreBundle\ProductCollection\ItemAction\ProductCollectionItemActionRegistry;
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
        $container->addCompilerPass(new RegisterProductFactoryPass(), priority: 10);
        $container->addCompilerPass(new RegisterProductFilterPass());
        $container->addCompilerPass(new FilterModulesPass());
        $container->addCompilerPass(new RegisterModelTypePass(AsPayment::TAG_NAME, PaymentRegistry::class, ['tl_iso_payment.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(AsShipping::TAG_NAME, ShippingRegistry::class, ['tl_iso_shipping.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(AsDocument::TAG_NAME, DocumentRegistry::class, ['tl_iso_document.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(AsGallery::TAG_NAME, GalleryRegistry::class, ['tl_iso_gallery.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(AsRuleCondition::TAG_NAME, RuleConditionRegistry::class, ['tl_iso_rule_condition.type'], true), priority: 10);
        $container->addCompilerPass(new RegisterActionPass(AsProductAction::TAG_NAME, ProductActionRegistry::class));
        $container->addCompilerPass(new RegisterActionPass(AsProductCollectionAction::TAG_NAME, ProductCollectionActionRegistry::class));
        $container->addCompilerPass(new RegisterActionPass(AsProductCollectionItemAction::TAG_NAME, ProductCollectionItemActionRegistry::class));
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
