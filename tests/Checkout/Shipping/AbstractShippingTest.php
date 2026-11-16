<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Checkout\Shipping;

use Isotope\CoreBundle\Checkout\Shipping\AbstractShipping;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Weight\Weight;
use Isotope\CoreBundle\Store\Weight\WeightUnit;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use PHPUnit\Framework\Attributes\DataProvider;

final class AbstractShippingTest extends TestCase
{
    #[DataProvider('weightLimitProvider')]
    public function testWeightLimits(Weight $weight, Weight|null $minimum, Weight|null $maximum, bool $expected): void
    {
        $cart = new Cart('cart');
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1'))->setWeight($weight));
        $shipping = new class(1, $this->createStoreContext()->setCart($cart)) extends AbstractShipping {
            public function hasError(): bool
            {
                return false;
            }

            public function review(): TemplateMarkup|null
            {
                return null;
            }
        };
        $shipping->setMinMaxWeight($minimum, $maximum);

        $this->assertSame($expected, $shipping->isAvailable());
    }

    public static function weightLimitProvider(): iterable
    {
        yield 'converted maximum boundary' => [new Weight(1, WeightUnit::Stone), null, new Weight(14, WeightUnit::Pound), true];
        yield 'converted minimum boundary' => [new Weight(14, WeightUnit::Pound), new Weight(1, WeightUnit::Stone), null, true];
        yield 'above maximum' => [new Weight(1.01, WeightUnit::Stone), null, new Weight(14, WeightUnit::Pound), false];
        yield 'below minimum' => [new Weight(13.9, WeightUnit::Pound), new Weight(1, WeightUnit::Stone), null, false];
        yield 'same unit boundaries' => [new Weight(14, WeightUnit::Pound), new Weight(14, WeightUnit::Pound), new Weight(14, WeightUnit::Pound), true];
    }
}
