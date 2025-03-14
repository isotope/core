<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Module;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\Interfaces\IsotopeProductCollection;
use Isotope\Interfaces\IsotopePurchasableCollection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;

#[AsPayment]
class CashPayment extends AbstractPayment
{
    public function __construct(
        int $id,
        private readonly int $newOrderStatus,
        Security $security,
        TokenChecker $tokenChecker,
    ) {
        parent::__construct($id, $security, $tokenChecker);
    }

    public function processPayment(IsotopeProductCollection $order, Module $objModule): bool
    {
        if (!$order instanceof IsotopePurchasableCollection) {
            return false;
        }

        $order->checkout();
        $order->updateOrderStatus($this->newOrderStatus);

        return true;
    }

    public function checkoutForm(IsotopeProductCollection $order, Module $objModule): Response|string|null
    {
        return null;
    }
}
