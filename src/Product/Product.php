<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Product\Extension\ProductExtensionInterface;

abstract class Product
{
    /**
     * @var array<ProductExtensionInterface>
     */
    private array $extensions = [];

    public function __construct(
        private readonly string $identifier,
        private array $data = [],
    ) {
    }

    public function __isset(string $key): bool
    {
        return $this->has($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function __get(string $key): mixed
    {
        return $this->get($key);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    abstract public function getName(): string;

    abstract public function getSku(): string;

    public function hasExtension(string $className): bool
    {
        return isset($this->extensions[$className]);
    }

    public function addExtension(ProductExtensionInterface $extension): self
    {
        $this->extensions[$extension::class] = $extension;

        return $this;
    }

    /**
     * @template T of ProductExtensionInterface
     *
     * @param class-string<T> $className
     *
     * @return T|null
     */
    public function getExtension(string $className): ProductExtensionInterface|null
    {
        return $this->extensions[$className] ?? null;
    }

    public function removeExtension(string $className): self
    {
        unset($this->extensions[$className]);

        return $this;
    }

    /**
     * @return array<ProductExtensionInterface>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * @param array<ProductExtensionInterface> $extensions
     */
    public function addExtensions(array $extensions): self
    {
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        return $this;
    }

    /**
     * @param array<ProductExtensionInterface> $extensions
     */
    public function setExtensions(array $extensions): self
    {
        $this->extensions = [];

        return $this->addExtensions($extensions);
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function set(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}
