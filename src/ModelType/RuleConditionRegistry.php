<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ModelType;

use Isotope\CoreBundle\Model\RuleConditionModel;
use Isotope\CoreBundle\RuleBuilder\Condition\RuleConditionInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * @extends AbstractModelTypeRegistry<RuleConditionInterface, RuleConditionModel>
 */
#[Autoconfigure(public: true)]
class RuleConditionRegistry extends AbstractModelTypeRegistry
{
    public function __construct(ServiceProviderInterface $factories)
    {
        parent::__construct($factories, RuleConditionInterface::class);
    }
}
