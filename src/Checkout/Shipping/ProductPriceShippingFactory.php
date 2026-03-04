<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\Model;
use Isotope\CoreBundle\Model\Shipping;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @extends AbstractShippingFactory<ProductPriceShipping>
 */
class ProductPriceShippingFactory extends AbstractShippingFactory
{
    /**
     * @param Shipping $model
     */
    public function doCreate(Model $model, StoreContext $context): AbstractShipping
    {
        return new ProductPriceShipping(
            $model->id,
            $context,
            ProductCalculation::from($model->productCalculation),
            (int) $model->tax_class,
        );
    }
}
