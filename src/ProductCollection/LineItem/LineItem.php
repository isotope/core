<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\LineItem;

use Isotope\CoreBundle\Product\Product;
use Isotope\CoreBundle\Store\Price\CalculatedPrice;
use Symfony\Contracts\Translation\TranslatableInterface;

class LineItem
{
    public const string TYPE_PRODUCT = 'product';

    public const string TYPE_SHIPPING = 'shipping';

    public const string TYPE_PAYMENT = 'payment';

    private int $id = 0;

    private array|null $configuration = null;

    private int $level = 0;

    private bool $dynamic = false;

    private string|null $sku = null;

    private string $label;

    private CalculatedPrice|null $price = null;

    private Product|null $product = null;

    /**
     * @var list<TranslatableInterface>
     */
    private array $errors = [];

    public function __construct(
        private string $type,
        private string $identifier,
        private int $quantity = 1,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSku(): string|null
    {
        return $this->sku;
    }

    public function setSku(string|null $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getProduct(): Product|null
    {
        return $this->product;
    }

    public function setProduct(Product|null $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getPrice(): CalculatedPrice|null
    {
        return $this->price;
    }

    public function setPrice(CalculatedPrice|null $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getConfiguration(): array|null
    {
        return $this->configuration;
    }

    public function setConfiguration(array|null $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function isDynamic(): bool
    {
        return $this->dynamic;
    }

    public function setDynamic(bool $dynamic): self
    {
        $this->dynamic = $dynamic;

        return $this;
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
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

    public function getHashData(): array
    {
        return [
            'identifier' => $this->getIdentifier(),
            'quantity' => $this->getQuantity(),
            'price' => $this->getPrice()?->totalPrice,
        ];
    }
}
