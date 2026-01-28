<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Ramsey\Collection\CollectionInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @template TType
 */
abstract class AbstractProductCollection
{
    protected LineItemCollection $lineItems;

    /**
     * @var array<TranslatableInterface>
     */
    private array $errors = [];

    public function __construct(
        private readonly string $identifier,
    ) {
        $this->lineItems = new LineItemCollection();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    abstract public static function getType(): string;

    public function isEmpty(): bool
    {
        return $this->lineItems->isEmpty();
    }

    public function getLineItems(): LineItemCollection
    {
        return $this->lineItems;
    }

    public function setLineItems(LineItemCollection $lineItems): self
    {
        $this->lineItems = $lineItems;

        return $this;
    }

    public function has(string $identifier, array|null $configuration): bool
    {
        return !$this->filter($identifier, $configuration)->isEmpty();
    }

    public function hasItem(LineItem $item): bool
    {
        return $this->has($item->getIdentifier(), $item->getConfiguration());
    }

    public function add(LineItem $item): void
    {
        $exists = $this->getItem($item);

        if ($exists) {
            $newQuantity = $exists->getQuantity() + $item->getQuantity();
            $exists->setQuantity($newQuantity);

            return;
        }

        $this->lineItems->add($item);
    }

    public function get(string $identifier, array|null $configuration): LineItem|null
    {
        $collection = $this->filter($identifier, $configuration);

        if ($collection->isEmpty()) {
            return null;
        }

        return $collection->first();
    }

    public function getItem(LineItem $item): LineItem|null
    {
        return $this->get($item->getIdentifier(), $item->getConfiguration());
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors || !$this->getLineItems()->where('hasErrors', true)->isEmpty();
    }

    public function addError(TranslatableInterface $message): self
    {
        $this->errors[] = $message;

        return $this;
    }

    /**
     * @return list<TranslatableInterface>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return CollectionInterface<LineItem>
     */
    private function filter(string $identifier, array|null $configuration): CollectionInterface
    {
        return $this->lineItems->filter(
            static fn (LineItem $v) => $v->getIdentifier() === $identifier && $v->getConfiguration() === $configuration,
        );
    }
}
