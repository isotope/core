<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\RuleBuilder\Condition;

use Isotope\CoreBundle\RuleBuilder\Condition\StoreDateCondition;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class StoreDateConditionTest extends TestCase
{
    #[DataProvider('dateProvider')]
    public function testRuleUsesInstantsAcrossTimezones(string $now, bool $expected): void
    {
        $previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Zurich');

        try {
            $context = $this->createStoreContext();
            $context->setTimestamp(new \DateTimeImmutable($now));
            $condition = new StoreDateCondition(
                new \DateTimeImmutable('2026-01-01T13:00:00+01:00'),
                new \DateTimeImmutable('2026-01-01T14:00:00+01:00'),
            );

            $this->assertSame($expected, $condition->isApplicable($context));
        } finally {
            date_default_timezone_set($previousTimezone);
        }
    }

    public static function dateProvider(): iterable
    {
        yield 'before start' => ['2026-01-01T11:59:00+00:00', false];
        yield 'within range in UTC' => ['2026-01-01T12:30:00+00:00', true];
        yield 'same instant in local timezone' => ['2026-01-01T13:30:00+01:00', true];
        yield 'after stop in UTC' => ['2026-01-01T13:01:00+00:00', false];
        yield 'one second before start' => ['2026-01-01T11:59:59+00:00', false];
        yield 'exact start' => ['2026-01-01T12:00:00+00:00', true];
        yield 'exact stop' => ['2026-01-01T13:00:00+00:00', true];
        yield 'one second after stop' => ['2026-01-01T13:00:01+00:00', false];
    }
}
