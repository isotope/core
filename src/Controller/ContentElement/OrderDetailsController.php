<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\FrontendUser;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $order = $this->loader->loadFromIdentifier($request->query->getString('uid'));

        if (!$order) {
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

        // TODO: check if payment is not complete

        if ($payment = $order->getPaymentMethod()) {
            if ($request->query->getBoolean('init_payment')) {
                return $this->initPayment($order, $payment);
            }

            switch ($request->query->get('transaction_state')) {
                case 'success':
                    $transactionModel = $this->orderTransactionHandler->switch($order, $request->query->get('transaction'));

                    return $this->handleResponse($payment->finalize($order, $transactionModel, $request), $order);

                case 'failed':
                    $transactionModel = $this->orderTransactionHandler->switch($order, $request->query->get('transaction'));

                    return $this->handleResponse($payment->failed($order, $transactionModel, $request), $order);
            }
        }

        $this->addCollectionToTemplate($template, $order, $context);
        $this->setTransactionState($template, $order);

        if ($request->isMethod(Request::METHOD_POST) && $request->request->getBoolean('change_payment')) {
            $context->setCart($this->orderConverter->convertToCart($order, $context));
            $template->change_payment = true;
            $template->payment_options = $this->getPaymentOptions($order, $context, $request);
        }

        return $template->getResponse();
    }

    private function setTransactionState(FragmentTemplate $template, Order $order): void
    {
        $template->payment_pending = false;
        $template->payment_processing = false;
        $template->payment_complete = false;

        if (null !== $order->getPaymentMethod() && ($state = $order->getTransactionStatus()->getTransactionState())) {
            $template->payment_pending = TransactionState::Pending === $state;
            $template->payment_processing = TransactionState::Processing === $state;
            $template->payment_complete = TransactionState::Complete === $state;
        }
    }

    private function getPaymentOptions(Order $order, StoreContext $context, Request $request): array
    {
        $payments = $this->paymentRegistry->get(PaymentModel::findMultipleByIds($context->getPaymentIds()), $context, true);
        $cart = $context->getCart();

        if (!$payments) {
            return [];
        }

        $options = [];

        foreach ($payments as $payment) {
            if (!$payment->isAvailable()) {
                continue;
            }

            $isActive = $payment->getId() === $cart->getPaymentMethod()?->getId();

            $options[] = [
                'payment' => $payment,
                'value' => $payment->getId(),
                'label' => $payment->getLabel(),
                'note' => $payment->getNote(),
                'active' => $isActive,
            ];

            if ($request->request->getInt('payment_id') === $payment->getId()) {
                if ($isActive) {
                    throw new RedirectResponseException($request->getUri().'&init_payment=1');
                }

                $cart->setPaymentMethod($payment);
                $cart->setModified();

                $this->orderTransactionHandler->cancel($order);

                $newCart = $this->calculator->calculate($cart, $context);

                $data = $this->orderConverter->convertToOrder($newCart, $context);

                $this->orderManager->updatePayment($order->getId(), $data, $context);

                throw new RedirectResponseException($request->getUri().'&init_payment=1');
            }
        }

        return $options;
    }

    private function initPayment(Order $order, PaymentInterface $payment): Response
    {
        $transactionModel = $this->orderTransactionHandler->init($order);

        $returnUrl = $this->generateContentUrl($this->getPageModel(), ['uid' => $order->getIdentifier(), 'transaction' => $transactionModel->uniqid, 'transaction_state' => 'success'], UrlGeneratorInterface::ABSOLUTE_URL);
        $errorUrl = $this->generateContentUrl($this->getPageModel(), ['uid' => $order->getIdentifier(), 'transaction' => $transactionModel->uniqid, 'transaction_state' => 'failed'], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->handleResponse($payment->process($order, $transactionModel, $returnUrl, $errorUrl), $order);
    }

    private function handleResponse(Response|null $response, Order $order): Response
    {
        if ($response instanceof RedirectResponse) {
            throw new ResponseException($response);
        }

        if (null !== $response) {
            return $response;
        }

        throw new RedirectResponseException($this->generateContentUrl($this->getPageModel(), ['uid' => $order->getIdentifier()]));
    }
}
