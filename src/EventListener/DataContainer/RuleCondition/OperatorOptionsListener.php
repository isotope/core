<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\RuleCondition;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\ModelType\Exception\FactoryNotFoundException;
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
        try {
            return $this->conditionRegistry
                ->get(RuleConditionModel::findById($id), $this->contextFactory->createBackend())
                ?->getOperators() ?? [];
        } catch (FactoryNotFoundException) {
            return [];
        }
    }
}
