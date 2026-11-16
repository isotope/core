<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\ProductCollection;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Isotope\CoreBundle\EventDispatcher\TransactionEventDispatcher;
use Isotope\CoreBundle\Model\ProductCollectionItemModel;
use Isotope\CoreBundle\Model\ProductCollectionModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\Event\SaveCollectionTransactionEvent;
use Isotope\CoreBundle\ProductCollection\Event\SaveItemTransactionEvent;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\ProductCollectionStorage;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class ProductCollectionStorageTest extends TestCase
{
    #[DataProvider('levelProvider')]
    public function testLineItemLevelIsSaved(int $level): void
    {
        $cart = new Cart('cart');
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1'))->setLevel($level));
        $collectionModel = $this->createClassWithPropertiesStub(ProductCollectionModel::class, ['id' => 42]);
        $itemModel = $this->createClassWithPropertiesMock(ProductCollectionItemModel::class, ['id' => 99, 'level' => 7]);
        $itemModel
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                function () use ($itemModel, $level): ProductCollectionItemModel {
                    $this->assertSame($level, $itemModel->level);

                    return $itemModel;
                },
            )
        ;
        $collectionAdapter = $this->createAdapterStub(['findByIdentifier']);
        $collectionAdapter
            ->method('findByIdentifier')
            ->willReturn($collectionModel)
        ;
        $collectionItemAdapter = $this->createAdapterStub(['findOneByCollection']);
        $collectionItemAdapter
            ->method('findOneByCollection')
            ->willReturn($itemModel)
        ;
        $framework = $this->createContaoFrameworkStub([
            ProductCollectionModel::class => $collectionAdapter,
            ProductCollectionItemModel::class => $collectionItemAdapter,
        ]);
        $storage = new ProductCollectionStorage($this->createStub(Connection::class), new TransactionEventDispatcher(new EventDispatcher()), $framework);
        $storage->save($cart, $this->createStoreContext());
    }

    public static function levelProvider(): iterable
    {
        yield 'negative level' => [-10];
        yield 'reset level' => [0];
        yield 'positive level' => [20];
    }

    #[DataProvider('saveProvider')]
    public function testSavedIdsAreAvailableOnTheCartAndItsItems(bool $new): void
    {
        $cart = new Cart('cart');
        $item = new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1');
        $cart->add($item);

        if (!$new) {
            $cart->setId(42);
            $item->setId(99);
        }

        $collectionModel = $this->createClassWithPropertiesMock(ProductCollectionModel::class, ['id' => $new ? null : 42]);
        $collectionModel
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                static function () use ($collectionModel): ProductCollectionModel {
                    $collectionModel->id = 42;

                    return $collectionModel;
                },
            )
        ;
        $itemModel = $this->createClassWithPropertiesMock(ProductCollectionItemModel::class, ['id' => $new ? null : 99]);
        $itemModel
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(
                static function () use ($itemModel): ProductCollectionItemModel {
                    $itemModel->id = 99;

                    return $itemModel;
                },
            )
        ;
        $collectionAdapter = $this->createAdapterStub(['findByIdentifier']);
        $collectionAdapter
            ->method('findByIdentifier')
            ->willReturn($new ? null : $collectionModel)
        ;
        $collectionItemAdapter = $this->createAdapterStub(['findOneByCollection']);
        $collectionItemAdapter
            ->method('findOneByCollection')
            ->willReturn($new ? null : $itemModel)
        ;
        $framework = $this->createContaoFrameworkStub(
            [
                ProductCollectionModel::class => $collectionAdapter,
                ProductCollectionItemModel::class => $collectionItemAdapter,
            ],
            [
                ProductCollectionModel::class => $collectionModel,
                ProductCollectionItemModel::class => $itemModel,
            ],
        );
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('executeStatement')
            ->with(
                'DELETE FROM tl_iso_product_collection_item WHERE pid=? AND id NOT IN (?)',
                [42, [0, 99]],
                [Types::INTEGER, ArrayParameterType::INTEGER],
            )
            ->willReturn(0)
        ;
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            SaveCollectionTransactionEvent::NAME.'.post',
            function (SaveCollectionTransactionEvent $event): void {
                $this->assertSame(42, $event->getCollection()->getId());
            },
        );
        $dispatcher->addListener(
            SaveItemTransactionEvent::NAME.'.post',
            function (SaveItemTransactionEvent $event): void {
                $this->assertSame(99, $event->getLineItem()->getId());
            },
        );
        $storage = new ProductCollectionStorage($connection, new TransactionEventDispatcher($dispatcher), $framework);
        $storage->save($cart, $this->createStoreContext());

        $this->assertSame(42, $cart->getId());
        $this->assertSame(99, $item->getId());
    }

    public static function saveProvider(): iterable
    {
        yield 'new cart and item' => [true];
        yield 'existing cart and item' => [false];
    }
}
