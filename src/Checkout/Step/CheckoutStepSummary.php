<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Isotope\CoreBundle\Twig\TemplateMarkup;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;

class CheckoutStepSummary implements \ArrayAccess, \IteratorAggregate, \Countable, \Stringable
{
    public function __construct(
        private readonly string|null $title,
        private readonly bool $completed,
        private readonly TemplateMarkup|null $details,
    ) {
    }

    public function getTitle(): string|null
    {
        return $this->title;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function __toString(): string
    {
        return (string) ($this->details ?? '');
    }

    public function getDetails(): TemplateMarkup|null
    {
        return $this->details;
    }

    public function getIterator(): \Traversable
    {
        return $this->details?->getIterator() ?? new \ArrayIterator([]);
    }

    public function count(): int
    {
        return $this->details?->count() ?? 0;
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->details?->offsetExists($offset) ?? false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->details?->offsetGet($offset) ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Cannot set properties on '.self::class);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Cannot unset properties on '.self::class);
    }
}
