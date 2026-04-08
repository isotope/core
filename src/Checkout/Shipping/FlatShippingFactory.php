<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\SerializedPropertyTrait;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Weight\Weight;

/**
 * @extends AbstractShippingFactory<FlatShipping>
 */
class FlatShippingFactory extends AbstractShippingFactory
{
    use SerializedPropertyTrait;

    /**
     * @param Shipping $model
     */
    public function doCreate(Model $model, StoreContext $context): AbstractShipping
    {
        $instance = new FlatShipping(
            $model->id,
            $context,
            FlatCalculation::tryFrom($model->flatCalculation),
            (int) $model->tax_class,
        );

        $instance
            ->setFlatProductTypes($this->csv($model->flatProductTypes, intval(...)))
            ->setFlatWeight(Weight::createFromTimePeriod($model->flatWeight))
        ;

        return $instance;
    }
}
