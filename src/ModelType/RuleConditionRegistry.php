<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Model\RuleCondition;
use Isotope\CoreBundle\RuleBuilder\Condition\RuleConditionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<RuleConditionInterface, RuleCondition>
 */
#[Autoconfigure(public: true)]
class RuleConditionRegistry extends AbstractModelTypeRegistry
{
    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, RuleConditionInterface::class);
    }
}
