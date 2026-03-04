<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Isotope\CoreBundle\Twig\TemplateMarkup;

class CheckoutStepSummary
{
    public function __construct(
        private readonly string $title,
        private readonly bool $completed,
        private readonly TemplateMarkup|null $details,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function getDetails(): TemplateMarkup|null
    {
        return $this->details;
    }
}
