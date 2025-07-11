<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product;

use Contao\ModuleModel;
use Contao\StringUtil;
use Isotope\Model\Product;

final class RenderConfig
{
    public string $template;
    public int $gallery;
    public bool $showQuantity = true;
    public bool $showOptions = true;
    public array $buttons = [];

    public function __construct(string $template, int $gallery)
    {
        $this->template = $template;
        $this->gallery = $gallery;
    }

    public function toArray(): array
    {
        return [
            'template' => $this->template,
            'gallery' => $this->gallery,
            'buttons' => $this->buttons,
            'useQuantity' => $this->showQuantity,
            'disableOptions' => !$this->showOptions,
        ];
    }

    public static function createFromModule(ModuleModel $model, Product $product): self
    {
        $config = new self(
            $model->iso_reader_layout ?: $product->getType()->reader_template,
            $model->iso_gallery ?: $product->getType()->reader_gallery,
        );

        $config->showQuantity = (bool) $model->iso_use_quantity;
        $config->showOptions = !$model->iso_disable_options;
        $config->buttons = StringUtil::deserialize($model->iso_buttons, true);

        return $config;
    }
}
