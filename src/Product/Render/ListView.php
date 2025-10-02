<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\ModuleModel;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Routing\ProductReference;

class ListView extends AbstractView
{
    public function getTemplate(ProductInterface $product): string
    {
        return '@Contao/'.($this->template ?: $product->getProductType()->list_template ?: 'isotope/product/list.html.twig');
    }

    public function getGallery(ProductInterface $product): int
    {
        return (int) ($this->gallery ?: $product->getProductType()->list_gallery);
    }

    public function getProductReference(ProductInterface $product): ProductReference
    {
        return new ProductReference(
            $product,
            ($this->config['defineRoot'] ?? false) ? (int) $this->config['jumpTo'] : null,
            (bool) ($this->config['iso_link_primary'] ?? false)
        );
    }

    public static function createFromModule(ModuleModel $model): self
    {
        return new ListView(
            'fmd'.$model->id,
            $model->iso_list_layout ?: null,
            $model->iso_gallery ?: null,
            $model->row(),
        );
    }
}
