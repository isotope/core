<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Document;

use Isotope\CoreBundle\Store\Document\NumberFormat;

/**
 * @internal
 */
abstract class AbstractDocument implements DocumentInterface
{
    private string $documentTitle = '';

    private int|null $galleryId = null;

    public function __construct(
        private readonly int $id,
        private readonly string $fileTitle,
        private readonly NumberFormat $numberFormat,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFileTitle(): string
    {
        return $this->fileTitle;
    }

    public function getNumberFormat(): NumberFormat
    {
        return $this->numberFormat;
    }

    public function getDocumentTitle(): string
    {
        return $this->documentTitle;
    }

    public function setDocumentTitle(string $documentTitle): static
    {
        $this->documentTitle = $documentTitle;

        return $this;
    }

    public function getGalleryId(): int|null
    {
        return $this->galleryId;
    }

    public function setGalleryId(int|null $galleryId): static
    {
        $this->galleryId = $galleryId;

        return $this;
    }
}
