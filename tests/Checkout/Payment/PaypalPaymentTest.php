<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Checkout\Payment;

use Codefog\HasteBundle\StringParser;
use Isotope\CoreBundle\Checkout\Payment\PaypalPayment;
use Isotope\CoreBundle\Model\OrderTransactionModel;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class PaypalPaymentTest extends TestCase
{
    #[DataProvider('discountProvider')]
    public function testPaymentAmountsAndDiscounts(array $discounts, float $expectedDiscount): void
    {
        $context = $this->createStoreContext();

        $item = new LineItem(LineItem::TYPE_PRODUCT, 'product', 3);
        $item->setLabel('Product');
        $item->setPrice(new CalculatedPrice(10, 30, quantity: 3));

        $order = new Order('test', $context);
        $order->add($item);

        foreach ($discounts as $index => $amount) {
            $discount = new LineItem(LineItem::TYPE_PRODUCT, 'discount_'.$index);
            $discount->setPrice(new CalculatedPrice($amount, $amount));
            $order->add($discount);
        }

        $parser = $this->createStub(StringParser::class);
        $parser
            ->method('convertToText')
            ->willReturnArgument(0)
        ;

        $router = $this->createStub(UrlGeneratorInterface::class);
        $router
            ->method('generate')
            ->willReturn('https://example.com/ipn')
        ;

        $markupFactory = new TemplateMarkupFactory(new Environment(new ArrayLoader()));

        $payment = new PaypalPayment(1, 'merchant@example.com', 'processing', 'failed', 'paid', $context);
        $payment->setContainer(new ServiceLocator([
            StringParser::class => static fn () => $parser,
            UrlGeneratorInterface::class => static fn () => $router,
            TemplateMarkupFactory::class => static fn () => $markupFactory,
        ]));

        $result = $payment->process(
            $order,
            $this->createClassWithPropertiesStub(OrderTransactionModel::class, ['uniqid' => 'transaction']),
            '/success',
            '/error',
        );

        $this->assertInstanceOf(TemplateMarkup::class, $result->message);
        $this->assertEqualsWithDelta($expectedDiscount, $result->message['discount'], PHP_FLOAT_EPSILON);
        $data = $result->message->getContext()['data'];
        $this->assertSame(3, $data['quantity_1']);
        $this->assertEqualsWithDelta(10.0, $data['amount_1'], PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(30.0, $data['amount_1'] * $data['quantity_1'], PHP_FLOAT_EPSILON);
    }

    public static function discountProvider(): iterable
    {
        yield 'no discount' => [[], 0.0];
        yield 'one discount' => [[-2.0], 2.0];
        yield 'multiple discounts' => [[-2.0, -3.0], 5.0];
    }
}
