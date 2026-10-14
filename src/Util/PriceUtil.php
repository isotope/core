<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Util;

use Contao\System;

class PriceUtil
{
    public static function roundBackendPrice(float|string $price): string
    {
        if (\is_string($price)) {
            if (1 === substr_count($price, ',') && !str_contains($price, '.')) {
                $price = str_replace(',', '.', $price);
            }
        }

        $price = rtrim(number_format(round((float) $price, 8), 8, '.', ''), '0');

        if ((int) strpos(strrev($price), '.') < 2) {
            $price = number_format((float) $price, 2, '.', '');
        }

        return $price;
    }

    public static function formatBackendPrice(float|string $price): string
    {
        return System::getFormattedNumber((float) $price, (int) strpos(strrev((string) $price), '.'));
    }
}
