<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Util;

class StringUtil
{
    public static function generateRandomId(): string
    {
        if (!function_exists('random_bytes')) {
            return uniqid('', true);
        }

        try {
            return bin2hex(random_bytes(32));
        } catch (\Exception) {
            return uniqid('', true);
        }
    }
}
