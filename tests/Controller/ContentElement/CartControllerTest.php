<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Controller\ContentElement\CartController;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CartControllerTest extends TestCase
{
    #[DataProvider('submissionProvider')]
    public function testQuantityUpdatesUseTheRecalculatedCart(bool $ajax): void
    {
        $cart = new Cart('cart');
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1'))->setId(1));
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.2'))->setId(2));

        $intermediateCart = new Cart('cart');
        $intermediateCart->setLineItems($cart->getLineItems());

        $updatedCart = new Cart('cart');
        $updatedCart->setLineItems($cart->getLineItems());

        $context = $this->createStoreContext();
        $context->setCart($cart);

        $updates = 0;

        $manager = $this->createMock(CartManager::class);
        $manager
            ->expects($this->exactly(2))
            ->method('changeQuantity')
            ->willReturnCallback(
                function ($collection, int $id, int $quantity, $storeContext) use ($cart, $intermediateCart, $updatedCart, $context, &$updates): Cart {
                    $this->assertSame($context, $storeContext);
                    $this->assertSame(0 === $updates ? $cart : $intermediateCart, $collection);
                    $this->assertSame($collection, $context->getCart());
                    $this->assertSame($updates + 1, $id);
                    $this->assertSame($updates + 2, $quantity);

                    return 0 === $updates++ ? $intermediateCart : $updatedCart;
                },
            )
        ;

        $response = new Response('Updated cart');
        $template = new FragmentTemplate('cart', static fn () => $response);

        $controller = $this->getMockBuilder(CartController::class)
            ->setConstructorArgs([$manager])
            ->onlyMethods(['isBackendScope', 'getStoreContext', 'createTemplate', 'getType', 'addDefaultDataToTemplate', 'tagResponse', 'addCollectionToTemplate'])
            ->getMock()
        ;
        $controller
            ->method('isBackendScope')
            ->willReturn(false)
        ;
        $controller
            ->method('getStoreContext')
            ->willReturn($context)
        ;
        $controller
            ->method('createTemplate')
            ->willReturn($template)
        ;
        $controller
            ->method('getType')
            ->willReturn('iso_cart')
        ;
        $controller
            ->expects($ajax ? $this->once() : $this->never())
            ->method('addCollectionToTemplate')
            ->with($template, $this->identicalTo($updatedCart), $context, 0)
            ->willReturnCallback(
                function () use ($context, $updatedCart, &$updates): void {
                    $this->assertSame(2, $updates);
                    $this->assertSame($updatedCart, $context->getCart());
                },
            )
        ;

        $model = $this->createClassWithPropertiesStub(ContentModel::class, ['id' => 42, 'iso_gallery' => 0]);

        $request = Request::create(
            '/cart?view=full',
            Request::METHOD_POST,
            [
                'FORM_SUBMIT' => 'cte42',
                'quantity' => [1 => '2', 2 => '3'],
            ],
        );

        if ($ajax) {
            $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            $request->headers->set('Isotope-Fragment', 'cte42');
        }

        try {
            $result = $controller($request, $model, 'main');
        } catch (ResponseException $exception) {
            $result = $exception->getResponse();
        }

        if ($ajax) {
            $this->assertSame($response, $result);
        } else {
            $this->assertSame(Response::HTTP_SEE_OTHER, $result->getStatusCode());
            $this->assertSame('/cart?view=full', $result->headers->get('Location'));
        }

        $this->assertSame(2, $updates);
        $this->assertSame($updatedCart, $context->getCart());
    }

    public static function submissionProvider(): iterable
    {
        yield 'regular POST redirects' => [false];
        yield 'AJAX POST renders updated fragment' => [true];
    }

    #[DataProvider('unhandledRequestProvider')]
    public function testUnhandledRequestsRenderWithoutRedirecting(string $method, array $parameters): void
    {
        $cart = new Cart('cart');
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.1'))->setId(1));

        $context = $this->createStoreContext();
        $context->setCart($cart);

        $manager = $this->createMock(CartManager::class);
        $manager
            ->expects($this->never())
            ->method('changeQuantity')
        ;

        $response = new Response('Cart');
        $template = new FragmentTemplate('cart', static fn () => $response);

        $controller = $this->getMockBuilder(CartController::class)
            ->setConstructorArgs([$manager])
            ->onlyMethods(['isBackendScope', 'getStoreContext', 'createTemplate', 'getType', 'addDefaultDataToTemplate', 'tagResponse', 'addCollectionToTemplate'])
            ->getMock()
        ;
        $controller
            ->method('isBackendScope')
            ->willReturn(false)
        ;
        $controller
            ->method('getStoreContext')
            ->willReturn($context)
        ;
        $controller
            ->method('createTemplate')
            ->willReturn($template)
        ;
        $controller
            ->method('getType')
            ->willReturn('iso_cart')
        ;
        $controller
            ->expects($this->once())
            ->method('addCollectionToTemplate')
            ->with($template, $this->identicalTo($cart), $context, 0)
        ;

        $model = $this->createClassWithPropertiesStub(ContentModel::class, ['id' => 42, 'iso_gallery' => 0]);

        $this->assertSame($response, $controller(Request::create('/cart', $method, $parameters), $model, 'main'));
    }

    public static function unhandledRequestProvider(): iterable
    {
        yield 'GET request' => [Request::METHOD_GET, ['FORM_SUBMIT' => 'cte42', 'quantity' => [1 => '2']]];
        yield 'another form' => [Request::METHOD_POST, ['FORM_SUBMIT' => 'cte43', 'quantity' => [1 => '2']]];
        yield 'no quantities' => [Request::METHOD_POST, ['FORM_SUBMIT' => 'cte42']];
    }
}
