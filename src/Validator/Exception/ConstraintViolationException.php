<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Validator\Exception;

use Isotope\CoreBundle\Exception\RuntimeException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ConstraintViolationException extends RuntimeException
{
    /**
     * @param array<mixed> $inputData
     */
    public function __construct(
        private readonly ConstraintViolationListInterface $violations,
        private readonly array $inputData,
    ) {
        parent::__construct(\sprintf('Caught %s violation errors.', $violations->count()));
    }

    public function getViolations(string|null $propertyPath = null): ConstraintViolationListInterface
    {
        if (!$propertyPath) {
            return $this->violations;
        }

        $violations = new ConstraintViolationList();

        foreach ($this->violations as $violation) {
            if ($violation->getPropertyPath() === $propertyPath) {
                $violations->add($violation);
            }
        }

        return $violations;
    }

    /**
     * @return array<mixed>
     */
    public function getInputData(): array
    {
        return $this->inputData;
    }

    public function getErrors(bool $withTrace = false): \Generator
    {
        /** @var ConstraintViolation $violation */
        foreach ($this->violations as $violation) {
            $error = [
                'code' => $violation->getCode(),
                'status' => '400',
                'title' => 'Constraint violation error',
                'detail' => $violation->getMessage(),
                'source' => [
                    'pointer' => '/'.ltrim($violation->getPropertyPath(), '/'),
                ],
                'meta' => [
                    'parameters' => $violation->getParameters(),
                ],
            ];

            if ($withTrace) {
                $error['trace'] = $this->getTrace();
            }

            yield $error;
        }
    }
}
