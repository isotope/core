<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsFilterModule;
use Isotope\CoreBundle\EventListener\DataContainer\Content\FilterModuleOptionsListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class FilterModulesPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $types = [];
        $services = array_keys($container->findTaggedServiceIds(AsFilterModule::TAG_NAME));

        foreach ($services as $id) {
            $definition = $container->findDefinition($id);

            foreach ($definition->getTag('contao.content_element') as $tag) {
                $types[] = $tag['type'];
            }
        }

        $container->getDefinition(FilterModuleOptionsListener::class)->setArgument(0, array_unique($types));
    }
}
