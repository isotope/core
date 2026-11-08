<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Factory;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model\Registry;
use Isotope\CoreBundle\Attribute\AttributeRegistry;
use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Product\AvailabilityChecker;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\Product\DefaultPriceFactory;
use Isotope\CoreBundle\Product\Factory\StandardProductFactory;
use Isotope\CoreBundle\Product\Render\DefaultProductRenderer;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\ItemCollector\ProductCollector;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Util\ProductCategoryUtil;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Translation\TranslatableMessage;

#[BackupGlobals(true)]
final class StandardProductFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Registry::getInstance()->reset();

        parent::tearDown();
    }

    #[DataProvider('parentPublicationProvider')]
    public function testVariantRequiresPublishedParent(bool $published, int $startOffset, int $stopOffset, bool $preview, bool $available): void
    {
        $GLOBALS['TL_MODELS']['tl_iso_producttype'] = ProductTypeModel::class;
        $type = $this->getMockBuilder(ProductTypeModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get', 'getAttributes', 'getVariantAttributes', 'hasVariants', 'onRegister', 'onUnregister'])
            ->getMock();
        $type->method('__get')->willReturnMap([['id', 42], ['shipping_exempt', '']]);
        $type->method('getAttributes')->willReturn(['published', 'start', 'stop']);
        $type->method('getVariantAttributes')->willReturn(['published', 'start', 'stop']);
        $type->expects($this->once())->method('onRegister');
        Registry::getInstance()->register($type);

        $columns = ['id', 'pid', 'type', 'published', 'start', 'stop', 'inherit'];
        $attributes = $this->createStub(AttributeRegistry::class);
        $attributes->method('getSystemColumns')->willReturn($columns);
        $tokenChecker = $this->createStub(TokenChecker::class);
        $tokenChecker->method('isPreviewMode')->willReturn($preview);
        $categories = $this->createStub(ProductCategoryUtil::class);
        $categories->method('getPublishedForProductInCurrentRoot')->willReturn([1]);
        $calculator = $this->createStub(PriceCalculator::class);
        $calculator->method('calculateProduct')->willReturn(new CalculatedPrice(10, 10));
        $dispatcher = new EventDispatcher();

        $factory = new StandardProductFactory(
            new AvailabilityChecker($tokenChecker, $categories),
            $this->createStub(DefaultProductRenderer::class),
            $this->createStub(DefaultPriceFactory::class),
            $attributes,
            $calculator,
            $dispatcher,
        );
        $context = $this->createStoreContext();
        $variant = $factory->create(new ProductDto('tl_iso_product.2', 1, [
            'id' => 2,
            'pid' => 1,
            'type' => 0,
            'published' => true,
            'start' => '',
            'stop' => '',
            'inherit' => '',
            '__parent' => [
                'id' => 1,
                'type' => 42,
                'published' => $published,
                'start' => $startOffset ? (string) (time() + $startOffset) : '',
                'stop' => $stopOffset ? (string) (time() + $stopOffset) : '',
            ],
        ]), $context);

        $this->assertSame($available, $variant->isAvailable());

        $item = (new LineItem(LineItem::TYPE_PRODUCT, $variant->getIdentifier()))->setProduct($variant);
        $original = new Cart('cart');
        $original->add($item);
        $calculated = new Cart('cart');
        (new ProductCollector($calculator, $dispatcher))->process($calculated, $original, $context);

        $this->assertSame(!$available, $calculated->hasErrors());

        if (!$available) {
            $this->assertEquals([new TranslatableMessage('ERR.collectionItemNotAvailable', [], 'contao_default')], $item->getErrors());
        }
    }

    public static function parentPublicationProvider(): iterable
    {
        yield 'published parent' => [true, 0, 0, false, true];
        yield 'unpublished parent' => [false, 0, 0, false, false];
        yield 'future parent' => [true, 3600, 0, false, false];
        yield 'expired parent' => [true, 0, -3600, false, false];
        yield 'parent within publication period' => [true, -3600, 3600, false, true];
        yield 'unpublished parent in preview' => [false, 0, 0, true, true];
        yield 'future parent in preview' => [true, 3600, 0, true, true];
        yield 'expired parent in preview' => [true, 0, -3600, true, true];
    }
}
