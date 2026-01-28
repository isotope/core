<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransactionDispatcher
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    public static function pre(string $name): string
    {
        return $name . '.pre';
    }

    public static function post(string $name): string
    {
        return $name . '.post';
    }

    public static function error(string $name): string
    {
        return $name . '.error';
    }

    /**
     * @template TTransactionResult of mixed
     *
     * @param Transaction<TTransactionResult> $transaction
     *
     * @return TTransactionResult
     */
    public function dispatch(Transaction $transaction, callable $function): mixed
    {
        $this->dispatcher->dispatch($transaction, self::pre($transaction->getEventName()));

        if (!$transaction->isPropagationStopped()) {
            try {
                $transaction->result = $function(...$transaction->getParams());
            } catch (\Throwable $e) {
                $transaction->exception = $e;

                $transaction->resetPropagation();

                $this->dispatcher->dispatch($transaction, self::error($transaction->getEventName()));

                // if the listener want to gracefully handle the exception, they can put in a result, otherwise we rethrow the exception
                if (null === $transaction->result) {
                    throw $e;
                }
            }
        }

        $transaction->resetPropagation();

        $this->dispatcher->dispatch($transaction, self::post($transaction->getEventName()));

        return $transaction->getResult();
    }
}
