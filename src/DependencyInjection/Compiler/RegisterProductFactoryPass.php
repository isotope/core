<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFactory;
use Isotope\CoreBundle\EventListener\DataContainer\ModelTypeOptionsListener;
use Isotope\CoreBundle\Product\ProductBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @internal
 */
class RegisterProductFactoryPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $types = [];
        $factories = [];
        $services = $this->findAndSortTaggedServices(AsProductFactory::TAG_NAME, $container);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag(AsProductFactory::TAG_NAME) as $attributes) {
                $type = $attributes['type'] ?? throw new LogicException('Tag '.AsProductFactory::TAG_NAME.' requires a type');
                $types[] = $type;
                $factories[$type] = $reference;
            }
        }

        $container
            ->getDefinition(ProductBuilder::class)
            ->setArgument(0, ServiceLocatorTagPass::register($container, $factories))
        ;

        $listener = new Definition(ModelTypeOptionsListener::class, [$types]);
        $listener->addTag('contao.callback', ['table' => 'tl_iso_producttype', 'target' => 'fields.class.options']);
        $container->setDefinition('isotope.listener.product_type_options', $listener);
    }
}
