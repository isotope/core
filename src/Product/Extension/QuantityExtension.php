<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Extension;

use Isotope\CoreBundle\Util\ClosureTrait;

final class QuantityExtension implements ProductExtensionInterface
{
    use ClosureTrait;

    /**
     * @internal
     */
    public function __construct(
        private readonly \Closure|int $minimum,
    ) {
    }

    public function getMinimum(): int
    {
        $this->resolveClosure($this->minimum);

        return $this->minimum;
    }

    // TODO: add maximum quantity
    // TODO: add purchase unit (e.g. price is per packs of 6)
    // TODO: add purchase steps (e.g. can only be sold in multiple of 6)


}
