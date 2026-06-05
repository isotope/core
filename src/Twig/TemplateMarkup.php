<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Twig;

use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Twig\Environment;

final class TemplateMarkup implements \ArrayAccess, \IteratorAggregate, \Countable, \Stringable
{
    public function __construct(
        private readonly Environment $twig,
        private readonly string $template,
        private readonly array $context,
    ) {
    }

    public function __toString(): string
    {
        return $this->twig->render($this->template, $this->context);
    }

    public function with(array $additionalContext = []): self
    {
        return new self($this->twig, $this->template, array_replace($this->context, $additionalContext));
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->context);
    }

    public function count(): int
    {
        return \count($this->context);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->context[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->context[$offset] ?? null;
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
