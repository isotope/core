<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\RuleBuilder\Condition;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\RuleBuilder\Condition\CartQuantityCondition;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CartQuantityConditionTest extends TestCase
{
    public function testCountWithAndWithoutCart(): void
    {
        $context = $this->createStoreContext();
        $translator = $this->createStub(TranslatorInterface::class);
        $emptyCondition = new CartQuantityCondition($translator, 'eq', 0, 0, 0);

        $this->assertTrue($emptyCondition->isApplicable($context));

        $cart = new Cart('test');
        $context->setCart($cart);
        $this->assertTrue($emptyCondition->isApplicable($context));

        $cart->add(new LineItem(LineItem::TYPE_PRODUCT, 'product_1', 2));
        $cart->add(new LineItem(LineItem::TYPE_PRODUCT, 'product_2', 1));
        $cart->add(new LineItem(LineItem::TYPE_SHIPPING, 'shipping', 1));

        $condition = new CartQuantityCondition($translator, 'eq', 3, 0, 0);

        $this->assertFalse($emptyCondition->isApplicable($context));
        $this->assertTrue($condition->isApplicable($context));
    }
}
