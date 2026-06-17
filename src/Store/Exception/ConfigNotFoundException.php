<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Exception;

use Isotope\CoreBundle\Exception\UnderflowException;

class ConfigNotFoundException extends UnderflowException
{
    public static function cannotRestoreOrder(int $configId, int $orderId): self
    {
        return new self(\sprintf('Config ID %s not found. Cannot restore order ID %s.', $configId, $orderId));
    }
}
