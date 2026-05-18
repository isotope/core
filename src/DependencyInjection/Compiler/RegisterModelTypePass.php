<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\EventListener\DataContainer\ModelTypeOptionsListener;
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
class RegisterModelTypePass implements CompilerPassInterface
{
    use ContainerRegistryTrait;
    use LoggerRegistryTrait;
    use PriorityTaggedServiceTrait;

    public function __construct(
        private readonly string $tagName,
        private readonly string $registryId,
        private readonly array $dcaOptions,
        private readonly bool $withCategory = false,
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $types = [];
        $factories = [];
        $services = $this->findAndSortTaggedServices($this->tagName, $container);
        $registry = $container->getDefinition($this->registryId);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag($this->tagName) as $attributes) {
                $type = $this->getType($definition, $attributes);

                if ($this->withCategory) {
                    $category = $attributes['category'] ?? 'miscellaneous';
                    $types[$category][] = $type;
                } else {
                    $types[] = $type;
                }
                $factories[$type] = new Reference($this->getFactory($definition, $attributes));
            }

            $this->registerContainer($reference, $definition, $container, $registry);
            $this->registerLogger($reference, $definition, $registry, $this->tagName);

            $container->removeDefinition((string) $reference);
        }

        $registry->setArgument(0, ServiceLocatorTagPass::register($container, $factories));

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

    private function getType(Definition $definition, array $attributes): string
    {
        if (isset($attributes['type'])) {
            return (string) $attributes['type'];
        }

        $className = $definition->getClass();
        $className = ltrim(strrchr((string) $className, '\\'), '\\');
        $suffix = preg_split('/[_.]/', $this->tagName);

        foreach ($suffix as $key) {
            if (str_ends_with($className, ucfirst($key))) {
                $className = substr($className, 0, \strlen($key) * -1);
            }
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
