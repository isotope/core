<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order\Transformer;

use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Order\Transformer\LineItemTransformer;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use PHPUnit\Framework\TestCase;

class LineItemTransformerTest extends TestCase
{
    public function testTransformCart(): void
    {
        $lineItem = (new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'))->setPrice(new CalculatedPrice(10, 10));
        $result = LineItemTransformer::transform($lineItem);

        $this->assertSame(
            [
                'type' => 'product',
                'sorting' => 1,
                'identifier' => 'tl_iso_product.42',
                'configuration' => null,
                'sku' => null,
                'label' => 'tl_iso_product.42',
                'quantity' => 1,
                'price' => 10.0,
                'price_data' => [
                    'unit_price' => 10.0,
                    'quantity' => 1,
                    'total_price' => 10.0,
                    'unit_label' => null,
                    'is_range' => false,
                    'calculated_taxes' => [],
                    'tax_rates' => ['rates' => [], 'included' => []],
                ],
                'tax_free_price' => 0,
                'tax_id' => '',
                'jumpTo' => 0,
                'level' => 0,
            ],
            $result,
        );
    }
}
