<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\Checkout\Payment;

use Contao\Model;
use Isotope\CoreBundle\Checkout\AbstractCheckoutMethodFactory;
use Isotope\CoreBundle\Model\PaymentModel;
use Isotope\CoreBundle\ModelType\ModelInstanceTrait;
use Isotope\CoreBundle\Store\Context\StoreContext;
use Isotope\CoreBundle\Util\StringUtil;

/**
 * @template TType of AbstractPayment
 *
 * @extends AbstractCheckoutMethodFactory<TType, PaymentModel>
 */
abstract class AbstractPaymentFactory extends AbstractCheckoutMethodFactory
{
    use ModelInstanceTrait;

    /**
     * @param PaymentModel $model
     */
    public function create(Model $model, StoreContext $context): AbstractPayment
    {
        $this->validateInstanceOf($model, PaymentModel::class);

        /** @var AbstractPayment $payment */
        $payment = parent::create($model, $context);
        $payment
            ->setAllowedShippings(StringUtil::csv($model->shipping_modules, intval(...)))
        ;

        return $payment;
    }
}
