<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

class DcaAttributes extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public array $attributes,
        array|null $groups = null,
        mixed $payload = null,
        mixed $options = null,
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
