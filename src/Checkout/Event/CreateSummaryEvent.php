<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Event;

use Isotope\CoreBundle\Twig\TemplateMarkup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class CreateSummaryEvent extends Event
{
    /**
     * @var array<int, list<TemplateMarkup>>
     */
    private array $preMarkup = [];

    /**
     * @var array<int, array<TemplateMarkup>>
     */
    private array $postMarkup = [];

    public function __construct(
        private readonly string $type,
        private readonly Request $request,
        private readonly bool $editing,
        private readonly bool $submitted,
        private bool $completed,
        private readonly array $config,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function isEditing(): bool
    {
        return $this->editing;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return array<TemplateMarkup>
     */
    public function getPreMarkup(): array
    {
        krsort($this->preMarkup);

        return array_merge(...$this->preMarkup);
    }

    public function addPreMarkup(TemplateMarkup $markup, int $priority = 0): self
    {
        $this->preMarkup[$priority][] = $markup;

        return $this;
    }

    /**
     * @return array<TemplateMarkup>
     */
    public function getPostMarkup(): array
    {
        krsort($this->postMarkup);

        return array_merge(...$this->postMarkup);
    }

    public function addPostMarkup(TemplateMarkup $markup, int $priority = 0): self
    {
        $this->postMarkup[$priority][] = $markup;

        return $this;
    }
}
