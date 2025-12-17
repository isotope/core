<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Cart;


use Isotope\CoreBundle\Store\Tax\CalculatedTaxCollection;
use Isotope\CoreBundle\Store\Tax\TaxRateCollection;
use Isotope\CoreBundle\Util\FloatComparator;

class CartPrice
{
    public function __construct(
        protected float $netPrice,
        protected float $totalPrice,
        protected float $subtotalPrice,
        protected CalculatedTaxCollection $calculatedTaxes,
        protected TaxRateCollection $taxRates,
        protected string $taxStatus,
        //?float $rawTotal = null
    ) {
        $this->netPrice = FloatComparator::cast($netPrice);
        $this->totalPrice = FloatComparator::cast($totalPrice);
        $this->subtotalPrice = FloatComparator::cast($subtotalPrice);
        $rawTotal ??= $totalPrice;
        //$this->rawTotal = FloatComparator::cast($rawTotal);
    }
}
