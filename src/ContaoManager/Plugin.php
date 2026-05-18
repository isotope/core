<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ContaoManager;

use Codefog\HasteBundle\CodefogHasteBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Config\ConfigInterface;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Isotope\CoreBundle\IsotopeBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;
use Terminal42\ConditionalSelectMenuBundle\Terminal42ConditionalSelectMenuBundle;
use Terminal42\DcawizardBundle\Terminal42DcawizardBundle;
use Terminal42\DcMultilingualBundle\Terminal42DcMultilingualBundle;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /**
     * @return array<ConfigInterface>
     */
    public function getBundles(ParserInterface $parser): array
    {
        return [
            (new BundleConfig(IsotopeBundle::class))->setLoadAfter([
                ContaoCoreBundle::class,
                Terminal42NotificationCenterBundle::class,
                Terminal42ConditionalSelectMenuBundle::class,
                Terminal42DcawizardBundle::class,
                CodefogHasteBundle::class,
                Terminal42DcMultilingualBundle::class,
            ])->setReplace(['isotope', 'isotope_rules', 'isotope_reports']),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): RouteCollection
    {
        return $resolver
            ->resolve(__DIR__.'/../Controller', 'attribute')
            ->load(__DIR__.'/../Controller')
        ;
    }
}
