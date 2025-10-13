<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum PriceDisplay: string implements TranslatableLabelInterface
{
    case Net = 'net';
    case Gross = 'gross';
    case Fixed = 'fixed';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('MSC.iso_priceDisplay.'.$this->value.'.0', [], 'contao_default');
    }
}
