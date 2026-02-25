<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model;
use Isotope\CoreBundle\Model\Payment;
use Isotope\CoreBundle\RuleBuilder\RuleMatcher;
use Isotope\CoreBundle\Store\Context\StoreContext;

/**
 * @extends AbstractPaymentFactory<CashPayment>
 */
class CashPaymentFactory extends AbstractPaymentFactory
{
    /**
     * @param Payment $model
     */
    public function doCreate(Model $model, StoreContext $context): AbstractPayment
    {
        return new CashPayment($model->id, $model->new_order_status, $context);
    }
}
