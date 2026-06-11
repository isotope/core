<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

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
        $seenTypes = [];

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            $tags = $definition->getTag($this->tagName);
            $createChildDefinition = \count($tags) > 1;

            // When creating child definitions, we clear the tag on the original service
            if ($createChildDefinition) {
                $definition->clearTag($this->tagName);
            }

            foreach ($tags as $attributes) {
                $type = $attributes['type'] ?? throw new LogicException('Tag '.$this->tagName.' requires a type');


                if ($createChildDefinition) {
                    $serviceId = $this->tagName.'._'.$type;
                    $actionDefinition = new ChildDefinition((string) $reference);
                    $actionDefinition->setTags($definition->getTags());
                    $actionDefinition->clearTag($this->tagName);
                    $actionReference = new Reference($serviceId);
                    $container->setDefinition($serviceId, $actionDefinition);
                } else {
                    $actionDefinition = $definition;
                    $actionReference = $reference;
                }

                 // Can only have one service per tag and type (highest priority wins)
                if (\in_array($type, $seenTypes, true)) {
                    $actionDefinition->clearTag($this->tagName);
                    continue;
                }

                $seenTypes[] = $type;

                $firstParam = (new \ReflectionClass($definition->getClass()))->getConstructor()->getParameters()[0];

                if ('type' === $firstParam->getName() && 'string' === $firstParam->getType()->getName()) {
                    $actionDefinition->setArgument(0, $type);
                }

                $actions[$type] = $actionReference;
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
