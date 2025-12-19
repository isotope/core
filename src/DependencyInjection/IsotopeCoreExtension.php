<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection;

use Isotope\CoreBundle\Attribute\AttributeFactoryInterface;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Backend\ShopConfig\ShopConfigInterface;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsDocument;
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
use Isotope\CoreBundle\ModelType\DocumentRegistry;
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
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class IsotopeCoreExtension extends ConfigurableExtension
{
    public function getAlias(): string
    {
        return 'isotope';
    }

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $container->setParameter('isotope.cart_timeout', $mergedConfig['cart_timeout']);
        $container->setParameter('isotope.order_timeout', $mergedConfig['order_timeout']);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../config'),
        );

        $loader->load('services.yaml');

        $container
            ->registerForAutoconfiguration(ShopConfigInterface::class)
            ->addTag('isotope.shop_config')
        ;

        $container
            ->registerForAutoconfiguration(AttributeFactoryInterface::class)
            ->addTag('isotope.attribute_factory')
        ;

        $container
            ->registerForAutoconfiguration(ProductActionInterface::class)
            ->addTag('isotope.product_action')
        ;

        $container
            ->registerForAutoconfiguration(ProductCollectionActionInterface::class)
            ->addTag('isotope.product_collection_action')
        ;

        $container
            ->registerForAutoconfiguration(ProductCollectionItemActionInterface::class)
            ->addTag('isotope.product_collection_item_action')
        ;

        $attributesForAutoconfiguration = [
            AsIsotopeHook::class => 'isotope.hook',
            AsProductFilter::class => 'isotope.product_filter',
            AsProductFactory::class => 'isotope.product_factory',
            AsAttribute::class => AttributeRegistry::TAG_NAME,
            AsDocument::class => DocumentRegistry::TAG_NAME,
            AsGallery::class => GalleryRegistry::TAG_NAME,
            AsPayment::class => PaymentRegistry::TAG_NAME,
            AsShipping::class => ShippingRegistry::TAG_NAME,
            AsProductAction::class => ProductActionRegistry::TAG_NAME,
            AsProductCollectionAction::class => ProductCollectionActionRegistry::TAG_NAME,
            AsProductCollectionItemAction::class => ProductCollectionItemActionRegistry::TAG_NAME,
            AsRuleCondition::class => RuleConditionRegistry::TAG_NAME,
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

                    $definition->addTag($tag, $tagAttributes);
                },
            );
        }
    }
}
