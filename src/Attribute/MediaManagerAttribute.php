<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;

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
