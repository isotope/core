<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\Store\Order\OrderConverter;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class OrderConverterTest extends TestCase
{
    public function testConvertToOrder(): void
    {
        $cart = new Cart('__identifier__');
        $cart->setBillingAddress(new AddressModel());
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'))->setPrice(new CalculatedPrice(10, 10)));
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.43'))->setPrice(new CalculatedPrice(20, 20)));
        $cart->add((new LineItem(LineItem::TYPE_SHIPPING, 'tl_iso_shipping.1'))->setPrice(new CalculatedPrice(30, 30))->setLevel(1));

        $cart->setSubtotals([
            0 => new CalculatedPrice(30, 30),
            1 => new CalculatedPrice(60, 60),
        ]);

        $cart->setTotal(new CalculatedPrice(60, 60));

        $orderConverter = new OrderConverter($this->createStub(EventDispatcherInterface::class));
        $result = $orderConverter->convertToOrder($cart, $this->createStoreContext());

        $this->assertSame(
            [
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
                [
                    'type' => 'product',
                    'sorting' => 2,
                    'identifier' => 'tl_iso_product.43',
                    'configuration' => null,
                    'sku' => null,
                    'label' => 'tl_iso_product.43',
                    'quantity' => 1,
                    'price' => 20.0,
                    'price_data' => [
                        'unit_price' => 20.0,
                        'quantity' => 1,
                        'total_price' => 20.0,
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
                [
                    'type' => 'subtotal',
                    'sorting' => 3,
                    'identifier' => 'subtotal.0',
                    'quantity' => 1,
                    'price' => 30.0,
                    'tax_free_price' => 0,
                    'price_data' => [
                        'unit_price' => 30.0,
                        'quantity' => 1,
                        'total_price' => 30.0,
                        'unit_label' => null,
                        'is_range' => false,
                        'calculated_taxes' => [],
                        'tax_rates' => ['rates' => [], 'included' => []],
                    ],
                    'tax_id' => '',
                    'level' => 0,
                ],
                [
                    'type' => 'shipping',
                    'sorting' => 4,
                    'identifier' => 'tl_iso_shipping.1',
                    'configuration' => null,
                    'sku' => null,
                    'label' => 'tl_iso_shipping.1',
                    'quantity' => 1,
                    'price' => 30.0,
                    'price_data' => [
                        'unit_price' => 30.0,
                        'quantity' => 1,
                        'total_price' => 30.0,
                        'unit_label' => null,
                        'is_range' => false,
                        'calculated_taxes' => [],
                        'tax_rates' => ['rates' => [], 'included' => []],
                    ],
                    'tax_free_price' => 0,
                    'tax_id' => '',
                    'jumpTo' => 0,
                    'level' => 1,
                ],
                [
                    'type' => 'subtotal',
                    'sorting' => 5,
                    'identifier' => 'subtotal.1',
                    'quantity' => 1,
                    'price' => 60.0,
                    'tax_free_price' => 0,
                    'price_data' => [
                        'unit_price' => 60.0,
                        'quantity' => 1,
                        'total_price' => 60.0,
                        'unit_label' => null,
                        'is_range' => false,
                        'calculated_taxes' => [],
                        'tax_rates' => ['rates' => [], 'included' => []],
                    ],
                    'tax_id' => '',
                    'level' => 1,
                ],
                [
                    'type' => 'total',
                    'sorting' => 6,
                    'quantity' => 1,
                    'price' => 60.0,
                    'tax_free_price' => 0,
                    'price_data' => [
                        'unit_price' => 60.0,
                        'quantity' => 1,
                        'total_price' => 60.0,
                        'unit_label' => null,
                        'is_range' => false,
                        'calculated_taxes' => [],
                        'tax_rates' => ['rates' => [], 'included' => []],
                    ],
                    'tax_id' => '',
                ],
            ],
            $result['line_items'],
        );
    }
}
