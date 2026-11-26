<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\ProductCollection\Cart;

use Isotope\CoreBundle\ProductCollection\AbstractProductCollection;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Cart\CartCalculator;
use Isotope\CoreBundle\ProductCollection\Cart\CartFactory;
use Isotope\CoreBundle\ProductCollection\ItemCollector\ItemCollectorInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\ProductCollectionHasher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Tax\TaxCalculator;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CartCalculatorTest extends TestCase
{
    #[DataProvider('originalPriceProvider')]
    public function testContextContainsTheReturnedCartAfterConvergence(float $originalPrice): void
    {
        $cart = new Cart('cart');
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1'))->setPrice(new CalculatedPrice($originalPrice, $originalPrice)));
        $cart->setTotal(new CalculatedPrice($originalPrice, $originalPrice));

        $context = $this->createStoreContext()->setCart($cart);

        $factory = $this->createStub(CartFactory::class);
        $factory
            ->method('create')
            ->willReturnCallback(static fn (StoreContext $context, string $identifier) => new Cart($identifier))
        ;

        $collector = $this->createStub(ItemCollectorInterface::class);
        $collector
            ->method('process')
            ->willReturnCallback(
                static function (AbstractProductCollection $collection): void {
                    $collection->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1'))->setLabel('Recalculated')->setPrice(new CalculatedPrice(100, 100)));
                },
            )
        ;

        $calculator = new CartCalculator($factory, [$collector], new PriceCalculator($this->createStub(TaxCalculator::class)), new ProductCollectionHasher(new EventDispatcher()));
        $calculated = $calculator->calculate($cart, $context);

        $this->assertNotSame($cart, $calculated);
        $this->assertSame($calculated, $context->getCart());
        $this->assertSame('Recalculated', $context->getCart()->getLineItems()->first()->getLabel());
    }

    public static function originalPriceProvider(): iterable
    {
        yield 'converges on first iteration' => [100.0];
        yield 'converges after a price change' => [50.0];
    }
}
