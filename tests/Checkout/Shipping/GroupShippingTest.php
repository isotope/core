<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Checkout\Shipping;

use Isotope\CoreBundle\Checkout\Shipping\GroupCalculation;
use Isotope\CoreBundle\Checkout\Shipping\GroupShipping;
use Isotope\CoreBundle\Checkout\Shipping\ShippingInterface;
use Isotope\CoreBundle\Store\Price\PriceCalculator;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class GroupShippingTest extends TestCase
{
    #[DataProvider('firstPriceProvider')]
    public function testFirstAvailablePrice(bool $available, bool $hasPrice, bool $expectPrice): void
    {
        $definition = new PriceDefinition(5, null);

        $method = $this->createStub(ShippingInterface::class);
        $method
            ->method('getId')
            ->willReturn(1)
        ;
        $method
            ->method('isAvailable')
            ->willReturn($available)
        ;
        $method
            ->method('getPriceDefinition')
            ->willReturn($hasPrice ? $definition : null)
        ;

        $calculator = $this->createStub(PriceCalculator::class);

        $shipping = new GroupShipping(2, $this->createStoreContext(), [$method], GroupCalculation::first, 0, false);
        $shipping->setContainer(new ServiceLocator([PriceCalculator::class => static fn () => $calculator]));

        $warnings = [];

        set_error_handler(
            static function (int $severity, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            },
            E_WARNING,
        );

        try {
            $price = $shipping->getPriceDefinition();
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $warnings);
        $this->assertSame($expectPrice ? $definition : null, $price);
    }

    public static function firstPriceProvider(): iterable
    {
        yield 'unavailable method' => [false, true, false];
        yield 'method without price' => [true, false, false];
        yield 'available priced method' => [true, true, true];
    }
}
