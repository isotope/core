<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Isotope\Model\Product;
use Twig\Extension\RuntimeExtensionInterface;

class ProductRuntime implements RuntimeExtensionInterface
{
    public function generatePrice(array $context): string
    {
        $product = $context['product'] ?? null;

        if (!$product instanceof Product) {
            return '';
        }

        $price = $product->getPrice();
        $type = $product->getType();

        if (null === $price || null === $type) {
            return '';
        }

        return $price->generate($type->showPriceTiers(), 1, $product->getOptions());
    }
}
