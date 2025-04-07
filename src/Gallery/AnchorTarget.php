<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Gallery;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum AnchorTarget: string implements TranslatableLabelInterface
{
    case none = 'none';
    case reader = 'reader';
    case lightbox = 'lightbox';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_gallery.'.$this->value, [], 'contao_tl_iso_gallery');
    }
}
