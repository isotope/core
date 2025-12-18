<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventListener\DataContainer\Product;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\StringUtil;
use Isotope\CoreBundle\Util\PriceUtil;

class PriceListener
{
    #[AsCallback('tl_iso_product', 'fields.price.load')]
    public function loadPrice(mixed $value): string
    {
        $value = StringUtil::deserialize($value, true);

        return serialize([
            'value' => PriceUtil::formatBackendPrice($value['price'] ?? '0.00'),
            'unit' => $value['tax_class'] ?? '',
        ]);
    }

    #[AsCallback('tl_iso_product', 'fields.price.save')]
    public function savePrice(mixed $value): string
    {
        $value = StringUtil::deserialize($value, true);

        return serialize([
            'price' => PriceUtil::roundBackendPrice($value['value'] ?? 0),
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
            $tiers[$tier['min']] = PriceUtil::roundBackendPrice($tier['price']);
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
                'price' => PriceUtil::formatBackendPrice($value),
            ];
        }

        $price['price_tiers'] = $tiers;

        return $price;
    }
}
