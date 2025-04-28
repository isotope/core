<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\ModelType\SerializedPropertyTrait;
use Isotope\Haste\Units\Mass\Weight;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractShippingFactory<FlatShipping>
 */
class FlatShippingFactory extends AbstractShippingFactory
{
    use SerializedPropertyTrait;

    public function __construct(
        private readonly Security $security,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    /**
     * @param Shipping $model
     */
    public function doCreate(Model $model): AbstractShipping
    {
        $instance = new FlatShipping(
            $model->id,
            FlatCalculation::tryFrom($model->flatCalculation),
            $this->security,
            $this->tokenChecker,
        );

        $instance
            ->setFlatProductTypes($this->unserialize($model->flatProductTypes, intval(...)))
            ->setFlatWeight(Weight::createFromTimePeriod($model->flatWeight))
        ;

        return $instance;
    }
}
