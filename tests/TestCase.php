<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test;

use Contao\TestCase\ContaoTestCase;
use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\Store\Address\AddressConfig;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\Currency;
use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Store\Price\PriceDisplay;
use Isotope\CoreBundle\Store\Price\TaxCalculation;

class TestCase extends ContaoTestCase
{
    protected function createStoreContext(PriceDisplay $priceDisplay = PriceDisplay::Gross, TaxCalculation $taxCalculation = TaxCalculation::Vertical): StoreContext
    {
        return new StoreContext(
            '__identifier__',
            0,
            0,
            0,
            [],
            'en',
            new MoneyFormat(),
            $priceDisplay,
            $taxCalculation,
            new Currency('USD', false, true, false, 2),
            1.0,
            new AddressConfig([], [], [], 'us'),
            new AddressConfig([], [], [], 'us'),
            $this->createClassWithPropertiesStub(AddressModel::class),
        );
    }
}
