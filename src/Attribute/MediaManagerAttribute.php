<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Types\Types;
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
        return ['type' => Types::BLOB, 'length' => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB, 'notnull' => false];
    }
}
