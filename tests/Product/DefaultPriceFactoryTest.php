<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product;

use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Model\TaxClassModel;
use Isotope\CoreBundle\Model\TaxRateModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DefaultPriceFactory;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinitionCollection;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DefaultPriceFactoryTest extends TestCase
{
    #[DataProvider('variantPriceProvider')]
    public function testVariantPricesHaveTheirOwnReference(bool $advanced, bool $inherited, bool $fallback): void
    {
        $type = $this->createStub(ProductTypeModel::class);
        $type
            ->method('getAttributes')
            ->willReturn(['price'])
        ;

        $type
            ->method('hasVariantPrices')
            ->willReturn(!$inherited)
        ;

        $type
            ->method('hasAdvancedPrices')
            ->willReturn($advanced)
        ;

        $type
            ->method('showPriceTiers')
            ->willReturn(false)
        ;

        $data = [
            'id' => 42,
            'pid' => 1,
            'fallback' => $fallback,
            ...$this->priceData($inherited ? 200 : 100),
            '__parent' => ['id' => 1, ...$this->priceData(100)],
        ];
        $context = $this->createStoreContext();
        $factory = new DefaultPriceFactory($this->createStub(RuleMatcher::class));
        $prices = $factory->create(new ProductDto('tl_iso_product.42', 1, $data), $type, $context);

        $this->assertCount($advanced ? 2 : 1, $prices);
        $this->assertInstanceOf(PriceDefinitionCollection::class, $prices);
        $this->assertSame($advanced ? ['42', '42'] : ['42'], $prices->column('getReference'));
        $this->assertEqualsWithDelta(100.0, $prices->first()->getAmount(), PHP_FLOAT_EPSILON);

        $product = new CoreProduct($data, $type, $this->createStub(ProductRendererInterface::class));
        $product->setIsVariant(true)->setPrices($prices);
        $calculator = new PriceCalculator(new TaxCalculator(
            $this->createContaoFrameworkStub([
                TaxClassModel::class => $this->createAdapterStub(['findById']),
                TaxRateModel::class => $this->createAdapterStub(['findById', 'findMultipleByIds']),
            ]),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        ));

        $displayPrice = $calculator->calculateProduct($product, 1, true, $context);
        $this->assertInstanceOf(CalculatedPrice::class, $displayPrice);
        $this->assertSame($advanced && $inherited ? 80.0 : 100.0, $displayPrice->getUnitPrice());
        $this->assertSame($advanced && $inherited, $displayPrice->isRange());

        $cartPrice = $calculator->calculateProduct($product, 10, false, $context);
        $this->assertInstanceOf(CalculatedPrice::class, $cartPrice);
        $this->assertSame($advanced ? 80.0 : 100.0, $cartPrice->getUnitPrice());
    }

    public static function variantPriceProvider(): iterable
    {
        yield 'simple variant price' => [false, false, false];
        yield 'simple fallback variant price' => [false, false, true];
        yield 'advanced variant price' => [true, false, false];
        yield 'advanced fallback variant price' => [true, false, true];
        yield 'inherited simple price' => [false, true, false];
        yield 'inherited simple fallback price' => [false, true, true];
        yield 'inherited advanced price' => [true, true, false];
        yield 'inherited advanced fallback price' => [true, true, true];
    }

    #[DataProvider('advancedPricesProvider')]
    public function testParentPricesKeepEachVariantsReference(bool $advanced): void
    {
        $type = $this->createStub(ProductTypeModel::class);
        $type
            ->method('hasVariantPrices')
            ->willReturn(true)
        ;

        $type
            ->method('hasAdvancedPrices')
            ->willReturn($advanced)
        ;
        $context = $this->createStoreContext();
        $factory = new DefaultPriceFactory($this->createStub(RuleMatcher::class));
        $prices = $factory->create(new ProductDto('tl_iso_product.1', 1, [
            'id' => 1,
            'pid' => 0,
            '__variants' => [
                ['id' => 42, ...$this->priceData(100)],
                ['id' => 43, ...$this->priceData(120)],
            ],
        ]), $type, $context);
        $this->assertInstanceOf(PriceDefinitionCollection::class, $prices);

        $this->assertSame($advanced ? ['42', '42', '43', '43'] : ['42', '43'], $prices->column('getReference'));
    }

    #[DataProvider('advancedPricesProvider')]
    public function testProductWithoutVariantsHasNoVariantReference(bool $advanced): void
    {
        $type = $this->createStub(ProductTypeModel::class);
        $type
            ->method('getAttributes')
            ->willReturn(['price'])
        ;

        $type
            ->method('hasAdvancedPrices')
            ->willReturn($advanced)
        ;
        $context = $this->createStoreContext();
        $factory = new DefaultPriceFactory($this->createStub(RuleMatcher::class));
        $prices = $factory->create(new ProductDto('tl_iso_product.1', 1, [
            'id' => 1,
            'pid' => 0,
            ...$this->priceData(100),
        ]), $type, $context);
        $this->assertInstanceOf(PriceDefinitionCollection::class, $prices);

        $this->assertSame($advanced ? [null, null] : [null], $prices->column('getReference'));
    }

    public static function advancedPricesProvider(): iterable
    {
        yield 'simple prices' => [false];
        yield 'advanced prices' => [true];
    }

    private function priceData(float $amount): array
    {
        return [
            'price' => serialize(['price' => $amount, 'tax_class' => '']),
            'prices' => serialize([['tax_class' => '', 'price_tiers' => [1 => $amount, 10 => $amount - 20]]]),
        ];
    }
}
