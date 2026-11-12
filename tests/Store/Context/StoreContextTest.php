<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Context;

use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StoreContextTest extends TestCase
{
    public function testInitialTimestampUsesUtc(): void
    {
        $previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Asia/Dhaka');

        try {
            $context = $this->createStoreContext();

            $this->assertSame('UTC', $context->getTimestamp()->getTimezone()->getName());
        } finally {
            date_default_timezone_set($previousTimezone);
        }
    }

    #[DataProvider('timestampProvider')]
    public function testSetTimestampPreservesInstantInUtc(string $value, string $expected): void
    {
        $context = $this->createStoreContext();
        $timestamp = new \DateTimeImmutable($value);
        $originalTimezone = $timestamp->getTimezone()->getName();

        $context->setTimestamp($timestamp);

        $this->assertSame('UTC', $context->getTimestamp()->getTimezone()->getName());
        $this->assertSame($expected, $context->getTimestamp()->format('Y-m-d H:i:s.u'));
        $this->assertSame($timestamp->format('U.u'), $context->getTimestamp()->format('U.u'));
        $this->assertSame($originalTimezone, $timestamp->getTimezone()->getName());
    }

    public static function timestampProvider(): iterable
    {
        yield 'positive offset crosses to previous day' => ['2026-01-02T01:30:00.123456+06:00', '2026-01-01 19:30:00.123456'];
        yield 'negative offset crosses to next day' => ['2026-01-01T22:30:00.000000-06:00', '2026-01-02 04:30:00.000000'];
        yield 'named timezone in summer' => ['2026-07-01 13:30:00 Europe/Zurich', '2026-07-01 11:30:00.000000'];
        yield 'already UTC' => ['2026-01-01 12:00:00 UTC', '2026-01-01 12:00:00.000000'];
    }
}
