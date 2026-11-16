<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Test\EventDispatcher;

use Isotope\CoreBundle\EventDispatcher\TransactionEventDispatcher;
use Isotope\CoreBundle\Store\Event\OrderTransactionEvent;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;
use Isotope\CoreBundle\Test\TestCase;
use Isotope\CoreBundle\Validator\Exception\ConstraintViolationException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

final class TransactionEventDispatcherTest extends TestCase
{
    public function testCallbackReceivesInputAfterPreListeners(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new OrderTransactionEvent(['total' => 10], $this->createStoreContext());
        $result = new CreateOrderResult(42, 'order');
        $calls = [];
        $dispatcher->addListener(
            'isotope.order.pre',
            static function () use (&$calls): void {
                $calls[] = 'pre';
            },
        );
        $dispatcher->addListener(
            'isotope.order.post',
            static function () use (&$calls): void {
                $calls[] = 'post';
            },
        );

        $actual = (new TransactionEventDispatcher($dispatcher))->dispatch(
            $event,
            function (OrderTransactionEvent $event) use (&$calls, $result): CreateOrderResult {
                $calls[] = 'persist';
                $this->assertSame(['total' => 10], $event->getData());

                return $result;
            },
        );

        $this->assertSame($result, $actual);
        $this->assertSame(['pre', 'persist', 'post'], $calls);
    }

    public function testValidationFailureUsesTransactionErrorHandling(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new OrderTransactionEvent(['total' => -1], $this->createStoreContext());
        $calls = [];
        $dispatcher->addListener(
            'isotope.order.pre',
            static function () use (&$calls): void {
                $calls[] = 'pre';
            },
        );
        $dispatcher->addListener(
            'isotope.order.error',
            static function () use (&$calls): void {
                $calls[] = 'error';
            },
        );
        $dispatcher->addListener(
            'isotope.order.post',
            static function () use (&$calls): void {
                $calls[] = 'post';
            },
        );

        try {
            (new TransactionEventDispatcher($dispatcher))->dispatch(
                $event,
                static function (OrderTransactionEvent $event) use (&$calls): CreateOrderResult {
                    $calls[] = 'validate';
                    $data = $event->getData();
                    $violations = Validation::createValidator()->validate($data, new Assert\Collection(fields: ['total' => new Assert\Positive()]));

                    if ($violations->count() > 0) {
                        throw new ConstraintViolationException($violations, $data);
                    }

                    $calls[] = 'persist';

                    return new CreateOrderResult(42, 'order');
                },
            );
            $this->fail('The validation exception must propagate.');
        } catch (ConstraintViolationException $caught) {
            $this->assertSame(['total' => -1], $caught->getInputData());
            $this->assertSame($caught, $event->exception);
        }

        $this->assertSame(['pre', 'validate', 'error'], $calls);
        $this->assertNull($event->getResult());
    }

    public function testPersistenceFailuresCanBeRecovered(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new OrderTransactionEvent([], $this->createStoreContext());
        $exception = new \DomainException('Persistence failed');
        $result = new CreateOrderResult(42, 'recovered');
        $calls = [];
        $dispatcher->addListener(
            'isotope.order.error',
            static function (OrderTransactionEvent $event) use (&$calls, $result): void {
                $calls[] = 'error';
                $event->result = $result;
            },
        );
        $dispatcher->addListener(
            'isotope.order.post',
            static function () use (&$calls): void {
                $calls[] = 'post';
            },
        );

        $actual = (new TransactionEventDispatcher($dispatcher))->dispatch(
            $event,
            static function () use ($exception): never {
                throw $exception;
            },
        );

        $this->assertSame($result, $actual);
        $this->assertSame($exception, $event->exception);
        $this->assertSame(['error', 'post'], $calls);
    }

    public function testStoppedTransactionsSkipTheCallback(): void
    {
        $dispatcher = new EventDispatcher();
        $event = new OrderTransactionEvent([], $this->createStoreContext());
        $result = new CreateOrderResult(42, 'handled');
        $dispatcher->addListener(
            'isotope.order.pre',
            static function (OrderTransactionEvent $event) use ($result): void {
                $event->result = $result;
                $event->stopPropagation();
            },
        );

        $actual = (new TransactionEventDispatcher($dispatcher))->dispatch(
            $event,
            function (): never {
                $this->fail('A stopped transaction must not persist.');
            },
        );

        $this->assertSame($result, $actual);
    }
}
