<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
class RegisterActionPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;


    public function __construct(
        private readonly string $tagName,
        private readonly string $registryId,
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $actions = [];
        $services = $this->findAndSortTaggedServices($this->tagName, $container);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag($this->tagName) as $attributes) {
                $name = $this->getName($definition, $attributes);

                $actions[$name] = $reference;
            }
        }

        $registry = $container->findDefinition($this->registryId);
        $registry->setArgument(0, ServiceLocatorTagPass::register($container, $actions));
    }

    private function getName(Definition $definition, array $attributes): string
    {
        if (isset($attributes['name'])) {
            return (string) $attributes['name'];
        }

        $className = $definition->getClass();
        $className = ltrim(strrchr((string) $className, '\\'), '\\');

        if (str_ends_with($className, 'Action')) {
            $className = substr($className, 0, -6);
        }

        return Container::underscore($className);
    }
}
