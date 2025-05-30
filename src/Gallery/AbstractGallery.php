<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\File\Metadata;
use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\FigureBuilder;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\FilesModel;
use Twig\Environment;

abstract class AbstractGallery implements GalleryInterface
{
    private AnchorTarget $anchor = AnchorTarget::none;

    private string|null $placeholder = null;

    private array|int|null $mainSize = null;

    private array|int|null $gallerySize = null;

    private string $name = '';

    private int $productId = 0;

    private array $files = [];

    private string|null $href = null;

    public function __construct(
        private readonly int $id,
        private string $template,
        private readonly Studio $imageStudio,
        private readonly Environment $twig,
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

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function setTemplate(string $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function withProductData(string $name, int $productId, array $files, string|null $href): static
    {
        $clone = clone $this;

        $clone->name = $name;
        $clone->productId = $productId;
        $clone->files = $files;
        $clone->href = $href;

        return $clone;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHref(): string|null
    {
        return $this->href;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function generateMainImage(): string
    {
        $file = $this->resolvePath($this->getFiles()[0] ?? null, true);

        if (!$file) {
            return '';
        }

        // TODO: implement ajax reload of gallery lightbox
        // if (Environment::get('isAjaxRequest')) {
        //    $strScripts = '';
        //    $arrTemplates = StringUtil::deserialize($this->lightbox_template, true);
        //
        //    if (!empty($arrTemplates)) {
        //        foreach ($arrTemplates as $strTemplate) {
        //            $objScript = new Template($strTemplate);
        //            $strScripts .= $objScript->parse();
        //        }
        //    }
        //
        //    $template->javascript = $strScripts;
        // }

        $figure = $this->prepareFigureBuilder('main', 0, $file, $this->getMainSize())->buildIfResourceExists();

        if (!$figure) {
            return '';
        }

        return $this->render($this->prepareContext('main', 0, $file, $figure));
    }

    public function generateGallery(int $skip = 1): string
    {
        $buffer = '';
        $files = \array_slice($this->getFiles(), $skip);

        // Make sure there is at least one image to generate the placeholder
        if (0 === $skip && [] === $files) {
            $files[] = null;
        }

        foreach ($files as $i => $file) {
            $file = $this->resolvePath($file, 0 === $i + $skip);

            if (!$file) {
                continue;
            }

            $figure = $this->prepareFigureBuilder('gallery', $i + $skip, $file, $this->getGallerySize())->buildIfResourceExists();

            if (!$figure) {
                return '';
            }

            $buffer .= $this->render($this->prepareContext('gallery', $i + $skip, $file, $figure));
        }

        return $buffer;
    }

    /**
     * Resolve image path of Isotope files and optionally return the placeholder if no image is given.
     *
     * @param array{src?: string}|null $file
     *
     * @return array{src: string}|null
     */
    protected function resolvePath(array|null $file, bool $placeholder = false): array|null
    {
        if (empty($file['src'])) {
            return $placeholder && $this->placeholder ? ['src' => FilesModel::findById($this->placeholder)?->path] : null;
        }

        $path = $file['src'];

        // File without path must be located in the isotope root folder
        if (!str_contains($path, '/')) {
            $file['src'] = 'isotope/'.strtolower($path[0]).'/'.$path;
        }

        return $file;
    }

    protected function prepareFigureBuilder(string $type, int $index, array $file, array|int|string|null $size): FigureBuilder
    {
        return $this->imageStudio
            ->createFigureBuilder()
            ->setSize($size)
            ->fromPath($file['src'])
            ->setMetadata($this->getMetadata($file))
            ->setLinkHref(AnchorTarget::reader === $this->getAnchor() ? $this->getHref() : null)
        ;
    }

    protected function prepareContext(string $type, int $index, array $file, Figure $figure): array
    {
        return [
            'gallery' => $this,
            'name' => $this->getName(),
            'product_id' => $this->getProductId(),
            'type' => $type,
            'index' => $index,
            'file' => $file,
            'image' => $figure,
        ];
    }

    protected function render(array $context): string
    {
        return $this->twig->render($this->template, $context);
    }

    /**
     * @param array{alt?: string, desc?: string, link?: string} $file
     */
    private function getMetadata(array $file): Metadata
    {
        $values = [
            Metadata::VALUE_ALT => $file['alt'] ?? null,
            Metadata::VALUE_CAPTION => $file['desc'] ?? null,
            Metadata::VALUE_URL => $file['link'] ?? null,
        ];

        return new Metadata($values);
    }
}
