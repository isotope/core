<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Store\Order\CreateOrderResult;
use Isotope\CoreBundle\Twig\TemplateMarkup;

#[AsPayment]
class CashPayment extends AbstractPayment
{
    public function __construct(
        int $id,
        private readonly int $newOrderStatus,
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






    public function processPayment(/*IsotopeProductCollection */$order, /*Module */$objModule): bool
    {
        //if (!$order instanceof IsotopePurchasableCollection) {
        //    return false;
        //}

        $order->checkout();
        $order->updateOrderStatus($this->newOrderStatus);

        return true;
    }
}
