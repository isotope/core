<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsProductFilter;
use Isotope\CoreBundle\Product\Query\ProductQueryFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class RegisterProductFilterPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container): void
    {
        $filters = [];
        $services = $this->findAndSortTaggedServices(AsProductFilter::TAG_NAME, $container);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag(AsProductFilter::TAG_NAME) as $tag) {
                $filters[$tag['operator']] = $definition->getClass();
            }

            $container->removeDefinition((string) $reference);
        }

        $container->getDefinition(ProductQueryFactory::class)->setArgument(0, $filters);
    }
}
