<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

trait TranslatableTrait
{
    private bool $translatable = false;

    public function isTranslatable(): bool
    {
        return $this->translatable;
    }

    public function setTranslatable(bool $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }
}
