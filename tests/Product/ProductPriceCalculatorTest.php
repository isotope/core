<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product;

use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductPriceDefinitionsEvent;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Product\ProductPriceCalculator;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Store\Price\PriceDefinitionCollection;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ProductPriceCalculatorTest extends TestCase
{
    public function testAdjustmentsPrecedeRangeSelectionAndDoNotMutateSourcePrices(): void
    {
        $product = $this->createProduct();
        $product->setPrices(new PriceDefinitionCollection([
            (new PriceDefinition(100, null))->setReference('1'),
            (new PriceDefinition(120, null))->setReference('2'),
        ]));
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ProductPriceDefinitionsEvent::class,
            static function (ProductPriceDefinitionsEvent $event): void {
                $event->setPrices(new PriceDefinitionCollection(array_map(
                    static fn (PriceDefinition $price) => '2' === $price->getReference() ? $price->withAmount(60) : $price,
                    $event->getPrices()->toArray(),
                )));
            },
        );
        $calculator = $this->createCalculator($dispatcher);
        $context = $this->createStoreContext();

        for ($i = 0; $i < 2; ++$i) {
            $price = $calculator->calculate($product, 1, true, $context);
            $this->assertEqualsWithDelta(60.0, $price->getUnitPrice(), PHP_FLOAT_EPSILON);
            $this->assertTrue($price->isRange());
        }

        $this->assertSame([100.0, 120.0], $product->getPrices()->column('getAmount'));
    }

    public function testExtensionsCanSupplyMissingPricesAndAdjustConfiguredTotalsInPriorityOrder(): void
    {
        $product = $this->createProduct();
        $dispatcher = new EventDispatcher();
        $context = $this->createStoreContext();
        $dispatcher->addListener(
            ProductPriceDefinitionsEvent::class,
            function (ProductPriceDefinitionsEvent $event) use ($product, $context): void {
                $this->assertSame($product, $event->getProduct());
                $this->assertSame($context, $event->getContext());
                $this->assertNull($event->getPrices());
                $this->assertSame(3, $event->getQuantity());
                $this->assertFalse($event->isRange());
                $this->assertSame(['gift' => 'yes'], $event->getConfiguration());
                $event->setPrices(new PriceDefinitionCollection([new PriceDefinition(10, null, 2)]));
            },
        );
        $dispatcher->addListener(
            ProductPriceEvent::class,
            function (ProductPriceEvent $event): void {
                $this->assertSame(3, $event->getQuantity());
                $this->assertFalse($event->isRange());
                $this->assertSame(['gift' => 'yes'], $event->getConfiguration());
                $this->assertEqualsWithDelta(30.0, $event->getPrice()->getTotalPrice(), PHP_FLOAT_EPSILON);
                $event->setPrice(new CalculatedPrice(12, 36, quantity: 3));
            },
            100,
        );
        $dispatcher->addListener(
            ProductPriceEvent::class,
            static function (ProductPriceEvent $event): void {
                $amount = $event->getPrice()->getUnitPrice() / 2;
                $event->setPrice(new CalculatedPrice($amount, $amount * $event->getQuantity(), quantity: $event->getQuantity()));
            },
            -100,
        );

        $price = $this->createCalculator($dispatcher)->calculate($product, 3, false, $context, ['gift' => 'yes']);
        $this->assertInstanceOf(CalculatedPrice::class, $price);
        $this->assertEqualsWithDelta(18.0, $price->getTotalPrice(), PHP_FLOAT_EPSILON);
        $this->assertNull($product->getPrices());
    }

    public function testSourceAdjustmentsRespectQuantityThresholds(): void
    {
        $product = $this->createProduct();
        $product->setPrices(new PriceDefinitionCollection([
            new PriceDefinition(100, null),
            new PriceDefinition(80, null, 10),
        ]));
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ProductPriceDefinitionsEvent::class,
            static function (ProductPriceDefinitionsEvent $event): void {
                $event->setPrices(new PriceDefinitionCollection(array_map(
                    static fn (PriceDefinition $price) => $price->withAmount($price->getAmount() / 2),
                    $event->getPrices()->toArray(),
                )));
            },
        );
        $calculator = $this->createCalculator($dispatcher);
        $context = $this->createStoreContext();

        $this->assertEqualsWithDelta(50.0, $calculator->calculate($product, 1, false, $context)->getTotalPrice(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(400.0, $calculator->calculate($product, 10, false, $context)->getTotalPrice(), PHP_FLOAT_EPSILON);
    }

    public function testMutableDefinitionMetadataIsIsolated(): void
    {
        $product = $this->createProduct();
        $source = (new PriceDefinition(100, null))->setReference('original');
        $product->setPrices(new PriceDefinitionCollection([$source]));
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ProductPriceDefinitionsEvent::class,
            static function (ProductPriceDefinitionsEvent $event): void {
                $event->getPrices()->first()->setReference('changed');
            },
        );

        $this->createCalculator($dispatcher)->calculate($product, 1, false, $this->createStoreContext());

        $this->assertSame('original', $source->getReference());
    }

    public function testFinalPriceCanBeProvidedWithoutDefinitions(): void
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ProductPriceEvent::class,
            static function (ProductPriceEvent $event): void {
                $event->setPrice(new CalculatedPrice(15, 15));
            },
        );

        $price = $this->createCalculator($dispatcher)->calculate($this->createProduct(), 1, false, $this->createStoreContext());
        $this->assertInstanceOf(CalculatedPrice::class, $price);
        $this->assertEqualsWithDelta(15.0, $price->getUnitPrice(), PHP_FLOAT_EPSILON);
    }

    private function createProduct(): CoreProduct
    {
        return new CoreProduct(
            ['id' => 1],
            $this->createClassWithPropertiesStub(ProductTypeModel::class),
            $this->createStub(ProductRendererInterface::class),
        );
    }

    private function createCalculator(EventDispatcher $dispatcher): ProductPriceCalculator
    {
        $taxCalculator = $this->createStub(TaxCalculator::class);
        $taxCalculator
            ->method('getTaxRates')
            ->willReturn([new TaxRateCollection(), new TaxRateCollection()])
        ;

        $taxCalculator
            ->method('calculateNetPrice')
            ->willReturnArgument(0)
        ;

        return new ProductPriceCalculator(new PriceCalculator($taxCalculator), $dispatcher);
    }
}
