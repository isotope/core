<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\EventDispatcher;

use Psr\EventDispatcher\StoppableEventInterface;

/**
 * @template TTransactionResult
 */
abstract class Transaction implements StoppableEventInterface
{
    /**
     * @var TTransactionResult
     */
    public mixed $result = null;

    public ?\Throwable $exception = null;

    private bool $propagationStopped = false;

    abstract public function getEventName(): string;

    /**
     * @return TTransactionResult
     */
    public function getResult(): mixed
    {
        return $this->result;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        $data = get_object_vars($this);

        unset($data['result'], $data['propagationStopped'], $data['exception']);

        return $data;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * If multiple event listeners are connected to the same event, no
     * further event listener will be triggered once any trigger calls
     * stopPropagation().
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * @internal
     */
    public function resetPropagation(): void
    {
        $this->propagationStopped = false;
    }
}
