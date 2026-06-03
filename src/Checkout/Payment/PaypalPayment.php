<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Isotope\CoreBundle\Controller\PaypalIpnController;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\Model\OrderTransactionModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

#[AsPayment(factory: CorePaymentFactory::class)]
#[WithMonologChannel('isotope.payment.paypal')]
class PaypalPayment extends AbstractPayment
{
    public function __construct(
        int $id,
        private readonly string $account,
        private readonly string $processingStatus,
        private readonly string $failedStatus,
        StoreContext $context,
    ) {
        parent::__construct($id, $context);
    }

    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            Environment::class,
            UrlGeneratorInterface::class,
        ];
    }

    public function hasError(): bool
    {
        return false;
    }

    public function review(): TemplateMarkup|null
    {
        return null;
    }

    public function process(Order $order, OrderTransactionModel $transactionModel, string $returnUrl, string $errorUrl): Response|null
    {
        $arrData     = array();
        $fltDiscount = 0;
        $i           = 0;

        foreach ($order->getLineItems() as $item) {
            if ($item->getPrice()->getTotalPrice() < 0) {
                $fltDiscount -= abs($item->getPrice()->getTotalPrice());
                continue;
            }

            $attributes = '';
            $config = $item->getConfiguration();

            if (!empty($config)) {
                //dd($config);
                //$config = array_map(
                //    static function(array $option) {
                //        return $option['label'] . ': ' . $option;
                //    },
                //    $config,
                //);

                //$attributes = ' (' . implode(', ', $config) . ')';
            }

            //$strName = HasteStringUtil::convertToText(
            //    $item->getLabel() . $attributes,
            //    HasteStringUtil::NO_TAGS | HasteStringUtil::NO_BREAKS | HasteStringUtil::NO_INSERTTAGS | HasteStringUtil::NO_ENTITIES
            //);
            $strName = $item->getLabel();

            // Make sure name is not empty, otherwise PayPal ignores all subsequent products
            // @see https://github.com/isotope/core/issues/2176
            if (empty($strName)) {
                $strName = 'ID '.$item->getId();
            }

            $arrData['item_number_'.++$i] = $item->getSku();
            $arrData['item_name_'.$i] = $strName;
            $arrData['amount_'.$i] = $item->getPrice()->getTotalPrice();
            $arrData['quantity_'.$i] = $item->getQuantity();
        }

        return new Response($this->get(Environment::class)->render(
            '@Contao/isotope/payment/paypal.html.twig',
            [
                'id' => $this->id,
                'action' => ('https://www.'.($this->isDebug() ? 'sandbox.' : '').'paypal.com/cgi-bin/webscr'),
                'paypal_account' => $this->account,
                'invoice' => $order->getDocumentNumber(),
                'data' => $arrData,
                'discount' => $fltDiscount,
                'address' => $order->getBillingAddress(),
                'currency' => $order->getContext()->getCurrency()->getCode(),
                'custom' => $transactionModel->uniqid,
                'return' => $returnUrl,
                'cancel_return' => $errorUrl,
                'notify_url' => $this->get(UrlGeneratorInterface::class)->generate(PaypalIpnController::class, [], UrlGeneratorInterface::ABSOLUTE_URL),
                'debug' => $this->isDebug(),
            ],
        ));
    }

    public function finalize(Order $order, OrderTransactionModel $transactionModel, Request $request): Response|null
    {
        // TODO: check that IPN has not already updated the order

        $this->updateTransactionStatus($order->getId(), $this->processingStatus);

        return null;
    }

    public function failed(Order $order, OrderTransactionModel $transactionModel, Request $request): Response|null
    {
        $transactionModel->status = 'cancelled';
        $transactionModel->save();

        $this->updateTransactionStatus($order->getId(), $this->failedStatus);

        return null;
    }
}
