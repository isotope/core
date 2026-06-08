<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Step;

use Isotope\CoreBundle\Checkout\Event\CheckoutStepEvent;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
abstract class AbstractCheckoutStep implements CheckoutStepInterface
{
    public function __construct(
        private readonly TemplateMarkupFactory $markupFactory,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    protected function dispatchEvent(StoreContext $context, Request $request, array $config): CheckoutStepEvent
    {
        $event = new CheckoutStepEvent($context, $request, $this->getType(), $config);

        return $this->eventDispatcher->dispatch($event);
    }

    protected function createSummary(CheckoutStepEvent $event, bool $completed, string|null $template = null, array $context = []): CheckoutStepSummary
    {
        return new CheckoutStepSummary(
            $this->getTitle(),
            $completed,
            $template ? $this->markupFactory->create(
                '@Contao/isotope/checkout_step/'.($completed ? 'summary/' : '').$template.'.html.twig',
                [
                    'type' => $event->getType(),
                    'form_id' => $event->getType().'_step',
                    'enctype' => $event->hasUploads() ? 'multipart/form-data' : null,
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
