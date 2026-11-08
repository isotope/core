<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Isotope\CoreBundle\Checkout\Step\CheckoutStepInterface;
use Isotope\CoreBundle\Checkout\Step\CheckoutStepSummary;
use Isotope\CoreBundle\Controller\ContentElement\CheckoutController;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Order\OrderNotifier;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

final class CheckoutControllerTest extends TestCase
{
    #[DataProvider('cartErrorsProvider')]
    public function testErrorsPreventCheckoutSubmission(bool $itemError, bool $modified): void
    {
        $cart = new Cart('cart');
        $item = new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42');
        $cart->add($item);
        ($itemError ? $item : $cart)->addError(new TranslatableMessage('error'));

        $context = $this->createStoreContext();
        $original = $modified ? (new Cart('cart'))->setModified() : $cart;
        $context->setCart($original);
        $manager = $this->createMock(CartManager::class);
        $manager->expects($this->never())->method('order');
        $manager->expects($modified ? $this->once() : $this->never())
            ->method('recalculate')->with($original, $context)->willReturn($cart);
        $step = $this->createStub(CheckoutStepInterface::class);
        $step->method('getSummary')->willReturn(new CheckoutStepSummary('Complete step', true));

        $controller = $this->getMockBuilder(CheckoutController::class)
            ->setConstructorArgs([
                ['step' => $step],
                $manager,
                $this->createStub(OrderNotifier::class),
                new EventDispatcher(),
            ])
            ->onlyMethods(['isBackendScope', 'getStoreContext', 'addCollectionToTemplate'])
            ->getMock();
        $controller->method('isBackendScope')->willReturn(false);
        $controller->method('getStoreContext')->willReturn($context);
        $controller->expects($this->once())->method('addCollectionToTemplate')
            ->with($this->isInstanceOf(FragmentTemplate::class), $cart, $context, 0);

        $model = $this->createStub(ContentModel::class);
        $model->method('row')->willReturn([]);
        $model->method('__get')->willReturnMap([
            ['iso_checkout_method', ''],
            ['iso_gallery', 0],
        ]);
        $template = new FragmentTemplate('checkout', static fn () => new Response());
        $request = Request::create('/checkout', Request::METHOD_POST, ['place_order' => '1']);

        (new \ReflectionMethod($controller, 'getResponse'))->invoke($controller, $template, $model, $request);

        $this->assertFalse($template->get('completed'));
    }

    public static function cartErrorsProvider(): iterable
    {
        yield 'collection error' => [false, false];
        yield 'item error' => [true, false];
        yield 'collection error after recalculation' => [false, true];
        yield 'item error after recalculation' => [true, true];
    }
}
