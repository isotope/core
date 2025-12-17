<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Price;

use Contao\StringUtil;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;

class DefaultPriceFactory
{
    public function create(ProductDto $dto, ProductType $type): PriceCollection|null
    {
        if (!\in_array('price', $type->getAttributes(), true) && !$type->hasVariantPrices()) {
            return null;
        }

        if (!$dto['pid'] && !empty($dto['__variants']) && $type->hasVariantPrices()) {
            $prices = [];

            foreach ($dto['__variants'] as $variant) {
                $prices[] = $this->createFromArray($variant, $type->hasAdvancedPrices(), (int) $variant['id']);
            }

            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return (new PriceCollection())->merge(...$prices);
        }

        return $this->createFromArray($dto->getArrayCopy(), $type->hasAdvancedPrices());
    }

    /**
     * @internal
     */
    public function createFromArray(array $data, bool $advancedPrices, int $variantId = 0): PriceCollection
    {
        $prices = new PriceCollection();

        if ($advancedPrices) {
            $price = StringUtil::deserialize($data['prices']);

            if (\is_array($price)) {
                foreach ($price as $config) {
                    foreach ($config['price_tiers'] as $quantity => $price) {
                        $prices->add($this->createPrice((float) $price, $quantity, $config)->setVariantId($variantId));
                    }
                }
            }
        } else {
            $price = StringUtil::deserialize($data['price']);

            if (\is_array($price)) {
                $prices->add($this
                    ->createPrice((float) ($price['price'] ?? 0), 1, ['tax_class' => $price['tax_class'] ?? ''])
                    ->setVariantId($variantId)
                );
            }
        }

        return $prices;
    }

    private function createPrice(float $price, int $quantity = 1, array $data = []): Price
    {
        // TODO: resolve tax_class to TaxCollection

        return new Price(
            $price,
            $quantity,
            $data['tax_class'] ? ((int) $data['tax_class']) : null,
            $data['config_id'] ?? null,
            $data['group_id'] ?? null,
            ($data['start'] ?? null) ? \DateTimeImmutable::createFromFormat('U', $data['start']) : null,
            ($data['stop'] ?? null) ? \DateTimeImmutable::createFromFormat('U', $data['stop']) : null,
        );
    }
}
