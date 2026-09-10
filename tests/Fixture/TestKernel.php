<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Fixture;

use Codefog\HasteBundle\CodefogHasteBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Isotope\CoreBundle\IsotopeBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use League\FlysystemBundle\FlysystemBundle;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use Terminal42\ChangeLanguage\Terminal42ChangeLanguageBundle;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new TwigBundle(),
            new DoctrineBundle(),
            new KnpMenuBundle(),
            new SchebTwoFactorBundle(),
            new FlysystemBundle(),
            new ContaoCoreBundle(),
            new CodefogHasteBundle(),
            new Terminal42NotificationCenterBundle(),
            new IsotopeBundle(),
            new Terminal42ChangeLanguageBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config/config_test.yaml');
    }
}
