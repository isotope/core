<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Util;

use Isotope\CoreBundle\Util\PostalCodeUtil;
use PHPUnit\Framework\TestCase;

final class PostalCodeUtilTest extends TestCase
{
    public function testNumericRangesPreserveLeadingZeroes(): void
    {
        self::assertSame(
            ['01000', '01001', '01002', '02002', '02001', '02000', '03000'],
            PostalCodeUtil::parseRange('01000-01002, 02002-02000, 03000'),
        );
    }

    public function testUnpaddedRangesKeepTheirExistingValues(): void
    {
        self::assertSame(['1', '5', '6', '7', 9, 10, 11], PostalCodeUtil::parseRange('1,5-7,9-11'));
    }
}
