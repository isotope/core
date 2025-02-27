<?php

namespace Isotope\CoreBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Isotope\CoreBundle\IsotopeCoreBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /**
     * @return array<ConfigInterface>
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            (new BundleConfig(IsotopeCoreBundle::class))->setLoadAfter([
                ContaoCoreBundle::class,
                'notification_center',
                'conditionalselectmenu',
                'dcawizard',
                'haste',
                'multicolumnwizard',
                'dc_multilingual',
            ]),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): RouteCollection
    {
        return $resolver->resolve('@IsotopeCoreBundle/src/Controller')->load('@IsotopeCoreBundle/src/Controller');
    }
}
