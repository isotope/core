<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

enum CheckoutMethod: string
{
    case Member = 'member';
    case Guest = 'guest';
    case Both = 'both';
}
