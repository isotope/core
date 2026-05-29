<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;
use Isotope\CoreBundle\EventListener\DataContainer\ModelTypeOptionsListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * @internal
 */
class RegisterAttributePass implements CompilerPassInterface
{
    use ContainerRegistryTrait;
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $types = [];
        $services = $this->findAndSortTaggedServices(AsAttribute::TAG_NAME, $container);
        $registry = $container->getDefinition(AttributeRegistry::class);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag(AsAttribute::TAG_NAME) as $attributes) {
                $types[] = $attributes['type'] ?? throw new LogicException('Tag '.AsAttribute::TAG_NAME.' requires a type');
            }

            $this->registerContainer($reference, $definition, $container, $registry);

            $container->removeDefinition((string) $reference);
        }

        $listener = new Definition(ModelTypeOptionsListener::class, [$types]);
        $listener->addTag('contao.callback', ['table' => 'tl_iso_attribute', 'target' => 'fields.type.options']);
        $container->setDefinition('isotope.listener.attribute_type_options', $listener);
    }
}
