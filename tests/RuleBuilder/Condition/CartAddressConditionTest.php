<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\RuleBuilder\Condition;

use Contao\CoreBundle\Intl\Countries;
use Isotope\CoreBundle\Intl\Subdivisions;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\RuleBuilder\Condition\CartAddressCondition;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CartAddressConditionTest extends TestCase
{
    #[DataProvider('subdivisionProvider')]
    public function testSubdivisionRestriction(string $subdivision, array|null $allowed, bool $expected): void
    {
        $address = $this->createClassWithPropertiesStub(AddressModel::class, [
            'country' => 'ch',
            'subdivision' => $subdivision,
        ]);

        $context = $this->createStoreContext()->setDefaultBillingAddress($address);

        $condition = new CartAddressCondition(
            $this->createStub(TranslatorInterface::class),
            $this->createStub(Countries::class),
            $this->createStub(Subdivisions::class),
            'billing',
            ['ch'],
            $allowed,
            null,
            '',
        );

        $this->assertSame($expected, $condition->isApplicable($context));
    }

    public static function subdivisionProvider(): iterable
    {
        yield 'matching subdivision' => ['CH-ZH', ['CH-ZH'], true];
        yield 'different subdivision' => ['CH-BE', ['CH-ZH'], false];
        yield 'missing subdivision' => ['', ['CH-ZH'], false];
        yield 'unrestricted subdivision' => ['CH-BE', null, true];
    }
}
