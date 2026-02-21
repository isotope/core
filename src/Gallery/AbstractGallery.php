<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\File\Metadata;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;
use Isotope\CoreBundle\ModelType\AbstractModelType;
use Isotope\CoreBundle\Product\CoreProduct;
use Isotope\CoreBundle\Widget\MediaManager;

abstract class AbstractGallery extends AbstractModelType implements GalleryInterface
{
    private AnchorTarget $anchor = AnchorTarget::none;

    private string|null $placeholder = null;

    private array|int|null $mainSize = null;

    private array|int|null $gallerySize = null;

    private array|int|string|null $lightboxSize = null;

    public function __construct(
        private readonly int $id,
        private string $template,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAnchor(): AnchorTarget
    {
        return $this->anchor;
    }

    public function setAnchor(AnchorTarget $anchor): static
    {
        $this->anchor = $anchor;

        return $this;
    }

    public function getPlaceholder(): string|null
    {
        return $this->placeholder;
    }

    public function setPlaceholder(string|null $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function getMainSize(): array|int|null
    {
        return $this->mainSize;
    }

    public function setMainSize(array|int|null $mainSize): static
    {
        $this->mainSize = $mainSize;

        return $this;
    }

    public function getGallerySize(): array|int|null
    {
        return $this->gallerySize;
    }

    public function setGallerySize(array|int|null $gallerySize): static
    {
        $this->gallerySize = $gallerySize;

        return $this;
    }

    public function getLightboxSize(): array|int|string|null
    {
        return $this->lightboxSize;
    }

    public function setLightboxSize(array|int|string|null $lightboxSize): static
    {
        $this->lightboxSize = $lightboxSize;

        return $this;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    protected function getFiles(CoreProduct $product, string $attribute): array
    {
        $files = MediaManager::mergeMediaData(
            StringUtil::deserialize($product->{$attribute}, true),
            StringUtil::deserialize($product->{$attribute.'_fallback'}, true),
        );

        if (
            [] === $files
            && ($placeholder = $this->getPlaceholder())
            && (
                !Validator::isUuid($placeholder)
                || ($placeholder = FilesModel::findByUuid($placeholder)?->path)
            )
        ) {
            $files[] = ['src' => $placeholder];
        }

        foreach ($files as $k => $file) {
            if (!isset($file['src'])) {
                unset($files[$k]);
                continue;
            }

            // File without path must be located in the isotope root folder
            if (!str_contains($file['src'], '/')) {
                $files[$k]['src'] = 'isotope/'.strtolower($file['src'][0]).'/'.$file['src'];
            }

            [$link, $rel] = explode('|', $file['link'] ?? '', 2) + [null, null];
            $files[$k]['link'] = $link;
            $files[$k]['rel'] = $rel;

            $files[$k]['metadata'] = [
                Metadata::VALUE_ALT => $file['alt'] ?? null,
                Metadata::VALUE_CAPTION => $file['desc'] ?? null,
                Metadata::VALUE_URL => $file['link'] ?? null,
            ];
        }

        return $files;
    }
}
