<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Extension;

final readonly class MetadataExtension implements ProductExtensionInterface
{
    /**
     * @internal
     */
    public function __construct(
        private string $title,
        private string $description,
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
