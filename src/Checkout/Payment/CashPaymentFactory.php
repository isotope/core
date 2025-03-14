<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Isotope\CoreBundle\DependencyInjection\Attribute\AsPayment;
use Isotope\CoreBundle\Model\Payment;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends AbstractPaymentFactory<CashPayment>
 */
class CashPaymentFactory extends AbstractPaymentFactory
{
    public function __construct(
        private readonly Security $security,
        private readonly TokenChecker $tokenChecker,
    ) {
    }

    /**
     * @param Payment $model
     */
    public function doCreate(Model $model): AbstractPayment
    {
        return new CashPayment($model->id, $model->new_order_status, $this->security, $this->tokenChecker);
    }
}
