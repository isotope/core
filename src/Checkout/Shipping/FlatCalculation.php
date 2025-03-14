<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum FlatCalculation: string implements TranslatableLabelInterface
{
    case perProduct = 'perProduct';
    case perItem = 'perItem';
    case perWeight = 'perWeight';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_shipping.'.$this->value, [], 'contao_tl_iso_shipping');
    }
}
