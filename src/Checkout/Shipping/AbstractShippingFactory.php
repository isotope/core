<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\Frontend;
use Isotope\Haste\Units\Mass\Weight;

/**
 * @template TType of AbstractShipping
 *
 * @extends AbstractCheckoutMethodFactory<AbstractShipping, Shipping>
 */
abstract class AbstractShippingFactory extends AbstractCheckoutMethodFactory
{
    /**
     * @param Shipping $model
     *
     * @return TType
     */
    public function create(Model $model): AbstractShipping
    {
        if (!$model instanceof Shipping) {
            throw new \RuntimeException(\sprintf('%s should be an instance of %s', $model::class, Shipping::class));
        }

        return parent::create($model)
            ->setMinMaxWeight((float) $model->minimum_weight, (float) $model->maximum_weight)
            ->setShippingWeight(Weight::createFromTimePeriod($model->shipping_weight))
            ->setAddressType(AddressType::tryFrom($model->address_type))
            ->setAllowedAddress(
                $this->unserialize($model->countries, 'strval'),
                $this->unserialize($model->subdivisions, 'strval'),
                Frontend::parsePostalCodes($model->postalCodes) ?: null,
            )
        ;
    }
}
