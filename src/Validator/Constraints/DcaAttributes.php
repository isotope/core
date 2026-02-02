<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Validator\Constraints;

use Contao\TextField;
use Contao\Widget;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\CallbackValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class DcaAttributes extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public array $attributes,
        ?array $groups = null,
        mixed $payload = null,
        mixed $options = null
    ) {
        parent::__construct($options, $groups, $payload);
    }
}
