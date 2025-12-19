<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\EventListener\DataContainer\ModelTypeOptionsListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
class RegisterAttributePass implements CompilerPassInterface
{
    use ContainerAwareTrait;
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $types = [];
        $services = $this->findAndSortTaggedServices('isotope.attribute', $container);
        $registry = $container->getDefinition(AttributeRegistry::class);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag('isotope.attribute') as $attributes) {
                $types[] = $this->getType($definition, $attributes);
            }

            $this->registerContainer($reference, $definition, $container, $registry);

            $container->removeDefinition((string) $reference);
        }

        $listener = new Definition(ModelTypeOptionsListener::class, [$types]);
        $listener->addTag('contao.callback', ['table' => 'tl_iso_attribute', 'target' => 'fields.type.options']);
        $container->setDefinition('isotope.listener.attribute_type_options', $listener);
    }

    private function getType(Definition $definition, array $attributes): string
    {
        if (isset($attributes['type'])) {
            return (string) $attributes['type'];
        }

        $className = $definition->getClass();
        $className = ltrim(strrchr((string) $className, '\\'), '\\');

        if (str_ends_with($className, 'Attribute')) {
            $className = substr($className, 0, -9);
        }

        return lcfirst(Container::camelize($className));
    }
}
