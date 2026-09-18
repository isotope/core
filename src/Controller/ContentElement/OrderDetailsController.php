<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FrontendUser;
use Contao\Input;
use Isotope\CoreBundle\Checkout\Payment\PaymentInterface;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\ModelType\PaymentRegistry;
use Isotope\CoreBundle\ProductCollection\Cart\CartCalculator;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\Order\OrderFactory;
use Isotope\CoreBundle\ProductCollection\OrderTransaction\OrderTransactionHandler;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Order\OrderConverter;
use Isotope\CoreBundle\Store\Order\TransactionState;
use Isotope\CoreBundle\Store\OrderManager;
use Isotope\CoreBundle\Util\MessagesUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('iso_orderdetails', category: 'isotope')]
class OrderDetailsController extends AbstractContentElementController
{
    public function __construct(
        private readonly OrderFactory $loader,
        private readonly OrderConverter $orderConverter,
        private readonly OrderManager $orderManager,
        private readonly PaymentRegistry $paymentRegistry,
        private readonly CartCalculator $calculator,
        private readonly OrderTransactionHandler $orderTransactionHandler,
        private readonly MessagesUtil $messages,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($this->isBackendScope($request)) {
            return new Response();
        }

        Input::setUnusedRouteParameters([]); // Disable check on custom page controller

        $identifier = ltrim($request->attributes->getString('parameters'), '/');

        if (!$identifier || !($order = $this->loader->loadFromIdentifier($identifier))) {
            throw new PageNotFoundException();
        }

        $context = $order->getContext();

        if ($context->getMemberId() > 0) {
            $user = $this->getUser();

            if (
                (!$user instanceof FrontendUser && $model->iso_loginRequired)
                || ($user instanceof FrontendUser && (int) $user->id !== $context->getMemberId())
            ) {
                throw new AccessDeniedException();
            }
        }

        $this->handlePayment($order, $context, $request, $template, $model->iso_transaction_cancelled);

        $this->addCollectionToTemplate($template, $order, $context, (int) $model->iso_gallery);

        return $template->getResponse();
    }

    private function handlePayment(Order $order, StoreContext $context, Request $request, FragmentTemplate $template, string|null $cancelStatus): void
    {
        $payment = $order->getPaymentMethod();
        $state = $order->getTransactionStatus()?->getTransactionState();

        $template->payment_options = [];
        $template->payment_pending = TransactionState::Pending === $state;
        $template->payment_processing = TransactionState::Processing === $state;
        $template->payment_complete = TransactionState::Complete === $state;
        $template->payment_cancelled = $cancelStatus === $order->getOrderStatus()->alias;

        if (!$payment || TransactionState::Complete === $state) {
            return;
        }

        if ($request->query->getBoolean('init_payment')) {
            $this->initPayment($order, $payment);
        }

        if (($transactionState = $request->query->get('transaction_state')) && \in_array($transactionState, ['success', 'failed'], true)) {
            $transactionModel = $this->orderTransactionHandler->switch($order, $request->query->get('transaction'));
            $redirectUrl = $this->generateContentUrl($this->getPageModel(), ['parameters' => '/'.$order->getIdentifier()]);

            $result = match ($transactionState) {
                'success' => $payment->finalize($order, $transactionModel, $request),
                'failed' => $payment->failed($order, $transactionModel, $request),
            };

            $this->messages->handleActionResult('payment.'.$transactionState, $result, $redirectUrl);
        }

        $paymentOptions = $this->getPaymentOptions($order, $context);

        if ($request->isMethod(Request::METHOD_POST) && $request->request->getBoolean('init_payment')) {
            $template->change_payment = $request->request->getBoolean('change_payment');

            if (!$request->request->has('payment_id') || $request->request->getInt('payment_id') === $payment->getId()) {
                $this->initPayment($order, $payment);
            }

            $newPayment = array_find(
                $paymentOptions,
                static fn (array $option) => $request->request->getInt('payment_id') === $option['value'],
            )['payment'] ?? null;

            if ($newPayment) {
                $cart = $this->orderConverter->convertToCart($order, $context);
                $context->setCart($cart);
                $cart->setPaymentMethod($newPayment);
                $cart->setModified();

                $this->orderTransactionHandler->cancel($order);

                $newCart = $this->calculator->calculate($cart, $context);
                $data = $this->orderConverter->convertToOrder($newCart, $context);

                $this->orderManager->updatePayment($order->getId(), $data, $context);

                $this->initPayment($order, $newPayment);
            }
        }

        $template->payment_options = $paymentOptions;
    }

    /**
     * @return list<array{
     *     payment: PaymentInterface,
     *     value: int,
     *     label: string,
     *     note: string|null,
     *     active: bool
     * }>
     */
    private function getPaymentOptions(Order $order, StoreContext $context): array
    {
        $payments = $this->paymentRegistry->get(PaymentModel::findMultipleByIds($context->getPaymentIds()), $context, true);

        if (!$payments) {
            return [];
        }

        $options = [];

        foreach ($payments as $payment) {
            if (!$payment->isAvailable()) {
                continue;
            }

            $options[] = [
                'payment' => $payment,
                'value' => $payment->getId(),
                'label' => $payment->getLabel(),
                'note' => $payment->getNote(),
                'active' => $payment->getId() === $order->getPaymentMethod()?->getId(),
            ];
        }

        return $options;
    }

    private function initPayment(Order $order, PaymentInterface $payment): never
    {
        $result = $this->orderManager->initPayment($order, $payment, $this->getPageModel());
        $redirectUrl = $this->generateContentUrl($this->getPageModel(), ['parameters' => '/'.$order->getIdentifier()]);

        $this->messages->handleActionResult('payment.init', $result, $redirectUrl);
    }
}
