<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\EventListener\DataContainer\ModelTypeOptionsListener;
use Isotope\CoreBundle\Product\ProductBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
class RegisterProductFactoryPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    private const string TAG_NAME = 'isotope.product_factory';

    public function process(ContainerBuilder $container): void
    {
        $types = [];
        $factories = [];
        $services = $this->findAndSortTaggedServices(self::TAG_NAME, $container);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag(self::TAG_NAME) as $attributes) {
                $type = $this->getType($definition, $attributes);
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

    private function getType(Definition $definition, array $attributes): string
    {
        if (isset($attributes['type'])) {
            return (string) $attributes['type'];
        }

        $className = $definition->getClass();
        $className = ltrim(strrchr((string) $className, '\\'), '\\');

        if (str_ends_with($className, 'ProductFactory')) {
            $className = substr($className, 0, -14);
        } elseif (str_ends_with($className, 'Factory')) {
            $className = substr($className, 0, -7);
        }

        return Container::underscore($className);
    }
}
