<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Intl;

use Contao\CoreBundle\Config\ResourceFinderInterface;
use Contao\System;
use Isotope\CoreBundle\Intl\Subdivisions;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Finder\Finder;

#[BackupGlobals(true)]
final class SubdivisionsTest extends TestCase
{
    #[DataProvider('countryProvider')]
    public function testCountryLookup(string $country, array $expected): void
    {
        $container = $this->getContainerWithContaoConfiguration();

        $finder = $this->createStub(ResourceFinderInterface::class);
        $finder
            ->method('findIn')
            ->willReturn((new Finder())->append([]))
        ;

        $container->set('contao.resource_finder', $finder);

        System::setContainer($container);

        $GLOBALS['TL_LANG']['DIV'] = ['ch' => ['CH-AG' => 'Aargau']];

        $this->assertSame($expected, (new Subdivisions())->getForCountry($country));
    }

    public static function countryProvider(): iterable
    {
        yield 'uppercase country' => ['CH', ['CH-AG' => 'Aargau']];
        yield 'lowercase country' => ['ch', ['CH-AG' => 'Aargau']];
        yield 'country without subdivisions' => ['XX', []];
        yield 'empty country' => ['', []];
    }
}
