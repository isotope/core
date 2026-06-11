<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Exception;

class ActionException extends RuntimeException
{
    public static function missingRequest(): self
    {
        return new self('A request is required to render a product action.');
    }

    public static function unknownProductAction(string $type): self
    {
        return new self('Unknown product action "'.$type.'"');
    }

    public static function unknownCollectionAction(string $type): self
    {
        return new self('Unknown product collection action "'.$type.'"');
    }

    public static function unknownItemAction(string $type): self
    {
        return new self('Unknown product collection item action "'.$type.'"');
    }
}
