<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\ProductCollection\Cart;

use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Cart\CartFactory;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CartFactoryTest extends TestCase
{
    public function testMemberCartMatchingTheIdentifierIsNotMergedOrDeleted(): void
    {
        $context = $this->createStoreContext()->setMemberId(42);
        $memberModel = $this->createClassWithPropertiesStub(ProductCollectionModel::class, ['id' => 10, 'uniqid' => $context->getIdentifier(), 'member' => 42]);
        $identifierModel = $this->createClassWithPropertiesStub(ProductCollectionModel::class, ['id' => '10', 'uniqid' => $context->getIdentifier(), 'member' => 42]);
        $collectionAdapter = $this->createAdapterStub(['findOneByMember', 'findByIdentifier']);
        $collectionAdapter
            ->method('findOneByMember')
            ->willReturn($memberModel)
        ;

        $collectionAdapter
            ->method('findByIdentifier')
            ->willReturn($identifierModel)
        ;
        $cart = new Cart($context->getIdentifier());
        $cart->add(new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1', 2));
        $storage = $this->createMock(ProductCollectionStorage::class);
        $storage
            ->expects($this->once())
            ->method('load')
            ->with($this->isInstanceOf(Cart::class), $memberModel, $context)
            ->willReturn($cart)
        ;

        $storage
            ->expects($this->never())
            ->method('save')
        ;

        $storage
            ->expects($this->never())
            ->method('delete')
        ;
        $factory = new CartFactory(new EventDispatcher(), $storage, $this->createContaoFrameworkStub([ProductCollectionModel::class => $collectionAdapter]));

        $this->assertSame($cart, $factory->load($context));
        $this->assertCount(1, $cart->getLineItems());
        $this->assertSame(2, $cart->getLineItems()->sumQuantity());
        $this->assertFalse($cart->isModified());
    }

    public function testDifferentGuestCartIsMergedAndDeletedAfterSaving(): void
    {
        $context = $this->createStoreContext()->setMemberId(42);
        $memberModel = $this->createClassWithPropertiesStub(ProductCollectionModel::class, ['id' => 10, 'uniqid' => 'member', 'member' => 42]);
        $guestModel = $this->createClassWithPropertiesStub(ProductCollectionModel::class, ['id' => 11, 'uniqid' => $context->getIdentifier(), 'member' => 0]);
        $collectionAdapter = $this->createAdapterStub(['findOneByMember', 'findByIdentifier']);
        $collectionAdapter
            ->method('findOneByMember')
            ->willReturn($memberModel)
        ;

        $collectionAdapter
            ->method('findByIdentifier')
            ->willReturn($guestModel)
        ;
        $memberCart = new Cart('member');
        $memberCart->add(new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1'));
        $guestCart = new Cart($context->getIdentifier());
        $guestCart->add(new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.2'));
        $storage = $this->createMock(ProductCollectionStorage::class);
        $storage
            ->expects($this->exactly(2))
            ->method('load')
            ->willReturnOnConsecutiveCalls($memberCart, $guestCart)
        ;
        $saved = false;
        $storage
            ->expects($this->once())
            ->method('save')
            ->with($memberCart, $context)
            ->willReturnCallback(
                function () use ($memberCart, &$saved): void {
                    $this->assertCount(2, $memberCart->getLineItems());
                    $saved = true;
                }
            )
        ;
        $storage
            ->expects($this->once())
            ->method('delete')
            ->with($guestModel)
            ->willReturnCallback(
                function () use (&$saved): bool {
                    $this->assertTrue($saved);

                    return true;
                }
            )
        ;
        $factory = new CartFactory(new EventDispatcher(), $storage, $this->createContaoFrameworkStub([ProductCollectionModel::class => $collectionAdapter]));

        $this->assertSame($memberCart, $factory->load($context));
        $this->assertSame(2, $memberCart->getLineItems()->sumQuantity());
    }
}
