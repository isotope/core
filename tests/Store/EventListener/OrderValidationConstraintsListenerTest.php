<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\EventListener;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Event\OrderValidationEvent;
use Isotope\CoreBundle\Store\EventListener\OrderValidationConstraintsListener;
use Isotope\CoreBundle\Store\Order\OrderConverter;
use Isotope\CoreBundle\Store\Order\Transformer\LineItemTransformer;
use Isotope\CoreBundle\Store\Order\Validation\LineItemValidator;
use Isotope\CoreBundle\Store\Order\Validation\OrderValidator;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

final class OrderValidationConstraintsListenerTest extends TestCase
{
    #[DataProvider('convertedMemberProvider')]
    public function testConvertedGuestAndMemberOrdersAreAccepted(int|null $memberId): void
    {
        $context = $this->createStoreContext()->setConfigId(1)->setMemberId($memberId);
        $price = new CalculatedPrice(10, 10);

        $cart = new Cart($context->getIdentifier());
        $cart->setId(1);
        $cart->setBillingAddress($this->createClassWithPropertiesStub(AddressModel::class));
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'))->setPrice($price));
        $cart->setSubtotals([0 => $price]);
        $cart->setTotal($price);

        $converter = new OrderConverter(new EventDispatcher());

        $this->assertCount(0, $this->validateOrder($converter->convertToOrder($cart, $context), $context));
    }

    public static function convertedMemberProvider(): iterable
    {
        yield 'guest zero' => [0];
        yield 'guest null' => [null];
        yield 'registered member' => [42];
    }

    public function testConvertedLineItemsAndAdditionalPropertiesAreAccepted(): void
    {
        $item = LineItemTransformer::transform(new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'));
        $item['custom_column'] = 'custom value';
        $item['configuration'] = ['custom' => ['value' => 42]];
        $item['extensions'] = ['custom' => ['value' => 42]];

        $this->assertCount(0, $this->validateOrder(['line_items' => [$item]]));
    }

    public function testEveryLineItemIsValidated(): void
    {
        $item = LineItemTransformer::transform(new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'));
        $invalidItem = array_replace($item, ['quantity' => 0]);
        $violations = $this->validateOrder(['line_items' => [$item, $invalidItem]]);

        $this->assertCount(1, $violations);
        $this->assertSame('[line_items][1][quantity]', $violations[0]->getPropertyPath());
    }

    #[DataProvider('invalidLineItemsProvider')]
    public function testInvalidLineItemCollections(mixed $items): void
    {
        $this->assertGreaterThan(0, $this->validateOrder(['line_items' => $items])->count());
    }

    public static function invalidLineItemsProvider(): iterable
    {
        yield 'null collection' => [null];
        yield 'non-array collection' => ['invalid'];
        yield 'empty collection' => [[]];
        yield 'null item' => [[null]];
        yield 'non-array item' => [['invalid']];
        yield 'empty item' => [[[]]];
    }

    public function testLineItemsAreRequired(): void
    {
        $violations = $this->validateOrder([]);

        $this->assertCount(1, $violations);
        $this->assertSame('[line_items]', $violations[0]->getPropertyPath());
    }

    public function testOrderAndForbiddenFieldViolationsAreBothReported(): void
    {
        $violations = $this->validateOrder(['notes' => 'not allowed']);

        $this->assertCount(2, $violations);

        $paths = [$violations[0]->getPropertyPath(), $violations[1]->getPropertyPath()];
        $this->assertContains('[line_items]', $paths);
        $this->assertContains('[notes]', $paths);
    }

    public function testAllValidatorsAddConstraintsAlongsideExistingConstraints(): void
    {
        $context = $this->createStoreContext();
        $existing = new Assert\NotNull();
        $constraints = [new Assert\Type('array')];
        $event = (new OrderValidationEvent([], $context))->addConstraint($existing);

        $orderValidator = $this->createMock(OrderValidator::class);
        $orderValidator
            ->expects($this->once())
            ->method('getConstraints')
            ->with($this->identicalTo($context))
            ->willReturn($constraints)
        ;

        $lineItemValidator = $this->createMock(LineItemValidator::class);
        $lineItemConstraints = [new Assert\Type('array'), new Assert\NotNull()];

        $lineItemValidator
            ->expects($this->once())
            ->method('getOrderConstraints')
            ->willReturn($lineItemConstraints)
        ;

        $addressValidator = $this->createMock(AddressValidator::class);
        $addressConstraints = [new Assert\Type('array')];

        $addressValidator
            ->expects($this->once())
            ->method('getOrderConstraints')
            ->with($this->identicalTo($context))
            ->willReturn($addressConstraints)
        ;

        $listener = new OrderValidationConstraintsListener($orderValidator, $lineItemValidator, $addressValidator);

        $listener($event);
        $this->assertSame([$existing, ...$constraints, ...$lineItemConstraints, ...$addressConstraints], $event->getConstraints());
    }

    private function validateOrder(array $data, StoreContext|null $context = null): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $addressValidator = $this->createStub(AddressValidator::class);
        $addressValidator
            ->method('getOrderConstraints')
            ->willReturn([new Assert\Collection(fields: ['billing_address' => new Assert\Type('array')], allowExtraFields: true)])
        ;

        $order = array_replace(
            [
                'member_id' => 1,
                'config_id' => 1,
                'store_id' => 0,
                'locale' => 'en',
                'source_collection_id' => 1,
                'billing_address' => [],
                'payment_id' => 0,
                'shipping_id' => 0,
                'subtotal' => 0,
                'total' => 0,
                'currency' => 'USD',
                'extensions' => ['custom' => ['value' => 42]],
            ],
            $data,
        );

        $context ??= $this->createStoreContext()->setConfigId(1)->setMemberId(1);

        $listener = new OrderValidationConstraintsListener(
            new OrderValidator(),
            new LineItemValidator(),
            $addressValidator,
        );

        $dispatcher = new EventDispatcher();
        $dispatcher->addListener('isotope.validation.order.create', $listener);

        $event = new OrderValidationEvent($order, $context);
        $dispatcher->dispatch($event, 'isotope.validation.order.create');

        return $validator->validate($order, $event->getConstraints());
    }
}
