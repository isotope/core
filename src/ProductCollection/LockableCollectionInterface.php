<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection;

interface LockableCollectionInterface
{
    public function isLocked(): bool;

    // \DateTimeImmutable::createFromFormat('U', (string) $collection->locked)
    public function getLockTime(): \DateTimeImmutable;
}
