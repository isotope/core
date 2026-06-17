<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class NotHasKeyValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NotHasKey) {
            throw new UnexpectedTypeException($constraint, NotHasKey::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_array($value)) {
            throw new UnexpectedValueException($value, 'array');
        }

        if (\array_key_exists($constraint->key, $value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->atPath('['.$constraint->key.']')
                ->setParameter('{{ key }}', $constraint->key)
                ->addViolation()
            ;
        }
    }
}
