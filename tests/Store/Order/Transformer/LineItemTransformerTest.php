<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order\Transformer;

use Contao\PageModel;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Order\Transformer\LineItemTransformer;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Test\TestCase;

final class LineItemTransformerTest extends TestCase
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
                    'base_price' => null,
                    'calculated_taxes' => [],
                    'tax_rates' => ['rates' => [], 'included' => []],
                ],
                'tax_free_price' => 0,
                'tax_id' => '',
                'referencePage' => null,
                'level' => 0,
            ],
            $result,
        );
    }

    public function testReferencePageIdIsPreserved(): void
    {
        $lineItem = new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42');
        $lineItem->setReferencePage(17);

        $this->assertSame(17, LineItemTransformer::transform($lineItem)['referencePage']);
    }

    public function testReferencePageModelIsConvertedToItsId(): void
    {
        $lineItem = new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42');
        $lineItem->setReferencePage($this->createClassWithPropertiesStub(PageModel::class, ['id' => '17']));

        $this->assertSame(17, LineItemTransformer::transform($lineItem)['referencePage']);
    }
}
