<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\PageModel;
use Isotope\CoreBundle\Checkout\CheckoutMethod;
use Isotope\CoreBundle\Checkout\Step\CheckoutStepInterface;
use Isotope\CoreBundle\Store\CartManager;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_checkout', category: 'isotope')]
class CheckoutController extends AbstractContentElementController
{
    /**
     * @param array<CheckoutStepInterface> $checkoutSteps
     */
    public function __construct(
        #[AutowireIterator('isotope.checkout_step')] private readonly iterable $checkoutSteps,
        private readonly CartManager $cartManager,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->enforceMemberLogin($model);

        $context = $this->getStoreContext();
        $cart = $context->getCart();

        $steps = [];
        $config = $model->row();
        $allCompleted = true;

        foreach ($this->checkoutSteps as $step) {
            $stepContext = $step->getSummary($request, $context, $config);

            if (!$stepContext) {
                continue;
            }

            $completed = $stepContext->isCompleted();

            $steps[] = [
                'title' => $stepContext->getTitle(),
                'details' => $allCompleted ? $stepContext->getDetails() : '',
                'is_complete' => $completed,
            ];

            if (!$completed) {
                $allCompleted = false;
            }
        }

        if ($cart->isModified()) {
            $this->cartManager->recalculate($cart, $context);
        }

        $template->steps = $steps;
        $template->completed = $allCompleted;
        $template->collection = $cart;

        if ($allCompleted && $request->isMethod(Request::METHOD_POST) && $request->request->getBoolean('place_order')) {
            /*$data = [
            'member' => 0,
            'config_id' => $context->getConfigId(),
            'store_id' => $context->getStoreId(),
            //'billing_address' => $cart->getBillingAddress()?->row() ?? [],
            'billing_address' => (new Address())->setRow([
                'company' => 'Musterfirma',
                'firstname' => 'Max',
                'lastname' => 'Muster',
                'street_1' => 'Musterstrasse 1',
                'postal' => '0000',
                'city' => 'Musterhausen',
                //'country' => 'CH',
            ]),

            'payment_id' => 0,
            'payment_data' => null,

            'shipping_id' => 0,
            'shipping_data' => null,

            'line_items' => [],
            'subtotal' => 0,
            'total' => 0,
            'currency' => $context->getCurrency()->getCode(),
            'locale' => $context->getLocale(),

            'order_status' => 1,
            //'date_paid' => null,
            //'date_shipped' => null,

            'notes' => 'foo',
        ];

        if ($shippingAddress = $cart->getShippingAddress()) {
            $data['shipping_address'] = $shippingAddress->row();
        }

        $this->orderManager->createOrder($data, $context);
*/
        dd('test');
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
}
