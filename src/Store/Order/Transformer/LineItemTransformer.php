<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Transformer;

use Isotope\CoreBundle\ProductCollection\Cart\Cart;
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
    public static function transformCart(Cart $cart, ?string $parentId = null): array
    {
        $output = [];
        $position = 1;

        foreach ($cart->getLineItems()->byLevel() as $level => $lineItems) {
            foreach ($lineItems as $lineItem) {
                $output = [...$output, ...self::transform($lineItem, $parentId, $position)];
            }

            $output[] = self::transformSubtotal($level, $cart->getSubtotal($level), $position);
        }

        $output[] = [
            'type' => LineItem::TYPE_TOTAL,
            'sorting' => $position,
            'identifier' => LineItem::TYPE_TOTAL,
            'quantity' => 1,
            'price' => $cart->getTotal()?->getTotalPrice() ?? 0.0,
            'tax_free_price' => 0,
            'price_data' => $cart->getTotal()?->jsonSerialize(),
            'tax_id' => '',
        ];

        return $output;
    }

    /**
     * TODO: $parentId and $sorting are preparation for nested items (e.g. bundles)
     *
     * @return array<string, array<string, mixed>>
     */
    public static function transform(LineItem $lineItem, ?string $parentId = null, int &$position = 1): array
    {
        $output = [];

        $price = $lineItem->getPrice();

        $data = [
            'type' => $lineItem->getType(),
            'parent_id' => $parentId,
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

        $position++;

        $output[] = $data;

        return $output;
    }

    public static function transformSubtotal(int $level, CalculatedPrice|null $price, &$position = 1): array
    {
        $data = [
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

        $position++;

        return $data;
    }
}
