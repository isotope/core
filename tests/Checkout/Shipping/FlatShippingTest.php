<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Checkout\Shipping;

use Isotope\CoreBundle\Checkout\Shipping\FlatCalculation;
use Isotope\CoreBundle\Checkout\Shipping\FlatShipping;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Extension\ShippingExtension;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Store\Weight\Weight;
use Isotope\CoreBundle\Store\Weight\WeightUnit;
use Isotope\CoreBundle\Test\TestCase;

final class FlatShippingTest extends TestCase
{
    public function testWeightPriceExcludesProductsExemptFromShipping(): void
    {
        $cart = new Cart('cart');

        foreach ([[false, 2], [true, 10]] as [$exempt, $amount]) {
            $weight = new Weight($amount, WeightUnit::Kilogram);
            $product = $this->createStub(CoreProduct::class);
            $product
                ->method('getExtension')
                ->willReturn(new ShippingExtension($exempt, false, $weight, null))
            ;
            $cart->add((new LineItem(LineItem::TYPE_PRODUCT, $exempt ? 'exempt' : 'shippable'))->setProduct($product)->setWeight($weight));
        }

        $shipping = new FlatShipping(1, $this->createStoreContext()->setCart($cart), FlatCalculation::perWeight);
        $shipping->setPriceDefinition(new PriceDefinition(5, null));
        $shipping->setFlatWeight(new Weight(1, WeightUnit::Kilogram));

        $this->assertEqualsWithDelta(10.0, $shipping->getPriceDefinition()?->getAmount(), PHP_FLOAT_EPSILON);
    }

    public function testPricePerOneKilogram(): void
    {
        $item = $this->createStub(LineItem::class);
        $item
            ->method('getType')
            ->willReturn(LineItem::TYPE_PRODUCT)
        ;

        $item
            ->method('getWeight')
            ->willReturn(new Weight(3, WeightUnit::Kilogram))
        ;

        $item
            ->method('getQuantity')
            ->willReturn(1)
        ;

        $cart = $this->createStub(Cart::class);
        $cart
            ->method('getLineItems')
            ->willReturn(new LineItemCollection([$item]))
        ;

        $context = $this->createStoreContext()->setCart($cart);
        $shipping = new FlatShipping(1, $context, FlatCalculation::perWeight);
        $shipping->setPriceDefinition(new PriceDefinition(5, null));
        $shipping->setFlatWeight(new Weight(1, WeightUnit::Kilogram));

        $this->assertEqualsWithDelta(15.0, $shipping->getPriceDefinition()?->getAmount(), PHP_FLOAT_EPSILON);
    }
}
