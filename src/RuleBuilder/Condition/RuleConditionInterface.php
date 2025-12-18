<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\RuleBuilder\Condition;

interface RuleConditionInterface
{
    public function isValid(): bool;

    public function isApplicable(): bool;

    public function getBackendLabel(): string;

    /**
     * @return array<string, string>
     */
    public function getOperators(): array;
}
