<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Isotope\CoreBundle\Model\ProductType;
use Isotope\CoreBundle\ModelType\AbstractModelType;
use Isotope\CoreBundle\ModelType\ContainerAwareInterface;
use Isotope\CoreBundle\Product\Render\DefaultProductRenderer;
use Isotope\CoreBundle\Product\Render\ProductRendererInterface;
use Isotope\Model\Product;

abstract class AbstractProduct extends AbstractModelType implements ProductInterface, ContainerAwareInterface
{
    public function __construct(
        protected readonly Product $model,
        protected readonly ProductType $productType,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [DefaultProductRenderer::class];
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->model->{$offset});
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->model->{$offset} ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException('Product data cannot be modified');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException('Product data cannot be modified');
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->model->row());
    }

    public function count(): int
    {
        return \count($this->model->row());
    }

    public function getType(): string
    {
        return $this->productType->class;
    }

    public function getId(): int
    {
        return (int) $this['id'];
    }

    public function getProductId(): int
    {
        return (int) ($this['pid'] ?: $this['id']);
    }

    public function getProductType(): ProductType
    {
        return $this->productType;
    }

    public function getName(): string
    {
        return $this['name'];
    }

    public function getSku(): string
    {
        return $this['sku'] ?? '';
    }

    public function getAlias(): string
    {
        return $this['alias'] ?: (string) $this->getId();
    }

    public function getMetaTitle(): string
    {
        return $this['meta_title'] ?: $this->getName();
    }

    public function getMetaDescription(): string
    {
        return $this['meta_description'] ?: $this['teaser'] ?: $this['description'] ?: '';
    }

    public function getRenderer(): ProductRendererInterface
    {
        return $this->get(DefaultProductRenderer::class);
    }
}
