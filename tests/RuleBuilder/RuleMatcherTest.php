<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\RuleBuilder;

use Contao\Model\Collection;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\Model\RuleModel;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\RuleBuilder\Condition\RuleConditionInterface;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Test\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class RuleMatcherTest extends TestCase
{
    #[DataProvider('nestedRuleProvider')]
    public function testNestedRulesDoNotRecurseIndefinitely(array $references, bool $expected): void
    {
        $ruleAdapter = $this->createAdapterStub(['findById']);
        $ruleAdapter
            ->method('findById')
            ->willReturn($this->createClassWithPropertiesStub(RuleModel::class, ['enabled' => true]))
        ;
        $conditionAdapter = $this->createAdapterStub(['findByRuleId']);
        $conditionAdapter
            ->method('findByRuleId')
            ->willReturnCallback(
                function (int $ruleId) {
                    $collection = $this->createStub(Collection::class);
                    $collection
                        ->method('getModels')
                        ->willReturn([$this->createClassWithPropertiesStub(RuleConditionModel::class, ['pid' => $ruleId])])
                    ;

                    return $collection;
                },
            )
        ;
        $registry = $this->createStub(RuleConditionRegistry::class);
        $matcher = new RuleMatcher($this->createContaoFrameworkStub([
            RuleModel::class => $ruleAdapter,
            RuleConditionModel::class => $conditionAdapter,
        ]), $registry);
        $calls = 0;
        $registry
            ->method('get')
            ->willReturnCallback(
                function (RuleConditionModel $model) use ($matcher, $references, &$calls): object {
                    $condition = $this->createStub(RuleConditionInterface::class);
                    $condition
                        ->method('isValid')
                        ->willReturn(true)
                    ;
                    $condition
                        ->method('isApplicable')
                        ->willReturnCallback(
                            static function (StoreContext $context) use ($model, $matcher, $references, &$calls): bool {
                                if (++$calls > 10) {
                                    throw new \RuntimeException('Nested rule evaluation did not terminate.');
                                }

                                $nestedId = $references[$model->pid];

                                return null === $nestedId || $matcher->isApplicable($nestedId, $context);
                            },
                        )
                    ;

                    return $condition;
                },
            )
        ;
        $context = $this->createStoreContext();

        $this->assertSame($expected, $matcher->isApplicable(1, $context));
        $this->assertSame($expected, $matcher->isApplicable(1, $context), 'Evaluation state must be cleared after each call.');
    }

    public static function nestedRuleProvider(): iterable
    {
        yield 'direct cycle' => [[1 => 1], false];
        yield 'indirect cycle' => [[1 => 2, 2 => 1], false];
        yield 'acyclic nesting' => [[1 => 2, 2 => null], true];
    }
}
