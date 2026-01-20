<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;

/**
 * @extends AbstractPaymentFactory<CashPayment>
 */
class CashPaymentFactory extends AbstractPaymentFactory
{
    public function __construct(
        private readonly TokenChecker $tokenChecker,
        private readonly RuleMatcher $ruleMatcher,
    ) {
    }

    /**
     * @param Payment $model
     */
    public function doCreate(Model $model): AbstractPayment
    {
        return new CashPayment($model->id, $model->new_order_status, $this->tokenChecker, $this->ruleMatcher);
    }
}
