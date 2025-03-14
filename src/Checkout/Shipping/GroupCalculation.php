<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum GroupCalculation: string implements TranslatableLabelInterface
{
    case first = 'first';
    case lowest = 'lowest';
    case highest = 'highest';
    case sum = 'summarize';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_shipping.'.$this->value, [], 'contao_tl_iso_shipping');
    }
}
