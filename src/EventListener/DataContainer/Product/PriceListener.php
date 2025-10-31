<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\StringUtil;
use Contao\System;

class PriceListener
{
    #[AsCallback('tl_iso_product', 'fields.price.load')]
    public function loadPrice(mixed $value): string
    {
        $value = StringUtil::deserialize($value, true);

        return serialize([
            'value' => $value['price'] ?? '0.00',
            'unit' => $value['tax_class'] ?? '',
        ]);
    }

    #[AsCallback('tl_iso_product', 'fields.price.save')]
    public function savePrice(mixed $value): string
    {
        $value = StringUtil::deserialize($value, true);

        return serialize([
            'price' => $this->roundPrice($value['value'] ?? 0),
            'tax_class' => $value['unit'] ?? '',
        ]);
    }

    #[AsCallback('tl_iso_product', 'fields.prices.load')]
    public function loadAdvancedPrices(mixed $value): array
    {
        return array_map($this->unpack(...), StringUtil::deserialize($value, true));
    }

    #[AsCallback('tl_iso_product', 'fields.prices.save')]
    public function saveAdvancedPrices(mixed $value): string
    {
        return serialize(array_map($this->pack(...), StringUtil::deserialize($value, true)));
    }

    private function pack(array $price): array
    {
        $tiers = [];

        foreach ($price['price_tiers'] as $tier) {
            $tiers[$tier['min']] = $this->roundPrice($tier['price']);
        }

        ksort($tiers);
        $price['price_tiers'] = $tiers;

        return $price;
    }

    private function unpack(array $price): array
    {
        $tiers = [];

        foreach ($price['price_tiers'] as $min => $value) {
            $tiers[] = [
                'min' => $min,
                'price' => $this->formatPrice($value),
            ];
        }

        $price['price_tiers'] = $tiers;

        return $price;
    }

    private function roundPrice(string|float $price): string
    {
        if (\is_string($price)) {
            if (1 === substr_count($price, ',') && !str_contains($price, '.')) {
                $price = str_replace(',', '.', $price);
            }
        }

        $price = round((float) $price, 8);
        $price = rtrim((string) $price, '0');

        if ((int) strpos(strrev($price), '.') < 2) {
            $price = number_format((float) $price, 2, '.', '');
        }

        return $price;
    }

    private function formatPrice(string|float $price): string
    {
        return System::getFormattedNumber($price, (int) strpos(strrev($price), '.'));
    }
}
