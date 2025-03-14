<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum AddressType: string implements TranslatableLabelInterface
{
    case custom = 'custom';
    case billing = 'billing';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_shipping.address_type.'.$this->value, [], 'contao_tl_iso_shipping');
    }
}
