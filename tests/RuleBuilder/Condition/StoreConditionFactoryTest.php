<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\RuleBuilder\Condition;

use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\RuleBuilder\Condition\StoreConditionFactory;
use Isotope\CoreBundle\RuleBuilder\Condition\StoreDateCondition;
use Isotope\CoreBundle\RuleBuilder\Condition\StoreTimeCondition;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

final class StoreConditionFactoryTest extends TestCase
{
    public function testCreateTimeConditionUsesConfiguredTimezone(): void
    {
        $previousTimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Zurich');

        try {
            $model = $this->createClassWithPropertiesStub(RuleConditionModel::class, [
                'type' => 'store_time',
                'start_time' => (string) (new \DateTimeImmutable('1970-01-01 13:00:00'))->getTimestamp(),
                'stop_time' => (string) (new \DateTimeImmutable('1970-01-01 14:00:00'))->getTimestamp(),
            ]);
            $context = $this->createStoreContext();
            $context->setTimestamp(new \DateTimeImmutable('2026-07-01T11:30:00+00:00'));
            $factory = new StoreConditionFactory($this->createStub(TranslatorInterface::class));

            $condition = $factory->create($model, $context);

            $this->assertInstanceOf(StoreTimeCondition::class, $condition);
            $this->assertTrue($condition->isApplicable($context));
        } finally {
            date_default_timezone_set($previousTimezone);
        }
    }

    #[DataProvider('dateBoundaryProvider')]
    public function testCreateDateCondition(string|null $start, string|null $stop, bool $valid, bool $applicable): void
    {
        $model = $this->createClassWithPropertiesStub(RuleConditionModel::class, [
            'type' => 'store_date',
            'start' => $start,
            'stop' => $stop,
        ]);
        $context = $this->createStoreContext();
        $context->setTimestamp(new \DateTimeImmutable('@100'));
        $factory = new StoreConditionFactory($this->createStub(TranslatorInterface::class));

        $condition = $factory->create($model, $context);

        $this->assertInstanceOf(StoreDateCondition::class, $condition);
        $this->assertSame($valid, $condition->isValid());
        $this->assertSame($applicable, $condition->isApplicable($context));
    }

    public static function dateBoundaryProvider(): iterable
    {
        yield 'bounded range' => ['50', '150', true, true];
        yield 'before start' => ['101', null, true, false];
        yield 'after stop' => ['', '99', true, false];
        yield 'start only at epoch zero' => ['0', '', true, true];
        yield 'stop only at epoch zero' => [null, '0', true, false];
        yield 'no boundaries' => ['', null, false, true];
    }
}
