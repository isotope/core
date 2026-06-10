<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\ProductCollection\Cart;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Cart\CartCalculator;
use Isotope\CoreBundle\ProductCollection\Cart\CartFactory;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Test\TestCase;

class CartCalculatorTest extends TestCase
{
    public function testCalculateSubtotal(): void
    {
        $cart = new Cart('__identifier__');
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'))->setPrice(new CalculatedPrice(10, 10)));
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.43'))->setPrice(new CalculatedPrice(20, 20)));
        $cart->add((new LineItem(LineItem::TYPE_SHIPPING, 'tl_iso_shipping.1'))->setPrice(new CalculatedPrice(30, 30))->setLevel(1));

        $factory = $this->createMock(CartFactory::class);
        $factory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(fn () => new Cart('__identifier__'))
        ;

        $priceCalculator = $this->createStub(PriceCalculator::class);

        $calculator = new CartCalculator([], $factory, $priceCalculator);
        $result = $calculator->calculate($cart, $this->createStoreContext());
    }
}
