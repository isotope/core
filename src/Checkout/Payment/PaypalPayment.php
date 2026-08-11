<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Codefog\HasteBundle\StringParser;
use Isotope\CoreBundle\Controller\PaypalIpnController;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\HttpFoundation\PaymentResult;
use Isotope\CoreBundle\Model\OrderTransactionModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Isotope\CoreBundle\Twig\TemplateMarkupFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsPayment(factory: CorePaymentFactory::class)]
class PaypalPayment extends AbstractPayment
{
    public function __construct(
        int $id,
        private readonly string $account,
        private readonly string $processingStatus,
        private readonly string $failedStatus,
        private readonly string $paidStatus,
        StoreContext $context,
    ) {
        parent::__construct($id, $context);
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            ...parent::getSubscribedServices(),
            UrlGeneratorInterface::class,
            StringParser::class,
            TemplateMarkupFactory::class,
        ];
    }

    public function getAccount(): string
    {
        return $this->account;
    }

    public function getProcessingStatus(): string
    {
        return $this->processingStatus;
    }

    public function getFailedStatus(): string
    {
        return $this->failedStatus;
    }

    public function getPaidStatus(): string
    {
        return $this->paidStatus;
    }

    public function hasError(): bool
    {
        return false;
    }

    public function review(): TemplateMarkup|null
    {
        return null;
    }

    public function process(Order $order, OrderTransactionModel $transactionModel, string $returnUrl, string $errorUrl): PaymentResult
    {
        $arrData = [];
        $fltDiscount = 0;
        $i = 0;

        foreach ($order->getLineItems() as $item) {
            if ($item->getPrice()?->getTotalPrice() < 0) {
                $fltDiscount -= abs($item->getPrice()->getTotalPrice());
                continue;
            }

            $attributes = '';
            $config = $item->getConfiguration();

            if (!empty($config)) {
                array_walk(
                    $config,
                    static function (&$value, $key): void {
                        $value = $key.': '.$value;
                    },
                );

                $attributes = ' ('.implode(', ', $config).')';
            }

            $strName = $this->get(StringParser::class)->convertToText(
                $item->getLabel().$attributes,
                StringParser::NO_TAGS | StringParser::NO_BREAKS | StringParser::NO_INSERTTAGS | StringParser::NO_ENTITIES,
            );

            // Make sure name is not empty, otherwise PayPal ignores all subsequent products
            // @see https://github.com/isotope/core/issues/2176
            if (empty($strName)) {
                $strName = 'ID '.$item->getId();
            }

            $arrData['item_number_'.++$i] = $item->getSku();
            $arrData['item_name_'.$i] = $strName;
            $arrData['amount_'.$i] = $item->getPrice()?->getTotalPrice() ?? 0.0;
            $arrData['quantity_'.$i] = $item->getQuantity();
        }

        return new PaymentResult($this->get(TemplateMarkupFactory::class)->create(
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

    public function finalize(Order $order, OrderTransactionModel $transactionModel, Request $request): PaymentResult
    {
        // TODO: check that IPN has not already updated the order

        $this->updateTransactionStatus($order->getId(), $this->processingStatus);

        return new PaymentResult();
    }

    public function failed(Order $order, OrderTransactionModel $transactionModel, Request $request): PaymentResult
    {
        $transactionModel->status = 'cancelled';
        $transactionModel->save();

        $this->updateTransactionStatus($order->getId(), $this->failedStatus);

        return new PaymentResult();
    }
}
