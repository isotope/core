<?php

declare(strict_types=1);

namespace Isotope\CoreBundle;

use Isotope\CoreBundle\DependencyInjection\IsotopeCoreExtension;
use Isotope\DependencyInjection\Compiler\RegisterHookListenersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IsotopeCoreBundle extends Bundle
{
    public function getContainerExtension(): IsotopeCoreExtension
    {
        return new IsotopeCoreExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterHookListenersPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
