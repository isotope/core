<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Isotope\CoreBundle\Product\ProductInterface;
use Twig\Extension\RuntimeExtensionInterface;

class ProductRuntime implements RuntimeExtensionInterface
{
    public function generatePrice(array $context): string
    {
        $product = $context['product'] ?? null;

        if (!$product instanceof ProductInterface) {
            return '';
        }

        $price = $product->getPrice();
        $type = $product->getProductType();

        if (null === $price || null === $type) {
            return '';
        }

        $configuration = array_filter(array_map(static fn ($o) => $o['value'] ?? null, $context['options'] ?? []));

        return $price->generate($type->showPriceTiers(), 1, $configuration);
    }
}
