<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Option;

use Isotope\CoreBundle\Store\Price\CalculatedPrice;

final class Option
{
    private CalculatedPrice|null $price = null;

    public function __construct(
        private readonly string $value,
        private string|null $label,
        private bool $group = false,
        private bool $default = false,
        private string $cssClass = '',
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setLabel(string|null $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label ?? $this->value;
    }

    public function setGroup(bool $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function isGroup(): bool
    {
        return $this->group;
    }

    public function setDefault(bool $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setCssClass(string $cssClass): self
    {
        $this->cssClass = $cssClass;

        return $this;
    }

    public function getCssClass(): string
    {
        return $this->cssClass;
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
}
