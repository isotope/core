<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\SerializedPropertyTrait;
use Isotope\Frontend;
use Isotope\Haste\Units\Mass\Weight;

/**
 * @template TType of AbstractShipping
 *
 * @extends AbstractCheckoutMethodFactory<AbstractShipping, Shipping>
 */
abstract class AbstractShippingFactory extends AbstractCheckoutMethodFactory
{
    use SerializedPropertyTrait;

    /**
     * @param Shipping $model
     *
     * @return TType
     */
    public function create(Model $model): AbstractShipping
    {
        if (!$model instanceof Shipping) {
            throw new \LogicException(\sprintf('%s should be an instance of %s', $model::class, Shipping::class));
        }

        /** @var AbstractShipping $shipping */
        $shipping = parent::create($model);

        $shipping
            ->setMinMaxWeight((float) $model->minimum_weight, (float) $model->maximum_weight)
            ->setShippingWeight(Weight::createFromTimePeriod($model->shipping_weight))
            ->setAddressType(AddressType::tryFrom($model->address_type))
            ->setAllowedAddress(
                $model->countries ? explode(',', $model->countries) : null,
                $model->subdivisions ? explode(',', $model->subdivisions) : null,
                Frontend::parsePostalCodes($model->postalCodes) ?: null,
            )
        ;

        return $shipping;
    }
}
