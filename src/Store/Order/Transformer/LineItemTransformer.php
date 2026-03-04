<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order\Transformer;

use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;

/**
 * @internal
 */
class LineItemTransformer
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function transformCollection(LineItemCollection $lineItems, ?string $parentId = null): array
    {
        $output = [];
        $position = 1;

        foreach ($lineItems as $lineItem) {
            $output = array_replace($output, self::transform($lineItem, $parentId, $position));
            ++$position;
        }

        return $output;
    }

    /**
     * TODO: $parentId and $sorting are preparation for nested items (e.g. bundles)
     *
     * @return array<string, array<string, mixed>>
     */
    public static function transform(LineItem $lineItem, ?string $parentId = null, int $sorting = 1): array
    {
        $output = [];

        $price = $lineItem->getPrice();

        $data = [
            'type' => $lineItem->getType(),
            'parent_id' => $parentId,
            'sorting' => $sorting,
            'identifier' => $lineItem->getIdentifier(),
            'configuration' => '',
            'sku' => '',
            'label' => $lineItem->getLabel(),
            'quantity' => $lineItem->getQuantity(),
            'price' => 0,
            'tax_free_price' => 0,
            'tax_id' => '',
            'jumpTo' => 0,
            'level' => $lineItem->getLevel(),
        ];

        $output[$lineItem->getId()] = array_filter($data, fn ($value) => $value !== null);

        return $output;
    }
}
