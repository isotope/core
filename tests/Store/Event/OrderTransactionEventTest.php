<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\Store\Event;

use Isotope\CoreBundle\EventDispatcher\TransactionEventDispatcher;
use Isotope\CoreBundle\Store\Event\OrderTransactionEvent;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;
use Isotope\CoreBundle\Test\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class OrderTransactionEventTest extends TestCase
{
    public function testPostListenersReceiveCreatedDataWithoutChangingInput(): void
    {
        $input = [
            'total' => 10,
            'currency' => 'CHF',
            'billing_address' => ['firstname' => 'Jane'],
            'line_items' => [['identifier' => 'tl_iso_product.42', 'downloads' => [['download_id' => 5]]]],
            'extensions' => ['custom' => ['value' => 42]],
        ];
        $created = $input;
        $created['billing_address']['id'] = 17;
        $created['line_items'][0]['id'] = 18;
        $created['order_status'] = '1';
        $event = new OrderTransactionEvent($input, $this->createStoreContext());
        $dispatcher = new EventDispatcher();
        $postCalled = false;
        $dispatcher->addListener(
            'isotope.order.post',
            function (OrderTransactionEvent $event) use ($input, $created, &$postCalled): void {
                $postCalled = true;
                $this->assertSame($input, $event->getData());
                $this->assertSame(array_replace($created, ['id' => 42, 'uniqid' => 'order']), $event->getResult()->getData());
            },
        );

        (new TransactionEventDispatcher($dispatcher))->dispatch(
            $event,
            static fn () => new CreateOrderResult(42, 'order', $created),
        );

        $this->assertTrue($postCalled);
        $copy = $event->getData();
        $copy['line_items'][0]['id'] = 999;
        $this->assertSame($input, $event->getData());
    }
}
