<?php

declare(strict_types=1);

namespace Isotope\DependencyInjection\Compiler;

use Isotope\EventListener\RegisterHooksListener;
use Symfony\Component\Config\Definition\Exception\InvalidDefinitionException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
class RegisterHookListenersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $hooks = $this->getHooks($container);

        if (empty($hooks)) {
            return;
        }

        // Sort the listeners by priority
        foreach (array_keys($hooks) as $hook) {
            krsort($hooks[$hook]);
        }

        $listener = new Definition(RegisterHooksListener::class, [$hooks]);
        $listener->setPublic(true);
        $listener->addTag('contao.hook', ['hook' => 'initializeSystem', 'priority' => 255]);

        $container->setDefinition('isotope.listener.'.ContainerBuilder::hash($listener), $listener);
    }

    /**
     * @return array<string, array<int, array<string>>>
     */
    private function getHooks(ContainerBuilder $container): array
    {
        $hooks = [];
        $serviceIds = $container->findTaggedServiceIds('isotope.hook');

        foreach ($serviceIds as $serviceId => $tags) {
            if ($container->hasAlias($serviceId)) {
                $serviceId = (string) $container->getAlias($serviceId);
            }

            $definition = $container->findDefinition($serviceId);
            $definition->setPublic(true);

            foreach ($tags as $attributes) {
                $this->addHookCallback($hooks, $serviceId, $definition->getClass(), $attributes);
            }
        }

        return $hooks;
    }

    /**
     * @throws InvalidDefinitionException
     */
    private function addHookCallback(array &$hooks, string $serviceId, string $class, array $attributes): void
    {
        if (!isset($attributes['hook'])) {
            throw new InvalidDefinitionException(sprintf('Missing hook attribute in tagged hook service with service id "%s"', $serviceId));
        }

        $priority = (int) ($attributes['priority'] ?? 0);

        $hooks[$attributes['hook']][$priority][] = [$serviceId, $this->getMethod($attributes, $class, $serviceId)];
    }

    private function getMethod(array $attributes, string $class, string $serviceId): string
    {
        $ref = new \ReflectionClass($class);
        $invalid = sprintf('The isotope.hook definition for service "%s" is invalid. ', $serviceId);

        if (isset($attributes['method'])) {
            if (!$ref->hasMethod($attributes['method'])) {
                $invalid .= sprintf('The class "%s" does not have a method "%s".', $class, $attributes['method']);

                throw new InvalidDefinitionException($invalid);
            }

            if (!$ref->getMethod($attributes['method'])->isPublic()) {
                $invalid .= sprintf('The "%s::%s" method exists but is not public.', $class, $attributes['method']);

                throw new InvalidDefinitionException($invalid);
            }

            return (string) $attributes['method'];
        }

        $method = 'on'.ucfirst($attributes['hook']);
        $private = false;

        if ($ref->hasMethod($method)) {
            if ($ref->getMethod($method)->isPublic()) {
                return $method;
            }

            $private = true;
        }

        if ($ref->hasMethod('__invoke')) {
            return '__invoke';
        }

        if ($private) {
            $invalid .= sprintf('The "%s::%s" method exists but is not public.', $class, $method);
        } else {
            $invalid .= sprintf('Either specify a method name or implement the "%s" or __invoke method.', $method);
        }

        throw new InvalidDefinitionException($invalid);
    }
}
