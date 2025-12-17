<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Event;

use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Store\Context\StoreContext;

class ProductConfigurationEvent
{
    public function __construct(
        private readonly CoreProduct $product,
        private readonly StoreContext $context,
        private array $data,
        private array $widgets,
        private array $ajaxAttributes,
    ) {
    }

    public function getProduct(): CoreProduct
    {
        return $this->product;
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): ProductConfigurationEvent
    {
        $this->data = $data;
        return $this;
    }

    public function getWidgets(): array
    {
        return $this->widgets;
    }

    public function setWidgets(array $widgets): ProductConfigurationEvent
    {
        $this->widgets = $widgets;
        return $this;
    }

    public function getAjaxAttributes(): array
    {
        return $this->ajaxAttributes;
    }

    public function setAjaxAttributes(array $ajaxAttributes): ProductConfigurationEvent
    {
        $this->ajaxAttributes = $ajaxAttributes;
        return $this;
    }
}
