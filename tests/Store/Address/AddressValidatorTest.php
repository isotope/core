<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Address;

use Isotope\CoreBundle\Store\Address\AddressValidator;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class AddressValidatorTest extends TestCase
{
    #[DataProvider('orderAddressProvider')]
    public function testOrderAddressConstraints(array $order, string|null $expectedPath): void
    {
        $validator = Validation::createValidator();
        $context = $this->createStoreContext();
        $addressValidator = $this->getMockBuilder(AddressValidator::class)
            ->setConstructorArgs([$validator])
            ->onlyMethods(['getConstraint'])
            ->getMock()
        ;

        $addressValidator
            ->expects($this->exactly(2))
            ->method('getConstraint')
            ->willReturnMap([
                [$context->getBillingAddressConfig(), new Assert\Collection(fields: ['country' => new Assert\Choice(['ch'])], allowExtraFields: true)],
                [$context->getShippingAddressConfig(), new Assert\Collection(fields: ['country' => new Assert\Choice(['de'])], allowExtraFields: true)],
            ])
        ;

        $violations = $validator->validate($order, $addressValidator->getOrderConstraints($context));

        if (null === $expectedPath) {
            $this->assertCount(0, $violations);
        } else {
            $this->assertCount(1, $violations);
            $this->assertSame($expectedPath, $violations[0]->getPropertyPath());
        }
    }

    public static function orderAddressProvider(): iterable
    {
        yield 'billing required' => [[], '[billing_address]'];
        yield 'billing cannot be null' => [['billing_address' => null], '[billing_address]'];
        yield 'billing must be an array' => [['billing_address' => 'invalid'], '[billing_address]'];
        yield 'shipping optional' => [['billing_address' => ['country' => 'ch']], null];
        yield 'separate address configurations' => [['billing_address' => ['country' => 'ch'], 'shipping_address' => ['country' => 'de']], null];
        yield 'invalid billing' => [['billing_address' => ['country' => 'de']], '[billing_address][country]'];
        yield 'invalid shipping' => [['billing_address' => ['country' => 'ch'], 'shipping_address' => ['country' => 'ch']], '[shipping_address][country]'];
        yield 'additional properties' => [['billing_address' => ['country' => 'ch', 'custom_column' => 'value'], 'line_items' => [], 'custom_column' => 'value'], null];
        yield 'shipping cannot be null when supplied' => [['billing_address' => ['country' => 'ch'], 'shipping_address' => null], '[shipping_address]'];
        yield 'shipping must be an array' => [['billing_address' => ['country' => 'ch'], 'shipping_address' => 'invalid'], '[shipping_address]'];

        foreach (['id', 'pid', 'ptable', 'tstamp', 'store_id'] as $field) {
            yield 'reserved billing '.$field => [['billing_address' => ['country' => 'ch', $field => null]], '[billing_address]['.$field.']'];
            yield 'reserved shipping '.$field => [['billing_address' => ['country' => 'ch'], 'shipping_address' => ['country' => 'de', $field => null]], '[shipping_address]['.$field.']'];
        }
    }
}
