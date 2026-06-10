<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Price;

use Isotope\CoreBundle\Model\TaxClassModel;
use Isotope\CoreBundle\Model\TaxRateModel;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Tax\CalculatedTax;
use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Store\Tax\TaxRate;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

class PriceCalculatorTest extends TestCase
{
    #[DataProvider('calculatePriceDefinitionProvider')]
    public function testCalculatePercentagePriceDefinition(float $subtotal, PriceDisplay $priceDisplay, int $taxId, float $expectedTotal, float $expectedTaxes): void
    {
        $base = new CalculatedPrice(
            $subtotal,
            $subtotal,
            new CalculatedTaxCollection([new CalculatedTax('1', 10.0, 10.0, 50.0), new CalculatedTax('2', 20.0, 20.0, 50.0)]),
            new TaxRateCollection([new TaxRate('1', 10.0, 50), new TaxRate('2', 20.0, 50)]),
        );

        $taxClassAdapter = $this->createAdapterStub(['findById']);
        $taxClassAdapter
            ->method('findById')
            ->willReturn($this->createClassWithPropertiesStub(TaxClassModel::class, [
                'id' => 1,
                'included' => '1',
                'rates' => '1,2',
            ]))
        ;

        $taxRateAdapter = $this->createAdapterStub(['findById', 'findMultipleByIds']);
        $taxRateAdapter
            ->method('findById')
            ->willReturn($this->createClassWithPropertiesStub(TaxRateModel::class, [
                'id' => 1,
                'rate' => 10,
            ]))
        ;

        $taxRateAdapter
            ->method('findMultipleByIds')
            ->willReturnCallback(function() use ($taxId) {
                if (PriceDefinition::SPLIT_TAX === $taxId) {
                    return [
                        $this->createClassWithPropertiesStub(TaxRateModel::class, [
                            'id' => 1,
                            'rate' => 10,
                        ]),
                        $this->createClassWithPropertiesStub(TaxRateModel::class, [
                            'id' => 2,
                            'rate' => 20,
                        ]),
                    ];
                }

                return [$this->createClassWithPropertiesStub(TaxRateModel::class, [
                    'id' => 1,
                    'rate' => 10,
                ])];
            })
        ;

        $context = $this->createStoreContext(priceDisplay: $priceDisplay);

        $taxCalculator = new TaxCalculator(
            $this->createContaoFrameworkStub([
                TaxClassModel::class => $taxClassAdapter,
                TaxRateModel::class => $taxRateAdapter,
            ]),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        );

        $calculator = new PriceCalculator($taxCalculator);

        $priceDefinition = new PriceDefinition(10, $taxId, percentage: true);

        $result = $calculator->calculatePriceDefinition($priceDefinition, $base, $context);

        $this->assertSame($expectedTotal, $result->getTotalPrice());
        $this->assertSame($expectedTaxes, $result->getCalculatedTaxes()->sum());
    }

    public static function calculatePriceDefinitionProvider(): iterable
    {
        yield 'Net subtotal without tax' => [
            200,
            PriceDisplay::Net,
            0,
            20.0,
            0.0,
        ];

        yield 'Gross subtotal without tax' => [
            230,
            PriceDisplay::Gross,
            0,
            20.0,
            0.0,
        ];

        yield 'Fixed subtotal without tax' => [
            230,
            PriceDisplay::Fixed,
            0,
            20.0,
            0.0,
        ];

        yield 'Net subtotal with 10% tax' => [
            200,
            PriceDisplay::Net,
            1,
            20.0,
            2.0,
        ];

        yield 'Gross subtotal with 10% tax' => [
            230,
            PriceDisplay::Gross,
            1,
            22.0,
            2.0,
        ];

        yield 'Fixed subtotal with 10% tax' => [
            230,
            PriceDisplay::Fixed,
            1,
            22.0,
            2.0,
        ];

        yield 'Net subtotal with split tax' => [
            200,
            PriceDisplay::Net,
            PriceDefinition::SPLIT_TAX,
            20.0,
            3.0,
        ];

        yield 'Gross subtotal with split tax' => [
            230,
            PriceDisplay::Gross,
            PriceDefinition::SPLIT_TAX,
            23.0,
            3.0,
        ];

        yield 'Fixed subtotal with split tax' => [
            230,
            PriceDisplay::Fixed,
            PriceDefinition::SPLIT_TAX,
            23.0,
            3.0,
        ];
    }

    #[DataProvider('sumPricesProvider')]
    public function testSumPrices(CalculatedPrice $basePrice, CalculatedPrice $addedPrice, CalculatedPrice $expected): void
    {
        $taxCalculator = new TaxCalculator(
            $this->createContaoFrameworkStub(),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class)
        );

        $calculator = new PriceCalculator($taxCalculator);

        $result = $calculator->sum($basePrice, $addedPrice, $this->createStoreContext());

        $this->assertPriceMatch($expected, $result);

        $this->assertSame(
            array_values(array_unique(array_merge($basePrice->getCalculatedTaxes()->getNumbers(), $addedPrice->getCalculatedTaxes()->getNumbers()))),
            $result->getCalculatedTaxes()->getNumbers(),
        );
    }

    public static function sumPricesProvider(): iterable
    {
        yield [
            new CalculatedPrice(100, 100),
            new CalculatedPrice(100, 100),
            new CalculatedPrice(200, 200),
        ];

        yield [
            new CalculatedPrice(100, 100, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100)])),
            new CalculatedPrice(100, 100),
            new CalculatedPrice(200, 200, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100)])),
        ];

        yield [
            new CalculatedPrice(100, 100, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100)])),
            new CalculatedPrice(100, 100, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100)])),
            new CalculatedPrice(200, 200, new CalculatedTaxCollection([new CalculatedTax('1', 38, 38, 200)])),
        ];

        yield [
            new CalculatedPrice(10, 100, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100)]), quantity: 10),
            new CalculatedPrice(10, 100, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100)]), quantity: 10),
            new CalculatedPrice(200, 200, new CalculatedTaxCollection([new CalculatedTax('1', 38, 38, 200)]), quantity: 10),
        ];

        yield [
            new CalculatedPrice(100, 100, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100)])),
            new CalculatedPrice(100, 100, new CalculatedTaxCollection([new CalculatedTax('2', 19, 19, 100)])),
            new CalculatedPrice(200, 200, new CalculatedTaxCollection([new CalculatedTax('1', 19, 19, 100), new CalculatedTax('2', 19, 19, 100)])),
        ];
    }

    private function assertPriceMatch(CalculatedPrice $expected, CalculatedPrice $actual): void
    {
        $this->assertSame($expected->getTotalPrice(), $actual->getTotalPrice());
        $this->assertSame($expected->getCalculatedTaxes()->sum(), $actual->getCalculatedTaxes()->sum());
        $this->assertSame($expected->getQuantity(), $actual->getQuantity());
    }
}
