<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\ShippingModel;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Weight\Weight;

/**
 * @template TType of AbstractShipping
 *
 * @extends AbstractCheckoutMethodFactory<AbstractShipping, ShippingModel>
 */
abstract class AbstractShippingFactory extends AbstractCheckoutMethodFactory
{
    use ModelInstanceTrait;

    /**
     * @param ShippingModel $model
     *
     * @return TType
     */
    #[\Override]
    public function create(Model $model, StoreContext $context): AbstractShipping
    {
        $this->validateInstanceOf($model, ShippingModel::class);

        /** @var AbstractShipping $shipping */
        $shipping = parent::create($model, $context);
        $shipping
            ->setMinMaxWeight(Weight::createFromTimePeriod($model->minimum_weight), Weight::createFromTimePeriod($model->maximum_weight))
            ->setShippingWeight(Weight::createFromTimePeriod($model->shipping_weight))
            ->setTrackingUrl(empty($model->tracking_url) ? null : $model->tracking_url)
        ;

        return $shipping;
    }
}
