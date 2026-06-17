<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Transformer;

use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;

/**
 * @internal
 */
class LineItemTransformer
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function transform(LineItem $lineItem, int $position = 1): array
    {
        $price = $lineItem->getPrice();

        return [
            'type' => $lineItem->getType(),
            'sorting' => $position,
            'identifier' => $lineItem->getIdentifier(),
            'configuration' => $lineItem->getConfiguration(),
            'sku' => $lineItem->getSku(),
            'label' => $lineItem->getLabel(),
            'quantity' => $lineItem->getQuantity(),
            'price' => $price?->getTotalPrice() ?? 0.0,
            'price_data' => $price?->jsonSerialize(),
            'tax_free_price' => 0,
            'tax_id' => implode(',', $price?->getCalculatedTaxes()->getNumbers() ?? []),
            'jumpTo' => 0,
            'level' => $lineItem->getLevel(),
        ];
    }

    public static function transformSubtotal(int $level, CalculatedPrice|null $price, int $position = 1): array
    {
        return [
            'type' => LineItem::TYPE_SUBTOTAL,
            'sorting' => $position,
            'identifier' => LineItem::TYPE_SUBTOTAL.'.'.$level,
            'quantity' => 1,
            'price' => $price?->getTotalPrice() ?? 0.0,
            'tax_free_price' => 0,
            'price_data' => $price?->jsonSerialize(),
            'tax_id' => '',
            'level' => $level,
        ];
    }
}
