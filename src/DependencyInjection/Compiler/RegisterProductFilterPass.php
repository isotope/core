<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

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
        $services = $this->findAndSortTaggedServices('isotope.product_filter', $container);

        foreach ($services as $reference) {
            $definition = $container->findDefinition((string) $reference);

            foreach ($definition->getTag('isotope.product_filter') as $tag) {
                $filters[$tag['operator']] = $definition->getClass();
            }

            $container->removeDefinition((string) $reference);
        }

        $container->getDefinition(ProductQueryFactory::class)->setArgument(0, $filters);
    }
}
