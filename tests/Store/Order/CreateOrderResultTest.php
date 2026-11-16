<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Order;

use Isotope\CoreBundle\Store\Order\CreateOrderResult;
use PHPUnit\Framework\TestCase;

final class CreateOrderResultTest extends TestCase
{
    public function testCreatedDataPreservesMetadataAndUsesAssignedIdentity(): void
    {
        $data = [
            'id' => 1,
            'uniqid' => 'old',
            'member_id' => null,
            'currency' => 'CHF',
            'subtotal' => 20,
            'total' => 22,
            'billing_address' => ['id' => 8, 'firstname' => 'Jane'],
            'billing_address_id' => 8,
            'shipping_address_id' => 0,
            'order_status' => '1',
            'transaction_status' => '2',
            'delivery_status' => '3',
            'line_items' => [['id' => 9, 'downloads' => [['download_id' => 5]]]],
            'extensions' => ['custom' => ['value' => 42]],
        ];
        $result = new CreateOrderResult(42, 'order', $data);

        $this->assertSame(42, $result->getCollectionId());
        $this->assertSame('order', $result->getIdentifier());
        $this->assertSame(array_replace($data, ['id' => 42, 'uniqid' => 'order']), $result->getData());
    }

    public function testExistingConstructorProvidesIdentityData(): void
    {
        $result = new CreateOrderResult(42, 'order');

        $this->assertSame(['id' => 42, 'uniqid' => 'order'], $result->getData());
    }
}
