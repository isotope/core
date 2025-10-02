<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\ModuleModel;
use Isotope\CoreBundle\Product\ProductInterface;

class ReaderView extends AbstractView
{
    public function getTemplate(ProductInterface $product): string
    {
        return '@Contao/'.($this->template ?: $product->getProductType()->reader_template ?: 'isotope/product/reader.html.twig');
    }

    public function getGallery(ProductInterface $product): int
    {
        return (int) ($this->gallery ?: $product->getProductType()->reader_gallery);
    }

    public static function createFromModule(ModuleModel $model): self
    {
        return new ReaderView(
            'fmd'.$model->id,
            $model->iso_reader_layout ?: null,
            $model->iso_gallery ?: null,
            $model->row(),
        );
    }
}
