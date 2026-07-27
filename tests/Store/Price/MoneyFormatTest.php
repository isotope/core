<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Price;

use Isotope\CoreBundle\Store\Price\MoneyFormat;
use Isotope\CoreBundle\Test\TestCase;

final class MoneyFormatTest extends TestCase
{
    public function testNegativeZeroHandling(): void
    {
        $service = new MoneyFormat();

        $result = $service->mathRound(-0.001);

        // Convert to string to check if it's positive zero (should not start with '-')
        $this->assertSame('0', (string) $result);
    }
}
