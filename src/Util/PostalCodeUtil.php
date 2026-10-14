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
                    $range = range($parts[0], $parts[1]);

                    if (
                        preg_match('/^(0?)[0-9]+$/', $parts[0], $start)
                        && preg_match('/^(0?)[0-9]+$/', $parts[1], $end)
                        && ('0' === $start[1] || '0' === $end[1])
                    ) {
                        $length = max(\strlen($parts[0]), \strlen($parts[1]));
                        $range = array_map(static fn ($code): string => str_pad((string) $code, $length, '0', STR_PAD_LEFT), $range);
                    }

                    $codes = array_merge($codes, $range);
                    break;
            }
        }

        return $codes;
    }
}
