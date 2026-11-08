<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Cart\CartCalculator;
use Isotope\CoreBundle\ProductCollection\Cart\CartFactory;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Exception\InvalidCartException;
use Isotope\CoreBundle\Store\Order\OrderConverter;
use Isotope\CoreBundle\Store\OrderManager;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Translation\TranslatableMessage;

final class CartManagerTest extends TestCase
{
    #[DataProvider('cartErrorsProvider')]
    public function testCannotOrderCartWithErrors(bool $itemError, bool $afterCalculation): void
    {
        $cart = new Cart('cart');
        $cart->add(new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'));
        $invalidCart = new Cart('cart');
        $item = new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42');
        $invalidCart->add($item);
        ($itemError ? $item : $invalidCart)->addError(new TranslatableMessage('error'));

        $calculator = $this->createMock(CartCalculator::class);
        $calculator
            ->expects($afterCalculation ? $this->once() : $this->never())
            ->method('calculate')
            ->willReturn($invalidCart)
        ;
        $converter = $this->createMock(OrderConverter::class);
        $converter
            ->expects($this->never())
            ->method('convertToOrder')
        ;
        $orderManager = $this->createMock(OrderManager::class);
        $orderManager
            ->expects($this->never())
            ->method('createOrder')
        ;

        $manager = new CartManager(
            $this->createStub(CartFactory::class),
            $this->createStub(ProductCollectionStorage::class),
            $calculator,
            $orderManager,
            $converter,
        );

        $this->expectException(InvalidCartException::class);
        $manager->order($afterCalculation ? $cart : $invalidCart, $this->createStoreContext());
    }

    public static function cartErrorsProvider(): iterable
    {
        yield 'collection error' => [false, false];
        yield 'item error' => [true, false];
        yield 'collection error after calculation' => [false, true];
        yield 'item error after calculation' => [true, true];
    }
}
