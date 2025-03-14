<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum ProductTypeCondition: string implements TranslatableLabelInterface
{
    case onlyAvailable = 'onlyAvailable';
    case notAvailable = 'notAvailable';
    case oneAvailable = 'oneAvailable';
    case allAvailable = 'allAvailable';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_payment.'.$this->value, [], 'contao_tl_iso_payment');
    }
}
