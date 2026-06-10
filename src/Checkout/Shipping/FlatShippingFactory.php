<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Model\ShippingModel;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Weight\Weight;
use Isotope\CoreBundle\Util\StringUtil;

/**
 * @extends AbstractShippingFactory<FlatShipping>
 */
class FlatShippingFactory extends AbstractShippingFactory
{
    /**
     * @param ShippingModel $model
     */
    public function doCreate(Model $model, StoreContext $context): AbstractShipping
    {
        $instance = new FlatShipping(
            $model->id,
            $context,
            FlatCalculation::tryFrom($model->flatCalculation),
        );

        $instance
            ->setFlatProductTypes(StringUtil::csv($model->flatProductTypes, intval(...)))
            ->setFlatWeight(Weight::createFromTimePeriod($model->flatWeight))
        ;

        return $instance;
    }
}
