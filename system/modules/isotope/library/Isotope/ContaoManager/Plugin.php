<?php

/*
 * Isotope eCommerce for Contao Open Source CMS
 *
 * Copyright (C) 2009 - 2019 terminal42 gmbh & Isotope eCommerce Workgroup
 *
 * @link       https://isotopeecommerce.org
 * @license    https://opensource.org/licenses/lgpl-3.0.html
 */

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

            $container->setDefinition(
                'isotope.listener.frontend_preview',
                (new Definition('Isotope\EventListener\FrontendPreviewListener'))
                    ->addTag('kernel.event_listener', ['event' => 'contao.preview_url_create', 'method' => 'onPreviewUrlCreate'])
            );
        });
    }
}
