<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\RuleBuilder\Condition;

use Isotope\CoreBundle\RuleBuilder\Condition\StoreTimeCondition;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StoreTimeConditionTest extends TestCase
{
    #[DataProvider('timeProvider')]
    public function testDailyTimeRange(string $now, bool $expected): void
    {
        $timezone = new \DateTimeZone('Europe/Zurich');
        $condition = new StoreTimeCondition(
            new \DateTimeImmutable('1970-01-01 13:00:00', $timezone),
            new \DateTimeImmutable('2000-02-02 14:00:00', $timezone),
        );
        $context = $this->createStoreContext();
        $context->setTimestamp(new \DateTimeImmutable($now));

        $this->assertSame($expected, $condition->isApplicable($context));
    }

    public static function timeProvider(): iterable
    {
        yield 'before start' => ['2026-01-01T11:59:59+00:00', false];
        yield 'exact start' => ['2026-01-01T12:00:00+00:00', true];
        yield 'within range' => ['2026-01-01T12:30:00+00:00', true];
        yield 'exact stop' => ['2026-01-01T13:00:00+00:00', true];
        yield 'after stop' => ['2026-01-01T13:00:01+00:00', false];
        yield 'different day' => ['2027-02-03T12:30:00+00:00', true];
        yield 'summer timezone offset' => ['2026-07-01T11:30:00+00:00', true];
        yield 'outside summer range' => ['2026-07-01T12:30:00+00:00', false];
    }
}
