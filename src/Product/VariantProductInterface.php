<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

interface VariantProductInterface
{
    public function hasVariants(): bool;

    /**
     * @return array<int>
     */
    public function getVariantIds(): array;

    public function isVariant(): bool;

    public function getMainProduct(): self;

    public function getDefaultVariant(): self|null;

    public function getVariant(array $configuration): self|null;

    public function hasVariantPrices(): bool;

    /**
     * Get variant option field values
     */
    public function getVariantConfig(): array;
}
