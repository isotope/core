<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Controller;

use Contao\CoreBundle\Controller\AbstractController;
use Isotope\CoreBundle\Model\OrderTransactionEventModel;
use Isotope\CoreBundle\Model\OrderTransactionModel;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\ProductCollection\OrderTransaction\OrderTransactionHandler;
use Isotope\CoreBundle\Store\OrderManager;
use Isotope\CoreBundle\Util\FloatComparator;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsController]
#[Route(
    path: '/_isotope/paypal-ipn',
    name: 'isotope_paypal_ipn',
    defaults: ['_scope' => 'frontend', '_token_check' => false]
)]
#[WithMonologChannel('isotope.payment.paypal')]
class PaypalIpnController extends AbstractController
{
    public function __construct(
        private readonly OrderTransactionHandler $orderTransactionHandler,
        private readonly OrderManager $orderManager,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->initializeContaoFramework();

        if ('Completed' !== $request->request->get('payment_status')) {
            $this->logger?->info('PayPal IPN: payment status "' . $request->request->get('payment_status') . '" not implemented.');

            return new Response();
        }

        if (null !== OrderTransactionEventModel::findOneBy('paypal_txn_id', $request->request->get('txn_id'))) {
            $this->logger?->info('PayPal IPN: duplicate transaction ID "' . $request->request->get('txn_id') . '".');

            return new Response();
        }

        if (!$this->validateInput($request)) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $handled = $this->orderTransactionHandler->handleEvent(
            OrderTransactionModel::findByUniqid($request->request->getString('custom')),
            $request->request->all(),
            fn (...$args) => $this->handleTransaction($request, ...$args),
        );

        if (!$handled) {
            $this->logger?->error('PayPal IPN: Unable to handle webhook', ['payload' => $request->request->all()]);
        }

        return new Response();
    }

    private function validateInput(Request $request): bool
    {
        try {
            $url = 'https://ipnpb.' . ($request->request->getBoolean('test_ipn') ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr?cmd=_notify-validate';

            $response = $this->httpClient->request('POST', $url, [
                'body' => $request->getContent(),
            ]);

            if ('VERIFIED' !== $response->getContent()) {
                $this->logger?->error('PayPal IPN: data rejected (' . $response->getContent() . ')');

                return false;
            }
        } catch (ExceptionInterface $exception) {
            $this->logger?->error('PayPal IPN: Request Error (' . $exception->getMessage() . ')', ['exception' => $exception]);

            return false;
        }

        return true;
    }

    private function handleTransaction(Request $request, Order $order, PaymentModel $paymentModel, OrderTransactionModel $transactionModel, OrderTransactionEventModel $eventModel): void
    {
        if (!$paymentModel->debug && 0 !== strcasecmp($request->request->getString('receiver_email'), $paymentModel->paypal_account)) {
            $this->logger?->error('PayPal IPN: Account email does not match (got '.$request->request->getString('receiver_email').', expected '.$paymentModel->paypal_account.')');

            return;
        }

        if (
            $order->getContext()->getCurrency()->getCode() !== $request->request->getString('mc_currency')
            || !FloatComparator::equals($order->getTotal()->getTotalPrice(), (float) $request->request->get('mc_gross'))
        ) {
            $this->logger?->error('PayPal IPN: manipulation in payment from '.$request->request->getString('payer_email').'.');

            return;
        }

        if ($transactionModel->status && $paymentModel->processing_status !== $transactionModel->status) {
            $this->logger?->info('PayPal IPN: checkout for Order ID "'.$order->getId().'" already completed.');

            return;
        }

        $transactionModel->status = 'paid';
        $transactionModel->completed = time();
        $transactionModel->save();

        $this->orderManager->updateOrder($order->getId(), [
            'transaction_status' => $paymentModel->paid_status,
            'date_paid' => time(),
        ]);

        $this->logger?->info('PayPal IPN: data accepted');
    }
}
