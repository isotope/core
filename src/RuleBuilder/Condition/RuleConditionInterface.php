<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

use Isotope\CoreBundle\Store\Context\StoreContext;

interface RuleConditionInterface
{
    public function isValid(): bool;

    public function isApplicable(StoreContext $context): bool;

    public function getBackendLabel(): string;

    /**
     * @return array<string, string>
     */
    public function getOperators(): array;
}
