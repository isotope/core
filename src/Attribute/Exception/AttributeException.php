<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Attribute\Exception;

use Isotope\CoreBundle\Exception\LogicException;

class AttributeException extends LogicException
{
    public static function guessInputType(string $class, string $method): self
    {
        return new self(\sprintf('Unable to guess the inputType for %s, implement %s instead.', $class, $method));
    }

    public static function guessFrontendWidget(string $class, string $method): self
    {
        return new self(\sprintf('Unable to guess the frontend widget for %s, implement %s instead.', $class, $method));
    }

    public static function invalidDatabaseOptions(string $fieldName): self
    {
        return new self('Options source for attribute "'.$fieldName.'" is not the database table');
    }

    public static function invalidProductOptions(string $fieldName): self
    {
        return new self('Options source for attribute "'.$fieldName.'" is not the product');
    }
}
