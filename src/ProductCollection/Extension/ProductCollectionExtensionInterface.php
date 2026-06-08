<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\ProductCollection\Extension;

interface ProductCollectionExtensionInterface extends \JsonSerializable
{
    public function isModified(): bool;

    public function jsonSerialize(): array;

    public static function createFromJson(array $data): self;
}
