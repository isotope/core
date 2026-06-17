<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Tax;

use Isotope\CoreBundle\Model\TaxClassModel;
use Isotope\CoreBundle\Model\TaxRateModel;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\TaxCalculation;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Store\Tax\TaxRate;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TaxCalculatorTest extends TestCase
{
    #[DataProvider('getTaxRatesProvider')]
    public function testGetTaxRates(int $taxClassId, array|null $taxClass, array|null $included, array $taxRates): void
    {
        $taxClassAdapter = $this->createAdapterStub(['findById']);
        $taxClassAdapter
            ->method('findById')
            ->willReturn($taxClass ? $this->createClassWithPropertiesStub(TaxClassModel::class, $taxClass) : null)
        ;

        $includedModel = $included ? $this->createClassWithPropertiesStub(TaxRateModel::class, $included) : null;
        $taxRateModels = array_map(fn ($p) => $this->createClassWithPropertiesStub(TaxRateModel::class, $p), $taxRates);

        $taxRateAdapter = $this->createAdapterStub(['findById', 'findMultipleByIds']);
        $taxRateAdapter
            ->method('findById')
            ->willReturn($includedModel)
        ;

        $taxRateAdapter
            ->method('findMultipleByIds')
            ->willReturn($taxRateModels)
        ;

        $calculator = new TaxCalculator(
            $this->createContaoFrameworkStub([
                TaxClassModel::class => $taxClassAdapter,
                TaxRateModel::class => $taxRateAdapter,
            ]),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        );

        [$scope1, $scope2] = $calculator->getTaxRates($taxClassId, $this->createStoreContext());

        if ($included) {
            $this->assertInstanceOf(TaxRate::class, $scope2->first());
            $this->assertSame((string) $included['id'], $scope2->first()->getId());
            $this->assertSame($included['rate'], $scope2->first()->getTaxRate());
            $this->assertEqualsWithDelta(100.0, $scope2->first()->getPercentage(), PHP_FLOAT_EPSILON);
        } else {
            $this->assertEmpty($scope2);
        }

        $this->assertCount(\count($taxRates) + ($included ? 1 : 0), $scope1);
        $expectedRates = array_merge($included ? [$included] : [], $taxRates);

        foreach ($scope1 as $i => $actualRate) {
            $config = $expectedRates[$i];

            $this->assertInstanceOf(TaxRate::class, $actualRate);
            $this->assertSame((string) $config['id'], $actualRate->getId());
            $this->assertSame($config['rate'], $actualRate->getTaxRate());
            $this->assertEqualsWithDelta(100.0, $actualRate->getPercentage(), PHP_FLOAT_EPSILON);
        }
    }

    public static function getTaxRatesProvider(): iterable
    {
        yield 'No tax ID' => [
            0,
            null,
            null,
            [],
        ];

        yield 'Tax with ID 1 not found' => [
            1,
            null,
            null,
            [],
        ];

        yield 'Tax class config is empty' => [
            1,
            [
                'includes' => 0,
                'rates' => '',
            ],
            null,
            [],
        ];

        yield 'Included tax rate not found' => [
            1,
            [
                'includes' => 1,
                'rates' => '',
            ],
            null,
            [],
        ];

        yield 'Adds included tax rate' => [
            1,
            [
                'includes' => 1,
                'rates' => '',
            ],
            [
                'id' => 1,
                'rate' => 19.0,
            ],
            [],
        ];

        yield 'Adds tax rates' => [
            1,
            [
                'includes' => 1,
                'rates' => '1',
            ],
            null,
            [
                [
                    'id' => 1,
                    'rate' => 19.0,
                ],
            ],
        ];
    }

    #[DataProvider('calculateNetPriceProvider')]
    public function testCalculateNetPrice(array $taxes, float $price, float $expected): void
    {
        $taxRates = new TaxRateCollection($taxes);

        $calculator = new TaxCalculator(
            $this->createContaoFrameworkStub(),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        );

        $result = $calculator->calculateNetPrice($price, $taxRates);

        $this->assertEqualsWithDelta($expected, $result, PHP_FLOAT_EPSILON);
    }

    public static function calculateNetPriceProvider(): iterable
    {
        yield 'Does not change net price without taxes' => [
            [],
            100,
            100,
        ];

        yield 'Does not change gross price without taxes' => [
            [],
            100,
            100,
        ];

        yield 'Subtracts included tax 1' => [
            [new TaxRate('1', 19)],
            100,
            100 / 119 * 100,
        ];

        yield 'Subtracts included tax 2' => [
            [new TaxRate('1', 7)],
            100,
            100 / 107 * 100,
        ];

        yield 'Subtracts included tax 3' => [
            [new TaxRate('1', 8.1)],
            100,
            100 / 108.1 * 100,
        ];

        yield 'Subtracts 50% of included tax' => [
            [new TaxRate('1', 10, 50), new TaxRate('2', 20, 50)],
            230,
            200,
        ];
    }

    /**
     * @param array<CalculatedTax> $expected
     */
    #[DataProvider('calculateTaxesProvider')]
    public function testCalculateTaxes(float $amount, TaxRateCollection $taxRates, array $expected): void
    {
        $calculator = new TaxCalculator(
            $this->createContaoFrameworkStub(),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        );

        $result = $calculator->calculateTaxes($amount, $taxRates, TaxCalculation::Horizontal, new MoneyFormat());

        /** @var CalculatedTax $actualTax */
        foreach ($result as $i => $actualTax) {
            $expectedTax = $expected[$i];

            $this->assertSame($expectedTax->jsonSerialize(), $actualTax->jsonSerialize());
        }
    }

    public static function calculateTaxesProvider(): iterable
    {
        yield [
            100,
            new TaxRateCollection([
                new TaxRate('1', 19),
            ]),
            [
                '1' => new CalculatedTax('1', 19, 19, 100),
            ],
        ];

        yield [
            100,
            new TaxRateCollection([
                new TaxRate('1', 10, percentage: 50),
                new TaxRate('2', 20, percentage: 50),
            ]),
            [
                '1' => new CalculatedTax('1', 5, 10, 50),
                '2' => new CalculatedTax('2', 10, 20, 50),
            ],
        ];
    }

    /**
     * @param array<TaxRate> $expectedRates
     */
    #[DataProvider('combineTaxRatesProvider')]
    public function testCombineTaxRates(TaxRateCollection $taxes1, TaxRateCollection $taxes2, float $price1, float $price2, array $expectedRates, array $expectedIncluded = []): void
    {
        $calculator = new TaxCalculator(
            $this->createContaoFrameworkStub(),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        );

        $result = $calculator->combineTaxRates($taxes1, $taxes2, $price1, $price2);

        $this->assertCount(\count($expectedRates), $result);

        foreach ($result as $i => $tax) {
            $this->assertSame(round($tax->getTaxRate(), 8), round($expectedRates[$i]->getTaxRate(), 8));
            $this->assertSame(round($tax->getPercentage(), 8), round($expectedRates[$i]->getPercentage(), 8));
        }
    }

    public static function combineTaxRatesProvider(): iterable
    {
        yield [
            new TaxRateCollection(),
            new TaxRateCollection(),
            0,
            0,
            [],
        ];

        yield [
            new TaxRateCollection(),
            new TaxRateCollection([new TaxRate('1', 10)]),
            100,
            100,
            ['1' => new TaxRate('1', 10, 50)],
        ];

        yield [
            new TaxRateCollection([new TaxRate('2', 19)]),
            new TaxRateCollection(),
            100,
            100,
            ['2' => new TaxRate('2', 19, 50)],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19)]),
            new TaxRateCollection([new TaxRate('1', 19)]),
            100,
            100,
            ['1' => new TaxRate('1', 19, 100)],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19)]),
            new TaxRateCollection([new TaxRate('2', 7)]),
            100,
            100,
            ['1' => new TaxRate('1', 19, 50), '2' => new TaxRate('2', 7, 50)],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19)]),
            new TaxRateCollection([new TaxRate('2', 7)]),
            600,
            400,
            ['1' => new TaxRate('1', 19, 60), '2' => new TaxRate('2', 7, 40)],
        ];

        yield [
            new TaxRateCollection([new TaxRate('1', 19), new TaxRate('2', 7)]),
            new TaxRateCollection([new TaxRate('2', 7)]),
            200,
            100,
            ['1' => new TaxRate('1', 19, 66.66666667), '2' => new TaxRate('2', 7, 100)],
        ];

        yield [
            new TaxRateCollection(),
            new TaxRateCollection([new TaxRate('1', 10)]),
            100,
            100,
            ['1' => new TaxRate('1', 10, 50)],
            ['1' => new TaxRate('1', 10, 50)],
        ];
    }

    #[DataProvider('combineCalculatedTaxesProvider')]
    public function testCombineCalculatedTaxes(CalculatedTaxCollection $taxes1, CalculatedTaxCollection $taxes2, array $expectedTaxes, array $expectedPrices): void
    {
        $calculator = new TaxCalculator(
            $this->createContaoFrameworkStub(),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        );

        $result = $calculator->combineCalculatedTaxes($taxes1, $taxes2);

        $this->assertCount(\count($expectedTaxes), $result);

        /**
         * @var CalculatedTax $tax
         */
        foreach ($result as $i => $tax) {
            $this->assertSame($tax->getAmount(), $expectedTaxes[$i]);
            $this->assertSame($tax->getPrice(), $expectedPrices[$i]);
        }
    }

    public static function combineCalculatedTaxesProvider(): iterable
    {
        yield [
            new CalculatedTaxCollection(),
            new CalculatedTaxCollection(),
            [],
            [],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection(),
            ['1' => 10.0],
            ['1' => 100.0],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            ['1' => 20.0],
            ['1' => 200.0],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection([new CalculatedTax('2', 10.0, 7, 100.0)]),
            ['1' => 10.0, '2' => 10.0],
            ['1' => 100.0, '2' => 100.0],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0)]),
            new CalculatedTaxCollection([new CalculatedTax('2', 10.0, 7, 100.0)]),
            ['1' => 10.0, '2' => 10.0],
            ['1' => 100.0, '2' => 100.0],
        ];

        yield [
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 19, 100.0), new CalculatedTax('4', 4.0, 7, 42.0)]),
            new CalculatedTaxCollection([new CalculatedTax('4', 10.0, 7, 100.0)]),
            ['1' => 10.0, '4' => 14.0],
            ['1' => 100.0, '4' => 142.0],
        ];
    }
}
