<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute;

interface TranslatableAttributeInterface
{
    public function isTranslatable(): bool;

    public function fetchTranslationFallback(): bool;
}
