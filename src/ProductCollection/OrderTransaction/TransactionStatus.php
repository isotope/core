<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\OrderTransaction;

use Isotope\CoreBundle\Util\ColorUtil;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TransactionStatus
{
    /**
     * @var array<string, TranslatableInterface>
     */
    private static array $labels = [];

    /**
     * @var array<string, string>
     */
    private static array $colors = [];

    public static function format(string $key, TranslatorInterface $translator): string
    {
        $label = self::label($key, $translator);

        if ($color = self::color($key)) {
            return sprintf(
                '<span style="%s">%s</span>',
                ColorUtil::getCssBackgroundStyles($color),
                $label,
            );
        }

        return $label;
    }

    public static function label(string $key, TranslatorInterface $translator): string
    {
        if (isset(self::$labels[$key])) {
            return self::$labels[$key]->trans($translator);
        }

        return $key;
    }

    public static function color(string $key): string|null
    {
        return self::$colors[$key] ?? null;
    }

    public static function register(string $key, TranslatableInterface $label, string|null $color = null): void
    {
        self::$labels[$key] = $label;

        if ($color) {
            self::$colors[$key] = $color;
        }
    }

    public static function registerDefault(string $key, string|null $color = null): void
    {
        self::register(
            $key,
            new TranslatableMessage('tl_iso_order_transaction.status.'.$key, [], 'contao_tl_iso_order_transaction'),
            $color,
        );
    }
}
