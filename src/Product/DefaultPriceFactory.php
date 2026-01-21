<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\StringUtil;
use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\Product\DataCollector\ProductDto;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Store\Price\PriceDefinitionCollection;

class DefaultPriceFactory
{
    public function __construct(private readonly RuleMatcher $ruleMatcher)
    {
    }

    public function create(ProductDto $dto, ProductType $type, StoreContext $context): PriceDefinitionCollection|null
    {
        if (!\in_array('price', $type->getAttributes(), true) && !$type->hasVariantPrices()) {
            return null;
        }

        if (!$dto['pid'] && !empty($dto['__variants']) && $type->hasVariantPrices()) {
            $prices = [];

            foreach ($dto['__variants'] as $variant) {
                $prices[] = $this->createFromArray($variant, $type->hasAdvancedPrices(), $context, (string) $variant['id']);
            }

            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return (new PriceDefinitionCollection())->merge(...$prices);
        }

        return $this->createFromArray($dto->getArrayCopy(), $type->hasAdvancedPrices(), $context);
    }

    /**
     * @internal
     */
    public function createFromArray(array $data, bool $advancedPrices, StoreContext $context, string|null $variantId = null): PriceDefinitionCollection
    {
        $prices = new PriceDefinitionCollection();

        if ($advancedPrices) {
            $price = StringUtil::deserialize($data['prices']);

            if (\is_array($price)) {
                foreach ($price as $config) {
                    if (($config['rule'] ?? null) && !$this->ruleMatcher->isApplicable((int) $config['rule'], $context)) {
                        continue;
                    }

                    foreach ($config['price_tiers'] as $quantity => $price) {
                        $prices->add($this->createPrice((float) $price, $quantity, $config)->setReference($variantId));
                    }

                    // Only add the first advanced price that matches the rules
                    break;
                }
            }
        } else {
            $price = StringUtil::deserialize($data['price']);

            if (\is_array($price)) {
                $prices->add($this
                    ->createPrice((float) ($price['price'] ?? 0), 1, ['tax_class' => $price['tax_class'] ?? ''])
                    ->setReference($variantId),
                );
            }
        }

        return $prices;
    }

    private function createPrice(float $price, int $quantity = 1, array $data = []): PriceDefinition
    {
        // TODO: resolve tax_class to TaxCollection

        return new PriceDefinition(
            $price,
            $data['tax_class'] ? ((int) $data['tax_class']) : null,
            $quantity,
        );
    }
}
