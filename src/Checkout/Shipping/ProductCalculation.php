<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Shipping;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

enum ProductCalculation: string implements TranslatableLabelInterface
{
    case highestProduct = 'highestProduct';
    case lowestProduct = 'lowestProduct';
    case sumProducts = 'sumProducts';
    case highestItem = 'highestItem';
    case lowestItem = 'lowestItem';
    case sumItems = 'sumItems';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_shipping.productCalculationOptions.'.$this->value, [], 'contao_tl_iso_shipping');
    }
}
