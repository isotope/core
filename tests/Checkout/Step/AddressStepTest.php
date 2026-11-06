<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Checkout\Step;

use Codefog\HasteBundle\Form\Form;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Isotope\CoreBundle\Checkout\Event\CheckoutStepEvent;
use Isotope\CoreBundle\Checkout\Step\AddressStep;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Address\AddressFormatter;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Address\FormBuilder;
use Isotope\CoreBundle\Store\AddressManager;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class AddressStepTest extends TestCase
{
    #[DataProvider('submissionProvider')]
    public function testGuestSubmission(array|bool $billingResult, array|bool $shippingResult, bool $addShipping, bool $skipShipping, bool $listenerError): void
    {
        $context = $this->createStoreContext();
        $cart = new Cart('test');
        $cart->add(new LineItem(LineItem::TYPE_PRODUCT, 'test'));
        $cart->setBillingAddress($this->createClassWithPropertiesStub(AddressModel::class, ['id' => 1]));
        $cart->setShippingAddress($this->createClassWithPropertiesStub(AddressModel::class, ['id' => 2]));
        $context->setCart($cart);

        $billingForm = $this->createStub(Form::class);
        $shippingForm = $this->createStub(Form::class);
        $builder = $this->createMock(FormBuilder::class);
        $builder
            ->method('build')
            ->willReturnCallback(static fn ($request, $address, $config, string $prefix) => 'billing_' === $prefix ? $billingForm : $shippingForm)
        ;

        $validateShipping = $addShipping && !$skipShipping;
        $builder
            ->expects($this->exactly($validateShipping ? 2 : 1))
            ->method('validate')
            ->willReturnCallback(static fn (Form $form): array|bool => $form === $billingForm ? $billingResult : $shippingResult)
        ;

        $twig = new Environment(new ArrayLoader());
        $builder
            ->method('render')
            ->willReturnCallback(static fn (Form $form, array $errors) => new TemplateMarkup($twig, 'form', ['errors' => $errors]))
        ;

        $translator = $this->createStub(TranslatorInterface::class);
        $translator
            ->method('trans')
            ->willReturnArgument(0)
        ;

        $dispatcher = new EventDispatcher();

        if ($listenerError) {
            $dispatcher->addListener(CheckoutStepEvent::class, static fn (CheckoutStepEvent $event) => $event->setHasError());
        }

        $step = new AddressStep(
            $this->createStub(AddressManager::class),
            $this->createStub(AddressValidator::class),
            $this->createStub(AddressFormatter::class),
            $builder,
            $translator,
            new TemplateMarkupFactory($twig),
            $dispatcher,
        );

        $request = Request::create(
            '/checkout',
            'POST',
            [
                'edit_address_step' => '1',
                'submit_address_step' => '1',
                'add_shipping_address' => $addShipping ? '1' : '',
            ],
        );

        $success = true === $billingResult && (!$validateShipping || true === $shippingResult) && !$listenerError;

        try {
            $summary = $step->getSummary($request, $context, ['iso_checkout_skippable' => $skipShipping ? 'shipping_address' : '']);
        } catch (RedirectResponseException $exception) {
            if (!$success) {
                throw $exception;
            }

            $this->assertSame('/checkout', $exception->getResponse()->headers->get('Location'));
            $this->assertTrue($cart->isModified());
            $this->assertSame($validateShipping, null !== $cart->getShippingAddress());

            return;
        }

        $this->assertFalse($success, 'Valid submissions should redirect.');
        $this->assertFalse($summary->isCompleted());
        $this->assertFalse($cart->isModified());
        $this->assertSame($validateShipping, $summary['add_shipping_address']);
        $this->assertSame($this->expectedErrors($billingResult), $summary['billing_address_form']['errors']);

        if (!$skipShipping) {
            $this->assertSame($validateShipping ? $this->expectedErrors($shippingResult) : [], $summary['shipping_address_form']['errors']);
        }
    }

    public static function submissionProvider(): iterable
    {
        yield 'billing widget errors' => [false, true, false, false, false];
        yield 'billing address violations' => [['Invalid billing country.'], true, false, false, false];
        yield 'shipping widget errors' => [true, false, true, false, false];
        yield 'shipping address violations' => [true, ['Invalid shipping country.'], true, false, false];
        yield 'both addresses invalid' => [['Invalid billing country.'], ['Invalid shipping country.'], true, false, false];
        yield 'unused shipping is not validated' => [true, false, false, false, false];
        yield 'separate shipping succeeds' => [true, true, true, false, false];
        yield 'skipped shipping is not validated' => [true, false, true, true, false];
        yield 'listener blocks redirect' => [true, true, true, false, true];
    }

    private function expectedErrors(array|bool $result): array
    {
        return match ($result) {
            true => [],
            false => ['MSC.address_error'],
            default => $result,
        };
    }
}
