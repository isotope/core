<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\EventListener;

use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Attribute\Option\Option;
use Isotope\CoreBundle\Attribute\Option\OptionCollection;
use Isotope\CoreBundle\Attribute\OptionsAttributeInterface;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Product\EventListener\ConfigurationPriceListener;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Price\BasePrice;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ConfigurationPriceListenerTest extends TestCase
{
    #[DataProvider('selectionProvider')]
    public function testSelectedOptionsContributeToThePriceAndPreserveMetadata(array|string $selection, float $expectedUnitPrice, int $quantity = 1, bool $isRange = false): void
    {
        $options = new OptionCollection([
            (new Option('a', 'Option A'))->setPrice(new CalculatedPrice(5, 5)),
            (new Option('b', 'Option B'))->setPrice(new CalculatedPrice(10, 10)),
            (new Option('0', 'Option zero'))->setPrice(new CalculatedPrice(2, 2)),
            new Option('free', 'Free option'),
        ]);

        $attribute = $this->createStub(OptionsAttributeInterface::class);
        $attribute
            ->method('getOptions')
            ->willReturn($options)
        ;

        $registry = $this->createStub(AttributeRegistry::class);
        $registry
            ->method('getPricedFields')
            ->willReturn(['extras'])
        ;
        $registry
            ->method('get')
            ->willReturn($attribute)
        ;

        $calculator = new PriceCalculator(new TaxCalculator(
            $this->createContaoFrameworkStub(),
            $this->createStub(RuleMatcher::class),
            $this->createStub(TranslatorInterface::class),
        ));

        $basePrice = new BasePrice(500, 100, '%s per 100g');

        $event = new ProductPriceEvent(
            $this->createStub(CoreProduct::class),
            $this->createStoreContext(),
            ['extras' => $selection],
            (new CalculatedPrice(100, 100 * $quantity, quantity: $quantity))->setBasePrice($basePrice)->setRange($isRange),
        );

        $listener = new ConfigurationPriceListener($registry, $calculator);
        $listener($event);

        $price = $event->getPrice();

        $this->assertInstanceOf(CalculatedPrice::class, $price);
        $this->assertSame($expectedUnitPrice, $price->getUnitPrice());
        $this->assertSame($expectedUnitPrice * $quantity, $price->getTotalPrice());
        $this->assertSame($quantity, $price->getQuantity());
        $this->assertSame($basePrice, $price->getBasePrice());
        $this->assertSame($isRange, $price->isRange());
    }

    public static function selectionProvider(): iterable
    {
        yield 'price range with multiple options' => [['a', 'b'], 115.0, 1, true];
        yield 'single selection' => ['a', 105.0];
        yield 'multiple selections' => [['a', 'b'], 115.0];
        yield 'one selected checkbox' => [['a'], 105.0];
        yield 'multiple product units' => [['a', 'b'], 115.0, 3];
        yield 'unknown option ignored' => [['a', 'unknown'], 105.0];
        yield 'unpriced option ignored' => [['a', 'free'], 105.0];
        yield 'duplicate selections charged once' => [['a', 'a', 'b'], 115.0];
        yield 'zero option value' => ['0', 102.0];
        yield 'numeric zero option value' => [[0], 102.0];
        yield 'no selected options' => [[], 100.0];
    }
}
