<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order;

use Isotope\CoreBundle\Model\AddressModel;
use Isotope\CoreBundle\ProductCollection\Cart\Cart;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Order\OrderConverter;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class OrderConverterTest extends TestCase
{
    public function testConvertedCartDoesNotShareLineItemsWithTheOrder(): void
    {
        $context = $this->createStoreContext();
        $order = new Order('order', $context);
        $price = new CalculatedPrice(10, 20, quantity: 2);
        $item = (new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42', 2))->setId(17)->setPrice($price);

        $order->add($item);
        $order->add(new LineItem(LineItem::TYPE_SHIPPING, 'tl_iso_shipping.1'));

        $converter = new OrderConverter($this->createStub(EventDispatcherInterface::class));
        $cart = $converter->convertToCart($order, $context);

        $cartItem = $cart->getLineItems()->first();
        $cartItem->setQuantity(3)->setPrice(new CalculatedPrice(15, 45, quantity: 3));

        $this->assertCount(1, $cart->getLineItems());
        $this->assertNotSame($item, $cartItem);
        $this->assertSame(2, $item->getQuantity());
        $this->assertSame($price, $item->getPrice());
        $this->assertSame(17, $item->getId());
        $this->assertSame(0, $cartItem->getId());
    }

    public function testConvertToOrder(): void
    {
        $cart = new Cart('__identifier__');
        $cart->setBillingAddress($this->createClassWithPropertiesStub(AddressModel::class));
        $cart->add((new LineItem(LineItem::TYPE_PRODUCT, 'tl_iso_product.42'))->setPrice(new CalculatedPrice(10, 10))->setReferencePage(17));
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
                        'base_price' => null,
                        'calculated_taxes' => [],
                        'tax_rates' => ['rates' => [], 'included' => []],
                    ],
                    'tax_free_price' => 0,
                    'tax_id' => '',
                    'referencePage' => 17,
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
                        'base_price' => null,
                        'calculated_taxes' => [],
                        'tax_rates' => ['rates' => [], 'included' => []],
                    ],
                    'tax_free_price' => 0,
                    'tax_id' => '',
                    'referencePage' => null,
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
                        'base_price' => null,
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
                        'base_price' => null,
                        'calculated_taxes' => [],
                        'tax_rates' => ['rates' => [], 'included' => []],
                    ],
                    'tax_free_price' => 0,
                    'tax_id' => '',
                    'referencePage' => null,
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
                        'base_price' => null,
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
                        'base_price' => null,
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
