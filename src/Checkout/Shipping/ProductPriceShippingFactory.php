<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;

/**
 * @extends AbstractShippingFactory<ProductPriceShipping>
 */
class ProductPriceShippingFactory extends AbstractShippingFactory
{
    public function __construct(
        private readonly TokenChecker $tokenChecker,
        private readonly RuleMatcher $ruleMatcher,
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
            $this->tokenChecker,
            $this->ruleMatcher,
        );
    }
}
