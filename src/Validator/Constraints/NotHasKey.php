<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class NotHasKey extends Constraint
{
    public string $message = 'The key "{{ key }}" should not be present in the array.';

    #[HasNamedArguments]
    public function __construct(public string $key, ?array $groups = null, mixed $payload = null, mixed $options = null)
    {
        parent::__construct($options, $groups, $payload);
    }
}
