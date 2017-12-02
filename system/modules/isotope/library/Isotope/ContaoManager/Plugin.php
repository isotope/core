<?php

namespace Isotope\ContaoManager;

use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class Plugin implements ConfigPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->setDefinition(
                'isotope.listener.console',
                (new Definition('Isotope\EventListener\SymlinkCommandListener'))
                    ->setArguments(['%kernel.project_dir%'])
                    ->addTag('kernel.event_listener', ['event' => 'console.terminate'])
            );
        });
    }
}
