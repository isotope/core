<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\UploadableWidgetInterface;
use Contao\Widget;
use Isotope\CoreBundle\Product\CoreProduct;

final class ProductConfiguration
{
    /**
     * @param array<string, mixed>  $data
     * @param array<string, Widget> $widgets
     * @param list<string>          $ajaxAttributes
     */
    public function __construct(
        private CoreProduct $product,
        private array $data,
        private array $widgets,
        private array $ajaxAttributes,
    ) {
    }

    public function getProduct(): CoreProduct
    {
        return $this->product;
    }

    public function setProduct(CoreProduct $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array<string, Widget>
     */
    public function getWidgets(): array
    {
        return $this->widgets;
    }

    public function setWidgets(array $widgets): self
    {
        $this->widgets = $widgets;

        return $this;
    }

    public function hasUploads(): bool
    {
        return array_any($this->widgets, static fn ($widget) => $widget instanceof UploadableWidgetInterface);
    }

    /**
     * TODO: do we need ajax attributes?
     *
     * @return list<string>
     */
    public function getAjaxAttributes(): array
    {
        return $this->ajaxAttributes;
    }

    public function setAjaxAttributes(array $ajaxAttributes): self
    {
        $this->ajaxAttributes = $ajaxAttributes;

        return $this;
    }
}
