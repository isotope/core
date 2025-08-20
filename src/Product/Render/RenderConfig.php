<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Render;

use Contao\ModuleModel;
use Contao\StringUtil;
use Isotope\CoreBundle\Product\ProductInterface;
use Isotope\CoreBundle\Routing\ProductReference;

final class RenderConfig
{
    public string $id;
    public string $template;
    public int $gallery;
    public ProductReference|null $reference = null;
    public array $buttons = [];
    public bool $withQuantity = true;
    public bool $withOptions = true;
    public bool $loadFallback = true;
    public array $options = [];

    public function __construct(string $id, string $template, int $gallery)
    {
        $this->id = $id;
        $this->template = $template;
        $this->gallery = $gallery;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'template' => $this->template,
            'gallery' => $this->gallery,
            'jumpTo' => $this->reference?->referencePage,
            'buttons' => $this->buttons,
            'useQuantity' => $this->withQuantity,
            'disableOptions' => !$this->withOptions,
            'loadFallback' => $this->loadFallback
        ];
    }

    public static function createList(ModuleModel $model, ProductInterface $product): self
    {
        $config = new self(
            'fmd'.$model->id.'_product'.$product->getProductId(),
            '@Contao/'.($model->iso_list_layout ?: $product->getProductType()->list_template ?: 'isotope/product/list.html.twig'),
            $model->iso_gallery ?: $product->getProductType()->list_gallery,
        );

        $config->withQuantity = (bool) $model->iso_use_quantity;
        $config->withOptions = !$model->iso_disable_options;
        $config->buttons = StringUtil::deserialize($model->iso_buttons, true);
        $config->options = $model->row();

        if ($model->defineRoot && $model->jumpTo) {
            $config->reference = new ProductReference($product, $model->jumpTo, (bool) $model->iso_link_primary);
        } elseif ($model->iso_link_primary) {
            $config->reference = new ProductReference($product, null, true);
        }

        return $config;
    }

    public static function createReader(ModuleModel $model, ProductInterface $product): self
    {
        $config = new self(
            'fmd'.$model->id.'_product'.$product->getProductId(),
            '@Contao/'.($model->iso_reader_layout ?: $product->getProductType()->reader_template ?: 'isotope/product/reader.html.twig'),
            $model->iso_gallery ?: $product->getProductType()->reader_gallery,
        );

        $config->withQuantity = (bool) $model->iso_use_quantity;
        $config->withOptions = !$model->iso_disable_options;
        $config->buttons = StringUtil::deserialize($model->iso_buttons, true);

        return $config;
    }
}
