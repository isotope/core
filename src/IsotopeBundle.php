<?php

declare(strict_types=1);

namespace Isotope\CoreBundle;

use Hashids\Hashids;
use Isotope\CoreBundle\Attribute\AttributeFactoryInterface;
use Isotope\CoreBundle\Backend\ShopConfig\ShopConfigInterface;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsCheckoutStep;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsGallery;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsIsotopeHook;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductAction;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionAction;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductCollectionItemAction;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFactory;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFilter;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsRuleCondition;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsShipping;
use Isotope\CoreBundle\DependencyInjection\Attribute\TypeAttributeInterface;
use Isotope\CoreBundle\DependencyInjection\Compiler\LoggerChannelFilePass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterActionPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterAttributePass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterHookListenersPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterModelTypePass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterProductFactoryPass;
use Isotope\CoreBundle\DependencyInjection\Compiler\RegisterProductFilterPass;
use Isotope\CoreBundle\ModelType\GalleryRegistry;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\Product\Action\ProductActionInterface;
use Isotope\CoreBundle\Product\Action\ProductActionRegistry;
use Isotope\CoreBundle\ProductCollection\Action\ProductCollectionActionInterface;
use Isotope\CoreBundle\ProductCollection\Action\ProductCollectionActionRegistry;
use Isotope\CoreBundle\ProductCollection\ItemAction\ProductCollectionItemActionInterface;
use Isotope\CoreBundle\ProductCollection\ItemAction\ProductCollectionItemActionRegistry;
use Isotope\CoreBundle\ProductCollection\OrderTransaction\TransactionStatus;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class IsotopeBundle extends AbstractBundle
{
    public function boot(): void
    {
        TransactionStatus::registerDefault('paid', '008c15');
        TransactionStatus::registerDefault('failed', 'ff0000');
        TransactionStatus::registerDefault('cancelled', '000000');
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterHookListenersPass());
        $container->addCompilerPass(new RegisterAttributePass(), priority: 10);
        $container->addCompilerPass(new RegisterProductFactoryPass(), priority: 10);
        $container->addCompilerPass(new RegisterProductFilterPass());
        $container->addCompilerPass(new RegisterModelTypePass(AsPayment::TAG_NAME, PaymentRegistry::class, ['tl_iso_payment.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(AsShipping::TAG_NAME, ShippingRegistry::class, ['tl_iso_shipping.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(AsGallery::TAG_NAME, GalleryRegistry::class, ['tl_iso_gallery.type']), priority: 10);
        $container->addCompilerPass(new RegisterModelTypePass(AsRuleCondition::TAG_NAME, RuleConditionRegistry::class, ['tl_iso_rule_condition.type'], true), priority: 10);
        $container->addCompilerPass(new RegisterActionPass(AsProductAction::TAG_NAME, ProductActionRegistry::class));
        $container->addCompilerPass(new RegisterActionPass(AsProductCollectionAction::TAG_NAME, ProductCollectionActionRegistry::class));
        $container->addCompilerPass(new RegisterActionPass(AsProductCollectionItemAction::TAG_NAME, ProductCollectionItemActionRegistry::class));
        $container->addCompilerPass(new LoggerChannelFilePass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->scalarNode('cart_timeout')
                    ->info('Defines how long guest carts are stored in the database before being purged.')
                    ->cannotBeEmpty()
                    ->defaultValue('30 days')
                    ->validate()
                        ->ifTrue(static fn (string $value) => false === strtotime($value))
                        ->thenInvalid('Enter a valid format that can be parsed by PHP strtotime().')
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $configurator, ContainerBuilder $container): void
    {
        $configurator->parameters()->set('isotope.cart_timeout', $config['cart_timeout']);

        $configurator
            ->services()
            ->load(__NAMESPACE__.'\\', '../src/')
            ->autoconfigure()
            ->autowire()
        ;

        $configurator
            ->services()
            ->set('isotope.hashids', Hashids::class)
            ->args(['%kernel.secret%', 8])
        ;

        $container
            ->registerForAutoconfiguration(ShopConfigInterface::class)
            ->addTag('isotope.shop_config')
        ;

        $container
            ->registerForAutoconfiguration(AttributeFactoryInterface::class)
            ->addTag('isotope.attribute_factory')
        ;

        $attributesForAutoconfiguration = [
            AsIsotopeHook::class => AsIsotopeHook::TAG_NAME,
            AsProductFilter::class => AsProductFilter::TAG_NAME,
            AsProductFactory::class => AsProductFactory::TAG_NAME,
            AsAttribute::class => AsAttribute::TAG_NAME,
            AsGallery::class => AsGallery::TAG_NAME,
            AsPayment::class => AsPayment::TAG_NAME,
            AsShipping::class => AsShipping::TAG_NAME,
            AsProductAction::class => AsProductAction::TAG_NAME,
            AsProductCollectionAction::class => AsProductCollectionAction::TAG_NAME,
            AsProductCollectionItemAction::class => AsProductCollectionItemAction::TAG_NAME,
            AsRuleCondition::class => AsRuleCondition::TAG_NAME,
            AsCheckoutStep::class => AsCheckoutStep::TAG_NAME,
        ];

        foreach ($attributesForAutoconfiguration as $attributeClass => $tag) {
            $container->registerAttributeForAutoconfiguration(
                $attributeClass,
                static function (ChildDefinition $definition, object $attribute, \Reflector $reflector) use ($attributeClass, $tag): void {
                    $tagAttributes = get_object_vars($attribute);

                    if ($reflector instanceof \ReflectionMethod) {
                        if (isset($tagAttributes['method'])) {
                            throw new LogicException(\sprintf('%s attribute cannot declare a method on "%s::%s()".', $attributeClass, $reflector->getDeclaringClass()->getName(), $reflector->getName()));
                        }

                        $tagAttributes['method'] = $reflector->getName();
                    }

                    if ($reflector instanceof \ReflectionClass && $attribute instanceof TypeAttributeInterface) {
                        $tagAttributes['type'] = $attribute->getType($reflector->getName());
                    }

                    $definition->addTag($tag, $tagAttributes);
                },
            );
        }
    }
}
