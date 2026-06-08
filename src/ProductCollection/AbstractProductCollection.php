<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

use Isotope\CoreBundle\ProductCollection\Extension\ProductCollectionExtensionInterface;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItem;
use Isotope\CoreBundle\ProductCollection\LineItem\LineItemCollection;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Ramsey\Collection\CollectionInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @template TType
 */
abstract class AbstractProductCollection
{
    protected int|null $id = null;

    protected LineItemCollection $lineItems;

    private array $subtotals = [];

    private CalculatedPrice|null $total = null;

    /**
     * @var array<TranslatableInterface>
     */
    private array $errors = [];

    /**
     * @var array<class-string, ProductCollectionExtensionInterface>
     */
    private array $extensions = [];

    public function __construct(
        private readonly string $identifier,
    ) {
        $this->lineItems = new LineItemCollection();
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function setId(int|null $collectionId): self
    {
        $this->id = $collectionId;

        return $this;
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

    public function getSubtotal(int $level = PHP_INT_MAX): CalculatedPrice|null
    {
        $totals = array_filter(
            $this->subtotals,
            static fn (int $key) => $key <= $level,
            ARRAY_FILTER_USE_KEY,
        );

        ksort($totals);

        return array_last($totals);
    }

    public function getSubtotals(): array
    {
        return $this->subtotals;
    }

    public function setSubtotals(array $subtotals): self
    {
        ksort($subtotals);

        $this->subtotals = $subtotals;

        return $this;
    }

    public function getTotal(): CalculatedPrice|null
    {
        return $this->total;
    }

    public function setTotal(CalculatedPrice|null $total): self
    {
        $this->total = $total;

        return $this;
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

    public function hasExtension(string $className): bool
    {
        return isset($this->extensions[$className]);
    }

    public function addExtension(ProductCollectionExtensionInterface $extension): self
    {
        $this->extensions[$extension::class] = $extension;

        return $this;
    }

    /**
     * @template T of ProductCollectionExtensionInterface
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getExtension(string $className): ProductCollectionExtensionInterface|null
    {
        return $this->extensions[$className] ?? null;
    }

    public function removeExtension(string $className): self
    {
        unset($this->extensions[$className]);

        return $this;
    }

    /**
     * @return array<class-string, ProductCollectionExtensionInterface>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param array<ProductCollectionExtensionInterface> $extensions
     */
    public function addExtensions(array $extensions): self
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        return $this;
    }

    /**
     * @param array<ProductCollectionExtensionInterface> $extensions
     */
    public function setExtensions(array $extensions): self
    {
        $this->extensions = [];

        return $this->addExtensions($extensions);
    }
}
