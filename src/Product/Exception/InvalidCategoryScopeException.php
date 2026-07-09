<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Product\Exception;

use Isotope\CoreBundle\Exception\RuntimeException;

class InvalidCategoryScopeException extends RuntimeException
{
    public function __construct(string $scope)
    {
        parent::__construct('Unable to determine categories for scope "'.$scope.'"');
    }
}
