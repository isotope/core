<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Extension;

/**
 * @internal
 */
class OrderConditionsExtension implements ProductCollectionExtensionInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    private bool $modified = false;

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;
        $this->modified = true;

        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return $this->data;
    }

    public static function createFromJson(array $data): self
    {
        $instance = new self();
        $instance->data = $data;

        return $instance;
    }
}
