<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\ProductCollection\ItemCollector;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Product\AvailabilityChecker;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\ItemCollector\ProductCollector;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ProductCollectorTest extends TestCase
{
    #[DataProvider('availabilityProvider')]
    public function testProductAvailability(bool $published, int $startOffset, int $stopOffset, bool $hasErrors): void
    {
        $context = $this->createStoreContext();
        $categoryUtil = $this->createStub(ProductCategoryUtil::class);
        $categoryUtil
            ->method('getPublishedForProductInCurrentRoot')
            ->willReturn([1])
        ;
        $checker = new AvailabilityChecker($this->createStub(TokenChecker::class), $categoryUtil);
        $type = $this->createStub(ProductTypeModel::class);
        $type
            ->method('getAttributes')
            ->willReturn([])
        ;

        $product = new CoreProduct(
            [
                'id' => 42,
                'published' => $published,
                'start' => $startOffset ? (string) (time() + $startOffset) : '',
                'stop' => $stopOffset ? (string) (time() + $stopOffset) : '',
            ],
            $type,
            $this->createStub(ProductRendererInterface::class),
        );
        $product->setAvailable(static fn () => $checker->isAvailable($product, $context));

        $item = (new LineItem(LineItem::TYPE_PRODUCT, $product->getIdentifier()))->setProduct($product);
        $original = new Cart('cart');
        $original->add($item);
        $collection = new Cart('cart');
        $price = new CalculatedPrice(10, 10);
        $calculator = $this->createStub(PriceCalculator::class);
        $calculator
            ->method('calculateProduct')
            ->willReturn($price)
        ;
        $collector = new ProductCollector($calculator, new EventDispatcher());

        $collector->process($collection, $original, $context);
        $collector->process(new Cart('cart'), $collection, $context);

        $this->assertSame($item, $collection->getLineItems()->first());
        $this->assertSame($price, $item->getPrice());
        $this->assertSame($hasErrors, $collection->hasErrors());
        $this->assertCount($hasErrors ? 1 : 0, $item->getErrors());

        if ($hasErrors) {
            $this->assertEquals(new TranslatableMessage('ERR.collectionItemNotAvailable', [], 'contao_default'), $item->getErrors()[0]);
        }
    }

    public static function availabilityProvider(): iterable
    {
        yield 'published' => [true, 0, 0, false];
        yield 'unpublished' => [false, 0, 0, true];
        yield 'not yet published' => [true, 3600, 0, true];
        yield 'expired' => [true, 0, -3600, true];
        yield 'within publication period' => [true, -3600, 3600, false];
    }

    #[DataProvider('unsupportedProductProvider')]
    public function testSkipsUnsupportedProducts(bool $missing): void
    {
        $item = new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42');
        $item->setProduct($missing ? null : $this->createStub(Product::class));
        $price = new CalculatedPrice(10, 10);
        $item->setPrice($price);
        $error = new TranslatableMessage('ERR.collectionItemNotAvailable', [], 'contao_default');
        $item->addError($error);
        $original = new Cart('cart');
        $original->add($item);
        $collection = new Cart('cart');
        $calculator = $this->createMock(PriceCalculator::class);
        $calculator
            ->expects($this->never())
            ->method('calculateProduct')
        ;
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects($this->never())
            ->method('dispatch')
        ;

        (new ProductCollector($calculator, $dispatcher))->process($collection, $original, $this->createStoreContext());

        $this->assertTrue($collection->isEmpty());
        $this->assertSame($item, $original->getLineItems()->first());
        $this->assertSame($price, $item->getPrice());
        $this->assertSame([$error], $item->getErrors());
    }

    public static function unsupportedProductProvider(): iterable
    {
        yield 'missing product' => [true];
        yield 'custom product' => [false];
    }
}
