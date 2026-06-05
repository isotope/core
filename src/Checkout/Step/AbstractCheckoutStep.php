<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Isotope\CoreBundle\Checkout\Event\CreateSummaryEvent;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

abstract class AbstractCheckoutStep implements CheckoutStepInterface
{
    public function __construct(
        private readonly TemplateMarkupFactory $markupFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    protected function dispatchEvent(Request $request, bool $completed, array $config): CreateSummaryEvent
    {
        $event = new CreateSummaryEvent($this->getType(), $request, $this->isEditing($request), $this->isSubmitted($request), $completed, $config);

        return $this->eventDispatcher->dispatch($event);
    }

    protected function createSummary(CreateSummaryEvent $event, string|null $template = null, array $context = []): CheckoutStepSummary
    {
        return new CheckoutStepSummary(
            $this->getTitle(),
            $event->isCompleted(),
            $template ? $this->markupFactory->create(
                '@Contao/isotope/checkout_step/'.($event->isCompleted() ? 'summary/' : '').$template.'.html.twig',
                [
                    'type' => $event->getType(),
                    'form_id' => $event->getType().'_step',
                    'pre' => $event->getPreMarkup(),
                    'post' => $event->getPostMarkup(),
                    ...$context
                ],
            ) : null,
        );
    }

    protected function isEditing(Request $request): bool
    {
        return $request->isMethod(Request::METHOD_POST) && $request->request->getBoolean('edit_'.$this->getType().'_step');
    }

    protected function isSubmitted(Request $request): bool
    {
        return $request->isMethod(Request::METHOD_POST) && $request->request->getBoolean('submit_'.$this->getType().'_step');
    }

    abstract protected function getType(): string;

    abstract protected function getTitle(): string|null;
}
