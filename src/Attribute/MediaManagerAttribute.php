<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsAttribute;

#[AsAttribute]
class MediaManagerAttribute extends AbstractAttribute implements TranslationFallbackAttributeInterface
{
    private array|null $extensions = null;

    public function getExtensions(): array|null
    {
        return $this->extensions;
    }

    public function setExtensions(array|null $extensions): self
    {
        $this->extensions = $extensions;

        return $this;
    }

    protected function getSqlDefinition(): array|string
    {
        return 'blob NULL';
    }
}
