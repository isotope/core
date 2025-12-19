<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Util;

final class ColorUtil
{
    public static function getCssBackgroundStyles(string $color): string
    {
        if ('' === $color) {
            return '';
        }

        $color = ltrim($color, '#');

        return \sprintf('background-color:#%s;color:#%s', $color, self::getContrastColor($color));
    }

    public static function getContrastColor(string $color, string $dark = '000', string $light = 'fff'): string
    {
        $rgb = array_map(hexdec(...), str_split($color, 2));
        $hue = 1 - (0.299 * $rgb[0] + 0.587 * $rgb[1] + 0.114 * $rgb[2]) / 255;

        return $hue > 0.5 ? $light : $dark;
    }
}
