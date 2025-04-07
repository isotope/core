<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Isotope\CoreBundle\Model\Shipping;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractShippingFactory<ProductPriceShipping>
 */
class ProductPriceShippingFactory extends AbstractShippingFactory
{
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
        return new ProductPriceShipping(
            $model->id,
            ProductCalculation::from($model->productCalculation),
            $this->security,
            $this->tokenChecker,
        );
    }
}
