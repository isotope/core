<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Product\Render;

use Isotope\CoreBundle\Model\ProductTypeModel;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Product\Event\ProductPriceEvent;
use Isotope\CoreBundle\Product\Render\DefaultProductRenderer;
use Isotope\CoreBundle\Product\Render\ProductConfiguration;
use Isotope\CoreBundle\Product\Render\ProductConfigurationFactory;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\CoreBundle\Product\Render\ViewInterface;
use Isotope\CoreBundle\Store\Context\ProductContext;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;

final class DefaultProductRendererTest extends TestCase
{
    public function testRenderingTwiceDoesNotApplyOptionChargesTwice(): void
    {
        $product = new CoreProduct(
            ['id' => 1, 'cssID' => []],
            $this->createClassWithPropertiesStub(ProductTypeModel::class),
            $this->createStub(ProductRendererInterface::class),
        );
        $product->setPrice(new CalculatedPrice(100, 100));
        $configurationFactory = $this->createStub(ProductConfigurationFactory::class);
        $configurationFactory
            ->method('create')
            ->willReturnCallback(static fn () => new ProductConfiguration($product, ['gift' => 'yes'], [], []))
        ;
        $view = $this->createStub(ViewInterface::class);
        $view
            ->method('useOptions')
            ->willReturn(true)
        ;

        $view
            ->method('getTemplate')
            ->willReturn('product.html.twig')
        ;
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(
            ProductPriceEvent::class,
            static function (ProductPriceEvent $event): void {
                $amount = $event->getPrice()->getUnitPrice() + 5;
                $event->setPrice(new CalculatedPrice($amount, $amount));
            },
        );
        $renderer = new DefaultProductRenderer($configurationFactory, $dispatcher, new TemplateMarkupFactory($this->createStub(Environment::class)));
        $context = new ProductContext($product, $this->createStoreContext());
        $request = Request::create('/product');
        $first = $renderer->render($context, $view, $request);
        $second = $renderer->render($context, $view, $request);

        $this->assertEqualsWithDelta(105.0, $first['product']->getPrice()->getUnitPrice(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(105.0, $second['product']->getPrice()->getUnitPrice(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(100.0, $product->getPrice()->getUnitPrice(), PHP_FLOAT_EPSILON);
        $this->assertSame($first['product'], $first['configuration']->getProduct());
        $this->assertSame($second['product'], $second['configuration']->getProduct());
    }
}
