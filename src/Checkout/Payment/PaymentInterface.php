<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Isotope\CoreBundle\HttpFoundation\PaymentResult;
use Isotope\CoreBundle\Model\OrderTransactionModel;
use Isotope\CoreBundle\ProductCollection\Order\Order;
use Isotope\CoreBundle\Store\Price\PriceDefinition;
use Isotope\CoreBundle\Twig\TemplateMarkup;
use Symfony\Component\HttpFoundation\Request;

/**
 * Interface for an Isotope payment method.
 */
interface PaymentInterface
{
    /**
     * Returns the ID of this payment method.
     */
    public function getId(): int;

    /**
     * Return label for the payment method.
     */
    public function getLabel(): string;

    /**
     * Returns the payment note.
     */
    public function getNote(): string;

    /**
     * Returns whether the payment method is available.
     */
    public function isAvailable(): bool;

    public function hasError(): bool;

    public function getLevel(): int;

    public function getPriceDefinition(): PriceDefinition|null;

    public function review(): TemplateMarkup|null;

    /**
     * Process payment on checkout confirmation page.
     */
    public function process(Order $order, OrderTransactionModel $transactionModel, string $returnUrl, string $errorUrl): PaymentResult;

    public function finalize(Order $order, OrderTransactionModel $transactionModel, Request $request): PaymentResult;

    public function failed(Order $order, OrderTransactionModel $transactionModel, Request $request): PaymentResult;
}
