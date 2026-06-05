<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TransactionEventDispatcher
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
     * @template TResult of mixed
     *
     * @param TransactionEvent<TResult> $event
     *
     * @return TResult
     */
    public function dispatch(TransactionEvent $event, callable $function): mixed
    {
        $this->dispatcher->dispatch($event, self::pre($event->getName()));

        if (!$event->isPropagationStopped()) {
            try {
                $event->result = $function($event);
            } catch (\Throwable $e) {
                $event->exception = $e;

                $event->resetPropagation();

                $this->dispatcher->dispatch($event, self::error($event->getName()));

                // if the listener want to gracefully handle the exception, they can put in a result, otherwise we rethrow the exception
                if (null === $event->result) {
                    throw $e;
                }
            }
        }

        $event->resetPropagation();

        $this->dispatcher->dispatch($event, self::post($event->getName()));

        return $event->getResult();
    }
}
