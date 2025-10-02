<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection\Compiler;

use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

trait ContainerAwareTrait
{
    private function registerContainer(Reference $reference, Definition $definition, ContainerBuilder $container, Definition $registry): void
    {
        $class = $definition->getClass() ?: (string) $reference;

        if (!is_a($class, ContainerAwareInterface::class, true)) {
            return;
        }

        $map = [];

        foreach ($class::getSubscribedServices() as $k => $v) {
            $map[is_numeric($k) ? $v : $k] = new Reference($v);
        }

        $registry->addMethodCall('setContainer', [$class, ServiceLocatorTagPass::register($container, $map, (string) $reference)]);
    }
}
