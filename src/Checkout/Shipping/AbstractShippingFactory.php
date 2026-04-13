<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Weight\Weight;

/**
 * @template TType of AbstractShipping
 *
 * @extends AbstractCheckoutMethodFactory<AbstractShipping, Shipping>
 */
abstract class AbstractShippingFactory extends AbstractCheckoutMethodFactory
{
    use ModelInstanceTrait;

    /**
     * @param Shipping $model
     *
     * @return TType
     */
    public function create(Model $model, StoreContext $context): AbstractShipping
    {
        $this->validateInstanceOf($model, Shipping::class);

        /** @var AbstractShipping $shipping */
        $shipping = parent::create($model, $context);
        $shipping
            ->setMinMaxWeight((float) $model->minimum_weight, (float) $model->maximum_weight)
            ->setShippingWeight(Weight::createFromTimePeriod($model->shipping_weight))
        ;

        return $shipping;
    }
}
