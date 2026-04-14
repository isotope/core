<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Category;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum CategoryScope: string implements TranslatableLabelInterface
{
    case Current = 'current_category';
    case CurrentAndFirstChild = 'current_and_first_child';
    case CurrentAndChildren = 'current_and_all_children';
    case Parent = 'parent';
    case Product = 'product';
    case All = 'global';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_content.iso_category_scope.'.$this->value.'.0', [], 'contao_tl_content');
    }
}
