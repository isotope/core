<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Contao\StringUtil;
use Isotope\CoreBundle\Model\BasePrice;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\Isotope;
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

        if (null === $price) {
            return '';
        }

        $configuration = array_filter(array_map(static fn ($o) => $o['value'] ?? null, $context['options'] ?? []));

        return $price->generate($type->showPriceTiers(), 1, $configuration);
    }

    public function generateBasePrice(array $context): string
    {
        $product = $context['product'] ?? null;

        if (!$product instanceof ProductInterface) {
            return '';
        }

        $data = StringUtil::deserialize($product['baseprice']);

        if (\is_array($data) && $data['unit'] > 0 && '' !== $data['value']) {
            $basePrice = BasePrice::findByPk((int) $data['unit']);

            if (null !== $basePrice && null !== $product->getPrice()) {
                return sprintf($basePrice->label, Isotope::formatPriceWithCurrency($product->getPrice()->getAmount() / $data['value'] * $basePrice->amount), $data['value']);
            }
        }

        return '';
    }
}
