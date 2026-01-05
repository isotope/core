<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\ContentModel;
use Isotope\CoreBundle\Product\CoreProduct;

class ReaderView extends AbstractView
{
    public function getTemplate(CoreProduct $product): string
    {
        return '@Contao/'.($this->template ?: $product->getProductType()->reader_template ?: 'isotope/product/reader.html.twig');
    }

    public function getGallery(CoreProduct $product): int
    {
        return (int) ($this->gallery ?: $product->getProductType()->reader_gallery);
    }

    public static function createFromContent(ContentModel $model): self
    {
        return new self(
            'cte'.$model->id,
            $model->iso_reader_layout ?: null,
            $model->iso_gallery ?: null,
            $model->row(),
        );
    }
}
