<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order\Validation;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Order\Validation\OrderValidator;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

final class OrderValidatorTest extends TestCase
{
    public function testLineItemsAreHandledBySeparateValidation(): void
    {
        $this->assertCount(0, $this->validateOrder([]));
        $this->assertCount(0, $this->validateOrder(['line_items' => 'not validated here']));
    }

    public function testAdditionalPropertiesAndExtensionsAreAccepted(): void
    {
        $this->assertCount(
            0,
            $this->validateOrder([
                'custom_column' => 'value',
                'extensions' => ['custom' => ['value' => 42]],
            ]),
        );
    }

    #[DataProvider('invalidOrderProvider')]
    public function testOrderFieldsAreValidated(string $field, mixed $value): void
    {
        $violations = $this->validateOrder([$field => $value]);

        $this->assertGreaterThan(0, $violations->count());
        $this->assertSame('['.$field.']', $violations[0]->getPropertyPath());
    }

    public static function invalidOrderProvider(): iterable
    {
        yield 'invalid config' => ['config_id', -1];
        yield 'invalid locale' => ['locale', 'invalid'];
        yield 'negative subtotal' => ['subtotal', -1];
        yield 'negative total' => ['total', -1];
        yield 'invalid currency' => ['currency', 'invalid'];
        yield 'invalid extensions' => ['extensions', 'invalid'];
        yield 'forbidden notes' => ['notes', 'not allowed'];
        yield 'different config' => ['config_id', 2];
        yield 'different store' => ['store_id', 1];
        yield 'different member' => ['member_id', 2];
        yield 'different locale' => ['locale', 'de'];
        yield 'different currency' => ['currency', 'EUR'];

        foreach (['id', 'type', 'uniqid', 'tstamp', 'locked', 'document_number', 'billing_address_id', 'shipping_address_id'] as $field) {
            yield 'reserved '.$field => [$field, null];
        }
    }

    #[DataProvider('memberProvider')]
    public function testGuestAndMemberIds(int|null $contextMember, int|null $orderMember, bool $valid): void
    {
        $context = $this->createStoreContext()->setConfigId(1)->setMemberId($contextMember);
        $violations = $this->validateOrder(['member_id' => $orderMember], $context);

        if ($valid) {
            $this->assertCount(0, $violations);
        } else {
            $this->assertGreaterThan(0, $violations->count());
            $this->assertSame('[member_id]', $violations[0]->getPropertyPath());
        }
    }

    public static function memberProvider(): iterable
    {
        yield 'guest zero' => [0, 0, true];
        yield 'guest null' => [null, null, true];
        yield 'null context and zero order' => [null, 0, true];
        yield 'zero context and null order' => [0, null, true];
        yield 'member order' => [42, 42, true];
        yield 'guest cannot claim member' => [null, 42, false];
        yield 'member cannot claim guest zero' => [42, 0, false];
        yield 'member cannot claim guest null' => [42, null, false];
        yield 'member cannot claim another member' => [42, 43, false];
    }

    private function validateOrder(array $data, StoreContext|null $context = null): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $context ??= $this->createStoreContext()->setConfigId(1)->setMemberId(1);
        $constraints = (new OrderValidator())->getConstraints($context);

        return $validator->validate(
            array_replace(
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
                ],
                $data,
            ),
            $constraints,
        );
    }
}
