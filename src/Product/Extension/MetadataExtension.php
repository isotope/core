<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Extension;

final class MetadataExtension implements ProductExtensionInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly string $title,
        private readonly string $description,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
