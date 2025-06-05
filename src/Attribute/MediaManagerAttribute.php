<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;
use Isotope\Interfaces\IsotopeProduct;

#[AsAttribute]
class MediaManagerAttribute extends AbstractAttribute implements TranslatableAttributeInterface
{
    private array $extensions = [];

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setExtensions(array $extensions): self
    {
        $this->extensions = $extensions;

        return $this;
    }

    public function generate(IsotopeProduct $product, array $options = []): string
    {
        throw new \BadMethodCallException('MediaManager attribute cannot be generated');
    }

    public function isTranslatable(): bool
    {
        return true;
    }

    public function fetchTranslationFallback(): bool
    {
        return true;
    }

    protected function getSqlDefinition(): array|string
    {
        return 'blob NULL';
    }
}
