<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum StatusType: string implements TranslatableLabelInterface
{
    case Order = 'order';
    case Transaction = 'transaction';
    case Delivery = 'delivery';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_orderstatus.type.'.$this->value, [], 'contao_tl_iso_orderstatus');
    }
}
