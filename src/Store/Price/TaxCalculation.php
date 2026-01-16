<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Price;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum TaxCalculation: string implements TranslatableLabelInterface
{
    case Horizontal = 'horizontal';
    case Vertical = 'vertical';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('MSC.iso_taxCalculation.'.$this->value.'.0', [], 'contao_default');
    }
}
