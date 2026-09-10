<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Store\Document;

final readonly class NumberFormat
{
    public function __construct(
        private string $prefix,
        private int $length,
    ) {
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}
