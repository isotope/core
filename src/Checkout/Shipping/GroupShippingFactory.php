<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Contao\StringUtil;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractShippingFactory<GroupShipping>
 */
class GroupShippingFactory extends AbstractShippingFactory
{
    public function __construct(
        private readonly ShippingRegistry $shippingRegistry,
        private readonly Security $security,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    /**
     * @param Shipping $model
     */
    public function doCreate(Model $model): AbstractShipping
    {
        $ids = StringUtil::deserialize($model->group_methods, true);

        // Prevent recursion if we should load ourselves
        if (($key = array_search($model->id, $ids)) !== false) {
            unset($ids[$key]);
        }

        $instance = new GroupShipping(
            $model->id,
            $this->shippingRegistry->get(Shipping::findMultipleByIds($ids)) ?? [],
            GroupCalculation::from($model->group_calculation),
            (bool) $model->inherit,
            $this->security,
            $this->tokenChecker
        );

        return $instance;
    }
}
