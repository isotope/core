<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum CheckoutMethod: string implements TranslatableLabelInterface
{
    case Member = 'member';
    case Guest = 'guest';
    case Both = 'both';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_content.iso_checkout_method.'.$this->value, [], 'contao_tl_content');
    }
}
