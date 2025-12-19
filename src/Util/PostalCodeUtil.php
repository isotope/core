<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Util;

use Contao\StringUtil;

class PostalCodeUtil
{
    /**
     * Parses a comma-separated and/or range of items into individual items.
     *
     * Example: $value = '1,5-7';
     * Returns ['1', '5', '6', '7'],
     */
    public static function parseRange(string $value): array
    {
        $codes = [];

        foreach (StringUtil::trimsplit(',', $value) as $code) {
            $parts = StringUtil::trimsplit('-', $code);

            // Ignore codes with more than 1 range
            switch (\count($parts)) {
                case 1:
                    $codes[] = $parts[0];
                    break;

                case 2:
                    $codes = array_merge($codes, range($parts[0], $parts[1]));
                    break;
            }
        }

        return $codes;
    }
}
