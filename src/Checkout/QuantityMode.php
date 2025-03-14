<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum QuantityMode: string implements TranslatableLabelInterface
{
    case cartItems = 'cart_items';
    case cartProducts = 'cart_products';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_payment.quantity_mode.'.$this->value, [], 'contao_tl_iso_payment');
    }
}
