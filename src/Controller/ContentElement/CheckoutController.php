<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use Isotope\CoreBundle\Checkout\CheckoutMethod;
use Isotope\CoreBundle\Checkout\Event\CheckoutStepEvent;
use Isotope\CoreBundle\Checkout\Step\CheckoutStepInterface;
use Isotope\CoreBundle\Store\CartManager;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;
use Isotope\CoreBundle\Store\Order\OrderNotifier;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsContentElement('iso_checkout', category: 'isotope')]
class CheckoutController extends AbstractContentElementController
{
    /**
     * @param array<CheckoutStepInterface> $checkoutSteps
     */
    public function __construct(
        #[AutowireLocator('isotope.checkout_step', 'type')] private readonly iterable $checkoutSteps,
        private readonly CartManager $cartManager,
        private readonly OrderNotifier $orderNotifier,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->enforceMemberLogin($model);

        $context = $this->getStoreContext();
        $cart = $context->getCart();

        if ($cart->isModified()) {
            $this->cartManager->recalculate($cart, $context);
        }

        $steps = [];
        $config = $model->row();
        $allCompleted = true;

        foreach ($this->checkoutSteps as $key => $step) {
            $summary = $step->getSummary($request, $context, $config);

            if (!$summary) {
                continue;
            }

            $steps[$key] = $summary;

            if (!$summary->isCompleted()) {
                $allCompleted = false;
            }
        }

        $isSubmitted = $request->isMethod(Request::METHOD_POST) && $request->request->getBoolean('place_order');

        $event = $this->eventDispatcher->dispatch(
            (new CheckoutStepEvent($context, $request, '_summary', $config))
                ->setFormId('place_order')
                ->setEditing(true)
                ->setSubmitted($isSubmitted)
        );

        $template->steps = $steps;
        $template->completed = $allCompleted;
        $template->collection = $cart;
        $template->pre_summary = $event->getPreMarkup();
        $template->post_summary = $event->getPostMarkup();

        if ($allCompleted && $isSubmitted && !$event->hasError()) {
            $result = $this->cartManager->order($cart, $context);

            if ($model->iso_notifications) {
                $this->sendNotifications($result, explode(',', $model->iso_notifications));
            }

            throw new RedirectResponseException($this->generateContentUrl(PageModel::findById($model->orderCompleteJumpTo), ['uid' => $result->getIdentifier(), 'init_payment' => true]));
        }

        return $template->getResponse();
    }

    /**
     * Redirect to the login page if a member must be logged in.
     */
    private function enforceMemberLogin(ContentModel $model): void
    {
        if (CheckoutMethod::Member->value !== $model->iso_checkout_method) {
            return;
        }

        throw new RedirectResponseException($this->generateContentUrl(PageModel::findById($model->iso_login_jumpTo)));
    }

    private function sendNotifications(CreateOrderResult $result, array $ids): void
    {
        foreach ($ids as $id) {
            $this->orderNotifier->sendNotification($result->getCollectionId(), (int) $id);
        }
    }
}
