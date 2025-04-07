<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\EventListener\DataContainer\ModelTypeOptionsListener;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @internal
 */
class RegisterModelTypePass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function __construct(
        private readonly string $tagName,
        private readonly string $registryId,
        private readonly array $dcaOptions,
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $types = [];
        $factories = [];
        $services = $this->findAndSortTaggedServices($this->tagName, $container);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag($this->tagName) as $attributes) {
                $type = $this->getType($definition, $attributes);
                $types[] = $type;
                $factories[$type] = new ServiceClosureArgument(new Reference($this->getFactory($definition, $attributes)));
            }

            $container->removeDefinition((string) $reference);
        }

        $registry = $container->getDefinition($this->registryId);
        $locator = new Definition(ServiceLocator::class);
        $locator->setArguments([$factories]);
        $registry->setArgument(0, $locator);

        if ([] === $this->dcaOptions) {
            return;
        }

        $listener = new Definition(ModelTypeOptionsListener::class, [$types]);

        foreach ($this->dcaOptions as $key) {
            [$table, $field] = explode('.', (string) $key, 2);
            $listener->addTag('contao.callback', ['table' => $table, 'target' => "fields.$field.options"]);
        }

        [,$name] = explode('.', $this->tagName, 2);

        $container->setDefinition('isotope.listener.'.$name.'_type_options', $listener);
    }

    protected function getType(Definition $definition, array $attributes): string
    {
        if (isset($attributes['type'])) {
            return (string) $attributes['type'];
        }

        $className = $definition->getClass();
        $className = ltrim(strrchr((string) $className, '\\'), '\\');
        [,$suffix] = explode('.', $this->tagName);

        if (str_ends_with($className, ucfirst($suffix))) {
            $className = substr($className, 0, \strlen($suffix) * -1);
        }

        return Container::underscore($className);
    }

    private function getFactory(Definition $definition, array $attributes): string
    {
        if (isset($attributes['factory'])) {
            return (string) $attributes['factory'];
        }

        $className = $definition->getClass();

        if (class_exists($className.'Factory')) {
            return $className.'Factory';
        }

        throw new LogicException('Missing factory service for '.$className);
    }
}
