<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Model\ShippingModel;
use Isotope\CoreBundle\ModelType\ShippingRegistry;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\StringUtil;

/**
 * @extends AbstractShippingFactory<GroupShipping>
 */
class GroupShippingFactory extends AbstractShippingFactory
{
    public function __construct(private readonly ShippingRegistry $shippingRegistry)
    {
    }

    /**
     * @param ShippingModel $model
     */
    public function doCreate(Model $model, StoreContext $context): AbstractShipping
    {
        $ids = StringUtil::csv($model->group_methods, intval(...), true);

        // Prevent recursion if we should load ourselves
        if (false !== ($key = array_search((int) $model->id, $ids, true))) {
            unset($ids[$key]);
        }

        return new GroupShipping(
            $model->id,
            $context,
            $this->shippingRegistry->get(ShippingModel::findMultipleByIds($ids), $context) ?? [],
            GroupCalculation::from($model->group_calculation),
            (int) $model->tax_class,
            (bool) $model->inherit,
        );
    }
}
