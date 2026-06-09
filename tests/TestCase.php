<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function createStoreContext(): StoreContext
    {
        return new StoreContext(
            '__identifier__',
            0,
            0,
            0,
            [],
            'en',
            new MoneyFormat(),
            PriceDisplay::Gross,
            TaxCalculation::Horizontal,
            new Currency('USD', false, true, false, 2),
            1.0,
            new AddressConfig([], [], [], 'us'),
            new AddressConfig([], [], [], 'us'),
            new AddressModel()
        );
    }
}
