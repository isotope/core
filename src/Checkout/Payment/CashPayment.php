<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\Model\OrderTransactionModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsPayment(factory: CorePaymentFactory::class)]
class CashPayment extends AbstractPayment
{
    public function __construct(
        int $id,
        private readonly string $inProgressStatus,
        StoreContext $context,
    ) {
        parent::__construct($id, $context);
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
        $this->updateTransactionStatus($order->getId(), $this->inProgressStatus);

        return new RedirectResponse($returnUrl);
    }

    public function finalize(Order $order, OrderTransactionModel $transactionModel, Request $request): Response|null
    {
        return null;
    }

    public function failed(Order $order, OrderTransactionModel $transactionModel, Request $request): Response|null
    {
        return null;
    }
}
