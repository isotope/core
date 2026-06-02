<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Order;

use Contao\CoreBundle\Translation\TranslatableLabelInterface;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @internal
 */
enum TransactionState: string implements TranslatableLabelInterface
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Complete = 'complete';

    public function label(): TranslatableMessage
    {
        return new TranslatableMessage('tl_iso_orderstatus.transaction_state_options.'.$this->value.'.0', [], 'contao_tl_iso_orderstatus');
    }
}
