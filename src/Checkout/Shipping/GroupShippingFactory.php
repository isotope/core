<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Twig\Environment;

/**
 * @extends AbstractShippingFactory<GroupShipping>
 */
class GroupShippingFactory extends AbstractShippingFactory
{
    public function __construct(
        private readonly ShippingRegistry $shippingRegistry,
    ) {
    }

    /**
     * @param Shipping $model
     */
    public function doCreate(Model $model, StoreContext $context): AbstractShipping
    {
        $ids = StringUtil::deserialize($model->group_methods, true);

        // Prevent recursion if we should load ourselves
        if (($key = array_search($model->id, $ids, true)) !== false) {
            unset($ids[$key]);
        }

        return new GroupShipping(
            $model->id,
            $context,
            $this->shippingRegistry->get(Shipping::findMultipleByIds($ids), $context) ?? [],
            GroupCalculation::from($model->group_calculation),
            (bool) $model->inherit,
        );
    }
}
