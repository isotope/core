<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Exception;

class ActionException extends RuntimeException
{
    public static function missingRequest(): self
    {
        return new self('A request is required to render a product action.');
    }

    public static function unknownProductAction(string $name): self
    {
        return new self('Unknown product action "'.$name.'"');
    }

    public static function unknownCollectionAction(string $name): self
    {
        return new self('Unknown product collection action "'.$name.'"');
    }

    public static function unknownItemAction(string $name): self
    {
        return new self('Unknown product collection item action "'.$name.'"');
    }
}
