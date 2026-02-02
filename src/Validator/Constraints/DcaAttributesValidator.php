<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Validator\Constraints;

use Contao\TextField;
use Contao\Widget;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DcaAttributesValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof DcaAttributes) {
            throw new UnexpectedTypeException($constraint, DcaAttributes::class);
        }

        /** @var class-string<Widget>|null $widgetClass */
        $widgetClass = $GLOBALS['TL_FFL'][$this->attributes['type'] ?? ''] ?? null;

        // Fall back to the text field widget for default validation
        if (!$widgetClass || !class_exists($widgetClass)) {
            $widgetClass = TextField::class;
        }

        $widget = new $widgetClass($constraint->attributes);
        $widget->value = $value;
        $widget->setInputCallback(static fn () => $value);

        try {
            $widget->validate();
        } catch (\Exception $e) {
            $this->context
                ->buildViolation($e->getMessage())
                ->setInvalidValue($value)
                ->addViolation()
            ;
        }

        foreach ($widget->getErrors() as $error) {
            $this->context
                ->buildViolation($error)
                ->setInvalidValue($value)
                ->addViolation()
            ;
        }
    }
}
