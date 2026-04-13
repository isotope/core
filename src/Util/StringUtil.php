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

    /**
     * @template TReturn
     * @param (callable(mixed $value):TReturn)|null $callback
     * @return ($forceArray is true ? array<TReturn> : array<TReturn>|null)
     */
    public static function unserialize(string|null $value, callable|null $callback = null, bool $forceArray = false): array|null
    {
        $values = \Contao\StringUtil::deserialize($value);

        if (!\is_array($values) || [] === $values) {
            return $forceArray ? [] : null;
        }

        if ($callback) {
            return array_map($callback, $values);
        }

        return array_filter($values);
    }

    /**
     * @template TReturn
     * @param (callable(mixed $value):TReturn)|null $callback
     * @return ($forceArray is true ? array<TReturn> : array<TReturn>|null)
     */
    public static function csv(string|null $value, callable|null $callback = null, bool $forceArray = false): array|null
    {
        if (!$value) {
            return $forceArray ? [] : null;
        }

        $values = array_filter(explode(',', $value));

        if ([] === $values) {
            return $forceArray ? [] : null;
        }

        if ($callback) {
            return array_map($callback, $values);
        }

        return array_filter($values);
    }
}
