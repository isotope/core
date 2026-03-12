<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RuleCondition;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\ModelType\RuleConditionRegistry;
use Isotope\CoreBundle\Store\Context\StoreContextFactory;

#[AsCallback('tl_iso_rule_condition', 'fields.operator.options')]
class OperatorOptionsListener
{
    public function __construct(
        private readonly RuleConditionRegistry $conditionRegistry,
        private readonly StoreContextFactory $contextFactory,
    ) {
    }

    public function __invoke(DataContainer $dc): array
    {
        return $this->getOperators((int) $dc->id);
    }

    private function getOperators(int $id): array
    {
        return $this->conditionRegistry->get(RuleCondition::findById($id), $this->contextFactory->createBackend())?->getOperators() ?? [];
    }
}
