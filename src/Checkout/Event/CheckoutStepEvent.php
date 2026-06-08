<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Event;

use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class CheckoutStepEvent extends Event
{
    private string $formId;

    private bool $editing;

    private bool $submitted;

    private bool $hasError = false;

    private bool $hasUploads = false;

    /**
     * @var array<int, list<TemplateMarkup>>
     */
    private array $preMarkup = [];

    /**
     * @var array<int, array<TemplateMarkup>>
     */
    private array $postMarkup = [];

    public function __construct(
        private readonly StoreContext $context,
        private readonly Request $request,
        private readonly string $type,
        private readonly array $config,
    ) {
        $this->formId = $this->type.'_step';
        $this->editing = $this->request->isMethod(Request::METHOD_POST) && $this->request->request->getBoolean('edit_'.$this->getType().'_step');
        $this->submitted = $this->request->isMethod(Request::METHOD_POST) && $this->request->request->getBoolean('submit_'.$this->getType().'_step');
    }

    public function getContext(): StoreContext
    {
        return $this->context;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getFormId(): string
    {
        return $this->formId;
    }

    public function setFormId(string $formId): self
    {
        $this->formId = $formId;

        return $this;
    }

    public function isEditing(): bool
    {
        return $this->editing;
    }

    public function setEditing(bool $editing): self
    {
        $this->editing = $editing;

        return $this;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    public function setSubmitted(bool $submitted): self
    {
        $this->submitted = $submitted;

        return $this;
    }

    public function hasError(): bool
    {
        return $this->hasError;
    }

    public function setHasError(bool $error = true): self
    {
        $this->hasError = $error;

        return $this;
    }

    public function hasUploads(): bool
    {
        return $this->hasUploads;
    }

    public function setHasUploads(bool $uploads = true): self
    {
        $this->hasUploads = $uploads;

        return $this;
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
